<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Model;

class SanctuaryEvent extends Model
{
    use HasPublicThumbnail;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'image',
        'external_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('event_date')->orderBy('title');
    }
}
