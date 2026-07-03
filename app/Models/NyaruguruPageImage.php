<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NyaruguruPageImage extends Model
{
    protected $fillable = [
        'nyaruguru_page_id',
        'image',
        'caption',
        'sort_order',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(NyaruguruPage::class, 'nyaruguru_page_id');
    }
}
