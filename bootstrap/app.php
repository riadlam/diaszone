<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
        
        // Add language middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);
        
        // Exclude webhook routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhook/baridimob',
            'webhook/mixpay',
            'webhook/nowpayments',
            'webhook/vipreseller',
            'webhook/telegram',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
