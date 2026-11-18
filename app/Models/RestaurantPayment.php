<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantPayment extends Model
{
    protected $fillable = ['restaurant_bill_id', 'amount', 'currency', 'method', 'user_id'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(RestaurantBill::class, 'restaurant_bill_id');
    }
}
