<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SofizPayCibService
{
    public function baseUrl(): string
    {
        return rtrim((string) config('services.sofizpay.base_url', 'https://sofizpay.com'), '/');
    }

    public function isSandbox(): bool
    {
        return (bool) config('services.sofizpay.sandbox', false);
    }

    public function merchantAccount(): string
    {
        return (string) config('services.sofizpay.merchant_account', '');
    }

    public function isConfigured(): bool
    {
        return $this->merchantAccount() !== '';
    }

    public function createPath(): string
    {
        return $this->isSandbox() ? '/sandbox/make-cib-transaction/' : '/make-cib-transaction/';
    }

    public function checkPath(): string
    {
        return $this->isSandbox() ? '/sandbox/cib-transaction-check/' : '/cib-transaction-check/';
    }

    /**
     * @param  array<string, mixed>  $queryParams
     * @return array{success: bool, data: array|null, raw: string|null, http_status: int|null}
     */
    public function createCibTransaction(array $queryParams): array
    {
        $url = $this->baseUrl() . $this->createPath();
        $timeout = (int) config('services.sofizpay.timeout', 30);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get($url, $queryParams);

            $body = $response->body();
            $decoded = $response->json();

            if (!is_array($decoded)) {
                Log::warning('SofizPay CIB create: non-JSON response', ['http_status' => $response->status(), 'snippet' => substr($body, 0, 500)]);

                return ['success' => false, 'data' => null, 'raw' => $body, 'http_status' => $response->status()];
            }

            $ok = $response->successful() && ($decoded['success'] ?? false) === true;

            return ['success' => $ok, 'data' => $decoded, 'raw' => $body, 'http_status' => $response->status()];
        } catch (\Throwable $e) {
            Log::error('SofizPay CIB create request failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'data' => null, 'raw' => null, 'http_status' => null];
        }
    }

    /**
     * @return array{success: bool, data: array|null, raw: string|null, http_status: int|null}
     */
    public function checkCibTransaction(string $orderNumber): array
    {
        $url = $this->baseUrl() . $this->checkPath();
        $timeout = (int) config('services.sofizpay.timeout', 30);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->get($url, ['order_number' => $orderNumber]);

            $body = $response->body();
            $decoded = $response->json();

            if (!is_array($decoded)) {
                Log::warning('SofizPay CIB check: non-JSON response', ['http_status' => $response->status(), 'snippet' => substr($body, 0, 500)]);

                return ['success' => false, 'data' => null, 'raw' => $body, 'http_status' => $response->status()];
            }

            $ok = $response->successful() && !isset($decoded['error']);

            return ['success' => $ok, 'data' => $decoded, 'raw' => $body, 'http_status' => $response->status()];
        } catch (\Throwable $e) {
            Log::error('SofizPay CIB check request failed', ['error' => $e->getMessage(), 'order_number' => $orderNumber]);

            return ['success' => false, 'data' => null, 'raw' => null, 'http_status' => null];
        }
    }

    /**
     * SofizPay CIB check success shape (from docs / live samples).
     *
     * @param  array<string, mixed>  $data
     */
    public function isPaidCheck(array $data): bool
    {
        $resp = (string) ($data['respCode'] ?? '');
        $err = $data['errorCode'] ?? null;
        $orderStatus = $data['orderStatus'] ?? null;

        $errOk = $err === 0 || $err === '0';
        $statusOk = $orderStatus === 2 || $orderStatus === '2';

        return $resp === '00' && $errOk && $statusOk;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function parsePaidAmountDzd(array $data): ?float
    {
        $raw = $data['Amount'] ?? $data['amount'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }

        return round((float) $raw, 2);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function parseDestinationAccount(array $data): ?string
    {
        $d = $data['destination_account'] ?? null;

        return $d ? (string) $d : null;
    }
}
