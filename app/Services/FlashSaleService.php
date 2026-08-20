<?php

namespace App\Services;

use App\Models\DiamondPack;
use App\Models\FlashSaleOffer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\GameProvider;
use App\Services\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FlashSaleService
{
    /**
     * Create a pending flash-sale order (no cart) ready for Baridimob / Crypto.
     *
     * @param  array<string, mixed>  $playerIds
     */
    public function createCheckoutOrder(FlashSaleOffer $offer, User $user, array $playerIds): Order
    {
        return DB::transaction(function () use ($offer, $user, $playerIds) {
            /** @var FlashSaleOffer $locked */
            $locked = FlashSaleOffer::query()
                ->whereKey($offer->id)
                ->lockForUpdate()
                ->with(['items.diamondPack'])
                ->firstOrFail();

            if (! $locked->isLive()) {
                throw ValidationException::withMessages([
                    'offer' => [__('flash_sale.not_available')],
                ]);
            }

            if (! GameProvider::usesDigiflazz($locked->game_type)) {
                throw ValidationException::withMessages([
                    'offer' => [__('flash_sale.not_available')],
                ]);
            }

            if ($locked->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'offer' => [__('flash_sale.not_available')],
                ]);
            }

            $ids = $this->resolvePlayerIds($locked->game_type, $playerIds);
            $primaryPack = $locked->items->first()->diamondPack;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'diamond_pack_id' => $primaryPack?->id,
                'flash_sale_offer_id' => $locked->id,
                'flash_sale_name' => $locked->name,
                'status' => 'pending',
                'user_id_ml' => $ids['user_id_ml'],
                'zone_id_ml' => $ids['zone_id_ml'],
                'player_id_ff' => $ids['player_id_ff'],
                'player_id_pubg' => $ids['player_id_pubg'],
                'player_id_hok' => $ids['player_id_hok'],
                'user_id_bs' => $ids['user_id_bs'],
                'server_bs' => $ids['server_bs'],
                'save_id' => $ids['save_id'],
                'server' => $ids['server'],
                'original_price' => $locked->original_price_dzd,
                'final_price' => $locked->sale_price_dzd,
                'quantity' => $locked->items->sum('quantity'),
            ]);

            foreach ($locked->items as $item) {
                $pack = $item->diamondPack;
                if (! $pack || ! $pack->is_active) {
                    throw ValidationException::withMessages([
                        'offer' => [__('flash_sale.not_available')],
                    ]);
                }

                if ($pack->game_type !== $locked->game_type) {
                    throw ValidationException::withMessages([
                        'offer' => [__('flash_sale.not_available')],
                    ]);
                }

                $quantity = max(1, min(20, (int) $item->quantity));
                $unitPriceDzd = (float) ($pack->price_dzd ?? ($pack->price * 260));
                $unitPriceUsd = (float) ($pack->price_usd ?? $pack->price);
                $discountPercentage = (float) ($pack->discount_percentage ?? 0);
                $subtotalDzd = $unitPriceDzd * $quantity;
                $discountAmount = ($unitPriceDzd * $discountPercentage / 100) * $quantity;
                $totalDzd = $subtotalDzd - $discountAmount;

                OrderItem::create([
                    'order_id' => $order->id,
                    'diamond_pack_id' => $pack->id,
                    'quantity' => $quantity,
                    'unit_price_dzd' => $unitPriceDzd,
                    'unit_price_usd' => $unitPriceUsd,
                    'discount_percentage' => $discountPercentage,
                    'subtotal_dzd' => $subtotalDzd,
                    'discount_amount_dzd' => $discountAmount,
                    'total_dzd' => $totalDzd,
                ]);
            }

            try {
                $order->load(['orderItems.diamondPack', 'user', 'flashSaleOffer']);
                $message = TelegramService::formatOrderMessage($order);
                $messageId = TelegramService::sendMessage($message);
                if ($messageId) {
                    $order->tlg_message_id = $messageId;
                    $order->save();
                }
            } catch (\Throwable $e) {
                // Non-fatal: order remains valid without Telegram.
            }

            return $order->fresh(['orderItems.diamondPack', 'flashSaleOffer']);
        });
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     user_id_ml: ?string,
     *     zone_id_ml: ?string,
     *     player_id_ff: ?string,
     *     player_id_pubg: ?string,
     *     player_id_hok: ?string,
     *     user_id_bs: ?string,
     *     server_bs: ?string,
     *     save_id: ?string,
     *     server: ?string
     * }
     */
    public function resolvePlayerIds(string $gameType, array $input): array
    {
        $ids = [
            'user_id_ml' => null,
            'zone_id_ml' => null,
            'player_id_ff' => null,
            'player_id_pubg' => null,
            'player_id_hok' => null,
            'user_id_bs' => null,
            'server_bs' => null,
            'save_id' => null,
            'server' => null,
        ];

        if ($gameType === 'mobilelegends') {
            $ids['user_id_ml'] = trim((string) ($input['user_id'] ?? ''));
            $ids['zone_id_ml'] = trim((string) ($input['zone_id'] ?? ''));
            if ($ids['user_id_ml'] === '' || $ids['zone_id_ml'] === '') {
                throw ValidationException::withMessages([
                    'user_id' => [__('flash_sale.user_zone_required')],
                ]);
            }

            return $ids;
        }

        if ($gameType === 'freefire') {
            $ids['player_id_ff'] = trim((string) ($input['player_id'] ?? $input['player_id_ff'] ?? ''));
            if ($ids['player_id_ff'] === '') {
                throw ValidationException::withMessages([
                    'player_id' => [__('flash_sale.player_id_required')],
                ]);
            }

            return $ids;
        }

        if (in_array($gameType, ['pubgmobile', 'pubg_mobile'], true)) {
            $ids['save_id'] = trim((string) ($input['player_id'] ?? $input['player_id_pubg'] ?? $input['save_id'] ?? ''));
            if ($ids['save_id'] === '') {
                throw ValidationException::withMessages([
                    'player_id' => [__('flash_sale.player_id_required')],
                ]);
            }

            return $ids;
        }

        if ($gameType === 'honorofkings') {
            $ids['player_id_hok'] = trim((string) ($input['player_id'] ?? $input['player_id_hok'] ?? ''));
            if ($ids['player_id_hok'] === '') {
                throw ValidationException::withMessages([
                    'player_id' => [__('flash_sale.player_id_required')],
                ]);
            }

            return $ids;
        }

        if ($gameType === 'bloodstrike') {
            $ids['user_id_bs'] = trim((string) ($input['user_id_bs'] ?? $input['user_id'] ?? ''));
            $ids['server_bs'] = trim((string) ($input['server_bs'] ?? $input['server'] ?? 'global'));
            if ($ids['user_id_bs'] === '' || $ids['server_bs'] === '') {
                throw ValidationException::withMessages([
                    'user_id_bs' => [__('flash_sale.bloodstrike_required')],
                ]);
            }

            return $ids;
        }

        $ids['save_id'] = trim((string) ($input['save_id'] ?? $input['user_id'] ?? $input['player_id'] ?? ''));
        $ids['server'] = isset($input['server']) ? trim((string) $input['server']) : null;
        if ($ids['save_id'] === '') {
            throw ValidationException::withMessages([
                'save_id' => [__('flash_sale.player_id_required')],
            ]);
        }

        return $ids;
    }
}
