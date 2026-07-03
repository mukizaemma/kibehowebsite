<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityRoom extends Model
{
    use HasFactory;
    
    protected $table = 'amenity_room';

    protected $fillable = [
        'amenity_id',
        'room_id',
    ];
}
