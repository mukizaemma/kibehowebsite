<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;
    
    protected $table = 'restaurants';

    protected $fillable = [
        'title',
        'description',
        'image',
        'cuisine_section_title',
        'cuisine_section_lead',
        'requires_reservation',
        'minimum_amount',
        'minimum_amount_local',
        'momo_code',
        'momo_account_name',
    ];

    protected $casts = [
        'requires_reservation' => 'boolean',
        'minimum_amount' => 'decimal:2',
        'minimum_amount_local' => 'decimal:2',
    ];

    public static function deskProfile(): self
    {
        return static::query()->orderBy('id')->first() ?: static::query()->create([
            'title' => 'Restaurant',
        ]);
    }

    public function images()
    {
        return $this->hasMany(Restoimage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function cuisines()
    {
        return $this->hasMany(RestaurantCuisine::class)->orderBy('sort_order')->orderBy('id');
    }
}
