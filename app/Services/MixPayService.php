<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MixPayService
{
    private $payeeId;
    private $endpoint;

    public function __construct($payeeId = null, $endpoint = null)
    {
        // Allow passing credentials directly (for testing or override)
        if ($payeeId !== null) {
            $this->payeeId = $payeeId;
            $this->endpoint = $endpoint ?? 'https://api.mixpay.me/v1/';
        } else {
            // Read directly from env() first (prioritize .env file)
            // Then fall back to config (for production with cached config)
            $this->payeeId = env('MIXPAY_UUID') ?? config('services.mixpay.uuid');
            $this->endpoint = $endpoint ?? 'https://api.mixpay.me/v1/';
        }
    }
    
    /**
     * Check if credentials are configured
     */
    public function hasCredentials()
    {
        return !empty($this->payeeId);
    }

    /**
     * Create a MixPay one-time payment
     * 
     * @param array $orderData
     * @return array
     */
    public function createOneTimePayment($orderData)
    {
        try {
            // MixPay requires these parameters
            $requestBody = [
                'payeeId' => $this->payeeId,
                'quoteAmount' => $orderData['quote_amount'], // Amount in quote currency (e.g., USD)
                'quoteAssetId' => $orderData['quote_asset_id'] ?? 'usd', // Quote currency (USD)
                'orderId' => $orderData['order_id'], // Must be 6-36 chars, unique
            ];
            
            // Settlement asset ID - required for MixPay
            if (isset($orderData['settlement_asset_id']) && !empty($orderData['settlement_asset_id'])) {
                $requestBody['settlementAssetId'] = $orderData['settlement_asset_id'];
            }

            // Payment asset ID - restricts which cryptocurrencies can be used for payment
            // If set, only this asset will be available for payment
            if (isset($orderData['payment_asset_id']) && !empty($orderData['payment_asset_id'])) {
                $requestBody['paymentAssetId'] = $orderData['payment_asset_id'];
            }
            if (isset($orderData['remark'])) {
                $requestBody['remark'] = $orderData['remark'];
            }
            if (isset($orderData['callback_url'])) {
                $requestBody['callbackUrl'] = $orderData['callback_url'];
            }
            if (isset($orderData['return_to'])) {
                $requestBody['returnTo'] = $orderData['return_to'];
            }
            if (isset($orderData['failed_return_to'])) {
                $requestBody['failedReturnTo'] = $orderData['failed_return_to'];
            }
            if (isset($orderData['strict_mode'])) {
                $requestBody['strictMode'] = $orderData['strict_mode'];
            }

            $response = Http::asForm()->post($this->endpoint . 'one_time_payment', $requestBody);

            $responseData = $response->json();
            
            // If settlement asset error, try to get available assets and retry
            if (!$response->successful() && 
                isset($responseData['code']) && 
                $responseData['code'] == 10052 && 
                str_contains($responseData['message'] ?? '', 'settlement asset')) {
                
                // Get available settlement assets
                $assetsResponse = $this->getSettlementAssets();
                if ($assetsResponse['success'] && !empty($assetsResponse['data'])) {
                    // Use the first available settlement asset
                    $availableAssets = $assetsResponse['data'];
                    if (is_array($availableAssets) && count($availableAssets) > 0) {
                        $firstAsset = is_array($availableAssets[0]) ? ($availableAssets[0]['assetId'] ?? $availableAssets[0]['id'] ?? null) : $availableAssets[0];
                        if ($firstAsset) {
                            $requestBody['settlementAssetId'] = $firstAsset;
                            // Retry with the first available asset
                            $response = Http::asForm()->post($this->endpoint . 'one_time_payment', $requestBody);
                            $responseData = $response->json();
                        }
                    }
                }
            }
            
            if ($response->successful()) {
                // MixPay returns: { "code": 0, "success": true, "data": { "code": "payment-code" } }
                if (isset($responseData['success']) && $responseData['success'] === true && isset($responseData['data']['code'])) {
                    $paymentCode = $responseData['data']['code'];
                    $paymentUrl = 'https://mixpay.me/code/' . $paymentCode;
                    
                    return [
                        'success' => true,
                        'data' => [
                            'code' => $paymentCode,
                            'payment_url' => $paymentUrl,
                        ],
                        'response_data' => $responseData,
                    ];
                }
            }

            // Log detailed error for debugging
            Log::error('MixPay API Error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'response_data' => $responseData,
                'request_body' => $requestBody,
            ]);

            // Extract error message from response
            $errorMessage = 'Failed to create MixPay payment';
            if (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['error'])) {
                $errorMessage = $responseData['error'];
            } elseif (isset($responseData['code']) && $responseData['code'] !== 0) {
                $errorMessage = 'MixPay API Error: Code ' . $responseData['code'];
                if (isset($responseData['message'])) {
                    $errorMessage .= ' - ' . $responseData['message'];
                }
            }

            return [
                'success' => false,
                'error' => $errorMessage,
                'response_data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('MixPay Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'MixPay service error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available settlement assets
     * 
     * @return array
     */
    public function getSettlementAssets()
    {
        try {
            $response = Http::get($this->endpoint . 'assets_settlement');

            $responseData = $response->json();
            
            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to get settlement assets',
                'response_data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('MixPay Get Settlement Assets Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'MixPay service error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment info by orderId or traceId
     * 
     * @param string $orderId
     * @return array
     */
    public function getPaymentInfo($orderId)
    {
        try {
            $response = Http::get($this->endpoint . 'payments', [
                'orderId' => $orderId,
            ]);

            $responseData = $response->json();
            
            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to get payment info',
                'response_data' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('MixPay Get Payment Info Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'MixPay service error: ' . $e->getMessage(),
            ];
        }
    }
}

