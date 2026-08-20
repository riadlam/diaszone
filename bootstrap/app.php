<?php

use App\Http\Controllers\PublicMediaController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // No session/cookies — static uploads via PublicMediaController.
            Route::get('/media/{path}', [PublicMediaController::class, 'show'])
                ->where('path', '.+')
                ->name('media.show');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'seller' => \App\Http\Middleware\SellerMiddleware::class,
        ]);
        
        // Add language middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
        ]);
        
        // Exclude webhook / public JSON API routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhook/baridimob',
            'webhook/mixpay',
            'webhook/nowpayments',
            'webhook/vipreseller',
            'webhook/telegram',
            'webhook/digiflazz',
            'api/validate-nickname',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Illuminate\Session\TokenMismatchException $e) {
            \Illuminate\Support\Facades\Log::warning('CSRF token mismatch', [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'path' => request()->path(),
            ]);
        });
    })->create();
