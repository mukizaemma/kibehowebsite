<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MeetingRoom extends Model
{
    /**
     * Canonical hotel inventory: two meeting rooms with capacity ranges.
     *
     * @return list<array{title: string, min_persons: int, max_persons: int, sort_order: int}>
     */
    public static function canonicalDefaults(): array
    {
        return [
            ['title' => 'Salle Kibeho', 'min_persons' => 80, 'max_persons' => 120, 'sort_order' => 1],
            ['title' => 'Salle Lourde', 'min_persons' => 150, 'max_persons' => 200, 'sort_order' => 2],
        ];
    }

    public static function ensureDefaultsForEventpage(Eventpage $page): void
    {
        if (! $page->meetingRooms()->exists()) {
            foreach (static::canonicalDefaults() as $row) {
                $row['slug'] = static::uniqueSlugForEventpage((int) $page->id, $row['title']);
                $page->meetingRooms()->create($row);
            }

            return;
        }

        // Keep legacy installs aligned to the hotel’s two halls once min_persons exists.
        if (
            \Illuminate\Support\Facades\Schema::hasColumn('meeting_rooms', 'min_persons')
            && $page->meetingRooms()->count() !== count(static::canonicalDefaults())
        ) {
            static::syncCanonicalForEventpage($page);
        }
    }

    /**
     * Align an event page to exactly the two canonical meeting rooms.
     * Preserves cover/gallery on matched rooms when possible.
     */
    public static function syncCanonicalForEventpage(Eventpage $page): void
    {
        $defaults = static::canonicalDefaults();
        $rooms = $page->meetingRooms()->orderBy('sort_order')->orderBy('id')->get();
        $usedIds = [];

        foreach ($defaults as $index => $def) {
            $existing = $rooms->firstWhere('title', $def['title'])
                ?? $rooms->get($index);

            if ($existing) {
                $existing->title = $def['title'];
                $existing->min_persons = $def['min_persons'];
                $existing->max_persons = $def['max_persons'];
                $existing->sort_order = $def['sort_order'];
                if (! filled($existing->slug)) {
                    $existing->slug = static::uniqueSlugForEventpage((int) $page->id, $def['title'], (int) $existing->id);
                }
                $existing->save();
                $usedIds[] = (int) $existing->id;
                continue;
            }

            $created = $page->meetingRooms()->create([
                'title' => $def['title'],
                'slug' => static::uniqueSlugForEventpage((int) $page->id, $def['title']),
                'min_persons' => $def['min_persons'],
                'max_persons' => $def['max_persons'],
                'sort_order' => $def['sort_order'],
            ]);
            $usedIds[] = (int) $created->id;
        }

        $page->meetingRooms()
            ->whereNotIn('id', $usedIds)
            ->get()
            ->each(function (self $room) {
                $room->images()->delete();
                $room->delete();
            });
    }

    public static function uniqueSlugForEventpage(int $eventpageId, string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: 'room';
        $slug = $base;
        $n = 1;
        while (
            static::query()
                ->where('eventpage_id', $eventpageId)
                ->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /** Max characters for grid / card teaser (full description is edited in admin). */
    public const GRID_EXCERPT_MAX_CHARS = 220;

    /**
     * Short teaser for listing cards — plain text from description, length-limited.
     */
    public function excerpt(): string
    {
        $html = (string) ($this->description ?? '');
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
        if ($plain === '') {
            return '';
        }

        return Str::limit($plain, self::GRID_EXCERPT_MAX_CHARS, '…');
    }

    public function capacityLabel(): string
    {
        $min = (int) ($this->min_persons ?? 0);
        $max = (int) ($this->max_persons ?? 0);

        if ($min > 0 && $max > 0 && $min !== $max) {
            return site_trans('meetings.capacity_range', [
                'min' => number_format($min),
                'max' => number_format($max),
            ]);
        }

        if ($max > 0) {
            return site_trans('meetings.capacity_up_to', ['count' => number_format($max)]);
        }

        if ($min > 0) {
            return site_trans('meetings.capacity_from', ['count' => number_format($min)]);
        }

        return site_trans('meetings.capacity_on_request');
    }

    protected $fillable = [
        'eventpage_id',
        'title',
        'slug',
        'min_persons',
        'max_persons',
        'description',
        'image',
        'sort_order',
    ];

    protected $casts = [
        'min_persons' => 'integer',
        'max_persons' => 'integer',
        'sort_order' => 'integer',
    ];

    public function eventpage(): BelongsTo
    {
        return $this->belongsTo(Eventpage::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MeetingRoomImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
