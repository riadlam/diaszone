<?php

namespace App\Http\Middleware;

use App\Services\SitePauseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PausePublicSite
{
    public function __construct(private SitePauseService $pause) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->pause->isPaused() || $this->shouldAllow($request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'paused' => true,
                'message' => 'DiasZone is temporarily paused for maintenance. Please try again soon.',
            ], 503);
        }

        return response()
            ->view('pages.paused', [
                'status' => $this->pause->status(),
            ], 503)
            ->header('Retry-After', '300');
    }

    private function shouldAllow(Request $request): bool
    {
        if ($request->is('admin', 'admin/*', 'livewire/*', 'up')) {
            return true;
        }

        if ($request->is('webhook', 'webhook/*')) {
            return true;
        }

        // Filament / Livewire assets
        if ($request->is('filament/*', 'vendor/filament/*', 'css/*', 'js/*', 'build/*')) {
            return true;
        }

        return false;
    }
}
