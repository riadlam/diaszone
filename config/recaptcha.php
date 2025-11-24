<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | Site Key: Public key used in the frontend (visible to users)
    | Secret Key: Private key used for server-side verification (never expose)
    |
    | Add these to your .env file:
    | RECAPTCHA_SITE_KEY=your_site_key_here
    | RECAPTCHA_SECRET_KEY=your_secret_key_here
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
];

