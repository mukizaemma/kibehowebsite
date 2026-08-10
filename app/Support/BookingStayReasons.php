<?php

namespace App\Support;

final class BookingStayReasons
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'pilgrimage' => site_trans('booking.stay_reason_pilgrimage'),
            'retreat' => site_trans('booking.stay_reason_retreat'),
            'mission' => site_trans('booking.stay_reason_mission'),
            'business' => site_trans('booking.stay_reason_business'),
            'leisure' => site_trans('booking.stay_reason_leisure'),
            'family' => site_trans('booking.stay_reason_family'),
            'other' => site_trans('booking.stay_reason_other'),
        ];
    }

    public static function label(?string $key): string
    {
        $key = trim((string) $key);
        $options = self::options();

        return $options[$key] ?? ($key !== '' ? $key : '—');
    }
}
