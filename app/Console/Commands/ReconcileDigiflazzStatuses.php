<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DigiflazzStatus;
use App\Models\VipResellerStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReconcileDigiflazzStatuses extends Command
{
    protected $signature = 'digiflazz:reconcile-statuses {--limit=100 : max number of records to process}';
    protected $description = 'Re-attach Digiflazz statuses to orders when ref_id encodes an order id that differs from the current attachment.';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("Scanning for digiflazz_statuses with ref_id containing order- and mismatched order_id (limit={$limit})...");

        // Use portable LIKE search and validate with preg_match in PHP
        $candidates = DigiflazzStatus::whereNotNull('ref_id')
            ->where('ref_id', 'like', 'order-%')
            ->take($limit)
            ->get();

        $count = 0;
        foreach ($candidates as $status) {
            if (preg_match('/order-(\d+)/', $status->ref_id, $m)) {
                $parsed = (int) $m[1];
                if ($status->order_id !== $parsed) {
                    $this->line("Reconciling status {$status->id}: ref_id={$status->ref_id} current_order_id={$status->order_id} -> parsed={$parsed}");
                    try {
                        $order = Order::where('id', $parsed)->first();
                        if (!$order) {
                            Log::warning('Reconcile: parsed order not found', ['digiflazz_status_id' => $status->id, 'parsed' => $parsed]);
                            continue;
                        }

                        $status->order_id = $order->id;
                        $status->save();

                        try {
                            VipResellerStatus::where('trxid', $status->trxid)->whereNull('order_id')->update(['order_id' => $order->id]);
                        } catch (\Throwable $_) {
                            // ignore mirror update failures
                        }

                        // Apply provider status to the order (updates status, notes, telegram, seller profit)
                        app(\App\Http\Controllers\Webhook\DigiflazzWebhookController::class)->applyStatusToOrder($order, $status);
                        $count++;
                    } catch (\Throwable $e) {
                        Log::warning('Reconcile: failed to reattach status', ['error' => $e->getMessage(), 'digiflazz_status_id' => $status->id]);
                        $this->error("Failed to reconcile status {$status->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        $this->info("Reconciliation complete. Processed {$count} records.");
        return 0;
    }
}
