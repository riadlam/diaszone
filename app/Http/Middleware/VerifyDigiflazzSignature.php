<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyDigiflazzSignature
{
    /**
     * Handle an incoming request.
     * Validates X-Hub-Signature (HMAC-SHA1) using DIGIFLAZZ_WEBHOOK_SECRET.
     * Enforces JSON content-type and limits request size to avoid abuse.
     */
    public function handle(Request $request, Closure $next)
    {
        $secret = env('DIGIFLAZZ_WEBHOOK_SECRET');

        // If secret not configured, allow local dev but reject in non-local envs
        if (empty($secret)) {
            if (!app()->isLocal()) {
                Log::warning('Digiflazz webhook received but DIGIFLAZZ_WEBHOOK_SECRET is not configured');
                return response()->json(['error' => 'Webhook secret not configured'], 403);
            }
            // In local environment, allow but log a notice
            Log::info('Digiflazz webhook secret not set (local dev). Signature check skipped.');
            return $next($request);
        }

        // Check content type
        $contentType = $request->header('Content-Type', '');
        if (stripos($contentType, 'application/json') === false) {
            Log::warning('Digiflazz webhook invalid content type', ['content_type' => $contentType]);
            return response()->json(['error' => 'Invalid content type'], 400);
        }

        // Size limit (256 KB)
        $raw = $request->getContent();
        if (strlen($raw) > 256 * 1024) {
            Log::warning('Digiflazz webhook payload too large', ['size' => strlen($raw)]);
            return response()->json(['error' => 'Payload too large'], 413);
        }

        $signatureHeader = $request->header('X-Hub-Signature');
        if (empty($signatureHeader)) {
            Log::warning('Digiflazz webhook missing X-Hub-Signature header');
            return response()->json(['error' => 'Missing signature'], 403);
        }

        $expected = 'sha1=' . hash_hmac('sha1', $raw, $secret);
        if (!hash_equals($expected, $signatureHeader)) {
            Log::warning('Digiflazz webhook signature mismatch', ['header' => $signatureHeader, 'expected' => $expected]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
