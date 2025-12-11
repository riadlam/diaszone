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
        $this->username = config('services.digiflazz.username') ?? env('DIGIFLAZZ_USERNAME');
        $this->sign = config('services.digiflazz.sign') ?? env('DIGIFLAZZ_SIGN');
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
        if (empty($this->username) || empty($this->sign)) {
            return ['result' => false, 'message' => 'Digiflazz not configured'];
        }

        // Prevent duplicate submissions from client refresh/retries:
        try {
            if (isset($order) && method_exists($order, 'id')) {
                $existing = \App\Models\DigiflazzStatus::where('order_id', $order->id)
                    ->whereIn('status', ['Sukses', 'sukses', 'SUCCESS', 'success', 'waiting', 'pending'])
                    ->latest()
                    ->first();
                if ($existing) {
                    return ['result' => false, 'message' => 'Order already submitted to Digiflazz', 'existing' => $existing->toArray()];
                }
            }
        } catch (\Throwable $e) {
            // proceed even if check fails
            \Log::warning('Digiflazz duplicate-check failed: ' . $e->getMessage());
        }

        // Determine customer_no: prefer player id / user+zone if available, fall back to order id
        $customerNo = $order->id;
        // prefer game-specific ids
        $playerId = $order->user_id_ml ?? $order->player_id_ff ?? $order->player_id_pubg ?? $order->player_id_hok ?? $order->user_id_bs ?? null;
        if ($pack->game_type === 'freefire' && $order->player_id_ff) {
            $customerNo = $order->player_id_ff;
        } elseif (!empty($order->user_id_ml) && !empty($order->zone_id_ml)) {
            $customerNo = $order->user_id_ml . '.' . $order->zone_id_ml;
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
            'sign' => md5($this->username . $this->sign . $refId),
        ];

        try {
            $resp = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/transaction', $payload);

            $json = $resp->json();

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
                    \App\Models\DigiflazzStatus::create([
                        'order_id' => $order->id ?? null,
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
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to persist initial DigiflazzStatus: ' . $e->getMessage(), ['ref_id' => $refId]);
            }

            return $result;

        } catch (\Throwable $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
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
}
