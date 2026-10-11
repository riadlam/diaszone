<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /** @var list<string> */
    private const AVAILABLE = ['en', 'fr', 'ar'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Manual switch (or previous visit) always wins.
        if (Session::has('locale')) {
            $locale = (string) Session::get('locale');
        } else {
            // First visit: follow phone/browser language settings.
            $locale = $this->detectFromRequest($request);
            Session::put('locale', $locale);
        }

        if (! in_array($locale, self::AVAILABLE, true)) {
            $locale = config('app.locale', 'en');
            Session::put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }

    private function detectFromRequest(Request $request): string
    {
        // Prefer Laravel's negotiated Accept-Language match.
        $preferred = $request->getPreferredLanguage(self::AVAILABLE);
        if (is_string($preferred) && in_array($preferred, self::AVAILABLE, true)) {
            return $preferred;
        }

        $header = strtolower((string) $request->header('Accept-Language', ''));
        if ($header === '') {
            return config('app.locale', 'en');
        }

        // e.g. "fr-DZ,fr;q=0.9,ar;q=0.8,en;q=0.7"
        foreach (explode(',', $header) as $part) {
            $tag = trim(explode(';', $part)[0] ?? '');
            if ($tag === '') {
                continue;
            }
            $primary = strtolower(substr($tag, 0, 2));
            if (in_array($primary, self::AVAILABLE, true)) {
                return $primary;
            }
        }

        return config('app.locale', 'en');
    }
}
