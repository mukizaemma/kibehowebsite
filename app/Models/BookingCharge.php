<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingCharge extends Model
{
    protected $fillable = [
        'booking_id',
        'description',
        'amount',
        'currency',
        'source',
        'restaurant_bill_id',
        'created_by',
        'removed_at',
        'removed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'removed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('removed_at');
    }
}
