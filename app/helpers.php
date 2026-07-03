<?php

declare(strict_types=1);

if (! function_exists('hotel_price')) {
    /**
     * Format a room or site price using the hotel's currency setting (USD or RWF).
     */
    function hotel_price(mixed $value, ?object $setting): string
    {
        $amount = (float) ($value ?? 0);
        $currency = strtolower((string) ($setting?->price_currency ?? 'usd'));
        if ($currency === 'rwf') {
            return 'RWF ' . number_format($amount, 0);
        }

        return '$' . number_format($amount, 0);
    }
}

if (! function_exists('booking_channel_enabled')) {
    function booking_channel_enabled(): bool
    {
        return \App\Support\BookingChannel::enabled();
    }
}

if (! function_exists('hotel_reservation_url_configured')) {
    /** Reservation URL from settings / env (ignores enable flag). */
    function hotel_reservation_url_configured(?object $setting = null): ?string
    {
        $setting = $setting ?? \App\Models\Setting::first();
        $fromSetting = trim((string) ($setting?->linktree ?? ''));
        if ($fromSetting !== '') {
            return $fromSetting;
        }

        $channels = \App\Support\HotelChannels::all();
        $fromChannels = trim((string) ($channels['reservation_url'] ?? ''));

        return $fromChannels !== '' ? $fromChannels : null;
    }
}

if (! function_exists('hotel_reservation_url')) {
    /** Active reservation URL — only when the booking channel is enabled and configured. */
    function hotel_reservation_url(?object $setting = null): ?string
    {
        if (! booking_channel_enabled()) {
            return null;
        }

        return hotel_reservation_url_configured($setting);
    }
}

if (! function_exists('site_meta_description')) {
    function site_meta_description(?object $setting = null): string
    {
        $setting = $setting ?? \App\Models\Setting::first();
        $description = trim((string) ($setting?->meta_description ?? ''));
        if ($description !== '') {
            return \Illuminate\Support\Str::limit($description, 320);
        }

        $quote = trim(strip_tags((string) ($setting?->quote ?? '')));
        if ($quote !== '') {
            return \Illuminate\Support\Str::limit($quote, 320);
        }

        return trim((string) ($setting?->company ?? ''));
    }
}

if (! function_exists('site_hotel_schema')) {
    /**
     * @return array<string, mixed>
     */
    function site_hotel_schema(?object $setting = null): array
    {
        $setting = $setting ?? \App\Models\Setting::first();
        $contact = \App\Models\HotelContact::first();
        $logoPath = trim((string) ($setting?->logo ?? ''));
        $logoUrl = $logoPath !== ''
            ? asset('storage/images'.$logoPath)
            : asset('assets/images/brand/kibeho-magnificat-logo.png');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Hotel',
            'name' => $setting?->company ?: config('app.name'),
            'url' => url('/'),
            'image' => $logoUrl,
            'logo' => $logoUrl,
        ];

        $address = trim((string) ($setting?->address ?? $contact?->address ?? ''));
        if ($address !== '') {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
                'addressLocality' => $contact?->city ?? null,
                'addressCountry' => $contact?->country ?? 'RW',
            ];
        }

        $phone = hotel_public_phone($setting);
        if ($phone) {
            $schema['telephone'] = $phone;
        }

        $email = trim((string) ($setting?->email ?? $contact?->email ?? ''));
        if ($email !== '') {
            $schema['email'] = $email;
        }

        if (! empty($setting?->star_rating)) {
            $schema['starRating'] = [
                '@type' => 'Rating',
                'ratingValue' => (int) $setting->star_rating,
            ];
        }

        $reservationUrl = hotel_reservation_url_configured($setting);
        if ($reservationUrl) {
            $schema['potentialAction'] = [
                '@type' => 'ReserveAction',
                'target' => $reservationUrl,
            ];
        }

        return array_filter($schema, fn ($value) => $value !== null && $value !== '');
    }
}

