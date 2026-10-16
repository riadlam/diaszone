<?php

namespace App\Console\Commands;

use App\Models\DigiflazzStatus;
use App\Models\Order;
use App\Services\VipResellerFulfillmentService;
use App\Services\WheelQualificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RepairVipSendingOrders extends Command
{
    protected $signature = 'vipreseller:repair-sending {--dry-run : Report only, do not update}';

    protected $description = 'Complete paid VIP Reseller orders stuck on sending and credit missing wheel spins';

    public function handle(
        VipResellerFulfillmentService $fulfillment,
        WheelQualificationService $wheel
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $completed = 0;
        $spins = 0;
        $skipped = 0;

        $orders = Order::query()
            ->where('status', 'sending')
            ->with(['orderItems.diamondPack', 'diamondPack', 'vipResellerStatuses'])
            ->orderBy('id')
            ->get()
            ->filter(function (Order $order) {
                if ($order->vipreseller_pack_id) {
                    return true;
                }
                if ($order->orderItems->contains(fn ($item) => $item->vipreseller_pack_id)) {
                    return true;
                }
                if ($order->diamondPack?->usesVipReseller()) {
                    return true;
                }

                return $order->orderItems->contains(fn ($item) => $item->diamondPack?->usesVipReseller());
            });

        $this->info('VIP sending candidates: '.$orders->count().($dryRun ? ' (dry-run)' : ''));

        foreach ($orders as $order) {
            $ready = $fulfillment->allVipItemsDelivered($order)
                || $order->allPackTopupsDelivered();

            if (! $ready) {
                $skipped++;
                $this->line("  skip #{$order->id} — deliveries incomplete");
                continue;
            }

            if (! $dryRun) {
                $order->status = 'completed';
                $order->save();

                try {
                    if ($order->seller_id && ! $order->seller_profit_paid) {
                        $order->creditSellerProfit();
                    }
                } catch (\Throwable $e) {
                    Log::warning('VIP repair: seller profit failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $completed++;
            $this->info("  complete #{$order->id}");

            $statuses = DigiflazzStatus::query()
                ->where('order_id', $order->id)
                ->where(function ($q) {
                    $q->whereRaw("LOWER(status) IN ('success', 'sukses')")
                        ->orWhere('rc', '00');
                })
                ->get();

            foreach ($statuses as $status) {
                if ($dryRun) {
                    continue;
                }
                try {
                    if ($wheel->creditFromVipResellerStatus($status)
                        || $wheel->creditFromDigiflazzStatus($status)) {
                        $spins++;
                    }
                } catch (\Throwable $e) {
                    Log::warning('VIP repair: spin credit failed', [
                        'order_id' => $order->id,
                        'status_id' => $status->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Done. completed={$completed} spins_credited={$spins} skipped={$skipped}");

        return self::SUCCESS;
    }
}
