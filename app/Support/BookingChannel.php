<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

final class BookingChannel
{
    public static function enabled(): bool
    {
        return Cache::remember('site.booking_channel_enabled', 300, function () {
            $setting = Setting::query()->select('booking_channel_enabled')->first();

            return (bool) ($setting?->booking_channel_enabled ?? true);
        });
    }

    public static function forgetEnabledCache(): void
    {
        Cache::forget('site.booking_channel_enabled');
    }
}