if (! function_exists('slideshow_cta_label')) {
    function slideshow_cta_label(?object $setting = null): string
    {
        $setting = $setting ?? \App\Models\Setting::first();
        $label = trim((string) ($setting?->slideshow_cta_label ?? ''));

        return $label !== '' ? $label : 'Book Now';
    }
}

if (! function_exists('hotel_book_now_url')) {
    /** External booking URL when channel is active, otherwise the contact page. */
    function hotel_book_now_url(?object $setting = null): string
    {
        $url = hotel_reservation_url($setting);

        return filled($url) ? $url : localized_route('contact');
    }
}

if (! function_exists('hotel_book_now_is_external')) {
    function hotel_book_now_is_external(?object $setting = null): bool
    {
        return booking_channel_enabled() && filled(hotel_reservation_url_configured($setting));
    }
}

if (! function_exists('hotel_public_phone')) {
    function hotel_public_phone(?object $setting = null): ?string
    {
        $setting = $setting ?? \App\Models\Setting::first();
        $reception = trim((string) ($setting?->reception_phone ?? ''));
        if ($reception !== '') {
            return $reception;
        }

        $contact = \App\Models\HotelContact::first();
        $fromContact = trim((string) ($contact?->phone ?? ''));
        if ($fromContact !== '') {
            return $fromContact;
        }

        $main = trim((string) ($setting?->phone ?? ''));

        return $main !== '' ? $main : null;
    }
}

if (! function_exists('price_currency_label')) {
    /**
     * Short label for admin room/pricing forms (Settings → website price currency).
     */
    function price_currency_label(?object $setting): string
    {
        return strtolower((string) ($setting?->price_currency ?? 'usd')) === 'rwf'
            ? 'RWF'
            : 'USD ($)';
    }
}

if (! function_exists('terms_content_html')) {
    /**
     * Public Terms page: render CMS HTML from Summernote, or upgrade legacy plain-text
     * (pasted/textarea) into paragraphs and line breaks so the page is readable.
     */
    function terms_content_html(?string $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }

        $t = $raw;
        if (preg_match('/<[a-z!\/][\s\/>]/i', $t)) {
            return $t;
        }

        $parts = preg_split('/\R{2,}/u', trim($t)) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $out[] = '<p>'.nl2br(e($part), false).'</p>';
        }

        return $out ? implode("\n", $out) : '';
    }
}

if (! function_exists('admin_image_validation_rule')) {
    /** Validation for CMS image uploads (compressed server-side if over 700 KB). */
    function admin_image_validation_rule(bool $required = false): string
    {
        return ($required ? 'required|' : 'nullable|').'image|max:15360';
    }
}

if (! function_exists('store_optimized_image')) {
    function store_optimized_image(\Illuminate\Http\UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        return app(\App\Services\OptimizedImageStorage::class)->store($file, $directory, $disk);
    }
}

if (! function_exists('site_trans')) {
    /** UI string: DB override → lang file → English fallback. */
    function site_trans(string $key, array $replace = []): string
    {
        return app(\App\Services\SiteTranslationService::class)->get($key, $replace);
    }
}

if (! function_exists('translations_enabled')) {
    function translations_enabled(): bool
    {
        return \App\Support\SiteLocale::translationsEnabled();
    }
}

if (! function_exists('site_locale')) {
    function site_locale(): string
    {
        return \App\Support\SiteLocale::normalize(app()->getLocale());
    }
}

if (! function_exists('localized_route')) {
    function localized_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $url = route($name, $parameters, $absolute);

        if (site_locale() === \App\Support\SiteLocale::FRENCH && translations_enabled()) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            if (! str_starts_with(ltrim($path, '/'), 'fr/') && ltrim($path, '/') !== 'fr') {
                $path = '/fr'.($path === '/' ? '' : $path);
            }

            if ($absolute) {
                return url($path);
            }

            return $path;
        }

        return $url;
    }
}

if (! function_exists('locale_switch_url')) {
    function locale_switch_url(string $targetLocale): string
    {
        return route('locale.switch', [
            'locale' => $targetLocale,
            'redirect' => '/'.ltrim(request()->path(), '/'),
        ]);
    }
}
