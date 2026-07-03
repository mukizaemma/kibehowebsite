<?php

namespace App\Http\Middleware;

use App\Support\SiteLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SiteLocale::translationsEnabled()) {
            app()->setLocale(SiteLocale::DEFAULT);
            session(['site_locale' => SiteLocale::DEFAULT]);

            if ($request->route('locale') === SiteLocale::FRENCH) {
                $path = $request->path();
                $stripped = preg_replace('#^fr/?#', '', $path) ?? '';

                return redirect('/'.ltrim($stripped, '/'));
            }

            URL::defaults(['locale' => null]);

            return $next($request);
        }

        $routeLocale = $request->route('locale');
        $locale = SiteLocale::normalize($routeLocale ?? session('site_locale', SiteLocale::DEFAULT));

        if ($routeLocale === SiteLocale::FRENCH) {
            session(['site_locale' => SiteLocale::FRENCH]);
        } elseif ($routeLocale === null) {
            session(['site_locale' => SiteLocale::DEFAULT]);
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale === SiteLocale::FRENCH ? SiteLocale::FRENCH : null]);

        return $next($request);
    }
}
