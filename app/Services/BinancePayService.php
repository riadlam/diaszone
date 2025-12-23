<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BinancePayService
{
    private $apiKey;
    private $secretKey;
    private $endpoint;

    public function __construct($apiKey = null, $secretKey = null, $endpoint = null)
    {
        // Allow passing credentials directly (for testing or override)
        if ($apiKey !== null && $secretKey !== null) {
            $this->apiKey = $apiKey;
            $this->secretKey = $secretKey;
            $this->endpoint = $endpoint ?? 'https://bpay.binanceapi.com/binancepay/openapi/';
        } else {
            // Read directly from env() first (prioritize .env file)
            // Then fall back to config (for production with cached config)
            $this->apiKey = env('BINANCE_PAY_API_KEY') ?? config('services.binance_pay.api_key');
            $this->secretKey = env('BINANCE_PAY_SECRET_KEY') ?? config('services.binance_pay.secret_key');
            $this->endpoint = env('BINANCE_PAY_ENDPOINT') ?? config('services.binance_pay.endpoint') ?? 'https://bpay.binanceapi.com/binancepay/openapi/';
        }
    }
    
    /**
     * Check if credentials are configured
     */
    public function hasCredentials()
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }
    
    /**
     * Generate a 32-character nonce using ASCII letters only (a-z, A-Z)
     * Required by Binance Pay API
     */
    private function generateNonce()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce = '';
        $length = 32;
        
        for ($i = 0; $i < $length; $i++) {
            $nonce .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $nonce;
    }

    /**
     * Create a Binance Pay order
     */
    public function createOrder($orderData)
    {
        $timestamp = round(microtime(true) * 1000);
        $nonce = $this->generateNonce();
        
        $requestBody = [
            'env' => [
                'terminalType' => 'WEB'
            ],
            'merchantTradeNo' => $orderData['merchant_trade_no'], // Unique order number
            'orderAmount' => $orderData['amount'],
            'currency' => 'USDT', // or 'BUSD', 'BNB', etc.
            'goods' => [
                'goodsType' => '01', // Virtual goods
                'goodsCategory' => 'D000',
                'referenceGoodsId' => $orderData['reference_goods_id'],
                'goodsName' => $orderData['goods_name'],
            ],
            'buyer' => [
                'referenceBuyerId' => $orderData['buyer_id'] ?? '',
                'buyerName' => [
                    'firstName' => $orderData['buyer_name'] ?? '',
                    'lastName' => '',
                ],
            ],
            'returnUrl' => $orderData['return_url'] ?? route('crypto-payment-success'),
            'cancelUrl' => $orderData['cancel_url'] ?? route('home'),
        ];

        $payload = json_encode($requestBody);
        $signature = $this->generateSignature($timestamp, $nonce, $payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'BinancePay-Timestamp' => $timestamp,
                'BinancePay-Nonce' => $nonce,
                'BinancePay-Certificate-SN' => $this->apiKey,
                'BinancePay-Signature' => $signature,
            ])->post($this->endpoint . 'v2/order', $requestBody);

            $responseData = $response->json();
            
            if ($response->successful()) {
                if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
                    return [
                        'success' => true,
                        'data' => $responseData['data'] ?? $responseData,
                    ];
                }
            }

            // Log detailed error for debugging
            Log::error('Binance Pay API Error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'response_data' => $responseData,
            ]);

            // Extract error message from response
            $errorMessage = 'Failed to create Binance Pay order';
            if (isset($responseData['errorMessage'])) {
                $errorMessage = $responseData['errorMessage'];
            } elseif (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['code'])) {
                $errorMessage = 'Binance Pay API Error: ' . $responseData['code'];
                if (isset($responseData['message'])) {
                    $errorMessage .= ' - ' . $responseData['message'];
                }
            }

            return [
                'success' => false,
                'error' => $errorMessage,
                'response_data' => $responseData, // Include for debugging
            ];
        } catch (\Exception $e) {
            Log::error('Binance Pay Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Query order status
     */
    public function queryOrder($merchantTradeNo)
    {
        $timestamp = round(microtime(true) * 1000);
        $nonce = $this->generateNonce();
        
        $requestBody = [
            'merchantTradeNo' => $merchantTradeNo,
        ];

        $payload = json_encode($requestBody);
        $signature = $this->generateSignature($timestamp, $nonce, $payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'BinancePay-Timestamp' => $timestamp,
                'BinancePay-Nonce' => $nonce,
                'BinancePay-Certificate-SN' => $this->apiKey,
                'BinancePay-Signature' => $signature,
            ])->post($this->endpoint . 'v2/order/query', $requestBody);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'SUCCESS') {
                    return [
                        'success' => true,
                        'data' => $data['data'],
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $response->json()['errorMessage'] ?? 'Failed to query order',
            ];
        } catch (\Exception $e) {
            Log::error('Binance Pay Query Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test Binance Pay API connection and credentials
     * This method attempts to query a test order to validate credentials
     */
    public function testConnection()
    {
        // Get raw env values for debugging
        $rawApiKey = env('BINANCE_PAY_API_KEY');
        $rawSecretKey = env('BINANCE_PAY_SECRET_KEY');
        $rawEndpoint = env('BINANCE_PAY_ENDPOINT');
        
        if (!$this->hasCredentials()) {
            return [
                'success' => false,
                'error' => 'Binance Pay credentials are not configured',
                'details' => [
                    'api_key_configured' => !empty($this->apiKey),
                    'secret_key_configured' => !empty($this->secretKey),
                    'endpoint' => $this->endpoint,
                    'debug' => [
                        'raw_env_api_key' => !empty($rawApiKey) ? (substr($rawApiKey, 0, 10) . '...' . substr($rawApiKey, -5)) : 'NOT FOUND',
                        'raw_env_secret_key' => !empty($rawSecretKey) ? (substr($rawSecretKey, 0, 10) . '...' . substr($rawSecretKey, -5)) : 'NOT FOUND',
                        'raw_env_endpoint' => $rawEndpoint ?? 'NOT FOUND',
                        'loaded_api_key' => !empty($this->apiKey) ? (substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5)) : 'NOT LOADED',
                        'loaded_secret_key' => !empty($this->secretKey) ? (substr($this->secretKey, 0, 10) . '...' . substr($this->secretKey, -5)) : 'NOT LOADED',
                    ],
                    'help' => 'Make sure BINANCE_PAY_API_KEY, BINANCE_PAY_SECRET_KEY, and BINANCE_PAY_ENDPOINT are in your .env file. After adding them, you may need to clear config cache: php artisan config:clear',
                ],
            ];
        }

        // Test by querying a non-existent order
        // If credentials are valid, we'll get a specific error about order not found
        // If credentials are invalid, we'll get an authentication error
        $testMerchantTradeNo = 'TEST_' . time();
        
        $timestamp = round(microtime(true) * 1000);
        $nonce = $this->generateNonce();
        
        $requestBody = [
            'merchantTradeNo' => $testMerchantTradeNo,
        ];

        $payload = json_encode($requestBody);
        $signature = $this->generateSignature($timestamp, $nonce, $payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'BinancePay-Timestamp' => $timestamp,
                'BinancePay-Nonce' => $nonce,
                'BinancePay-Certificate-SN' => $this->apiKey,
                'BinancePay-Signature' => $signature,
            ])->post($this->endpoint . 'v2/order/query', $requestBody);

            $responseData = $response->json();
            $statusCode = $response->status();

            // If we get a 200 response, credentials are valid (even if order doesn't exist)
            if ($response->successful()) {
                // Check the response status
                if (isset($responseData['status'])) {
                    // If status is SUCCESS or if we get an order not found error, credentials are valid
                    if ($responseData['status'] === 'SUCCESS' || 
                        (isset($responseData['code']) && str_contains($responseData['code'], 'ORDER_NOT_EXIST'))) {
                        return [
                            'success' => true,
                            'message' => 'Binance Pay credentials are valid and working!',
                            'details' => [
                                'api_key' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5), // Partial key for security
                                'endpoint' => $this->endpoint,
                                'response_status' => $responseData['status'] ?? 'N/A',
                                'http_status' => $statusCode,
                            ],
                        ];
                    }
                }
            }

            // Check for authentication errors
            $errorMessage = $responseData['errorMessage'] ?? $responseData['message'] ?? 'Unknown error';
            $errorCode = $responseData['code'] ?? null;
            
            // Extract IP from error message if present
            $requestIp = null;
            if (preg_match('/request ip: ([\d\.]+)/i', $errorMessage, $matches)) {
                $requestIp = $matches[1];
            }
            
            if ($statusCode === 401 || 
                str_contains($errorMessage, 'Certificate-SN') ||
                str_contains($errorMessage, 'Signature') ||
                str_contains($errorMessage, 'Unauthorized') ||
                str_contains($errorMessage, 'Invalid') ||
                $statusCode === 403) {
                
                // Check if it's an IP whitelist issue (code 400004)
                $isIpWhitelistIssue = ($errorCode === '400004' || str_contains($errorMessage, 'IP'));
                
                return [
                    'success' => false,
                    'error' => $isIpWhitelistIssue ? 'IP Address Not Whitelisted' : 'Binance Pay credentials are INVALID',
                    'message' => $isIpWhitelistIssue 
                        ? 'Your server IP address is not whitelisted in Binance Pay merchant dashboard'
                        : 'The API key or secret key is incorrect, OR you are using a regular Binance trading API key instead of a Binance Pay merchant API key',
                    'details' => [
                        'api_key' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                        'endpoint' => $this->endpoint,
                        'http_status' => $statusCode,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'request_ip' => $requestIp,
                        'full_response' => $responseData,
                    ],
                    'important_note' => '⚠️ CRITICAL: Binance Pay requires API keys from the Binance Pay MERCHANT DASHBOARD, NOT from the regular Binance trading API. These are two separate systems!',
                    'solution' => $isIpWhitelistIssue ? [
                        'step_1' => 'Log into Binance Pay Merchant Dashboard (NOT regular Binance)',
                        'step_2' => 'URL: https://merchant.binance.com/ or check your Binance Pay merchant portal',
                        'step_3' => 'Navigate to API Management / IP Whitelist settings',
                        'step_4' => 'Add your server IP address: ' . ($requestIp ?? 'Check error message for IP'),
                        'step_5' => 'Wait 2-5 minutes for changes to take effect',
                        'step_6' => 'Test again using /test/binance route',
                        'note' => 'If your server IP changes frequently, consider using a static IP or whitelisting your entire server IP range',
                    ] : [
                        'step_1' => '⚠️ VERIFY: Are you using Binance Pay Merchant API keys? (NOT regular Binance trading API keys)',
                        'step_2' => 'Log into Binance Pay Merchant Dashboard: https://merchant.binance.com/',
                        'step_3' => 'Create API keys from the Binance Pay merchant dashboard (not from regular Binance API management)',
                        'step_4' => 'Ensure the API key has "Process Payments" permission enabled',
                        'step_5' => 'Verify API key and secret key are copied correctly (no extra spaces)',
                        'step_6' => 'Check that IP whitelist is configured: ' . ($requestIp ?? 'your server IP'),
                        'step_7' => 'Wait 2-5 minutes after making changes before testing again',
                        'common_mistake' => '❌ Using regular Binance trading API keys (with Spot Trading, Margin Trading permissions) will NOT work with Binance Pay API',
                        'correct_way' => '✅ You MUST use Binance Pay Merchant API keys created from the merchant dashboard',
                    ],
                ];
            }

            // Other errors (might be valid credentials but other issues)
            return [
                'success' => false,
                'error' => 'Binance Pay API test failed',
                'message' => 'Credentials might be valid but test encountered an error',
                'details' => [
                    'api_key' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                    'endpoint' => $this->endpoint,
                    'http_status' => $statusCode,
                    'error_message' => $errorMessage,
                    'full_response' => $responseData,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Binance Pay Test Connection Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Connection test failed',
                'message' => $e->getMessage(),
                'details' => [
                    'api_key' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                    'endpoint' => $this->endpoint,
                ],
            ];
        }
    }

    /**
     * Generate signature for Binance Pay API
     */
    private function generateSignature($timestamp, $nonce, $payload)
    {
        $message = $timestamp . "\n" . $nonce . "\n" . $payload . "\n";
        return strtoupper(hash_hmac('sha512', $message, $this->secretKey));
    }
}

