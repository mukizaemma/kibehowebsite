<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;
    protected $table='amenities';

    protected $fillable = [
        'title',
        'icon',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
        
    }

}
