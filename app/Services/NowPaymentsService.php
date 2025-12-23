<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NowPaymentsService
{
    private $apiKey;
    private $endpoint;

    public function __construct($apiKey = null, $endpoint = null)
    {
        // Allow passing credentials directly (for testing or override)
        if ($apiKey !== null) {
            $this->apiKey = $apiKey;
            $this->endpoint = $endpoint ?? 'https://api.nowpayments.io/v1/';
        } else {
            // Read directly from env() first (prioritize .env file)
            // Then fall back to config (for production with cached config)
            $this->apiKey = env('NOWPAYMENTS_API_KEY') ?? config('services.nowpayments.api_key');
            $this->endpoint = env('NOWPAYMENTS_ENDPOINT') ?? config('services.nowpayments.endpoint') ?? 'https://api.nowpayments.io/v1/';
        }
    }
    
    /**
     * Check if credentials are configured
     */
    public function hasCredentials()
    {
        return !empty($this->apiKey);
    }

    /**
     * Create a NOWPayments invoice/payment
     */
    public function createPayment($orderData)
    {
        try {
            // NOWPayments requires price_currency to match a supported fiat currency
            // Common supported currencies: usd, eur, btc, eth
            // If pay_currency is not specified, NOWPayments will allow user to choose
            $requestBody = [
                'price_amount' => $orderData['amount'],
                'price_currency' => $orderData['price_currency'] ?? 'usd',
                'ipn_callback_url' => $orderData['ipn_callback_url'] ?? route('nowpayments.webhook'),
                'order_id' => $orderData['order_id'] ?? $orderData['merchant_trade_no'],
                'order_description' => $orderData['goods_name'] ?? 'Mobile Legends Diamonds',
                'success_url' => $orderData['return_url'] ?? route('crypto-payment-success'),
                'cancel_url' => $orderData['cancel_url'] ?? route('home'),
            ];
            
            // pay_currency is required by NOWPayments
            // Use the specified currency or default to usdttrc20 (USDT on TRC20 network)
            $requestBody['pay_currency'] = $orderData['pay_currency'] ?? 'usdttrc20';

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->endpoint . 'payment', $requestBody);

            $responseData = $response->json();
            
            if ($response->successful()) {
                if (isset($responseData['payment_id'])) {
                    return [
                        'success' => true,
                        'data' => $responseData,
                    ];
                }
            }

            // Log detailed error for debugging
            Log::error('NOWPayments API Error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'response_data' => $responseData,
            ]);

            // Extract error message from response
            $errorMessage = 'Failed to create NOWPayments payment';
            if (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['error'])) {
                $errorMessage = $responseData['error'];
            } elseif (isset($responseData['code'])) {
                $errorMessage = 'NOWPayments API Error: ' . $responseData['code'];
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
            Log::error('NOWPayments Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a NOWPayments invoice (returns invoice_url for payment page)
     */
    public function createInvoice($orderData)
    {
        try {
            $requestBody = [
                'price_amount' => $orderData['amount'],
                'price_currency' => $orderData['price_currency'] ?? 'usd',
                'ipn_callback_url' => $orderData['ipn_callback_url'] ?? route('nowpayments.webhook'),
                'order_id' => $orderData['order_id'] ?? $orderData['merchant_trade_no'],
                'order_description' => $orderData['goods_name'] ?? 'Mobile Legends Diamonds',
                'success_url' => $orderData['return_url'] ?? route('crypto-payment-success'),
                'cancel_url' => $orderData['cancel_url'] ?? route('home'),
            ];
            
            // pay_currency is optional for invoices - user can choose on the invoice page
            if (isset($orderData['pay_currency'])) {
                $requestBody['pay_currency'] = $orderData['pay_currency'];
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->endpoint . 'invoice', $requestBody);

            $responseData = $response->json();
            
            if ($response->successful()) {
                if (isset($responseData['invoice_id']) || isset($responseData['invoice_url'])) {
                    return [
                        'success' => true,
                        'data' => $responseData,
                    ];
                }
            }

            // Log detailed error for debugging
            Log::error('NOWPayments Invoice API Error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'response_data' => $responseData,
            ]);

            // Extract error message from response
            $errorMessage = 'Failed to create NOWPayments invoice';
            if (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['error'])) {
                $errorMessage = $responseData['error'];
            } elseif (isset($responseData['code'])) {
                $errorMessage = 'NOWPayments API Error: ' . $responseData['code'];
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
            Log::error('NOWPayments Invoice Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->endpoint . 'payment/' . $paymentId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to get payment status',
            ];
        } catch (\Exception $e) {
            Log::error('NOWPayments Get Status Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available currencies
     */
    public function getAvailableCurrencies()
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->endpoint . 'currencies');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to get currencies',
            ];
        } catch (\Exception $e) {
            Log::error('NOWPayments Get Currencies Exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test NOWPayments API connection and credentials
     */
    public function testConnection()
    {
        // Get raw env values for debugging
        $rawApiKey = env('NOWPAYMENTS_API_KEY');
        $rawEndpoint = env('NOWPAYMENTS_ENDPOINT');
        
        if (!$this->hasCredentials()) {
            return [
                'success' => false,
                'error' => 'NOWPayments API key is not configured',
                'details' => [
                    'api_key_configured' => !empty($this->apiKey),
                    'endpoint' => $this->endpoint,
                    'debug' => [
                        'raw_env_api_key' => !empty($rawApiKey) ? (substr($rawApiKey, 0, 10) . '...' . substr($rawApiKey, -5)) : 'NOT FOUND',
                        'raw_env_endpoint' => $rawEndpoint ?? 'NOT FOUND',
                        'loaded_api_key' => !empty($this->apiKey) ? (substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5)) : 'NOT LOADED',
                    ],
                    'help' => 'Make sure NOWPAYMENTS_API_KEY is in your .env file. After adding it, you may need to clear config cache: php artisan config:clear',
                ],
            ];
        }

        // Test by getting available currencies (this endpoint requires valid API key)
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->endpoint . 'currencies');

            $responseData = $response->json();
            $statusCode = $response->status();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'NOWPayments API credentials are valid and working!',
                    'details' => [
                        'api_key' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                        'endpoint' => $this->endpoint,
                        'http_status' => $statusCode,
                        'response_data' => $responseData,
                    ],
                ];
            }

            // Check for authentication errors
            $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'Unknown error';
            
            if ($statusCode === 401 || $statusCode === 403) {
                return [
                    'success' => false,
                    'error' => 'NOWPayments API key is INVALID',
                    'message' => 'The API key is incorrect or expired',
                    'details' => [
                        'api_key' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -5),
                        'endpoint' => $this->endpoint,
                        'http_status' => $statusCode,
                        'error_message' => $errorMessage,
                        'full_response' => $responseData,
                    ],
                    'solution' => [
                        'step_1' => 'Log into your NOWPayments dashboard',
                        'step_2' => 'Navigate to API Settings',
                        'step_3' => 'Copy your API key',
                        'step_4' => 'Add NOWPAYMENTS_API_KEY to your .env file',
                        'step_5' => 'Wait a few minutes for changes to take effect',
                        'step_6' => 'Test again using /test/nowpayments route',
                    ],
                ];
            }

            // Other errors
            return [
                'success' => false,
                'error' => 'NOWPayments API test failed',
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
            Log::error('NOWPayments Test Connection Exception', ['error' => $e->getMessage()]);
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
}

