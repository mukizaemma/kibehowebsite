<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KibehoPage extends Model
{
    use HasPublicThumbnail;

    protected $fillable = [
        'title',
        'description',
        'cover_image',
        'official_website_url',
        'status',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(KibehoPageImage::class)->orderBy('sort_order');
    }

    public static function current(): self
    {
        $page = static::query()->first();

        if ($page) {
            return $page;
        }

        return static::query()->create([
            'title' => 'Explore Kibeho Sanctuary',
            'description' => null,
            'official_website_url' => 'https://www.kibeho.org/',
            'status' => 'Active',
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}
