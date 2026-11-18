<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantReservation extends Model
{
    protected $fillable = [
        'guest_name',
        'phone',
        'party_size',
        'reserved_for',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'reserved_for' => 'datetime',
    ];

    public function bills()
    {
        return $this->hasMany(RestaurantBill::class);
    }
}
