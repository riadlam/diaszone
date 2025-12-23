<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('seller')->check()) {
            return redirect()->route('seller.login');
        }

        $seller = Auth::guard('seller')->user();

        // Check if seller is active
        if (!$seller->isActive()) {
            Auth::guard('seller')->logout();
            return redirect()->route('seller.login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        return $next($request);
    }
}
