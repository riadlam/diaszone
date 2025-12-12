<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DigiflazzService
{
    protected string $username;
    protected string $sign;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = trim((string)(config('services.digiflazz.username') ?? env('DIGIFLAZZ_USERNAME') ?? ''));
        $this->sign = trim((string)(config('services.digiflazz.sign') ?? env('DIGIFLAZZ_SIGN') ?? ''));
        $this->baseUrl = config('services.digiflazz.base_url', 'https://api.digiflazz.com/v1');
    }

    /**
     * Place a top-up order via Digiflazz
     * @param \App\Models\DiamondPack $pack
     * @param \App\Models\Order $order
     * @return array
     */
    public function placeOrder($pack, $order): array
    {
        // For multi-item orders, use placeOrderWithRefId instead
        // This method is kept for backward compatibility
        $refId = 'order-' . $order->id . '-' . Str::random(8);
        return $this->placeOrderWithRefId($pack, $order, $refId);
    }

    /**
     * Place a top-up order via Digiflazz with custom ref_id (for multi-item orders)
     * @param \App\Models\DiamondPack $pack
     * @param \App\Models\Order $order
     * @param string $refId Custom ref_id (format: "order-{order_id}-item-{order_item_id}-{random}")
     * @param int|null $orderItemId Optional order_item_id to store in DigiflazzStatus
     * @return array
     */
    public function placeOrderWithRefId($pack, $order, string $refId, ?int $orderItemId = null): array
    {
        if (empty($this->username) || empty($this->sign)) {
            return ['result' => false, 'message' => 'Digiflazz not configured'];
        }

        // Note: Duplicate check is now handled atomically in the calling code (CheckoutController)
        // with proper transaction locking. This method focuses on the API call itself.

        // Determine customer_no: prefer player id / user+zone if available, fall back to order id
        $customerNo = $order->id;
        // prefer game-specific ids
        $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs ?? null;
        if ($pack->game_type === 'freefire' && $order->player_id_ff) {
            $customerNo = $order->player_id_ff;
        } elseif (!empty($order->user_id_ml) && !empty($order->zone_id_ml)) {
            // Digiflazz expects a single customer_no numeric string for ML: concatenate user id + zone
            // e.g., user_id=205762973 and zone=4048 => customer_no=2057629734048
            $customerNo = (string)$order->user_id_ml . (string)$order->zone_id_ml;
        } elseif ($playerId) {
            $customerNo = $playerId;
        }

        $refId = 'order-' . $order->id . '-' . Str::random(8);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $pack->code,
            'customer_no' => (string) $customerNo,
            'ref_id' => $refId,
            // sign is md5(username + apiKey + ref_id)
            'sign' => $this->computeSign($refId),
        ];

        try {
            $resp = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/transaction', $payload);

            $json = $resp->json();

            // Diagnostic logging: if Digiflazz returns a signature error, log masked sign and payload for debugging
            if (isset($json['data']['message']) && stripos($json['data']['message'], 'Signature') !== false) {
                \Log::warning('Digiflazz reported signature error', [
                    'order_id' => $order->id ?? null,
                    'ref_id' => $refId,
                    'customer_no' => (string) $customerNo,
                    'computed_sign_mask' => substr($this->computeSign($refId), 0, 6) . '...'
                ]);
            }

            // Normalize response to expected shape
            $result = [
                'result' => ($resp->successful() && (isset($json['data']) || isset($json['status']) && $json['status'] === 'SUCCESS')),
                'data' => $json['data'] ?? $json,
                'message' => $json['message'] ?? ($json['status'] ?? null),
                'ref_id' => $refId,
                'full_response' => $json,
            ];

            // If the caller provided an Order in the response flow, persist an initial DigiflazzStatus here
            try {
                if (isset($order) && method_exists($order, 'id')) {
                    // Use updateOrCreate to avoid duplicate ref_id records and make call idempotent
                    \App\Models\DigiflazzStatus::updateOrCreate(
                        ['ref_id' => $refId],
                        [
                            'order_id' => $order->id ?? null,
                            'order_item_id' => $orderItemId,
                            'diamond_pack_id' => $pack->id,
                            'ref_id' => $refId,
                            'trxid' => $json['data']['trxid'] ?? null,
                            'buyer_sku_code' => $json['data']['buyer_sku_code'] ?? ($pack->code ?? null),
                            'customer_no' => $json['data']['customer_no'] ?? null,
                            'rc' => $json['data']['rc'] ?? null,
                            'status' => $json['data']['status'] ?? ($json['status'] ?? null),
                            'message' => $json['data']['message'] ?? null,
                            'price' => $json['data']['price'] ?? null,
                            'sn' => $json['data']['sn'] ?? null,
                            'additional_data' => $json,
                            'event' => 'create'
                        ]
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to persist initial DigiflazzStatus (idempotent): ' . $e->getMessage(), ['ref_id' => $refId]);
            }

            return $result;

        } catch (\Throwable $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }


    /**
     * Compute digiflazz sign for a ref_id (md5 of username + sign + ref_id)
     */
    public function computeSign(string $refId): string
    {
        return md5($this->username . $this->sign . $refId);
    }

    /**
     * Check status for postpaid via commands=status-pasca
     * @param string $buyerSku
     * @param string $customerNo
     * @param string $refId
     * @return array
     */
    public function checkPostpaidStatus(string $buyerSku, string $customerNo, string $refId): array
    {
        if (empty($this->username) || empty($this->sign)) {
            return ['result' => false, 'message' => 'Digiflazz not configured'];
        }

        $payload = [
            'commands' => 'status-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $buyerSku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => md5($this->username . $this->sign . $refId),
        ];

        try {
            $resp = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/transaction', $payload);

            $json = $resp->json();
            return ['result' => $resp->successful(), 'data' => $json, 'message' => $json['message'] ?? null];
        } catch (\Throwable $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check deposit balance using Digiflazz cek-saldo endpoint
     * Request payload: { cmd: 'deposit', username: username, sign: md5(username + apiKey + 'depo') }
     * Response example: { data: { deposit: 500000000000 } }
     *
     * @return array ['result' => bool, 'deposit' => float|null, 'data' => array|null, 'message' => string|null]
     */
    public function cekSaldo(): array
    {
        if (empty($this->username) || empty($this->sign)) {
            return ['result' => false, 'message' => 'Digiflazz not configured'];
        }

        $payload = [
            'cmd' => 'deposit',
            'username' => $this->username,
            'sign' => md5($this->username . $this->sign . 'depo'),
        ];

        try {
            $resp = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/cek-saldo', $payload);

            $json = $resp->json();

            $deposit = $json['data']['deposit'] ?? null;

            return [
                'result' => $resp->successful(),
                'deposit' => $deposit,
                'data' => $json['data'] ?? $json,
                'message' => $json['message'] ?? null,
                'full_response' => $json,
            ];
        } catch (\Throwable $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }
}
