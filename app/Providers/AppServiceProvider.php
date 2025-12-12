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
        
        // Set language path early in the registration phase
        $this->app->useLangPath(base_path('lang'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure language path is set (fallback)
        if ($this->app->make('path.lang') !== base_path('lang')) {
            $this->app->useLangPath(base_path('lang'));
        }
        // storage_public_url helper is declared in global namespace
        // Register console commands (reconcile digiflazz statuses)
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ReconcileDigiflazzStatuses::class,
            ]);
        }
    }
}
