<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VipResellerService
{
    private $apiId;
    private $apiKey;
    private $sign;
    private $baseUrl;

    public function __construct()
    {
        $this->apiId = env('VIP_RESELLER_API_ID');
        $this->apiKey = env('VIP_RESELLER_API_KEY');
        $this->sign = env('VIP_RESELLER_SIGN');
        $this->baseUrl = env('VIP_RESELLER_BASE_URL', 'https://vip-reseller.co.id/api');
        
        // Log credentials check (without exposing full values)
        Log::info('VIP Reseller Service initialized', [
            'api_id_set' => !empty($this->apiId),
            'api_key_set' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey ?? ''),
            'sign_set' => !empty($this->sign),
            'sign_length' => strlen($this->sign ?? ''),
            'base_url' => $this->baseUrl,
        ]);
    }

    /**
     * Check nickname for Mobile Legends
     * 
     * @param string $userId User ID (target)
     * @param string $zoneId Zone ID (additional_target)
     * @return array
     */
    public function checkNickname($userId, $zoneId)
    {
        try {
            // Validate credentials are set
            if (empty($this->apiKey) || empty($this->sign)) {
                Log::error('VIP Reseller credentials missing', [
                    'api_key_empty' => empty($this->apiKey),
                    'sign_empty' => empty($this->sign),
                ]);
                return [
                    'result' => false,
                    'data' => null,
                    'message' => 'API credentials not configured. Please contact support.',
                ];
            }
            
            // Prepare form data exactly as curl example
            $formData = [
                'key' => $this->apiKey,
                'sign' => $this->sign,
                'type' => 'get-nickname',
                'code' => 'mobile-legends',
                'target' => $userId,
                'additional_target' => $zoneId,
            ];
            
            // Log request data (without exposing full credentials)
            Log::info('VIP Reseller nickname check request', [
                'url' => $this->baseUrl . '/game-feature',
                'type' => $formData['type'],
                'code' => $formData['code'],
                'target' => $formData['target'],
                'additional_target' => $formData['additional_target'],
                'key_set' => !empty($formData['key']),
                'key_length' => strlen($formData['key'] ?? ''),
                'sign_set' => !empty($formData['sign']),
                'sign_length' => strlen($formData['sign'] ?? ''),
            ]);
            
            // Use asForm() which sends as application/x-www-form-urlencoded
            $response = Http::asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post($this->baseUrl . '/game-feature', $formData);

            $data = $response->json();

            // Log response for debugging
            Log::info('VIP Reseller nickname check response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'data' => $data,
            ]);

            // API returns: {"result": true, "data": "nickname", "message": "Success"}
            if ($response->successful() && isset($data['result']) && $data['result'] === true) {
                // The nickname is in the 'data' field directly (string)
                $nickname = $data['data'] ?? null;
                
                return [
                    'result' => true,
                    'data' => $nickname,
                    'message' => $data['message'] ?? 'Success',
                ];
            }

            // API returns: {"result": false, "message": "error message"}
            return [
                'result' => false,
                'data' => null,
                'message' => $data['message'] ?? 'Failed to validate nickname. Please check your User ID and Zone ID.',
            ];
        } catch (\Exception $e) {
            Log::error('VIP Reseller nickname check error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'zone_id' => $zoneId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error validating nickname: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Place an order/recharge for Mobile Legends
     * 
     * @param string $code Package code from diamond_packs table
     * @param string $userId User ID (data_no)
     * @param string $zoneId Zone ID (data_zone)
     * @return array
     */
    public function placeOrder($code, $userId, $zoneId)
    {
        try {
            // Validate credentials are set
            if (empty($this->apiKey) || empty($this->sign)) {
                Log::error('VIP Reseller credentials missing for order placement', [
                    'api_key_empty' => empty($this->apiKey),
                    'sign_empty' => empty($this->sign),
                ]);
                return [
                    'result' => false,
                    'data' => null,
                    'message' => 'API credentials not configured. Please contact support.',
                ];
            }
            
            // Validate required parameters
            if (empty($code) || empty($userId) || empty($zoneId)) {
                Log::error('VIP Reseller order placement missing parameters', [
                    'code' => $code,
                    'user_id' => $userId,
                    'zone_id' => $zoneId,
                ]);
                return [
                    'result' => false,
                    'data' => null,
                    'message' => 'Missing required parameters: code, user_id, or zone_id',
                ];
            }
            
            // Prepare form data exactly as curl example
            $formData = [
                'key' => $this->apiKey,
                'sign' => $this->sign,
                'type' => 'order',
                'code' => $code,
                'service' => $code, // Using code as service, adjust if service is different
                'data_no' => $userId,
                'data_zone' => $zoneId,
            ];
            
            // Log request data (without exposing full credentials)
            Log::info('VIP Reseller order placement request', [
                'url' => $this->baseUrl . '/game-feature',
                'type' => $formData['type'],
                'code' => $formData['code'],
                'service' => $formData['service'],
                'data_no' => $formData['data_no'],
                'data_zone' => $formData['data_zone'],
                'key_set' => !empty($formData['key']),
                'sign_set' => !empty($formData['sign']),
            ]);
            
            // Use asForm() which sends as application/x-www-form-urlencoded
            $response = Http::asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post($this->baseUrl . '/game-feature', $formData);

            $data = $response->json();

            // Log response for debugging
            Log::info('VIP Reseller order placement response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data,
            ]);

            // API returns: {"result": true, "data": {...}, "message": "Success"}
            if ($response->successful() && isset($data['result']) && $data['result'] === true) {
                return [
                    'result' => true,
                    'data' => $data['data'] ?? null,
                    'message' => $data['message'] ?? 'Order placed successfully',
                ];
            }

            // API returns: {"result": false, "message": "error message"}
            return [
                'result' => false,
                'data' => null,
                'message' => $data['message'] ?? 'Failed to place order. Please try again.',
            ];
        } catch (\Exception $e) {
            Log::error('VIP Reseller order placement error: ' . $e->getMessage(), [
                'code' => $code,
                'user_id' => $userId,
                'zone_id' => $zoneId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'result' => false,
                'data' => null,
                'message' => 'Error placing order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Place an order/recharge for Free Fire (no zone_id required)
     * 
     * @param string $code Package code from diamond_packs table (e.g., FF100-S13)
     * @param string $playerId Player ID (data_no)
     * @return array
     */
    public function placeFreefireOrder($code, $playerId)
    {
        try {
            // Validate credentials are set
            if (empty($this->apiKey) || empty($this->sign)) {
                Log::error('VIP Reseller credentials missing for Free Fire order placement', [
                    'api_key_empty' => empty($this->apiKey),
                    'sign_empty' => empty($this->sign),
                ]);
                return [
                    'result' => false,
                    'data' => null,
                    'message' => 'API credentials not configured. Please contact support.',
                ];
            }
            
            // Validate required parameters
            if (empty($code) || empty($playerId)) {
                Log::error('VIP Reseller Free Fire order placement missing parameters', [
                    'code' => $code,
                    'player_id' => $playerId,
                ]);
                return [
                    'result' => false,
                    'data' => null,
                    'message' => 'Missing required parameters: code or player_id',
                ];
            }
            
            // Prepare form data for Free Fire order
            // Free Fire uses 'free-fire' as the game code and doesn't require zone
            $formData = [
                'key' => $this->apiKey,
                'sign' => $this->sign,
                'type' => 'order',
                'service' => $code,       // Pack code like FF100-S13
                'data_no' => $playerId,   // Player ID
            ];
            
            // Log request data (without exposing full credentials)
            Log::info('VIP Reseller Free Fire order placement request', [
                'url' => $this->baseUrl . '/game-feature',
                'type' => $formData['type'],
                'service' => $formData['service'],
                'data_no' => $formData['data_no'],
                'key_set' => !empty($formData['key']),
                'sign_set' => !empty($formData['sign']),
            ]);
            
            // Use asForm() which sends as application/x-www-form-urlencoded
            $response = Http::asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post($this->baseUrl . '/game-feature', $formData);

            $data = $response->json();

            // Log response for debugging
            Log::info('VIP Reseller Free Fire order placement response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data,
            ]);

            // API returns: {"result": true, "data": {...}, "message": "Success"}
            if ($response->successful() && isset($data['result']) && $data['result'] === true) {
                return [
                    'result' => true,
                    'data' => $data['data'] ?? null,
                    'message' => $data['message'] ?? 'Order placed successfully',
                ];
            }

            // API returns: {"result": false, "message": "error message"}
            return [
                'result' => false,
                'data' => null,
                'message' => $data['message'] ?? 'Failed to place Free Fire order. Please try again.',
            ];
        } catch (\Exception $e) {
            Log::error('VIP Reseller Free Fire order placement error: ' . $e->getMessage(), [
                'code' => $code,
                'player_id' => $playerId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'result' => false,
                'data' => null,
                'message' => 'Error placing Free Fire order: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get profile/balance from VIP Reseller API
     * 
     * @return array
     */
    public function getProfile()
    {
        try {
            // Validate credentials are set
            if (empty($this->apiKey) || empty($this->sign)) {
                Log::error('VIP Reseller credentials missing for profile', [
                    'api_key_empty' => empty($this->apiKey),
                    'sign_empty' => empty($this->sign),
                ]);
                return [
                    'result' => false,
                    'data' => null,
                    'message' => 'API credentials not configured. Please contact support.',
                ];
            }
            
            // Prepare form data
            $formData = [
                'key' => $this->apiKey,
                'sign' => $this->sign,
            ];
            
            // Log request data (without exposing full credentials)
            Log::info('VIP Reseller profile request', [
                'url' => $this->baseUrl . '/profile',
                'key_set' => !empty($formData['key']),
                'sign_set' => !empty($formData['sign']),
            ]);
            
            // Use asForm() which sends as application/x-www-form-urlencoded
            $response = Http::asForm()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post($this->baseUrl . '/profile', $formData);

            $data = $response->json();

            // Log response for debugging
            Log::info('VIP Reseller profile response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data,
            ]);

            // API returns: {"result": true, "data": {...}, "message": "Success"}
            if ($response->successful() && isset($data['result']) && $data['result'] === true) {
                return [
                    'result' => true,
                    'data' => $data['data'] ?? null,
                    'message' => $data['message'] ?? 'Successfully got your account details.',
                ];
            }

            // API returns: {"result": false, "message": "error message"}
            return [
                'result' => false,
                'data' => null,
                'message' => $data['message'] ?? 'Failed to get profile. Please try again.',
            ];
        } catch (\Exception $e) {
            Log::error('VIP Reseller profile error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'result' => false,
                'data' => null,
                'message' => 'Error getting profile: ' . $e->getMessage(),
            ];
        }
    }
}

