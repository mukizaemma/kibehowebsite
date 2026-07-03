<?php

namespace App\Models;

use App\Models\Concerns\HasPublicThumbnail;
use Illuminate\Database\Eloquent\Model;

class GikongoroDiocesePage extends Model
{
    use HasPublicThumbnail;

    protected $fillable = [
        'title',
        'description',
        'header_image',
        'profile_image',
        'stats_background_image',
        'status',
    ];

    public static function current(): self
    {
        $page = static::query()->first();

        if ($page) {
            return $page;
        }

        $page = static::query()->create([
            'title' => 'Gikongoro Diocese',
            'description' => null,
            'status' => 'Active',
        ]);

        $defaults = [
            ['label' => 'Schools', 'value' => '0', 'icon' => 'fa-solid fa-school', 'sort_order' => 1],
            ['label' => 'Health Facilities', 'value' => '0', 'icon' => 'fa-solid fa-hospital', 'sort_order' => 2],
            ['label' => 'Parishes', 'value' => '0', 'icon' => 'fa-solid fa-church', 'sort_order' => 3],
            ['label' => 'Religious Communities', 'value' => '0', 'icon' => 'fa-solid fa-people-roof', 'sort_order' => 4],
        ];

        foreach ($defaults as $row) {
            GikongoroDioceseStat::create($row + ['is_active' => true]);
        }

        return $page;
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }
}
