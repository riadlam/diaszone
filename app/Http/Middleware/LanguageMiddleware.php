<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get available locales
        $availableLocales = ['en', 'fr', 'ar'];
        
        // Check if locale is in session, otherwise use default
        $locale = Session::get('locale', config('app.locale', 'en'));
        
        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            $locale = 'en';
        }
        
        // Set the application locale
        App::setLocale($locale);
        
        return $next($request);
    }
}
