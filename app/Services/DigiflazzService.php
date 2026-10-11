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

        if (empty($pack->code)) {
            return ['result' => false, 'message' => 'Missing Digiflazz buyer SKU code on pack'];
        }

        $resolved = $this->resolveCustomerNo($pack, $order);
        if (! empty($resolved['error'])) {
            \Log::warning('DigiflazzService: cannot build customer_no', [
                'order_id' => $order->id ?? null,
                'pack_id' => $pack->id ?? null,
                'game_type' => $pack->game_type ?? null,
                'error' => $resolved['error'],
            ]);

            return ['result' => false, 'message' => $resolved['error']];
        }

        $customerNo = (string) $resolved['customer_no'];

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => (string) $pack->code,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            // sign is md5(username + apiKey + ref_id)
            'sign' => $this->computeSign($refId),
        ];

        try {
            \Log::info('Digiflazz placeOrder payload', [
                'order_id' => $order->id ?? null,
                'order_item_id' => $orderItemId,
                'game_type' => $pack->game_type ?? null,
                'buyer_sku_code' => $payload['buyer_sku_code'],
                'customer_no' => $payload['customer_no'],
                'ref_id' => $refId,
            ]);

            $resp = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/transaction', $payload);

            $json = $resp->json() ?? [];
            $data = is_array($json['data'] ?? null) ? $json['data'] : [];
            $providerStatus = strtolower((string) ($data['status'] ?? ($json['status'] ?? '')));
            $rc = (string) ($data['rc'] ?? '');
            $providerMessage = (string) ($data['message'] ?? ($json['message'] ?? ''));

            // Digiflazz accepts the top-up when status is Pending/Sukses (or rc 00/03/99).
            // Gagal / missing data must not be treated as a successful place.
            $accepted = $resp->successful()
                && $data !== []
                && (
                    in_array($providerStatus, ['sukses', 'pending'], true)
                    || in_array($rc, ['00', '03', '99'], true)
                );

            if (stripos($providerMessage, 'Signature') !== false) {
                \Log::warning('Digiflazz reported signature error', [
                    'order_id' => $order->id ?? null,
                    'ref_id' => $refId,
                    'customer_no' => $customerNo,
                    'computed_sign_mask' => substr($this->computeSign($refId), 0, 6).'...',
                ]);
            }

            $result = [
                'result' => $accepted,
                'data' => $data !== [] ? $data : $json,
                'message' => $providerMessage !== '' ? $providerMessage : ($accepted ? 'OK' : 'Digiflazz rejected the top-up'),
                'ref_id' => $refId,
                'full_response' => $json,
            ];

            try {
                if (isset($order) && method_exists($order, 'id')) {
                    \App\Models\DigiflazzStatus::updateOrCreate(
                        ['ref_id' => $refId],
                        [
                            'order_id' => $order->id ?? null,
                            'order_item_id' => $orderItemId,
                            'diamond_pack_id' => $pack->id,
                            'ref_id' => $refId,
                            'trxid' => $data['trxid'] ?? null,
                            'buyer_sku_code' => $data['buyer_sku_code'] ?? ($pack->code ?? null),
                            'customer_no' => $data['customer_no'] ?? $customerNo,
                            'rc' => $data['rc'] ?? null,
                            'status' => $data['status'] ?? ($json['status'] ?? null),
                            'message' => $providerMessage !== '' ? $providerMessage : null,
                            'price' => $data['price'] ?? null,
                            'sn' => $data['sn'] ?? null,
                            'additional_data' => $json,
                            'event' => 'create',
                        ]
                    );
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to persist initial DigiflazzStatus (idempotent): '.$e->getMessage(), ['ref_id' => $refId]);
            }

            return $result;

        } catch (\Throwable $e) {
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Digiflazz customer_no per game. Never fall back to the local order id.
     *
     * @return array{customer_no?: string, error?: string}
     */
    protected function resolveCustomerNo($pack, $order): array
    {
        $gameType = strtolower(trim((string) ($pack->game_type ?? '')));

        if ($gameType === 'freefire') {
            $id = trim((string) ($order->player_id_ff ?? ''));

            return $id !== ''
                ? ['customer_no' => $id]
                : ['error' => 'Missing Free Fire player ID for Digiflazz customer_no'];
        }

        if (\App\Support\GameProvider::isPubgMobile($gameType)) {
            $id = trim((string) ($order->save_id ?: $order->player_id_pubg ?: ''));

            return $id !== ''
                ? ['customer_no' => $id]
                : ['error' => 'Missing PUBG Mobile player ID for Digiflazz customer_no'];
        }

        if ($gameType === 'honorofkings') {
            $id = trim((string) ($order->player_id_hok ?? ''));

            return $id !== ''
                ? ['customer_no' => $id]
                : ['error' => 'Missing Honor of Kings player ID for Digiflazz customer_no'];
        }

        if ($gameType === 'bloodstrike') {
            $id = trim((string) ($order->user_id_bs ?? ''));

            return $id !== ''
                ? ['customer_no' => $id]
                : ['error' => 'Missing Blood Strike user ID for Digiflazz customer_no'];
        }

        if ($gameType === 'punishinggrayraven') {
            if (! empty($order->save_id) && ! empty($order->server)) {
                return ['customer_no' => (string) $order->save_id.','.(string) $order->server];
            }

            return ['error' => 'Missing Punishing Gray Raven save_id/server for Digiflazz customer_no'];
        }

        if ($gameType === 'wutheringwaves') {
            if (! empty($order->save_id) && ! empty($order->server)) {
                return ['customer_no' => (string) $order->save_id.'|'.(string) $order->server];
            }

            return ['error' => 'Missing Wuthering Waves save_id/server for Digiflazz customer_no'];
        }

        if ($gameType === 'mobilelegends' || (! empty($order->user_id_ml) && ! empty($order->zone_id_ml))) {
            if (! empty($order->user_id_ml) && ! empty($order->zone_id_ml)) {
                // Digiflazz ML: concatenate user id + zone, e.g. 2057629734048
                return ['customer_no' => (string) $order->user_id_ml.(string) $order->zone_id_ml];
            }

            return ['error' => 'Missing Mobile Legends user/zone for Digiflazz customer_no'];
        }

        if (! empty($order->save_id)) {
            return ['customer_no' => (string) $order->save_id];
        }

        return ['error' => 'Missing player destination for Digiflazz customer_no'];
    }


    /**
     * Compute digiflazz sign for a ref_id (md5 of username + sign + ref_id)
     */
    public function computeSign(string $refId): string
    {
        return md5($this->username . $this->sign . $refId);
    }

    /**
     * Check product availability by SKU code
     * Returns product data if available, null if not available
     * 
     * @param string $code Buyer SKU code (e.g., 'mlbb-275dias')
     * @return array|null Product data with availability info, or null if not available
     */
    public function checkProductAvailability(string $code): ?array
    {
        if (empty($this->username) || empty($this->sign)) {
            return null;
        }

        // Generate signature for price-list API with code parameter
        $sign = md5($this->username . $this->sign . 'pricelist');

        try {
            $response = Http::timeout(10)
                ->post($this->baseUrl . '/price-list', [
                    'cmd' => 'prepaid',
                    'username' => $this->username,
                    'sign' => $sign,
                    'code' => $code,
                    'category' => 'Games',
                ]);

            if (!$response->successful()) {
                \Log::warning('Digiflazz: Failed to check product availability', [
                    'code' => $code,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $products = $data['data'] ?? [];

            // Find product with matching buyer_sku_code
            foreach ($products as $product) {
                if (($product['buyer_sku_code'] ?? '') === $code) {
                    // Check if product is active
                    $isActive = ($product['buyer_product_status'] ?? false) === true
                        && ($product['seller_product_status'] ?? false) === true;
                    
                    return [
                        'available' => $isActive,
                        'buyer_product_status' => $product['buyer_product_status'] ?? false,
                        'seller_product_status' => $product['seller_product_status'] ?? false,
                        'multi' => $product['multi'] ?? false,
                        'price' => $product['price'] ?? null,
                        'product_name' => $product['product_name'] ?? '',
                        'buyer_sku_code' => $product['buyer_sku_code'] ?? $code,
                    ];
                }
            }

            // Product code not found in response
            return [
                'available' => false,
                'buyer_product_status' => false,
                'seller_product_status' => false,
                'multi' => false,
                'price' => null,
                'product_name' => '',
                'buyer_sku_code' => $code,
            ];
        } catch (\Exception $e) {
            \Log::error('Digiflazz: Exception checking product availability', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);
            return null;
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
