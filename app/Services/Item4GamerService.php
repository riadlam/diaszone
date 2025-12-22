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
            // Log the request details for debugging
            Log::info('Item4Gamer placeOrder request', [
                'url' => $this->baseUrl . '/order/add-order',
                'payload' => $payload,
                'api_key_preview' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                'api_key_length' => strlen($this->apiKey),
            ]);
            
            // Item4Gamer API - match exact curl format that works
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'api-key' => $this->apiKey,
                'User-Agent' => 'PostmanRuntime/7.39.0',
            ])->post($this->baseUrl . '/order/add-order', $payload);

            $statusCode = $response->status();
            $json = $response->json();
            $body = $response->body();

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
                // Handle different error scenarios
                $errorMessage = 'Unknown error from Item4Gamer API';
                
                if ($statusCode === 403) {
                    $errorMessage = 'Item4Gamer API: Access forbidden. Please check your API key.';
                } elseif ($statusCode === 401) {
                    $errorMessage = 'Item4Gamer API: Unauthorized. Invalid API key.';
                } elseif ($json) {
                    $errorMessage = $json['message'] ?? ($json['data']['message'] ?? ($json['error'] ?? 'Unknown error from Item4Gamer API'));
                } elseif ($body) {
                    $errorMessage = "Item4Gamer API error (HTTP {$statusCode}): " . substr($body, 0, 200);
                } else {
                    $errorMessage = "Item4Gamer API error (HTTP {$statusCode}): No response body";
                }
                
                Log::error('Item4Gamer placeOrder failed', [
                    'order_id' => $order->id,
                    'pack_id' => $pack->id,
                    'variation_id' => $pack->code,
                    'player_id' => $playerId,
                    'quantity' => $quantity,
                    'status_code' => $statusCode,
                    'response_body' => $body,
                    'response_json' => $json,
                    'api_key_preview' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'order_id' => null,
                    'total' => null,
                    'currency' => null,
                    'full_response' => $json ?? ['raw_body' => $body],
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
            // Match exact curl format that works
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'api-key' => $this->apiKey,
                'User-Agent' => 'PostmanRuntime/7.39.0',
            ])->get($this->baseUrl . '/order/get-order', [
                'order_id' => $orderId,
            ]);

            $statusCode = $response->status();
            $json = $response->json();
            $body = $response->body();

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
                // Handle different error scenarios
                $errorMessage = 'Unknown error from Item4Gamer API';
                
                if ($statusCode === 403) {
                    $errorMessage = 'Item4Gamer API: Access forbidden. Please check your API key.';
                } elseif ($statusCode === 401) {
                    $errorMessage = 'Item4Gamer API: Unauthorized. Invalid API key.';
                } elseif ($json) {
                    $errorMessage = $json['message'] ?? ($json['data']['message'] ?? ($json['error'] ?? 'Unknown error from Item4Gamer API'));
                } elseif ($body) {
                    $errorMessage = "Item4Gamer API error (HTTP {$statusCode}): " . substr($body, 0, 200);
                } else {
                    $errorMessage = "Item4Gamer API error (HTTP {$statusCode}): No response body";
                }
                
                Log::warning('Item4Gamer getOrderStatus failed', [
                    'item4gamer_order_id' => $orderId,
                    'status_code' => $statusCode,
                    'response_body' => $body,
                    'response_json' => $json,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'status' => null,
                    'order_data' => null,
                    'full_response' => $json ?? ['raw_body' => $body],
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
