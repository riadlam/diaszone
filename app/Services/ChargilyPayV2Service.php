<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChargilyPayV2Service
{
    private $apiSecret;
    private $baseUrl;
    private $isLiveMode;

    public function __construct($apiSecret = null, $isLiveMode = false)
    {
        if ($apiSecret !== null) {
            $this->apiSecret = $apiSecret;
            $this->isLiveMode = $isLiveMode;
        } else {
            // Read from config file (works with cached config) - try both new and old variable names
            // The config file reads from .env, so this will work even when config is cached
            $v2Secret = config('services.chargily_pay_v2.secret');
            $epaySecret = config('laravel-chargily-epay.secret');
            $this->apiSecret = $v2Secret ?? $epaySecret;
            $this->isLiveMode = config('services.chargily_pay_v2.live_mode', false);
            
            // Debug logging (without exposing full secret)
            if (empty($this->apiSecret)) {
                Log::warning('Chargily Pay v2: No secret found', [
                    'CHARGILY_PAY_V2_SECRET_exists' => !empty($v2Secret),
                    'CHARGILY_EPAY_SECRET_exists' => !empty($epaySecret),
                    'config_secret_exists' => !empty(config('laravel-chargily-epay.secret')),
                    'v2_secret_length' => $v2Secret ? strlen($v2Secret) : 0,
                    'epay_secret_length' => $epaySecret ? strlen($epaySecret) : 0,
                ]);
            } else {
                Log::info('Chargily Pay v2: Secret loaded', [
                    'secret_length' => strlen($this->apiSecret),
                    'secret_preview' => substr($this->apiSecret, 0, 10) . '...' . substr($this->apiSecret, -5),
                    'source' => $v2Secret ? 'CHARGILY_PAY_V2_SECRET' : 'CHARGILY_EPAY_SECRET',
                ]);
            }
        }
        
        // Base URL for Chargily Pay v2 API
        // Use pay.chargily.net (more reliable DNS resolution)
        $this->baseUrl = env('CHARGILY_PAY_V2_BASE_URL', 'https://pay.chargily.net/api/v2/');
    }
    
    /**
     * Check if credentials are configured
     */
    public function hasCredentials()
    {
        return !empty($this->apiSecret);
    }
    
    /**
     * Create a checkout
     * 
     * @param array $checkoutData
     * @return array
     */
    public function createCheckout($checkoutData)
    {
        try {
            $requestBody = [
                'amount' => $checkoutData['amount'], // Amount in DZD (Chargily Pay v2 expects DZD, not centimes)
                'currency' => $checkoutData['currency'] ?? 'dzd',
                'payment_method' => $checkoutData['payment_method'] ?? 'edahabia', // edahabia, cib, or chargily_app
                'success_url' => $checkoutData['success_url'],
            ];
            
            // Optional fields
            if (isset($checkoutData['description']) && !empty($checkoutData['description'])) {
                $requestBody['description'] = $checkoutData['description'];
            }
            
            if (isset($checkoutData['failure_url']) && !empty($checkoutData['failure_url'])) {
                $requestBody['failure_url'] = $checkoutData['failure_url'];
            }
            
            if (isset($checkoutData['webhook_endpoint']) && !empty($checkoutData['webhook_endpoint'])) {
                $requestBody['webhook_endpoint'] = $checkoutData['webhook_endpoint'];
            }
            
            if (isset($checkoutData['locale']) && !empty($checkoutData['locale'])) {
                $requestBody['locale'] = $checkoutData['locale']; // ar, en, or fr
            }
            
            if (isset($checkoutData['customer_id']) && !empty($checkoutData['customer_id'])) {
                $requestBody['customer_id'] = $checkoutData['customer_id'];
            }
            
            if (isset($checkoutData['metadata']) && !empty($checkoutData['metadata'])) {
                $requestBody['metadata'] = $checkoutData['metadata'];
            }
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiSecret,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . 'checkouts', $requestBody);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                if (isset($responseData['checkout_url'])) {
                    return [
                        'success' => true,
                        'data' => $responseData,
                        'checkout_url' => $responseData['checkout_url'],
                        'checkout_id' => $responseData['id'] ?? null,
                    ];
                }
            }
            
            // Log detailed error for debugging
            Log::error('Chargily Pay v2 API Error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'response_data' => $responseData,
                'request_body' => $requestBody,
                'endpoint' => $this->baseUrl . 'checkouts',
            ]);
            
            $errorMessage = 'Failed to create Chargily Pay checkout';
            if (isset($responseData['message'])) {
                $errorMessage = $responseData['message'];
            } elseif (isset($responseData['error'])) {
                $errorMessage = $responseData['error'];
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'response_data' => $responseData,
                'http_status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Chargily Pay v2 Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Chargily Pay v2 service error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve a checkout by ID
     * 
     * @param string $checkoutId
     * @return array
     */
    public function retrieveCheckout($checkoutId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiSecret,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . 'checkouts/' . $checkoutId);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            }
            
            Log::error('Chargily Pay v2 Retrieve Checkout Error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'response_data' => $responseData,
                'checkout_id' => $checkoutId,
            ]);
            
            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to retrieve checkout',
                'response_data' => $responseData,
                'http_status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Chargily Pay v2 Retrieve Checkout Exception', [
                'error' => $e->getMessage(),
                'checkout_id' => $checkoutId,
            ]);
            
            return [
                'success' => false,
                'error' => 'Chargily Pay v2 service error: ' . $e->getMessage(),
            ];
        }
    }
}

