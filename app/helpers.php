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

if (! function_exists('hotel_reservation_url')) {
    /**
     * Global online reservation URL (channel manager).
     * Settings → Booking & review links → "Online reservation URL" (stored as linktree).
     * Falls back to HOTEL_RESERVATION_URL / booking_com_url when unset.
     */
    function hotel_reservation_url(?object $setting = null): ?string
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
