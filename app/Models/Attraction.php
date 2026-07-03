<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Model;

class Attraction extends Model
{
    use HasPublicThumbnail;
    protected $fillable = [
        'title',
        'image',
        'description',
    ];
}
