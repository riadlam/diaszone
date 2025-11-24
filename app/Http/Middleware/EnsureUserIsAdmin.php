<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access the admin panel.');
        }

        $user = Auth::user();

        // Check if user is admin
        if (!$user->isAdmin()) {
            // Log unauthorized access attempt
            \Illuminate\Support\Facades\Log::warning('Unauthorized admin access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
            
            return redirect()->route('home')->with('error', 'You do not have permission to access the admin panel.');
        }

        // Check if user is active
        if (!$user->isActive()) {
            \Illuminate\Support\Facades\Log::warning('Inactive admin user access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => $user->status,
                'ip' => $request->ip(),
            ]);
            
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account is inactive. Please contact support.');
        }

        // Log admin access (for security auditing)
        \Illuminate\Support\Facades\Log::info('Admin dashboard access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'url' => $request->path(),
        ]);

        return $next($request);
    }
}
