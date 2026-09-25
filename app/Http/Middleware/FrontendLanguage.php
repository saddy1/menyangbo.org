<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FrontendLanguage
{
    public function handle(Request $request, Closure $next)
    {
        // Admin pages retain the application's existing language and behavior.
        if ($request->is('admin', 'admin/*', 'login/admin', 'sitemap.xml')) {
            return $next($request);
        }

        $locale = $request->query('lang');
        if (!in_array($locale, ['en', 'ne'], true)) {
            $locale = $request->session()->get('frontend_locale', 'ne');
        }
        $locale = in_array($locale, ['en', 'ne'], true) ? $locale : 'ne';
        $request->session()->put('frontend_locale', $locale);
        $request->attributes->set('frontend_locale', $locale);
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $response = $next($request);
        } finally {
            app()->setLocale($previousLocale);
        }
        $response->headers->set('Content-Language', $locale);
        $response->setVary('Cookie', false);

        return $response;
    }
}
