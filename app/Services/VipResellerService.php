<?php

namespace App\Services;

use App\Support\SafeLog;
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
        // Use config() so credentials work when config is cached on production
        $this->apiId = config('services.vip_reseller.api_id') ?: env('VIP_RESELLER_API_ID');
        $this->apiKey = config('services.vip_reseller.api_key') ?: env('VIP_RESELLER_API_KEY');
        $this->sign = config('services.vip_reseller.sign') ?: env('VIP_RESELLER_SIGN');
        $this->baseUrl = config('services.vip_reseller.base_url')
            ?: env('VIP_RESELLER_BASE_URL', 'https://vip-reseller.co.id/api');
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
                SafeLog::error('Provider nickname check: credentials missing', [
                    'api_key_empty' => empty($this->apiKey),
                    'sign_empty' => empty($this->sign),
                    'api_id_set' => !empty($this->apiId),
                    'base_url' => $this->baseUrl,
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
                'target' => (string) $userId,
                'additional_target' => (string) $zoneId,
            ];
            
            $url = rtrim($this->baseUrl, '/') . '/game-feature';

            // Log request data (without exposing full credentials)
            SafeLog::info('Provider nickname check request', [
                'url' => $url,
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
                ->connectTimeout(8)
                ->timeout(20)
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post($url, $formData);

            $rawBody = $response->body();
            $data = $response->json();

            // Always log full provider response so production 500s/API errors are visible
            SafeLog::info('Provider nickname check response', [
                'http_status' => $response->status(),
                'successful' => $response->successful(),
                'raw_body' => $rawBody,
                'parsed_json' => $data,
                'target' => $formData['target'],
                'additional_target' => $formData['additional_target'],
            ]);

            // API returns: {"result": true, "data": "nickname", "message": "Success"}
            if ($response->successful() && is_array($data) && ($data['result'] ?? false) === true) {
                // The nickname is in the 'data' field directly (string)
                $nickname = $data['data'] ?? null;
                
                return [
                    'result' => true,
                    'data' => $nickname,
                    'message' => $data['message'] ?? 'Success',
                ];
            }

            $providerMessage = is_array($data)
                ? ($data['message'] ?? null)
                : null;

            SafeLog::warning('Provider nickname check failed', [
                'http_status' => $response->status(),
                'provider_message' => $providerMessage,
                'raw_body' => $rawBody,
                'target' => $formData['target'],
                'additional_target' => $formData['additional_target'],
            ]);

            // API returns: {"result": false, "message": "error message"}
            return [
                'result' => false,
                'data' => null,
                'message' => $providerMessage ?: 'Failed to validate nickname. Please check your User ID and Zone ID.',
                'provider_http_status' => $response->status(),
                'provider_body' => $rawBody,
            ];
        } catch (\Throwable $e) {
            SafeLog::error('Provider nickname check exception', [
                'user_id' => $userId,
                'zone_id' => $zoneId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'result' => false,
                'data' => null,
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
                Log::error('Provider credentials missing for order placement', [
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
                Log::error('Provider order placement missing parameters', [
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
            Log::info('Provider order placement request', [
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
            Log::info('Provider order placement response', [
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
            Log::error('Provider order placement error: ' . $e->getMessage(), [
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
                Log::error('Provider credentials missing for Free Fire order placement', [
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
                Log::error('Provider Free Fire order placement missing parameters', [
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
            Log::info('Provider Free Fire order placement request', [
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
            Log::info('Provider Free Fire order placement response', [
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
            Log::error('Provider Free Fire order placement error: ' . $e->getMessage(), [
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
    * Get profile/balance from provider API
     * 
     * @return array
     */
    public function getProfile()
    {
        try {
            // Validate credentials are set
            if (empty($this->apiKey) || empty($this->sign)) {
                Log::error('Provider credentials missing for profile', [
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
            Log::info('Provider profile request', [
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
            Log::info('Provider profile response', [
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
            Log::error('Provider profile error: ' . $e->getMessage(), [
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

