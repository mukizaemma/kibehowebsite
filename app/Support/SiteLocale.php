<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SiteLocale
{
    public const DEFAULT = 'en';

    public const FRENCH = 'fr';

    /** @return list<string> */
    public static function supported(): array
    {
        return [self::DEFAULT, self::FRENCH];
    }

    public static function translationsEnabled(): bool
    {
        return Cache::remember('site.translations_enabled', 300, function () {
            $setting = Setting::query()->select('translations_enabled')->first();

            return (bool) ($setting?->translations_enabled ?? false);
        });
    }

    public static function forgetEnabledCache(): void
    {
        Cache::forget('site.translations_enabled');
    }

    public static function isFrench(string $locale): bool
    {
        return $locale === self::FRENCH;
    }

    public static function normalize(?string $locale): string
    {
        $locale = strtolower((string) $locale);

        if ($locale === self::FRENCH && self::translationsEnabled()) {
            return self::FRENCH;
        }

        return self::DEFAULT;
    }
}
