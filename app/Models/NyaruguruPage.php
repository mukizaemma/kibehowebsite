<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NyaruguruPage extends Model
{
    use HasPublicThumbnail;

    protected $fillable = [
        'title',
        'description',
        'home_title',
        'home_lead',
        'cover_image',
        'status',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(NyaruguruPageImage::class)->orderBy('sort_order');
    }

    public static function current(): self
    {
        $page = static::query()->first();

        if ($page) {
            return $page;
        }

        return static::query()->create([
            'title' => 'Discover Nyaruguru',
            'description' => null,
            'status' => 'Active',
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}
