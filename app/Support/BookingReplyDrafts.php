<?php

namespace App\Support;

use App\Models\Booking;
use App\Services\BookingSubmissionService;

/**
 * Autofill draft replies for admin confirm/reject emails.
 * Admins are expected to edit before sending.
 */
final class BookingReplyDrafts
{
    /**
     * @return array{confirmed: string, cancelled: string}
     */
    public static function for(Booking $booking): array
    {
        return [
            'confirmed' => self::confirmed($booking),
            'cancelled' => self::cancelled($booking),
        ];
    }

    public static function confirmed(Booking $booking): string
    {
        $hotel = BookingSubmissionService::hotelName();
        $guest = trim((string) ($booking->names ?? 'Guest')) ?: 'Guest';
        $item = self::itemName($booking);
        $dates = self::datesLine($booking);
        $ref = '#'.$booking->id;

        return <<<TEXT
Dear {$guest},

Thank you for choosing {$hotel}. We are pleased to confirm your reservation for {$item}{$dates} (Ref {$ref}).

Please arrive with a valid ID. If your arrival time or guest count changes, reply to this email so we can update your booking.

[Please review and edit this message before sending.]

Warm regards,
{$hotel} Reservations
TEXT;
    }

    public static function cancelled(Booking $booking): string
    {
        $hotel = BookingSubmissionService::hotelName();
        $guest = trim((string) ($booking->names ?? 'Guest')) ?: 'Guest';
        $item = self::itemName($booking);
        $dates = self::datesLine($booking);
        $ref = '#'.$booking->id;

        return <<<TEXT
Dear {$guest},

Thank you for your interest in {$hotel}. Unfortunately we are unable to confirm your reservation for {$item}{$dates} (Ref {$ref}) at this time.

[Please edit this message — include the reason if appropriate, and suggest alternative dates or room types when possible.]

We would be glad to help you find another option. Reply to this email or contact us directly.

Warm regards,
{$hotel} Reservations
TEXT;
    }

    public static function itemName(Booking $booking): string
    {
        if (($booking->reservation_type ?? '') === 'facility' && $booking->facility) {
            return (string) $booking->facility->title;
        }
        if (($booking->reservation_type ?? '') === 'tour_activity' && $booking->tourActivity) {
            return (string) $booking->tourActivity->title;
        }
        if ($booking->room) {
            return (string) $booking->room->title;
        }

        return 'your stay';
    }

    private static function datesLine(Booking $booking): string
    {
        $checkin = self::formatDate($booking->checkin_date ?? $booking->checkin ?? null);
        $checkout = self::formatDate($booking->checkout_date ?? $booking->checkout ?? null);

        if ($checkin && $checkout && $checkin !== $checkout) {
            return " from {$checkin} to {$checkout}";
        }
        if ($checkin) {
            return " on {$checkin}";
        }

        return '';
    }

    private static function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable) {
            return is_string($value) ? $value : null;
        }
    }
}
