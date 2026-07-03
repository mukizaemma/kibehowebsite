<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTranslationOverride extends Model
{
    protected $fillable = [
        'key',
        'locale',
        'value',
    ];
}
