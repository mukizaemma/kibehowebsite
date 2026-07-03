<?php

namespace App\Http\Controllers;

use App\Support\SiteLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        $locale = SiteLocale::normalize($locale);
        session(['site_locale' => $locale]);

        $target = $request->query('redirect', '/');
        if (! is_string($target) || ! str_starts_with($target, '/')) {
            $target = '/';
        }

        if ($locale === SiteLocale::FRENCH && SiteLocale::translationsEnabled()) {
            if (! str_starts_with(ltrim($target, '/'), 'fr/') && ltrim($target, '/') !== 'fr') {
                $target = '/fr'.($target === '/' ? '' : $target);
            }
        } else {
            $target = preg_replace('#^/fr(?=/|$)#', '', $target) ?: '/';
        }

        return redirect($target);
    }
}
