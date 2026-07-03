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
        'media_type',
        'video_url',
        'video_file',
    ];

}
