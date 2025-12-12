<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\DigiflazzStatus;
use App\Models\Order;

class DigiflazzWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log receipt of webhook for debugging
        $eventHeader = $request->header('X-Digiflazz-Event');
        $userAgent = $request->header('User-Agent');
        Log::info('Digiflazz webhook received', [
            'event' => $eventHeader,
            'user_agent' => $userAgent,
            'ip' => $request->ip(),
            'body_trim' => substr($request->getContent(), 0, 2048)
        ]);

        // Validate User-Agent (per Digiflazz docs)
        $expectedAgents = ['Digiflazz-Hookshot', 'Digiflazz-Pasca-Hookshot'];
        if ($userAgent && !in_array($userAgent, $expectedAgents)) {
            Log::warning('Digiflazz webhook: unexpected User-Agent', [
                'user_agent' => $userAgent,
                'expected' => $expectedAgents
            ]);
        }

        // Extract payload (per Digiflazz docs, always wrapped in 'data' key)
        $payload = $request->json('data');
        if (empty($payload) || !is_array($payload)) {
            Log::warning('Digiflazz webhook: empty or invalid payload', [
                'body' => $request->getContent(),
                'parsed_data' => $request->json('data')
            ]);
            return response()->json(['error' => 'Empty payload'], 400);
        }

        // Signature validation if secret configured
        $secret = env('DIGIFLAZZ_WEBHOOK_SECRET');
        $signatureHeader = $request->header('X-Hub-Signature');
        if ($secret) {
            $raw = $request->getContent();
            $expected = 'sha1=' . hash_hmac('sha1', $raw, $secret);
            if (!$signatureHeader || !hash_equals($expected, $signatureHeader)) {
                Log::warning('Digiflazz webhook signature mismatch', [
                    'header' => $signatureHeader,
                    'expected' => $expected
                ]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        $refId = $payload['ref_id'] ?? null;
        if (empty($refId)) {
            Log::warning('Digiflazz webhook: missing ref_id', ['payload' => $payload]);
            return response()->json(['error' => 'Missing ref_id'], 400);
        }

        // Digiflazz sometimes uses 'trx_id' key instead of 'trxid' - normalize
        $trxId = $payload['trxid'] ?? $payload['trx_id'] ?? null;
        $customerNo = $payload['customer_no'] ?? null;
        $buyerSku = $payload['buyer_sku_code'] ?? null;
        $rc = $payload['rc'] ?? null;
        $status = $payload['status'] ?? null;

        try {
            DB::transaction(function () use ($request, $payload, $refId, $trxId, $customerNo, $buyerSku, $rc, $status) {
                // STEP 1: Find or create DigiflazzStatus record
                $statusRecord = DigiflazzStatus::where('ref_id', $refId)
                    ->orWhere(function ($q) use ($trxId) {
                        if ($trxId) {
                            $q->where('trxid', $trxId);
                        }
                    })
                    ->first();

                $data = [
                    'ref_id' => $refId,
                    'trxid' => $trxId,
                    'buyer_sku_code' => $buyerSku,
                    'customer_no' => $customerNo,
                    'rc' => $rc,
                    'status' => $status,
                    'message' => $payload['message'] ?? null,
                    'price' => $payload['price'] ?? null,
                    'sn' => $payload['sn'] ?? null,
                    'additional_data' => $payload,
                    'event' => $request->header('X-Digiflazz-Event') ?? null,
                ];

                if ($statusRecord) {
                    $statusRecord->update($data);
                } else {
                    $statusRecord = DigiflazzStatus::create($data);
                }

                Log::info('Digiflazz webhook: status record persisted', [
                    'id' => $statusRecord->id,
                    'ref_id' => $refId,
                    'trxid' => $trxId,
                    'status' => $status,
                    'order_id' => $statusRecord->order_id
                ]);

                // STEP 2: Extract order ID from ref_id (GUARANTEED MATCH)
                // Format: "order-{order_id}-item-{order_item_id}-{random}" or "order-{order_id}-{random}"
                $orderId = null;
                $orderItemId = null;
                if (preg_match('/^order-(\d+)-item-(\d+)-/', $refId, $matches)) {
                    // New format: order-item style (multi-item orders)
                    $orderId = (int)$matches[1];
                    $orderItemId = (int)$matches[2];
                } elseif (preg_match('/^order-(\d+)-/', $refId, $matches)) {
                    // Old format: backward compatibility
                    $orderId = (int)$matches[1];
                }

                // STEP 3: If ref_id doesn't contain order ID pattern, try customer_no fallback (legacy)
                if (!$orderId && $customerNo) {
                    $order = $this->findOrderByCustomerNo($customerNo, $refId);
                    if ($order) {
                        $orderId = $order->id;
                        Log::info('Digiflazz webhook: matched by customer_no fallback', [
                            'customer_no' => $customerNo,
                            'ref_id' => $refId,
                            'order_id' => $orderId
                        ]);
                    }
                }

                // STEP 4: Link status to order and order_item if not already linked
                // Refresh status record to get latest order_id (might have been set in parallel request)
                $statusRecord->refresh();
                
                if ($orderId && !$statusRecord->order_id) {
                    $order = Order::where('id', $orderId)->lockForUpdate()->first();
                    if ($order) {
                        // Double-check order_id wasn't set by another request while we were processing
                        $statusRecord->refresh();
                        if (!$statusRecord->order_id) {
                            $statusRecord->order_id = $orderId;
                            // Link to order_item if available
                            if ($orderItemId) {
                                $statusRecord->order_item_id = $orderItemId;
                                // Also set diamond_pack_id from order_item
                                $orderItem = \App\Models\OrderItem::find($orderItemId);
                                if ($orderItem) {
                                    $statusRecord->diamond_pack_id = $orderItem->diamond_pack_id;
                                }
                            } elseif (!$statusRecord->diamond_pack_id && $order->diamond_pack_id) {
                                // Fallback: use order's primary diamond_pack_id
                                $statusRecord->diamond_pack_id = $order->diamond_pack_id;
                            }
                            $statusRecord->save();

                            // Link VipResellerStatus mirrors
                            if ($trxId) {
                                try {
                                    \App\Models\VipResellerStatus::where('trxid', $trxId)
                                        ->whereNull('order_id')
                                        ->update(['order_id' => $orderId]);
                                } catch (\Throwable $e) {
                                    Log::warning('Failed to update VipResellerStatus order_id', [
                                        'error' => $e->getMessage(),
                                        'trxid' => $trxId
                                    ]);
                                }
                            }

                            // Apply status update to order
                            $this->applyStatusToOrder($order, $statusRecord);

                            Log::info('Digiflazz webhook: linked status to order', [
                                'digiflazz_status_id' => $statusRecord->id,
                                'order_id' => $orderId,
                                'ref_id' => $refId
                            ]);
                        } else {
                            Log::info('Digiflazz webhook: status already linked by another request', [
                                'digiflazz_status_id' => $statusRecord->id,
                                'existing_order_id' => $statusRecord->order_id,
                                'attempted_order_id' => $orderId
                            ]);
                            // Still apply status update using the existing order_id
                            $existingOrder = Order::where('id', $statusRecord->order_id)->lockForUpdate()->first();
                            if ($existingOrder) {
                                $this->applyStatusToOrder($existingOrder, $statusRecord);
                            }
                        }
                    } else {
                        Log::warning('Digiflazz webhook: order not found', [
                            'order_id' => $orderId,
                            'ref_id' => $refId
                        ]);
                    }
                } elseif ($statusRecord->order_id) {
                    // Status already linked, just apply status update
                    $order = Order::where('id', $statusRecord->order_id)->lockForUpdate()->first();
                    if ($order) {
                        $this->applyStatusToOrder($order, $statusRecord);
                        Log::info('Digiflazz webhook: applied status update', [
                            'digiflazz_status_id' => $statusRecord->id,
                            'order_id' => $order->id
                        ]);
                    }
                } else {
                    Log::warning('Digiflazz webhook: could not match to order', [
                        'ref_id' => $refId,
                        'customer_no' => $customerNo
                    ]);
                }

                // STEP 5: Create/update VipResellerStatus mirror (for backward compatibility)
                try {
                    $buyerLastSaldo = $payload['buyer_last_saldo'] ?? null;
                    $additional = array_merge($payload, [
                        'buyer_last_saldo' => $buyerLastSaldo,
                        'balance' => $buyerLastSaldo
                    ]);
                    
                    \App\Models\VipResellerStatus::updateOrCreate(
                        ['trxid' => $trxId],
                        [
                            'order_id' => $statusRecord->order_id ?? null,
                            'trxid' => $trxId,
                            'data' => $customerNo ?? null,
                            'zone' => $payload['zone'] ?? null,
                            'status' => strtolower($status ?? '') === 'sukses' || $rc === '00' ? 'success' : (strtolower($status ?? '') === 'pending' ? 'waiting' : 'error'),
                            'note' => $payload['message'] ?? null,
                            'price' => $payload['price'] ?? null,
                            'balance' => $buyerLastSaldo,
                            'additional_data' => $additional,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to create/update VipResellerStatus mirror', [
                        'error' => $e->getMessage()
                    ]);
                }
            });

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::error('Digiflazz webhook handler failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Find order by customer_no - ONLY USED AS LAST RESORT FALLBACK
     * ref_id parsing is the primary and guaranteed method
     * 
     * @param string $customerNo
     * @param string|null $refId Optional ref_id to try parsing order ID from first
     * @return Order|null
     */
    protected function findOrderByCustomerNo($customerNo, $refId = null)
    {
        // First, try to extract order ID from ref_id if available (even in fallback)
        if ($refId && preg_match('/^order-(\d+)-/', $refId, $m)) {
            $orderId = (int)$m[1];
            $order = Order::find($orderId);
            if ($order) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by ref_id pattern', [
                    'customer_no' => $customerNo,
                    'ref_id' => $refId,
                    'order_id' => $order->id
                ]);
                return $order;
            }
        }

        $timeWindow = now()->subHours(24); // Only match recent orders
        $statusPriority = ['sending', 'pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'];

        // Try matching numeric order id directly
        if (preg_match('/^\d+$/', $customerNo)) {
            $order = Order::where('id', (int)$customerNo)
                ->where('created_at', '>=', $timeWindow)
                ->first();
            if ($order) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by numeric order id', [
                    'customer_no' => $customerNo,
                    'order_id' => $order->id
                ]);
                return $order;
            }
        }

        // Try Mobile Legends pattern user.zone
        if (strpos($customerNo, '.') !== false) {
            [$userId, $zone] = explode('.', $customerNo, 2);
            $candidates = Order::where('user_id_ml', $userId)
                ->where('zone_id_ml', $zone)
                ->where('created_at', '>=', $timeWindow)
                ->whereIn('status', $statusPriority)
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($candidates->count() > 1) {
                Log::warning('Digiflazz webhook: multiple orders match customer_no (user.zone)', [
                    'customer_no' => $customerNo,
                    'count' => $candidates->count(),
                    'order_ids' => $candidates->pluck('id')->toArray()
                ]);
            }
            
            if ($candidates->count() > 0) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by user.zone', [
                    'customer_no' => $customerNo,
                    'order_id' => $candidates->first()->id
                ]);
                return $candidates->first();
            }
        }

        // Try Mobile Legends concatenated pattern user+zone (e.g., 2057629734048)
        if (preg_match('/^\d+$/', $customerNo)) {
            try {
                $driver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            } catch (\Throwable $e) {
                $driver = null;
            }

            if ($driver === 'sqlite') {
                $candidates = Order::whereRaw("user_id_ml || zone_id_ml = ?", [$customerNo])
                    ->where('created_at', '>=', $timeWindow)
                    ->whereIn('status', $statusPriority)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $candidates = Order::whereRaw("CONCAT(user_id_ml, zone_id_ml) = ?", [$customerNo])
                    ->where('created_at', '>=', $timeWindow)
                    ->whereIn('status', $statusPriority)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            if ($candidates->count() > 1) {
                Log::warning('Digiflazz webhook: multiple orders match customer_no (concat)', [
                    'customer_no' => $customerNo,
                    'count' => $candidates->count(),
                    'order_ids' => $candidates->pluck('id')->toArray()
                ]);
            }

            if ($candidates->count() > 0) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by concatenated user+zone', [
                    'customer_no' => $customerNo,
                    'order_id' => $candidates->first()->id
                ]);
                return $candidates->first();
            }
        }

        // Try matching common player id fields (Free Fire, PUBG, Honor of Kings, Blood Strike)
        $candidates = Order::where(function ($q) use ($customerNo) {
                $q->where('player_id_ff', $customerNo)
                  ->orWhere('player_id_pubg', $customerNo)
                  ->orWhere('player_id_hok', $customerNo)
                  ->orWhere('user_id_bs', $customerNo);
            })
            ->where('created_at', '>=', $timeWindow)
            ->whereIn('status', $statusPriority)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($candidates->count() > 1) {
            Log::warning('Digiflazz webhook: multiple orders match customer_no (player id)', [
                'customer_no' => $customerNo,
                'count' => $candidates->count(),
                'order_ids' => $candidates->pluck('id')->toArray()
            ]);
        }

        if ($candidates->count() > 0) {
            Log::info('Digiflazz webhook: findOrderByCustomerNo matched by player id', [
                'customer_no' => $customerNo,
                'order_id' => $candidates->first()->id
            ]);
            return $candidates->first();
        }

        Log::info('Digiflazz webhook: findOrderByCustomerNo found no match', ['customer_no' => $customerNo]);
        return null;
    }

    // Made public so it can be reused by reconciliation CLI and other maintenance tasks
    public function applyStatusToOrder(Order $order, DigiflazzStatus $statusRecord)
    {
        $status = strtolower($statusRecord->status ?? '');
        $rc = $statusRecord->rc ?? null;
        $oldStatus = $order->status;
        Log::info('Digiflazz webhook: applying status to order', [
            'order_id' => $order->id,
            'order_item_id' => $statusRecord->order_item_id,
            'old_status' => $oldStatus,
            'digiflazz_status' => $statusRecord->status,
            'rc' => $rc
        ]);
        
        // Load order items for multi-item orders
        $order->load('orderItems');
        
        // Map Digiflazz responses to our order statuses
        if ($status === 'sukses' || $rc === '00') {
            // For multi-item orders, check if all order_items are completed
            $hasOrderItems = $order->orderItems->count() > 0;
            
            if ($hasOrderItems) {
                // Multi-item order: check each order_item
                $allItemsComplete = true;
                $totalRequired = 0;
                $totalCompleted = 0;
                
                foreach ($order->orderItems as $item) {
                    $required = $item->quantity;
                    $completed = $item->successfulTopupsCount();
                    
                    $totalRequired += $required;
                    $totalCompleted += $completed;
                    
                    if ($completed < $required) {
                        $allItemsComplete = false;
                    }
                }
                
                Log::info('Digiflazz webhook: multi-item order progress', [
                    'order_id' => $order->id,
                    'total_completed' => $totalCompleted,
                    'total_required' => $totalRequired,
                    'all_complete' => $allItemsComplete
                ]);
                
                if ($allItemsComplete) {
                    $order->update(['status' => 'completed']);
                } else {
                    $order->update(['status' => 'sending']);
                    $order->notes = trim(($order->notes ?? '') . "\nDigiflazz: {$totalCompleted}/{$totalRequired} top-ups completed");
                    $order->save();
                }
            } else {
                // Legacy single-pack order: use old logic
                $quantity = $order->quantity ?? 1;
                if ($quantity > 1) {
                    $succeeded = $order->successfulDigiflazzTopupsCount();
                    if ($succeeded >= $quantity) {
                        $order->update(['status' => 'completed']);
                    } else {
                        $order->update(['status' => 'sending']);
                        $order->notes = trim(($order->notes ?? '') . "\nDigiflazz: {$succeeded}/{$quantity} top-ups completed");
                        $order->save();
                    }
                } else {
                    $order->update(['status' => 'completed']);
                }
            }
        } elseif ($status === 'pending' || in_array($rc, ['03', '99'])) {
            $order->update(['status' => 'sending']);
        } else {
            // All other cases considered failed
            $order->update(['status' => 'failed']);
        }

        Log::info('Digiflazz webhook: order status updated', ['order_id' => $order->id, 'old_status' => $oldStatus, 'new_status' => $order->status]);

        // Save a note on order (append)
        $note = $statusRecord->message ?? ($statusRecord->additional_data['message'] ?? null);
        if ($note) {
            $order->notes = trim(($order->notes ?? '') . "\nDigiflazz: " . $note);
            $order->save();
        }

        // When provider confirms completion, credit seller profit if applicable
        if ($status === 'completed') {
            try {
                if ($order->seller_id && !$order->seller_profit_paid) {
                    $order->creditSellerProfit();
                    Log::info('Digiflazz webhook: credited seller profit on order completion', ['order_id' => $order->id]);
                }
            } catch (\Exception $e) {
                Log::warning('Digiflazz webhook: failed to credit seller profit', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        // Update Telegram notification to reflect provider status change
        try {
            $order->load('orderItems.diamondPack', 'diamondPack', 'user', 'seller');
            $updatedMessage = \App\Services\TelegramService::formatOrderMessage($order);

            if ($order->tlg_message_id) {
                \App\Services\TelegramService::editMessageText($order->tlg_message_id, $updatedMessage);
            } else {
                $messageId = \App\Services\TelegramService::sendMessage($updatedMessage);
                if ($messageId) {
                    $order->tlg_message_id = $messageId;
                    $order->save();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update Telegram message for Digiflazz webhook', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
