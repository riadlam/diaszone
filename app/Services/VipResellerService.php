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
            $response = Http::asForm()->post($this->baseUrl . '/game-feature', [
                'key' => $this->apiKey,
                'sign' => $this->sign,
                'type' => 'get-nickname',
                'code' => 'mobile-legends',
                'target' => $userId,
                'additional_target' => $zoneId,
            ]);

            $data = $response->json();

            // Log response for debugging
            Log::info('VIP Reseller nickname check response', [
                'status' => $response->status(),
                'data' => $data,
            ]);

            if ($response->successful() && isset($data['result']) && $data['result'] === true) {
                // The nickname might be in data array or directly in data
                $nickname = $data['data']['nickname'] ?? $data['nickname'] ?? $data['data'][0]['nickname'] ?? null;
                
                return [
                    'success' => true,
                    'nickname' => $nickname,
                    'data' => $data['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Failed to validate nickname. Please check your User ID and Zone ID.',
                'data' => $data ?? [],
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

