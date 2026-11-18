<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantBill extends Model
{
    protected $fillable = [
        'reference',
        'status',
        'guest_name',
        'currency',
        'booking_id',
        'restaurant_reservation_id',
        'notes',
        'created_by',
        'cancelled_at',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(RestaurantBillLine::class);
    }

    public function payments()
    {
        return $this->hasMany(RestaurantPayment::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reservation()
    {
        return $this->belongsTo(RestaurantReservation::class, 'restaurant_reservation_id');
    }

    public function total(): float
    {
        return (float) $this->lines->sum(fn (RestaurantBillLine $line) => (float) $line->unit_price * (int) $line->quantity);
    }

    public function paid(): float
    {
        return (float) $this->payments->sum(fn (RestaurantPayment $payment) => (float) $payment->amount);
    }

    public function cashPaid(): float
    {
        return (float) $this->payments
            ->reject(fn (RestaurantPayment $payment) => $payment->method === 'room')
            ->sum(fn (RestaurantPayment $payment) => (float) $payment->amount);
    }

    public function roomPaid(): float
    {
        return (float) $this->payments
            ->filter(fn (RestaurantPayment $payment) => $payment->method === 'room')
            ->sum(fn (RestaurantPayment $payment) => (float) $payment->amount);
    }

    public function balance(): float
    {
        if ($this->status === 'cancelled') {
            return 0.0;
        }

        return round($this->total() - $this->paid(), 2);
    }
}
