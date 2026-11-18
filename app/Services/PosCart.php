<?php

namespace App\Services;

class PosCart
{
    public const KEY = 'desk.pos.cart';

    public static function get(): array
    {
        $cart = session(self::KEY, []);

        return [
            'currency' => ($cart['currency'] ?? 'usd') === 'rwf' ? 'rwf' : 'usd',
            'guest_name' => (string) ($cart['guest_name'] ?? ''),
            'lines' => array_values($cart['lines'] ?? []),
            'pay_amount' => $cart['pay_amount'] ?? '',
            'booking_id' => $cart['booking_id'] ?? '',
            'restaurant_reservation_id' => $cart['restaurant_reservation_id'] ?? '',
        ];
    }

    public static function put(array $cart): void
    {
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function total(array $cart): float
    {
        $total = 0.0;
        foreach ($cart['lines'] as $line) {
            $total += (float) $line['unit_price'] * (int) $line['quantity'];
        }

        return round($total, 2);
    }
}
