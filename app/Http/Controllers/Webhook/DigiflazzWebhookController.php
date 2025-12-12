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
        // Log receipt of webhook for debugging (ping and raw body)
        $eventHeader = $request->header('X-Digiflazz-Event');
        Log::info('Digiflazz webhook received', ['event' => $eventHeader, 'ip' => $request->ip(), 'body_trim' => substr($request->getContent(), 0, 2048)]);

        $payload = $request->json('data') ?? $request->json()->all();

        // Signature validation if secret configured
        $secret = env('DIGIFLAZZ_WEBHOOK_SECRET');
        $signatureHeader = $request->header('X-Hub-Signature');
        if ($secret) {
            $raw = $request->getContent();
            $expected = 'sha1=' . hash_hmac('sha1', $raw, $secret);
            if (!$signatureHeader || !hash_equals($expected, $signatureHeader)) {
                Log::warning('Digiflazz webhook signature mismatch', ['header' => $signatureHeader, 'expected' => $expected]);
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        if (empty($payload) || !is_array($payload)) {
            Log::warning('Digiflazz webhook: empty payload', ['body' => $request->getContent()]);
            return response()->json(['error' => 'Empty payload'], 400);
        }

        $refId = $payload['ref_id'] ?? null;
        // Digiflazz sometimes uses 'trx_id' key instead of 'trxid' - normalize
        $trxId = $payload['trxid'] ?? $payload['trx_id'] ?? null;
        $customerNo = $payload['customer_no'] ?? null;
        $buyerSku = $payload['buyer_sku_code'] ?? null;
        $rc = $payload['rc'] ?? null;
        $status = $payload['status'] ?? null;

        try {
            // Use normalized trxId when looking up existing records
            $statusRecord = DigiflazzStatus::where('ref_id', $refId)->orWhere('trxid', $trxId)->first();

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

            Log::info('Digiflazz webhook: status record persisted', ['id' => $statusRecord->id ?? null, 'ref_id' => $statusRecord->ref_id ?? $refId, 'trxid' => $statusRecord->trxid ?? null, 'status' => $statusRecord->status ?? null, 'order_id' => $statusRecord->order_id ?? null]);

            // Diagnostic: if both ref_id encodes an order id and customer_no resolves
            // to a different order, warn so we can investigate collisions.
            if (!empty($refId) && $customerNo && preg_match('/order-(\d+)/', $refId, $m)) {
                $parsed = (int) $m[1];
                try {
                    $customerMatch = $this->findOrderByCustomerNo($customerNo);
                } catch (\Throwable $_) {
                    $customerMatch = null;
                }
                if ($customerMatch && $customerMatch->id !== $parsed) {
                    Log::warning('Digiflazz webhook: identifier conflict - ref_id parsed order differs from customer_no match', ['ref_id' => $refId, 'parsed_order_id' => $parsed, 'customer_no' => $customerNo, 'customer_matched_order_id' => $customerMatch->id, 'digiflazz_status_id' => $statusRecord->id]);
                }
            }

            // Mirror important fields into VipResellerStatus (admin view) so Telegram and admin see provider balance & status
            try {
                $buyerLastSaldo = $payload['buyer_last_saldo'] ?? ($payload['data']['buyer_last_saldo'] ?? null);
                $additional = array_merge($payload, ['buyer_last_saldo' => $buyerLastSaldo ?? null, 'balance' => $buyerLastSaldo ?? null]);
                    $vip = \App\Models\VipResellerStatus::updateOrCreate([
                    'trxid' => $trxId,
                ], [
                    'order_id' => $statusRecord->order_id ?? null,
                    'trxid' => $payload['trxid'] ?? null,
                    'data' => $payload['customer_no'] ?? null,
                    'zone' => $payload['zone'] ?? null,
                    // 'service' is legacy-only and does not exist on `digiflazz_statuses`.
                    // Store it inside additional_data instead if needed.
                    'status' => strtolower($status) === 'sukses' || $rc === '00' ? 'success' : (strtolower($status) === 'pending' ? 'waiting' : 'error'),
                    'note' => $payload['message'] ?? null,
                    'price' => $payload['price'] ?? null,
                    'balance' => $buyerLastSaldo ?? null,
                    'additional_data' => $additional,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to create/update VipResellerStatus mirror for Digiflazz webhook', ['error' => $e->getMessage()]);
            }

            // Try to attach to an order if not attached already
            // Prefer an explicit order id embedded in ref_id (e.g., order-123-...) before
            // attempting customer_no-based matching, to avoid attaching to the wrong
            // order when customer_no can match multiple in-flight orders.
            if (!$statusRecord->order_id && !empty($refId) && preg_match('/order-(\d+)/', $refId, $m)) {
                $parsedOrderId = (int) $m[1];
                try {
                    DB::transaction(function () use ($statusRecord, $parsedOrderId, $refId) {
                        $orderByRef = Order::where('id', $parsedOrderId)->lockForUpdate()->first();
                        if ($orderByRef) {
                            $statusRecord->order_id = $orderByRef->id;
                            $statusRecord->save();
                            try {
                                \App\Models\VipResellerStatus::where('trxid', $statusRecord->trxid)->whereNull('order_id')->update(['order_id' => $orderByRef->id]);
                            } catch (\Throwable $_) {
                                // ignore
                            }
                            $this->applyStatusToOrder($orderByRef, $statusRecord);
                            Log::info('Digiflazz webhook: attached by ref_id parsed order id (early)', ['ref_id' => $refId, 'order_id' => $parsedOrderId, 'digiflazz_status_id' => $statusRecord->id]);
                        }
                    });
                } catch (\Throwable $e) {
                    Log::warning('Digiflazz webhook: early fallback attach by ref_id failed', ['error' => $e->getMessage(), 'ref_id' => $refId]);
                }
            }

            // If still not attached, try matching by customer_no (legacy behavior)
            if (!$statusRecord->order_id && $customerNo) {
                $order = $this->findOrderByCustomerNo($customerNo);
                    Log::info('Digiflazz webhook: findOrderByCustomerNo result', ['customer_no' => $customerNo, 'found_order_id' => $order->id ?? null]);
                if ($order) {
                    $statusRecord->order_id = $order->id;
                    // Persist statusRecord and attach/link to an order atomically to prevent races
                    DB::transaction(function () use ($statusRecord, $data, $customerNo, $status, $rc) {
                        if ($statusRecord) {
                            $statusRecord->update($data);
                        } else {
                            $statusRecord = DigiflazzStatus::create($data);
                        }

                        // Helper to link any existing VipResellerStatus mirrors by trxid
                        $linkMirrors = function ($orderId) use ($statusRecord) {
                            try {
                                if (!empty($statusRecord->trxid)) {
                                    \App\Models\VipResellerStatus::where('trxid', $statusRecord->trxid)
                                        ->whereNull('order_id')
                                        ->update(['order_id' => $orderId]);
                                }
                            } catch (\Throwable $e) {
                                Log::warning('Failed to update VipResellerStatus order_id after attaching DigiflazzStatus', ['error' => $e->getMessage(), 'trxid' => $statusRecord->trxid]);
                            }
                        };

                        if (!$statusRecord->order_id && $customerNo) {
                            $order = $this->findOrderByCustomerNo($customerNo);
                            if ($order) {
                                // Lock the order row to prevent concurrent attachment
                                $orderLocked = Order::where('id', $order->id)->lockForUpdate()->first();
                                if ($orderLocked) {
                                    $statusRecord->order_id = $orderLocked->id;
                                    $statusRecord->save();

                                        Log::info('Digiflazz webhook: attached DigiflazzStatus to order', ['digiflazz_status_id' => $statusRecord->id, 'order_id' => $orderLocked->id]);

                                    // Link mirrors and save
                                    $linkMirrors($orderLocked->id);

                                    // Update order status based on Digiflazz status/rc
                                    $this->applyStatusToOrder($orderLocked, $statusRecord);
                                }
                            }
                        } elseif ($statusRecord->order_id) {
                            $order = Order::where('id', $statusRecord->order_id)->lockForUpdate()->first();
                            if ($order) {
                                $linkMirrors($order->id);
                                $this->applyStatusToOrder($order, $statusRecord);
                                    Log::info('Digiflazz webhook: applied status to already-linked order', ['digiflazz_status_id' => $statusRecord->id, 'order_id' => $order->id]);
                            }
                        }
                    });

                    }
                }

                // After primary attachment logic
                Log::info('Digiflazz webhook: after primary attachment logic', ['digiflazz_status_id' => $statusRecord->id, 'order_id' => $statusRecord->order_id, 'ref_id' => $refId, 'customer_no' => $customerNo ?? null]);

                // Fallback: if still not attached and ref_id contains an order id like 'order-123-...'
                if (!$statusRecord->order_id && !empty($refId)) {
                    Log::info('Digiflazz webhook: attempting fallback attach by ref_id', ['ref_id' => $refId]);
                    if (preg_match('/order-(\d+)/', $refId, $m)) {
                        $parsedOrderId = (int) $m[1];
                        Log::info('Digiflazz webhook: parsed order id from ref_id', ['ref_id' => $refId, 'parsed' => $parsedOrderId]);
                        try {
                            $orderByRef = Order::where('id', $parsedOrderId)->lockForUpdate()->first();
                            if ($orderByRef) {
                                Log::info('Digiflazz webhook: fallback found order', ['order_id' => $orderByRef->id, 'order_status' => $orderByRef->status]);
                                $statusRecord->order_id = $orderByRef->id;
                                $statusRecord->save();
                                // link mirrors and apply status
                                try {
                                    \App\Models\VipResellerStatus::where('trxid', $statusRecord->trxid)->whereNull('order_id')->update(['order_id' => $orderByRef->id]);
                                } catch (\Throwable $_) {
                                    // ignore
                                }
                                $this->applyStatusToOrder($orderByRef, $statusRecord);
                                Log::info('Digiflazz webhook: attached by ref_id parsed order id', ['ref_id' => $refId, 'order_id' => $parsedOrderId, 'digiflazz_status_id' => $statusRecord->id]);
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Digiflazz webhook: fallback attach by ref_id failed', ['error' => $e->getMessage(), 'ref_id' => $refId]);
                        }
                    }
                }

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::error('Digiflazz webhook handler failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    protected function findOrderByCustomerNo($customerNo)
    {
        // Try matching numeric order id directly
        if (preg_match('/^\d+$/', $customerNo)) {
            $order = Order::find((int)$customerNo);
            if ($order) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by numeric id', ['customer_no' => $customerNo, 'matched_by' => 'numeric_id', 'order_id' => $order->id]);
                return $order;
            }
        }

        // Try Mobile Legends pattern user.zone
        if (strpos($customerNo, '.') !== false) {
            [$userId, $zone] = explode('.', $customerNo, 2);
            $order = Order::where('user_id_ml', $userId)->where('zone_id_ml', $zone)->latest()->first();
            if ($order) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by dotted user.zone', ['customer_no' => $customerNo, 'matched_by' => 'user.zone', 'order_id' => $order->id]);
                return $order;
            }
        }

        // Try Mobile Legends concatenated pattern user+zone (e.g., 2057629734048)
        if (preg_match('/^\d+$/', $customerNo)) {
            try {
                $driver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            } catch (\Throwable $e) {
                $driver = null;
            }
            // Prefer orders that are actively being processed (sending/waiting) to avoid linking to older completed orders
            $statusPriority = ['sending', 'pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay'];

            if ($driver === 'sqlite') {
                $candidates = Order::whereRaw("user_id_ml || zone_id_ml = ?", [$customerNo])
                    ->whereIn('status', $statusPriority)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($candidates->count()) {
                    $found = $candidates->first();
                    Log::info('Digiflazz webhook: findOrderByCustomerNo matched by concatenated user+zone (sqlite)', ['customer_no' => $customerNo, 'matched_by' => 'concat_user_zone', 'order_id' => $found->id]);
                    return $found;
                }

                $order = Order::whereRaw("user_id_ml || zone_id_ml = ?", [$customerNo])->latest()->first();
            } else {
                $candidates = Order::whereRaw("CONCAT(user_id_ml, zone_id_ml) = ?", [$customerNo])
                    ->whereIn('status', $statusPriority)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($candidates->count()) {
                    $found = $candidates->first();
                    Log::info('Digiflazz webhook: findOrderByCustomerNo matched by concatenated user+zone', ['customer_no' => $customerNo, 'matched_by' => 'concat_user_zone', 'order_id' => $found->id]);
                    return $found;
                }

                $order = Order::whereRaw("CONCAT(user_id_ml, zone_id_ml) = ?", [$customerNo])->latest()->first();
            }

            if ($order) {
                Log::info('Digiflazz webhook: findOrderByCustomerNo matched by concatenated user+zone (fallback)', ['customer_no' => $customerNo, 'matched_by' => 'concat_user_zone', 'order_id' => $order->id]);
                return $order;
            }
        }

        // Try matching common player id fields
        $order = Order::where('player_id_ff', $customerNo)
            ->orWhere('player_id_pubg', $customerNo)
            ->orWhere('player_id_hok', $customerNo)
            ->orWhere('user_id_bs', $customerNo)
            ->latest()
            ->first();

        if ($order) {
            Log::info('Digiflazz webhook: findOrderByCustomerNo matched by player id field', ['customer_no' => $customerNo, 'matched_by' => 'player_id', 'order_id' => $order->id]);
            return $order;
        }

        Log::info('Digiflazz webhook: findOrderByCustomerNo found no match', ['customer_no' => $customerNo]);

        return $order;
    }

    // Made public so it can be reused by reconciliation CLI and other maintenance tasks
    public function applyStatusToOrder(Order $order, DigiflazzStatus $statusRecord)
    {
        $status = strtolower($statusRecord->status ?? '');
        $rc = $statusRecord->rc ?? null;
        $oldStatus = $order->status;
        Log::info('Digiflazz webhook: applying status to order', ['order_id' => $order->id, 'old_status' => $oldStatus, 'digiflazz_status' => $statusRecord->status, 'rc' => $rc]);

        // Map Digiflazz responses to our order statuses
        if ($status === 'sukses' || $rc === '00') {
            $order->update(['status' => 'completed']);
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
            $order->load('diamondPack', 'user', 'seller');
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
