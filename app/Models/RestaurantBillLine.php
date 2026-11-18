<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantBillLine extends Model
{
    protected $fillable = [
        'restaurant_bill_id',
        'menu_item_id',
        'item_name',
        'unit_price',
        'quantity',
        'currency',
        'created_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(RestaurantBill::class, 'restaurant_bill_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
