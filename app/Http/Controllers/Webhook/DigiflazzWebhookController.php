<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $customerNo = $payload['customer_no'] ?? null;
        $buyerSku = $payload['buyer_sku_code'] ?? null;
        $rc = $payload['rc'] ?? null;
        $status = $payload['status'] ?? null;

        try {
            $statusRecord = DigiflazzStatus::where('ref_id', $refId)->orWhere('trxid', $payload['trxid'] ?? null)->first();

            $data = [
                'ref_id' => $refId,
                'trxid' => $payload['trxid'] ?? null,
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

            // Try to attach to an order if not attached already
            if (!$statusRecord->order_id && $customerNo) {
                $order = $this->findOrderByCustomerNo($customerNo);
                if ($order) {
                    $statusRecord->order_id = $order->id;
                    $statusRecord->save();

                    // Update order status based on Digiflazz status/rc
                    $this->applyStatusToOrder($order, $statusRecord);
                }
            } elseif ($statusRecord->order_id) {
                $order = Order::find($statusRecord->order_id);
                if ($order) {
                    $this->applyStatusToOrder($order, $statusRecord);
                        // No realtime broadcasting — webhook updates DB; client should read order status from server when appropriate
                }
            }

            Log::info('Digiflazz webhook processed', ['ref_id' => $refId, 'order_id' => $statusRecord->order_id ?? null, 'status' => $status]);
            return response()->json(['ok' => true], 200);

        } catch (\Throwable $e) {
            Log::error('Digiflazz webhook exception: ' . $e->getMessage(), ['payload' => $payload]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    protected function findOrderByCustomerNo(string $customerNo)
    {
        // Try matching numeric order id
        if (preg_match('/^\d+$/', $customerNo)) {
            $order = Order::find((int)$customerNo);
            if ($order) return $order;
        }

        // Try Mobile Legends pattern user.zone
        if (strpos($customerNo, '.') !== false) {
            [$userId, $zone] = explode('.', $customerNo, 2);
            $order = Order::where('user_id_ml', $userId)->where('zone_id_ml', $zone)->latest()->first();
            if ($order) return $order;
        }

        // Try Mobile Legends concatenated pattern user+zone (e.g., 2057629734048)
        if (preg_match('/^\d+$/', $customerNo)) {
            $order = Order::whereRaw("CONCAT(user_id_ml, zone_id_ml) = ?", [$customerNo])->latest()->first();
            if ($order) return $order;
        }

        // Try matching common player id fields
        $order = Order::where('player_id_ff', $customerNo)
            ->orWhere('player_id_pubg', $customerNo)
            ->orWhere('player_id_hok', $customerNo)
            ->orWhere('user_id_bs', $customerNo)
            ->latest()
            ->first();

        return $order;
    }

    protected function applyStatusToOrder(Order $order, DigiflazzStatus $statusRecord)
    {
        $status = strtolower($statusRecord->status ?? '');
        $rc = $statusRecord->rc ?? null;

        // Map Digiflazz responses to our order statuses
        if ($status === 'sukses' || $rc === '00') {
            $order->update(['status' => 'completed']);
        } elseif ($status === 'pending' || in_array($rc, ['03', '99'])) {
            $order->update(['status' => 'sending']);
        } else {
            // All other cases considered failed
            $order->update(['status' => 'failed']);
        }

        // Save a note on order (append)
        $note = $statusRecord->message ?? ($statusRecord->additional_data['message'] ?? null);
        if ($note) {
            $order->notes = trim(($order->notes ?? '') . "\nDigiflazz: " . $note);
            $order->save();
        }
    }
}
