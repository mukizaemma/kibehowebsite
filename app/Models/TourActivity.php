<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourActivity extends Model
{
    use HasFactory;
    use HasPublicThumbnail;

    protected $fillable = [
        'title',
        'slug',
        'cover_image',
        'description',
        'status',
        'sort_order',
        'added_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(TourActivityImage::class)->orderBy('order')->orderBy('id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
