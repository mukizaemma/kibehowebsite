<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NyaruguruActivityImage extends Model
{
    protected $fillable = [
        'nyaruguru_activity_id',
        'image',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(NyaruguruActivity::class, 'nyaruguru_activity_id');
    }
}
