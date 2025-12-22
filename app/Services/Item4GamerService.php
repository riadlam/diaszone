<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Item4GamerService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = trim((string)(config('services.item4gamer.api_key') ?? env('ITEM4GAMER_API_KEY') ?? ''));
        $this->baseUrl = config('services.item4gamer.base_url', 'https://item4gamer.com/wp-json/reseller/v1');
    }

    /**
     * Place an order via Item4Gamer API
     * 
     * @param \App\Models\DiamondPack $pack
     * @param \App\Models\Order $order
     * @param int $quantity
     * @param string $playerId Player/User ID to top up
     * @return array ['success' => bool, 'order_id' => int|null, 'total' => float|null, 'currency' => string|null, 'message' => string, 'full_response' => array]
     */
    public function placeOrder($pack, $order, int $quantity, string $playerId): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Item4Gamer API key not configured',
                'order_id' => null,
                'total' => null,
                'currency' => null,
                'full_response' => [],
            ];
        }

        // Get customer info from order (if user is logged in) or use defaults
        if ($order->user) {
            // Split name into first and last name
            $nameParts = explode(' ', $order->user->name ?? 'Customer', 2);
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName = $nameParts[1] ?? '';
            
            $customer = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $order->user->email ?? 'customer@example.com',
                'phone' => $order->user->phone ?? '0000000000',
            ];
        } else {
            // If no user, use defaults
            $customer = [
                'first_name' => 'Customer',
                'last_name' => '',
                'email' => 'customer@example.com',
                'phone' => '0000000000',
            ];
        }

        $payload = [
            'variation_id' => $pack->code, // The code column from diamond_packs table
            'quantity' => $quantity,
            'customer' => $customer,
            'data' => [
                'name' => (string) $playerId, // Player ID/User ID
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $this->apiKey,
            ])->post($this->baseUrl . '/order/add-order', $payload);

            $json = $response->json();

            // Check if response is successful
            if ($response->successful() && isset($json['data']['status']) && $json['data']['status'] == 200) {
                $data = $json['data'];
                
                return [
                    'success' => true,
                    'order_id' => $data['order_id'] ?? null,
                    'total' => $data['total'] ?? null,
                    'currency' => $data['currency'] ?? 'USD',
                    'message' => 'Order placed successfully',
                    'full_response' => $json,
                ];
            } else {
                $errorMessage = $json['message'] ?? ($json['data']['message'] ?? 'Unknown error from Item4Gamer API');
                
                Log::error('Item4Gamer placeOrder failed', [
                    'order_id' => $order->id,
                    'pack_id' => $pack->id,
                    'variation_id' => $pack->code,
                    'player_id' => $playerId,
                    'quantity' => $quantity,
                    'response' => $json,
                    'status_code' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'order_id' => null,
                    'total' => null,
                    'currency' => null,
                    'full_response' => $json,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Item4Gamer placeOrder exception', [
                'order_id' => $order->id,
                'pack_id' => $pack->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage(),
                'order_id' => null,
                'total' => null,
                'currency' => null,
                'full_response' => [],
            ];
        }
    }

    /**
     * Get order status from Item4Gamer API
     * 
     * @param int $orderId Item4Gamer order ID
     * @return array ['success' => bool, 'status' => string|null, 'order_data' => array|null, 'message' => string, 'full_response' => array]
     */
    public function getOrderStatus(int $orderId): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Item4Gamer API key not configured',
                'status' => null,
                'order_data' => null,
                'full_response' => [],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $this->apiKey,
            ])->get($this->baseUrl . '/order/get-order', [
                'order_id' => $orderId,
            ]);

            $json = $response->json();

            // Check if response is successful
            if ($response->successful() && isset($json['data']['status']) && $json['data']['status'] == 200) {
                $orderData = $json['data']['order'] ?? null;
                $status = $orderData['status'] ?? null;

                return [
                    'success' => true,
                    'status' => $status,
                    'order_data' => $orderData,
                    'message' => 'Status retrieved successfully',
                    'full_response' => $json,
                ];
            } else {
                $errorMessage = $json['message'] ?? ($json['data']['message'] ?? 'Unknown error from Item4Gamer API');
                
                Log::warning('Item4Gamer getOrderStatus failed', [
                    'item4gamer_order_id' => $orderId,
                    'response' => $json,
                    'status_code' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'status' => null,
                    'order_data' => null,
                    'full_response' => $json,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Item4Gamer getOrderStatus exception', [
                'item4gamer_order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get order status: ' . $e->getMessage(),
                'status' => null,
                'order_data' => null,
                'full_response' => [],
            ];
        }
    }
}
