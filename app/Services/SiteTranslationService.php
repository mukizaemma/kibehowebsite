<?php

namespace App\Services;

use App\Models\SiteTranslationOverride;
use App\Support\SiteLocale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

class SiteTranslationService
{
    private const CACHE_VERSION = 'v1';

    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = SiteLocale::normalize($locale ?? app()->getLocale());
        $cacheKey = self::CACHE_VERSION.'.site_trans.'.$locale.'.'.$key;

        $value = Cache::remember($cacheKey, 3600, function () use ($key, $locale) {
            $override = SiteTranslationOverride::query()
                ->where('key', $key)
                ->where('locale', $locale)
                ->value('value');

            if ($override !== null && $override !== '') {
                return $override;
            }

            $fromFile = Lang::get('site.'.$key, [], $locale);
            if ($fromFile !== 'site.'.$key) {
                return (string) $fromFile;
            }

            if ($locale !== SiteLocale::DEFAULT) {
                $fallback = Lang::get('site.'.$key, [], SiteLocale::DEFAULT);
                if ($fallback !== 'site.'.$key) {
                    return (string) $fallback;
                }
            }

            return str_replace('_', ' ', str_replace('.', ' ', $key));
        });

        if ($replace !== []) {
            foreach ($replace as $search => $replacement) {
                $value = str_replace(':'.$search, (string) $replacement, $value);
            }
        }

        return $value;
    }

    public function setOverride(string $key, string $locale, ?string $value): void
    {
        $locale = SiteLocale::normalize($locale);

        if ($value === null || trim($value) === '') {
            SiteTranslationOverride::query()
                ->where('key', $key)
                ->where('locale', $locale)
                ->delete();
            $this->forgetKey($key, $locale);

            return;
        }

        SiteTranslationOverride::query()->updateOrCreate(
            ['key' => $key, 'locale' => $locale],
            ['value' => $value]
        );

        $this->forgetKey($key, $locale);
    }

    /** @return array<int, array{key: string, group: string, label: string, en: string, fr: string, en_override: bool, fr_override: bool}> */
    public function adminRows(?string $groupFilter = null, ?string $search = null): array
    {
        $keys = $this->allKeys();
        $overrides = SiteTranslationOverride::query()->get()->groupBy('key');

        $rows = [];
        foreach ($keys as $key) {
            $group = explode('.', $key, 2)[0];
            if ($groupFilter && $groupFilter !== $group) {
                continue;
            }

            $label = str_replace('_', ' ', explode('.', $key, 2)[1] ?? $key);
            $enOverride = $overrides->get($key)?->firstWhere('locale', SiteLocale::DEFAULT);
            $frOverride = $overrides->get($key)?->firstWhere('locale', SiteLocale::FRENCH);

            $enDefault = Lang::get('site.'.$key, [], SiteLocale::DEFAULT);
            $frDefault = Lang::get('site.'.$key, [], SiteLocale::FRENCH);

            $row = [
                'key' => $key,
                'group' => $group,
                'label' => ucfirst($label),
                'en' => $enOverride?->value ?? (is_string($enDefault) && $enDefault !== 'site.'.$key ? $enDefault : ''),
                'fr' => $frOverride?->value ?? (is_string($frDefault) && $frDefault !== 'site.'.$key ? $frDefault : ''),
                'en_override' => $enOverride !== null,
                'fr_override' => $frOverride !== null,
                'en_default' => is_string($enDefault) && $enDefault !== 'site.'.$key ? $enDefault : '',
                'fr_default' => is_string($frDefault) && $frDefault !== 'site.'.$key ? $frDefault : '',
            ];

            if ($search) {
                $haystack = strtolower($key.' '.$row['label'].' '.$row['en'].' '.$row['fr']);
                if (! str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /** @return list<string> */
    public function groups(): array
    {
        return collect($this->allKeys())
            ->map(fn ($key) => explode('.', $key, 2)[0])
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** @return list<string> */
    public function allKeys(): array
    {
        $flatten = function (array $items, string $prefix = '') use (&$flatten): array {
            $keys = [];
            foreach ($items as $key => $value) {
                $full = $prefix === '' ? $key : $prefix.'.'.$key;
                if (is_array($value)) {
                    $keys = array_merge($keys, $flatten($value, $full));
                } else {
                    $keys[] = $full;
                }
            }

            return $keys;
        };

        $en = Lang::get('site', [], SiteLocale::DEFAULT);

        return $flatten(is_array($en) ? $en : []);
    }

    public function missingFrenchCount(): int
    {
        return collect($this->adminRows())
            ->filter(fn (array $row) => trim($row['fr']) === '')
            ->count();
    }

    public function forgetAll(): void
    {
        foreach (SiteLocale::supported() as $locale) {
            foreach ($this->allKeys() as $key) {
                $this->forgetKey($key, $locale);
            }
        }
    }

    public function forgetKey(string $key, string $locale): void
    {
        Cache::forget(self::CACHE_VERSION.'.site_trans.'.$locale.'.'.$key);
    }
}
