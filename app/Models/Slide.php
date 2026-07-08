<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use HasFactory;
    
    protected $table = 'slides';

    protected $fillable = [
        'heading',
        'subheading',
        'button',
        'link',
        'image',
        'sort_order',
        'media_type',
        'video_url',
        'video_file',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

}
