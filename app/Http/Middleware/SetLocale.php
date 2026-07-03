<?php

namespace App\Http\Middleware;

use App\Support\SiteLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        $isFrenchPath = $path === 'fr' || str_starts_with($path, 'fr/');

        if (! SiteLocale::translationsEnabled()) {
            app()->setLocale(SiteLocale::DEFAULT);
            session(['site_locale' => SiteLocale::DEFAULT]);

            if ($isFrenchPath) {
                $stripped = preg_replace('#^fr/?#', '', $path) ?? '';

                return redirect('/'.ltrim($stripped, '/'));
            }

            return $next($request);
        }

        if ($isFrenchPath) {
            session(['site_locale' => SiteLocale::FRENCH]);
            app()->setLocale(SiteLocale::FRENCH);
        } else {
            session(['site_locale' => SiteLocale::DEFAULT]);
            app()->setLocale(SiteLocale::DEFAULT);
        }

        return $next($request);
    }
}
