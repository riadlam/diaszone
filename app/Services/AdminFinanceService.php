<?php

namespace App\Services;

use App\Models\DiamondPack;
use App\Models\DigiflazzStatus;
use App\Models\Item4GamerOrder;
use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminFinanceService
{
    public const IDR_TO_DZD = 64.67;

    /**
     * @return list<string>
     */
    public function paidStatuses(): array
    {
        return ['completed', 'sending', 'processing'];
    }

    public function unitCost(?DiamondPack $pack): float
    {
        if (! $pack) {
            return 0.0;
        }

        if ($pack->base_price_dzd !== null && ! $pack->hasUnconvertedCost()) {
            return (float) $pack->base_price_dzd;
        }

        return ((float) ($pack->price ?? 0)) / self::IDR_TO_DZD;
    }

    public function orderRevenue(Order $order): float
    {
        if ($order->final_price !== null) {
            return (float) $order->final_price;
        }

        if ($order->relationLoaded('orderItems') && $order->orderItems->isNotEmpty()) {
            return (float) $order->orderItems->sum('total_dzd');
        }

        if ($order->orderItems()->exists()) {
            return (float) $order->orderItems()->sum('total_dzd');
        }

        return TelegramService::calculateOrderRevenue($order->loadMissing(['orderItems.diamondPack', 'diamondPack']));
    }

    public function orderDeliveriesCount(Order $order): int
    {
        $order->loadMissing(['digiflazzStatuses', 'item4gamerOrders', 'orderItems']);

        $digiflazz = $order->digiflazzStatuses
            ->filter(fn (DigiflazzStatus $status): bool => $this->isSuccessfulDigiflazz($status))
            ->count();

        $item4gamer = (int) $order->item4gamerOrders
            ->filter(fn (Item4GamerOrder $provider): bool => $this->isSuccessfulItem4Gamer($provider))
            ->sum('quantity');

        if ($digiflazz + $item4gamer === 0 && $order->status === 'completed') {
            if ($order->orderItems->isNotEmpty()) {
                return (int) $order->orderItems->sum('quantity');
            }

            return max(1, (int) ($order->quantity ?: 1));
        }

        return $digiflazz + $item4gamer;
    }

    public function orderCost(Order $order): float
    {
        $order->loadMissing([
            'digiflazzStatuses.diamondPack',
            'item4gamerOrders.diamondPack',
            'orderItems.diamondPack',
            'diamondPack',
        ]);

        $cost = 0.0;

        foreach ($order->digiflazzStatuses as $status) {
            if (! $this->isSuccessfulDigiflazz($status)) {
                continue;
            }

            $pack = $status->diamondPack
                ?? $order->orderItems->firstWhere('id', $status->order_item_id)?->diamondPack
                ?? $order->diamondPack;

            $cost += $this->unitCost($pack);
        }

        foreach ($order->item4gamerOrders as $provider) {
            if (! $this->isSuccessfulItem4Gamer($provider)) {
                continue;
            }

            $pack = $provider->diamondPack
                ?? $order->orderItems->firstWhere('id', $provider->order_item_id)?->diamondPack
                ?? $order->diamondPack;

            $cost += $this->unitCost($pack) * max(1, (int) $provider->quantity);
        }

        if ($cost > 0) {
            return $cost;
        }

        if ($order->status !== 'completed') {
            return 0.0;
        }

        if ($order->orderItems->isNotEmpty()) {
            foreach ($order->orderItems as $item) {
                $cost += $this->unitCost($item->diamondPack) * max(1, (int) $item->quantity);
            }

            return $cost;
        }

        $quantity = max(1, (int) ($order->quantity ?: 1));

        return $this->unitCost($order->diamondPack) * $quantity;
    }

    public function orderProfit(Order $order): float
    {
        return $this->orderRevenue($order) - $this->orderCost($order);
    }

    public function grossMarginPercentage(float $revenue, float $profit): ?float
    {
        if ($revenue <= 0) {
            return null;
        }

        return ($profit / $revenue) * 100;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function ordersQuery(?CarbonInterface $from = null, ?CarbonInterface $to = null, array $filters = []): Builder
    {
        $query = Order::query()
            ->whereIn('status', $this->paidStatuses())
            ->when($from, fn (Builder $builder) => $builder->where('created_at', '>=', $from->copy()->startOfDay()))
            ->when($to, fn (Builder $builder) => $builder->where('created_at', '<=', $to->copy()->endOfDay()));

        if ($paymentMethod = $filters['payment_method'] ?? null) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($gameType = $filters['game_type'] ?? null) {
            $query->where(function (Builder $inner) use ($gameType): void {
                $inner->whereHas('diamondPack', fn (Builder $pack) => $pack->where('game_type', $gameType))
                    ->orWhereHas('orderItems.diamondPack', fn (Builder $pack) => $pack->where('game_type', $gameType));
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     revenue: float,
     *     cost: float,
     *     profit: float,
     *     margin: ?float,
     *     orders_count: int,
     *     deliveries_count: int
     * }
     */
    public function summarizePeriod(?CarbonInterface $from = null, ?CarbonInterface $to = null, array $filters = []): array
    {
        $orders = $this->ordersQuery($from, $to, $filters)
            ->with([
                'user',
                'diamondPack',
                'orderItems.diamondPack',
                'digiflazzStatuses.diamondPack',
                'item4gamerOrders.diamondPack',
            ])
            ->orderByDesc('created_at')
            ->get();

        return $this->summarizeOrders($orders);
    }

    /**
     * @return array{
     *     revenue: float,
     *     cost: float,
     *     profit: float,
     *     margin: ?float,
     *     orders_count: int,
     *     deliveries_count: int
     * }
     */
    public function summarizeOrders(Collection $orders): array
    {
        $revenue = 0.0;
        $cost = 0.0;
        $deliveries = 0;

        foreach ($orders as $order) {
            $revenue += $this->orderRevenue($order);
            $cost += $this->orderCost($order);
            $deliveries += $this->orderDeliveriesCount($order);
        }

        $profit = $revenue - $cost;

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'margin' => $this->grossMarginPercentage($revenue, $profit),
            'orders_count' => $orders->count(),
            'deliveries_count' => $deliveries,
        ];
    }

    /**
     * @return Collection<int, array{
     *     date: string,
     *     revenue: float,
     *     cost: float,
     *     profit: float,
     *     margin: ?float,
     *     orders_count: int,
     *     deliveries_count: int
     * }>
     */
    public function dailyBreakdown(Collection $orders): Collection
    {
        return $orders
            ->groupBy(fn (Order $order): string => $order->created_at?->timezone('Africa/Algiers')->toDateString() ?? 'unknown')
            ->map(function (Collection $dayOrders, string $date): array {
                $summary = $this->summarizeOrders($dayOrders);

                return [
                    'date' => $date,
                    ...$summary,
                ];
            })
            ->sortKeysDesc()
            ->values();
    }

    public function deliveredTopupsCount(?CarbonInterface $from = null, ?CarbonInterface $to = null): int
    {
        $query = DigiflazzStatus::query()->successful();

        if ($from) {
            $query->where('created_at', '>=', $from->copy()->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', $to->copy()->endOfDay());
        }

        $digiflazz = (int) $query->count();

        $item4gamerQuery = Item4GamerOrder::query()
            ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['completed', 'success']);

        if ($from) {
            $item4gamerQuery->where('created_at', '>=', $from->copy()->startOfDay());
        }

        if ($to) {
            $item4gamerQuery->where('created_at', '<=', $to->copy()->endOfDay());
        }

        return $digiflazz + (int) $item4gamerQuery->sum('quantity');
    }

    public function formatMoney(float $amount): string
    {
        return number_format($amount, 0, '.', ' ').' DZD';
    }

    public function formatPercentage(?float $percentage): string
    {
        if ($percentage === null) {
            return '—';
        }

        return number_format($percentage, 1).' %';
    }

    /**
     * @return array<string, string>
     */
    public function paymentMethodOptions(): array
    {
        return Order::query()
            ->whereNotNull('payment_method')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method', 'payment_method')
            ->map(fn (string $method): string => ucwords(str_replace('_', ' ', $method)))
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function gameTypeOptions(): array
    {
        return Order::query()
            ->whereHas('diamondPack')
            ->with('diamondPack:id,game_type')
            ->get()
            ->pluck('diamondPack.game_type')
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn (string $gameType): array => [$gameType => \App\Support\GameProvider::label($gameType)])
            ->all();
    }

    protected function isSuccessfulDigiflazz(DigiflazzStatus $status): bool
    {
        return strtolower((string) $status->status) === 'sukses'
            || (string) $status->rc === '00';
    }

    protected function isSuccessfulItem4Gamer(Item4GamerOrder $provider): bool
    {
        return in_array(strtolower((string) $provider->status), ['completed', 'success'], true);
    }
}
