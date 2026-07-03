<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeGalleryFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_key',
        'image_path',
        'caption',
        'source_type',
        'source_id',
        'sort_order',
    ];
}
