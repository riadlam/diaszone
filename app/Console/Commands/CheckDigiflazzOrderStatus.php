<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckDigiflazzOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'digiflazz:check-order-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Digiflazz order statuses and update orders to completed if all top-ups are successful';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Digiflazz order statuses...');
        Log::info('Digiflazz cron: order status check started');

        try {
            // Get all orders with status 'sending'
            $sendingOrders = Order::where('status', 'sending')
                ->with('orderItems')
                ->get();

            $this->info("Found {$sendingOrders->count()} orders with status 'sending'");
            Log::info('Digiflazz cron: found sending orders', [
                'count' => $sendingOrders->count(),
            ]);

            $checked = 0;
            $completed = 0;
            $errors = 0;

            foreach ($sendingOrders as $order) {
                try {
                    $checked++;
                    
                    // Load order items to check if it's a multi-item order
                    $hasOrderItems = $order->orderItems->count() > 0;

                    if ($hasOrderItems) {
                        // Multi-item order: Check each order_item
                        $allItemsComplete = true;
                        $totalRequired = 0;
                        $totalCompleted = 0;

                        foreach ($order->orderItems as $item) {
                            $required = $item->quantity;

                            // Query ALL successful digiflazz_statuses records for this order_item
                            $completedCount = DB::table('digiflazz_statuses')
                                ->where('order_item_id', $item->id)
                                ->where(function ($q) {
                                    $q->whereRaw("LOWER(status) = 'sukses'")
                                      ->orWhere('rc', '00');
                                })
                                ->count();

                            $totalRequired += $required;
                            $totalCompleted += $completedCount;

                            if ($completedCount < $required) {
                                $allItemsComplete = false;
                            }
                        }

                        if ($allItemsComplete && $totalRequired > 0) {
                            $order->update(['status' => 'completed']);
                            $this->info("Order {$order->id} marked as completed ({$totalCompleted}/{$totalRequired} top-ups)");
                            $completed++;
                            
                            Log::info('Digiflazz cron: order marked as completed', [
                                'order_id' => $order->id,
                                'completed' => $totalCompleted,
                                'required' => $totalRequired
                            ]);
                            
                            // Credit seller profit if applicable
                            try {
                                if ($order->seller_id && !$order->seller_profit_paid) {
                                    $order->creditSellerProfit();
                                }
                            } catch (\Exception $e) {
                                Log::warning('Digiflazz cron: failed to credit seller profit', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    } else {
                        // Legacy single-pack order
                        $quantity = $order->quantity ?? 1;

                        // Query successful records for this order (legacy: order_id directly, not order_item_id)
                        $completedCount = DB::table('digiflazz_statuses')
                            ->where('order_id', $order->id)
                            ->where(function ($q) {
                                $q->whereRaw("LOWER(status) = 'sukses'")
                                  ->orWhere('rc', '00');
                            })
                            ->count();

                        if ($completedCount >= $quantity && $quantity > 0) {
                            $order->update(['status' => 'completed']);
                            $this->info("Order {$order->id} marked as completed ({$completedCount}/{$quantity} top-ups)");
                            $completed++;
                            
                            Log::info('Digiflazz cron: order marked as completed', [
                                'order_id' => $order->id,
                                'completed' => $completedCount,
                                'required' => $quantity
                            ]);
                            
                            // Credit seller profit if applicable
                            try {
                                if ($order->seller_id && !$order->seller_profit_paid) {
                                    $order->creditSellerProfit();
                                }
                            } catch (\Exception $e) {
                                Log::warning('Digiflazz cron: failed to credit seller profit', [
                                    'order_id' => $order->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Error processing order {$order->id}: " . $e->getMessage());
                    Log::error('Digiflazz cron: error processing order', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("\nCheck completed!");
            $this->info("Checked: {$checked} orders");
            $this->info("Completed: {$completed} orders");
            if ($errors > 0) {
                $this->warn("Errors: {$errors} orders");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Digiflazz cron: fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
