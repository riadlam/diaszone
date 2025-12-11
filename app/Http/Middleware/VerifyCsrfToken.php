<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * We exclude webhook endpoints so external services can POST without a CSRF token.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/webhook/digiflazz',
        '/webhook/nowpayments',
        '/webhook/mixpay',
        '/webhook/baridimob',
        '/webhook/vipreseller',
    ];
}
