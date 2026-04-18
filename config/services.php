<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'digiflazz' => [
        'username' => env('DIGIFLAZZ_USERNAME'),
        'sign' => env('DIGIFLAZZ_SIGN'),
        'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
    ],

    'nowpayments' => [
        'api_key' => env('NOWPAYMENTS_API_KEY'),
        'endpoint' => env('NOWPAYMENTS_ENDPOINT', 'https://api.nowpayments.io/v1/'),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),
    ],

    'item4gamer' => [
        'api_key' => env('ITEM4GAMER_API_KEY'),
        'base_url' => env('ITEM4GAMER_BASE_URL', 'https://item4gamer.com/wp-json/reseller/v1'),
    ],

    'sofizpay' => [
        'enabled' => env('SOFIZPAY_ENABLED', true),
        'sandbox' => env('SOFIZPAY_SANDBOX', false),
        'base_url' => env('SOFIZPAY_BASE_URL', 'https://sofizpay.com'),
        'merchant_account' => env('SOFIZPAY_MERCHANT_ACCOUNT'),
        'timeout' => (int) env('SOFIZPAY_TIMEOUT', 30),
        // Use "no" for server-side create: SofizPay returns JSON with payment_url. "yes" can 302 to SATIM HTML and breaks Laravel Http::get().
        'redirect' => env('SOFIZPAY_REDIRECT', 'no'),
        'keep_return_url' => env('SOFIZPAY_KEEP_RETURN_URL', 'True'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim((string) env('APP_URL', 'http://localhost'), '/').'/auth/google/callback'),
    ],

];
