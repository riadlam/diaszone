<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load application helpers (global functions)
        $helpers = __DIR__ . '/../helpers.php';
        if (file_exists($helpers)) require_once $helpers;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->useLangPath(base_path('lang'));
        // storage_public_url helper is declared in global namespace
        // Register console commands (reconcile digiflazz statuses)
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ReconcileDigiflazzStatuses::class,
            ]);
        }
    }
}
