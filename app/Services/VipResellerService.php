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
            // Prepare form data exactly as curl example
            $formData = [
                'key' => $this->apiKey,
                'sign' => $this->sign,
                'type' => 'get-nickname',
                'code' => 'mobile-legends',
                'target' => $userId,
                'additional_target' => $zoneId,
            ];
            
            // Log request data
            Log::info('VIP Reseller nickname check request', [
                'url' => $this->baseUrl . '/game-feature',
                'data' => $formData,
                'api_key_length' => strlen($this->apiKey ?? ''),
                'sign_length' => strlen($this->sign ?? ''),
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
}

