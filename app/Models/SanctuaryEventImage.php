<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SanctuaryEventImage extends Model
{
    protected $fillable = [
        'sanctuary_event_id',
        'image',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(SanctuaryEvent::class, 'sanctuary_event_id');
    }
}
