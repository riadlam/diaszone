<?php

namespace App\Console\Commands;

use App\Models\Item4GamerOrder;
use App\Models\Order;
use App\Services\Item4GamerService;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckItem4GamerOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'item4gamer:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Item4Gamer order statuses and update order completion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Item4Gamer order statuses...');

        try {
            $item4gamerService = app(Item4GamerService::class);

            // Get all pending Item4Gamer orders (status is pending or processing)
            $pendingOrders = Item4GamerOrder::whereIn('status', ['pending', 'processing'])
                ->whereNotNull('item4gamer_order_id')
                ->with(['order', 'orderItem', 'orderItem.order', 'orderItem.item4gamerOrders', 'diamondPack'])
                ->get();

            $this->info("Found {$pendingOrders->count()} pending Item4Gamer orders");

            $checked = 0;
            $updated = 0;
            $errors = 0;

            foreach ($pendingOrders as $item4gamerOrder) {
                try {
                    // Call Item4Gamer API to check status
                    $statusResult = $item4gamerService->getOrderStatus($item4gamerOrder->item4gamer_order_id);

                    if (!$statusResult['success']) {
                        $this->warn("Failed to get status for Item4Gamer order {$item4gamerOrder->item4gamer_order_id}: {$statusResult['message']}");
                        $errors++;
                        continue;
                    }

                    $apiStatus = strtolower($statusResult['status'] ?? '');
                    $checked++;

                    // Determine our status based on Item4Gamer status
                    $newStatus = $item4gamerOrder->status;
                    if (in_array($apiStatus, ['completed', 'success'])) {
                        $newStatus = 'completed';
                    } elseif (in_array($apiStatus, ['cancelled', 'failed', 'refunded'])) {
                        $newStatus = 'cancelled';
                    } elseif (in_array($apiStatus, ['pending', 'processing', 'waiting'])) {
                        $newStatus = 'processing';
                    }

                    // Update Item4GamerOrder if status changed
                    $oldStatus = $item4gamerOrder->status;
                    if ($newStatus !== $oldStatus) {
                        $item4gamerOrder->status = $newStatus;
                        $item4gamerOrder->additional_data = $statusResult['order_data'] ?? $statusResult['full_response'] ?? $item4gamerOrder->additional_data;
                        $item4gamerOrder->save();

                        $this->info("Updated Item4Gamer order {$item4gamerOrder->item4gamer_order_id} status: {$oldStatus} -> {$newStatus}");
                        $updated++;

                        Log::info('Item4Gamer status check: Order status updated', [
                            'item4gamer_order_id' => $item4gamerOrder->id,
                            'item4gamer_order_item_id' => $item4gamerOrder->item4gamer_order_id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'order_id' => $item4gamerOrder->order_id,
                        ]);
                    }

                    // Check if parent order should be updated
                    $order = $item4gamerOrder->order;
                    if ($order) {
                        $this->checkAndUpdateOrderStatus($order);
                    }
                } catch (\Exception $e) {
                    $this->error("Error checking Item4Gamer order {$item4gamerOrder->item4gamer_order_id}: {$e->getMessage()}");
                    Log::error('Item4Gamer status check: Exception', [
                        'item4gamer_order_id' => $item4gamerOrder->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $errors++;
                }
            }

            $this->info("Completed: {$checked} checked, {$updated} updated, {$errors} errors");

            return 0;
        } catch (\Exception $e) {
            $this->error("Fatal error: {$e->getMessage()}");
            Log::error('Item4Gamer status check: Fatal exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Check if all order items are completed and update order status accordingly
     */
    private function checkAndUpdateOrderStatus(Order $order): void
    {
        $order->load('orderItems.item4gamerOrders');

        $totalItems = $order->orderItems->count();
        if ($totalItems === 0) {
            return; // No items to check
        }

        $completedItems = 0;
        $failedItems = 0;
        $pendingItems = 0;

        // Count completed/failed/pending items
        foreach ($order->orderItems as $orderItem) {
            $item4gamerOrder = $orderItem->item4gamerOrders->first();
            if (!$item4gamerOrder) {
                $pendingItems++;
                continue;
            }

            $status = strtolower($item4gamerOrder->status ?? '');
            if (in_array($status, ['completed', 'success'])) {
                $completedItems++;
            } elseif (in_array($status, ['cancelled', 'failed', 'refunded'])) {
                $failedItems++;
            } else {
                $pendingItems++;
            }
        }

        // Determine new order status
        $newOrderStatus = $order->status;
        if ($completedItems === $totalItems) {
            // All items completed
            if ($order->status !== 'completed') {
                $newOrderStatus = 'completed';
            }
        } elseif ($completedItems > 0 || $pendingItems > 0) {
            // Partial completion (e.g., 2/4 completed) - keep as "sending" to show progress
            if ($order->status === 'completed') {
                // If it was completed but now has pending items, set back to sending
                $newOrderStatus = 'sending';
            } elseif (!in_array($order->status, ['sending', 'processing'])) {
                $newOrderStatus = 'sending';
            }
        } elseif ($failedItems === $totalItems) {
            // All items failed
            if (!in_array($order->status, ['cancelled', 'failed'])) {
                $newOrderStatus = 'cancelled';
            }
        }

        // Update order status if changed
        if ($newOrderStatus !== $order->status) {
            $oldStatus = $order->status;
            $order->status = $newOrderStatus;
            $order->save();

            $this->info("Updated order {$order->order_number} status: {$oldStatus} -> {$newOrderStatus} (Items: {$completedItems}/{$totalItems} completed)");

            Log::info('Item4Gamer status check: Order status updated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newOrderStatus,
                'completed_items' => $completedItems,
                'total_items' => $totalItems,
                'failed_items' => $failedItems,
                'pending_items' => $pendingItems,
            ]);

            // Update Telegram message if exists
            if ($order->tlg_message_id) {
                try {
                    $order->load('orderItems.diamondPack', 'user', 'seller');
                    $updatedMessage = TelegramService::formatOrderMessage($order);
                    
                    if ($newOrderStatus === 'completed') {
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '✅ <b>Order Completed</b>', $updatedMessage);
                    } elseif ($newOrderStatus === 'cancelled') {
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', '❌ <b>Order Cancelled</b>', $updatedMessage);
                    } else {
                        // Show progress like "2/4 completed"
                        $updatedMessage = str_replace('🆕 <b>New Order Created</b>', "⏳ <b>Order Processing ({$completedItems}/{$totalItems} completed)</b>", $updatedMessage);
                    }
                    
                    TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
                } catch (\Exception $e) {
                    Log::error('Item4Gamer status check: Failed to update Telegram message', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

