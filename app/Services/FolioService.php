<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\BookingPayment;
use App\Models\RestaurantBill;
use App\Models\RestaurantPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FolioService
{
    public function addPayment(Booking $booking, float $amount, string $currency, string $method, ?string $note = null): BookingPayment
    {
        $currency = $this->currency($currency);
        $stayCurrency = $this->currency($booking->currency);
        if ($currency !== $stayCurrency) {
            throw ValidationException::withMessages([
                'currency' => 'This stay is in '.strtoupper($stayCurrency).'. Record '.strtoupper($currency).' on its own document.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Enter an amount greater than zero.',
            ]);
        }

        $payment = BookingPayment::query()->create([
            'booking_id' => $booking->id,
            'amount' => round($amount, 2),
            'currency' => $currency,
            'method' => $method,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);

        $booking->paid_amount = round((float) $booking->paid_amount + $amount, 2);
        $booking->balance_amount = round((float) $booking->total_amount - (float) $booking->paid_amount, 2);
        $booking->payment_status = $booking->balance_amount <= 0 ? 'paid' : 'partial';
        $booking->save();

        return $payment;
    }

    public function addCharge(Booking $booking, string $description, float $amount, string $currency, string $source = 'extra', ?int $billId = null): BookingCharge
    {
        $currency = $this->currency($currency);
        $stayCurrency = $this->currency($booking->currency);
        if ($currency !== $stayCurrency) {
            throw ValidationException::withMessages([
                'currency' => 'This stay is in '.strtoupper($stayCurrency).'. A '.strtoupper($currency).' charge cannot be added into that balance.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Enter an amount greater than zero.',
            ]);
        }

        $charge = BookingCharge::query()->create([
            'booking_id' => $booking->id,
            'description' => $description,
            'amount' => round($amount, 2),
            'currency' => $currency,
            'source' => $source,
            'restaurant_bill_id' => $billId,
            'created_by' => auth()->id(),
        ]);

        $booking->total_amount = round((float) $booking->total_amount + $amount, 2);
        $booking->balance_amount = round((float) $booking->balance_amount + $amount, 2);
        if ($booking->balance_amount > 0 && $booking->payment_status === 'paid') {
            $booking->payment_status = ((float) $booking->paid_amount) > 0 ? 'partial' : 'pending';
        }
        $booking->save();

        return $charge;
    }

    public function removeCharge(BookingCharge $charge): void
    {
        if ($charge->removed_at) {
            return;
        }

        $booking = $charge->booking;
        $charge->removed_at = now();
        $charge->removed_by = auth()->id();
        $charge->save();

        if ($booking && $this->currency($charge->currency) === $this->currency($booking->currency)) {
            $booking->total_amount = round(max(0, (float) $booking->total_amount - (float) $charge->amount), 2);
            $booking->balance_amount = round((float) $booking->total_amount - (float) $booking->paid_amount, 2);
            $booking->save();
        }

        app(AuditLogger::class)->write(
            'charge_removed',
            BookingCharge::class,
            $charge->id,
            'Removed '.$charge->description.' of '.desk_money((float) $charge->amount, $charge->currency).' from '.$booking?->names.'.',
            [
                'guest' => $booking?->names,
                'description' => $charge->description,
                'amount' => (float) $charge->amount,
                'currency' => $charge->currency,
            ]
        );
    }

    public function chargeBillToRoom(RestaurantBill $bill, Booking $booking): BookingCharge
    {
        return DB::transaction(function () use ($bill, $booking) {
            $bill->load('lines', 'payments');
            $amount = $bill->balance();
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'booking_id' => 'This bill has nothing left to charge.',
                ]);
            }

            if ($booking->status !== 'checked_in' || $booking->checked_out_at) {
                throw ValidationException::withMessages([
                    'booking_id' => 'Choose a guest who is still in house.',
                ]);
            }

            $charge = $this->addCharge(
                $booking,
                'Restaurant '.$bill->reference,
                $amount,
                $bill->currency,
                'restaurant',
                $bill->id
            );

            RestaurantPayment::query()->create([
                'restaurant_bill_id' => $bill->id,
                'amount' => $amount,
                'currency' => $this->currency($bill->currency),
                'method' => 'room',
                'user_id' => auth()->id(),
            ]);

            $bill->booking_id = $booking->id;
            $bill->save();

            return $charge;
        });
    }

    public function currency(mixed $value): string
    {
        return strtolower((string) $value) === 'rwf' ? 'rwf' : 'usd';
    }
}
