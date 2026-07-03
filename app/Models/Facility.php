<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;
    use HasPublicThumbnail;
    protected $table='facilities';

    public const EXPLORE_KIBEHO_SLUG = 'explore-kibeho';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'official_website_url',
        'image',
        'cover_image',
        'status',
        'added_by',
    ];

    public function isExploreKibehoSanctuary(): bool
    {
        return $this->slug === self::EXPLORE_KIBEHO_SLUG;
    }

    public function images(){
        return $this->hasMany(Facilityimage::class);
    }

    public function addedBy(){
        return $this->belongsTo(User::class, 'added_by');
    }
}
