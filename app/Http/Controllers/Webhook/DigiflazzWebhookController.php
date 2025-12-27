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
                // Try to find by ref_id first (what we sent)
                $statusRecord = DigiflazzStatus::where('ref_id', $refId)->first();
                
                // If not found by ref_id, try to find by trxid (Digiflazz might have changed ref_id)
                if (!$statusRecord && $trxId) {
                    $statusRecord = DigiflazzStatus::where('trxid', $trxId)->first();
                    if ($statusRecord) {
                        Log::info('Digiflazz webhook: found existing status by trxid instead of ref_id', [
                            'trxid' => $trxId,
                            'original_ref_id' => $statusRecord->ref_id,
                            'webhook_ref_id' => $refId,
                            'existing_order_item_id' => $statusRecord->order_item_id,
                        ]);
                    }
                }

                // Preserve existing order_item_id and diamond_pack_id when updating
                $preserveOrderItemId = $statusRecord ? $statusRecord->order_item_id : null;
                $preserveDiamondPackId = $statusRecord ? $statusRecord->diamond_pack_id : null;
                $preserveOrderId = $statusRecord ? $statusRecord->order_id : null;

            $data = [
                    'ref_id' => $refId, // Update ref_id to webhook's version (Digiflazz may change it)
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
                
                // Preserve important fields if they exist
                if ($preserveOrderItemId) {
                    $data['order_item_id'] = $preserveOrderItemId;
                }
                if ($preserveDiamondPackId) {
                    $data['diamond_pack_id'] = $preserveDiamondPackId;
                }
                if ($preserveOrderId) {
                    $data['order_id'] = $preserveOrderId;
                }

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
                
                // If status record doesn't have order_item_id yet, try to find an existing record for the same order
                // that has order_item_id set (created by DigiflazzService when we sent the request)
                if (!$statusRecord->order_item_id && $orderId && ($buyerSku || $customerNo)) {
                    $existingStatusWithItem = DigiflazzStatus::where('order_id', $orderId)
                        ->whereNotNull('order_item_id')
                        ->where(function ($q) use ($buyerSku, $customerNo) {
                            if ($buyerSku) {
                                $q->where('buyer_sku_code', $buyerSku);
                            }
                            if ($customerNo) {
                                $q->orWhere('customer_no', $customerNo);
                            }
                        })
                        ->first();
                    
                    if ($existingStatusWithItem) {
                        // Found existing record with order_item_id - copy it to current record
                        $statusRecord->order_item_id = $existingStatusWithItem->order_item_id;
                        $statusRecord->diamond_pack_id = $existingStatusWithItem->diamond_pack_id;
                        $statusRecord->order_id = $orderId;
                        $statusRecord->save();
                        
                        Log::info('Digiflazz webhook: copied order_item_id from existing status record', [
                            'current_status_id' => $statusRecord->id,
                            'existing_status_id' => $existingStatusWithItem->id,
                            'order_item_id' => $existingStatusWithItem->order_item_id,
                            'order_id' => $orderId,
                        ]);
                    }
            }

                // Link order_id if we have it and status record doesn't have it yet
                if ($orderId && !$statusRecord->order_id) {
                    $order = Order::where('id', $orderId)->lockForUpdate()->first();
                    if ($order) {
                        // Double-check order_id wasn't set by another request while we were processing
                        $statusRecord->refresh();
                        if (!$statusRecord->order_id) {
                            $statusRecord->order_id = $orderId;
                            
                            // Link to order_item: prioritize extracted orderItemId, then existing, then match by criteria
                            if ($orderItemId) {
                                // Use extracted order_item_id from ref_id
                                $statusRecord->order_item_id = $orderItemId;
                                $orderItem = \App\Models\OrderItem::find($orderItemId);
                                if ($orderItem) {
                                    $statusRecord->diamond_pack_id = $orderItem->diamond_pack_id;
                                }
                            } elseif (!$statusRecord->order_item_id) {
                                // Try to match order_item by order_id + buyer_sku_code + customer_no
                                $order->load('orderItems.diamondPack');
                                if ($order->orderItems && $order->orderItems->count() > 0 && $buyerSku && $customerNo) {
                                    foreach ($order->orderItems as $item) {
                                        if ($item->diamondPack && $item->diamondPack->code === $buyerSku) {
                                            // Additional validation: check if customer_no matches order's game IDs
                                            $customerMatches = false;
                                            if ($order->user_id_ml && str_contains($customerNo, $order->user_id_ml)) {
                                                $customerMatches = true;
                                            } elseif ($order->player_id_ff && str_contains($customerNo, $order->player_id_ff)) {
                                                $customerMatches = true;
                                            }
                                            
                                            // If customer matches or we're confident, link it
                                            if ($customerMatches || $order->orderItems->count() === 1) {
                                                $statusRecord->order_item_id = $item->id;
                                                $statusRecord->diamond_pack_id = $item->diamond_pack_id;
                                                Log::info('Digiflazz webhook: matched order_item by buyer_sku_code and customer_no', [
                                                    'order_item_id' => $item->id,
                                                    'buyer_sku_code' => $buyerSku,
                                                    'customer_no' => $customerNo,
                                                ]);
                                                break;
                                            }
                                        }
                }
            }

                                // Fallback: if still no order_item_id but order has only one item, use it
                                if (!$statusRecord->order_item_id && $order->orderItems && $order->orderItems->count() === 1) {
                                    $singleItem = $order->orderItems->first();
                                    $statusRecord->order_item_id = $singleItem->id;
                                    $statusRecord->diamond_pack_id = $singleItem->diamond_pack_id;
                                    Log::info('Digiflazz webhook: linked to single order_item as fallback', [
                                        'order_item_id' => $singleItem->id,
                                    ]);
                                } elseif (!$statusRecord->diamond_pack_id && $order->diamond_pack_id) {
                                    // Last fallback: use order's primary diamond_pack_id (legacy orders)
                                    $statusRecord->diamond_pack_id = $order->diamond_pack_id;
                                }
                        } else {
                                // Status record already has order_item_id - ensure diamond_pack_id is set
                                if (!$statusRecord->diamond_pack_id) {
                                    $existingOrderItem = \App\Models\OrderItem::find($statusRecord->order_item_id);
                                    if ($existingOrderItem) {
                                        $statusRecord->diamond_pack_id = $existingOrderItem->diamond_pack_id;
                                    } elseif ($order->diamond_pack_id) {
                                        $statusRecord->diamond_pack_id = $order->diamond_pack_id;
                                    }
                                }
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

                            // Refresh status record to ensure we have the latest order_item_id before applying
                            $statusRecord->refresh();
                            
                            // Ensure statusRecord is saved before applying status (needed for accurate counting)
                            if (!$statusRecord->exists || $statusRecord->wasRecentlyCreated) {
                                    $statusRecord->save();
                            }
                            
                            // Apply status update to order
                            $this->applyStatusToOrder($order, $statusRecord);

                            Log::info('Digiflazz webhook: linked status to order with order_item', [
                                'digiflazz_status_id' => $statusRecord->id,
                                'order_id' => $orderId,
                                'order_item_id' => $statusRecord->order_item_id,
                                'diamond_pack_id' => $statusRecord->diamond_pack_id,
                                'ref_id' => $refId,
                            ]);

                        } else {
                            // Order already linked - but check if order_item_id needs updating
                            if (!$statusRecord->order_item_id && $orderId) {
                                $order->load('orderItems.diamondPack');
                                if ($order->orderItems && $order->orderItems->count() > 0 && $buyerSku && $customerNo) {
                                    foreach ($order->orderItems as $item) {
                                        if ($item->diamondPack && $item->diamondPack->code === $buyerSku) {
                                            $statusRecord->order_item_id = $item->id;
                                            $statusRecord->diamond_pack_id = $item->diamond_pack_id;
                                            $statusRecord->save();
                                            Log::info('Digiflazz webhook: updated order_item_id for already-linked status', [
                                                'digiflazz_status_id' => $statusRecord->id,
                                                'order_item_id' => $item->id,
                                            ]);
                                            break;
                        }
                                    }
                    }
                }

                            // Apply status update even if already linked (webhook might have new status)
                            $order = Order::where('id', $orderId)->lockForUpdate()->first();
                            if ($order) {
                                $this->applyStatusToOrder($order, $statusRecord);
                            }
                            
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
        // Use database transaction with locking to handle concurrent webhooks
        return DB::transaction(function () use ($order, $statusRecord) {
            // Lock the order to prevent concurrent updates
            $orderLocked = Order::where('id', $order->id)->lockForUpdate()->first();
            if (!$orderLocked) {
                Log::error('Digiflazz webhook: failed to lock order', ['order_id' => $order->id]);
                return;
            }
            
            $oldStatus = $orderLocked->status;
            
            Log::info('Digiflazz webhook: applying status to order', [
                'order_id' => $orderLocked->id,
                'order_item_id' => $statusRecord->order_item_id,
                'old_status' => $oldStatus,
                'digiflazz_status' => $statusRecord->status,
                'status_record_id' => $statusRecord->id
            ]);
            
            // SIMPLE APPROACH: Query digiflazz_statuses table directly to check all records for this order
            // Load order items to get required quantities
            $orderLocked->load('orderItems');
            
            $hasOrderItems = $orderLocked->orderItems->count() > 0;
            
            if ($hasOrderItems) {
                // Multi-item order: Check each order_item
                $allItemsComplete = true;
                $totalRequired = 0;
                $totalCompleted = 0;
                
                foreach ($orderLocked->orderItems as $item) {
                    $required = $item->quantity;
                    
                    // Query ALL successful digiflazz_statuses records for this order_item
                    $completed = DB::table('digiflazz_statuses')
                        ->where('order_item_id', $item->id)
                        ->where(function ($q) {
                            $q->whereRaw("LOWER(status) = 'sukses'")
                              ->orWhere('rc', '00');
                        })
                        ->count();
                    
                    Log::info('Digiflazz webhook: order_item status check', [
                        'order_item_id' => $item->id,
                        'required' => $required,
                        'completed' => $completed
                    ]);
                    
                    $totalRequired += $required;
                    $totalCompleted += $completed;
                    
                    if ($completed < $required) {
                        $allItemsComplete = false;
                    }
                }
                
                Log::info('Digiflazz webhook: order completion check', [
                    'order_id' => $orderLocked->id,
                    'total_completed' => $totalCompleted,
                    'total_required' => $totalRequired,
                    'all_complete' => $allItemsComplete
                ]);
                
                if ($allItemsComplete) {
                    $orderLocked->update(['status' => 'completed']);
                    Log::info('Digiflazz webhook: order marked as completed', [
                        'order_id' => $orderLocked->id,
                        'completed' => $totalCompleted,
                        'required' => $totalRequired
                    ]);
                } else {
                    $orderLocked->update(['status' => 'sending']);
                    $orderLocked->notes = trim(($orderLocked->notes ?? '') . "\nDigiflazz: {$totalCompleted}/{$totalRequired} top-ups completed");
                    $orderLocked->save();
                }
            } else {
                // Legacy single-pack order
                $quantity = $orderLocked->quantity ?? 1;
                
                // Query successful records for this order (legacy: order_id directly, not order_item_id)
                $completed = DB::table('digiflazz_statuses')
                    ->where('order_id', $orderLocked->id)
                    ->where(function ($q) {
                        $q->whereRaw("LOWER(status) = 'sukses'")
                          ->orWhere('rc', '00');
                    })
                    ->count();
                
                if ($completed >= $quantity) {
                    $orderLocked->update(['status' => 'completed']);
                } else {
                    $orderLocked->update(['status' => 'sending']);
                    $orderLocked->notes = trim(($orderLocked->notes ?? '') . "\nDigiflazz: {$completed}/{$quantity} top-ups completed");
                    $orderLocked->save();
                }
            }

            Log::info('Digiflazz webhook: order status updated', [
                'order_id' => $orderLocked->id,
                'old_status' => $oldStatus,
                'new_status' => $orderLocked->status
            ]);

        // Save a note on order (append)
        $note = $statusRecord->message ?? ($statusRecord->additional_data['message'] ?? null);
        if ($note) {
                $orderLocked->notes = trim(($orderLocked->notes ?? '') . "\nDigiflazz: " . $note);
                $orderLocked->save();
            }

            // When order is completed, credit seller profit if applicable
            if ($orderLocked->status === 'completed') {
                try {
                    if ($orderLocked->seller_id && !$orderLocked->seller_profit_paid) {
                        $orderLocked->creditSellerProfit();
                        Log::info('Digiflazz webhook: credited seller profit on order completion', ['order_id' => $orderLocked->id]);
                }
            } catch (\Exception $e) {
                    Log::warning('Digiflazz webhook: failed to credit seller profit', ['order_id' => $orderLocked->id, 'error' => $e->getMessage()]);
            }
        }

        // Update Telegram notification to reflect provider status change
        try {
                $orderLocked->refresh();
                $orderLocked->load('orderItems.diamondPack', 'diamondPack', 'user', 'seller');
                $updatedMessage = \App\Services\TelegramService::formatOrderMessage($orderLocked);

                if ($orderLocked->tlg_message_id) {
                    \App\Services\TelegramService::editMessageText($orderLocked->tlg_message_id, $updatedMessage);
            } else {
                $messageId = \App\Services\TelegramService::sendMessage($updatedMessage);
                if ($messageId) {
                        $orderLocked->tlg_message_id = $messageId;
                        $orderLocked->save();
                }
            }
        } catch (\Exception $e) {
                Log::warning('Failed to update Telegram message for Digiflazz webhook', [
                    'order_id' => $orderLocked->id,
                    'order_status' => $orderLocked->status,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }); // End of DB transaction
    }
}
