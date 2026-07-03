<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\HomeGalleryFeature;
use App\Models\Room;
use Illuminate\Support\Collection;

class AggregatedGalleryService
{
    /**
     * @return Collection<int, array{
     *     key: string,
     *     url: string,
     *     caption: string,
     *     source_label: string,
     *     source_type: string,
     *     source_id: int,
     *     image_path: string
     * }>
     */
    public function allImages(bool $activeOnly = true): Collection
    {
        $items = collect();
        $seenPaths = [];

        $roomsQuery = Room::query()->with('images')->orderBy('title');
        if ($activeOnly) {
            $roomsQuery->where('status', 'Active');
        }

        foreach ($roomsQuery->get() as $room) {
            $this->pushRoomImages($items, $seenPaths, $room);
        }

        $facilitiesQuery = Facility::query()->with('images')->orderBy('title');
        if ($activeOnly) {
            $facilitiesQuery->where('status', 'Active');
        }

        foreach ($facilitiesQuery->get() as $facility) {
            $this->pushFacilityImages($items, $seenPaths, $facility);
        }

        return $items->values();
    }

    /**
     * @return Collection<int, array{
     *     key: string,
     *     url: string,
     *     caption: string,
     *     source_label: string,
     *     source_type: string,
     *     source_id: int,
     *     image_path: string
     * }>
     */
    public function homeFeaturedImages(): Collection
    {
        $lookup = $this->allImages(false)->keyBy('key');

        return HomeGalleryFeature::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (HomeGalleryFeature $feature) use ($lookup) {
                $current = $lookup->get($feature->image_key);

                if ($current) {
                    return $current;
                }

                if (! filled($feature->image_path)) {
                    return null;
                }

                return [
                    'key' => $feature->image_key,
                    'url' => $this->storageUrl((string) $feature->image_path),
                    'caption' => (string) ($feature->caption ?? ''),
                    'source_label' => '',
                    'source_type' => (string) $feature->source_type,
                    'source_id' => (int) $feature->source_id,
                    'image_path' => (string) $feature->image_path,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return array<int, string>
     */
    public function homeFeaturedKeys(): array
    {
        return HomeGalleryFeature::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('image_key')
            ->all();
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function syncHomeFeatured(array $keys): void
    {
        $keys = array_values(array_unique(array_filter($keys)));

        if (count($keys) > 3) {
            throw new \InvalidArgumentException('You can select up to 3 images for the home page gallery.');
        }

        $lookup = $this->allImages(false)->keyBy('key');

        HomeGalleryFeature::query()->delete();

        foreach ($keys as $index => $key) {
            $item = $lookup->get($key);
            if (! $item) {
                continue;
            }

            HomeGalleryFeature::create([
                'image_key' => $item['key'],
                'image_path' => $item['image_path'],
                'caption' => $item['caption'] ?: null,
                'source_type' => $item['source_type'],
                'source_id' => $item['source_id'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    protected function pushRoomImages(Collection $items, array &$seenPaths, Room $room): void
    {
        $roomLabel = 'Room: '.$room->title;

        if (filled($room->cover_image)) {
            $this->pushImage(
                $items,
                $seenPaths,
                'room_cover:'.$room->id,
                (string) $room->cover_image,
                $room->title,
                $roomLabel,
                'room_cover',
                (int) $room->id
            );
        }

        if (filled($room->image) && $this->normalizePath((string) $room->image) !== $this->normalizePath((string) ($room->cover_image ?? ''))) {
            $this->pushImage(
                $items,
                $seenPaths,
                'room_legacy:'.$room->id,
                (string) $room->image,
                $room->title,
                $roomLabel,
                'room_legacy',
                (int) $room->id
            );
        }

        foreach ($room->images as $image) {
            if (! filled($image->image)) {
                continue;
            }

            $caption = filled($image->caption) ? (string) $image->caption : $room->title;
            $this->pushImage(
                $items,
                $seenPaths,
                'room_image:'.$image->id,
                (string) $image->image,
                $caption,
                $roomLabel,
                'room_image',
                (int) $image->id
            );
        }
    }

    protected function pushFacilityImages(Collection $items, array &$seenPaths, Facility $facility): void
    {
        $facilityLabel = 'Service: '.$facility->title;

        if (filled($facility->cover_image)) {
            $this->pushImage(
                $items,
                $seenPaths,
                'facility_cover:'.$facility->id,
                (string) $facility->cover_image,
                $facility->title,
                $facilityLabel,
                'facility_cover',
                (int) $facility->id
            );
        }

        if (filled($facility->image) && $this->normalizePath((string) $facility->image) !== $this->normalizePath((string) ($facility->cover_image ?? ''))) {
            $this->pushImage(
                $items,
                $seenPaths,
                'facility_legacy:'.$facility->id,
                (string) $facility->image,
                $facility->title,
                $facilityLabel,
                'facility_legacy',
                (int) $facility->id
            );
        }

        foreach ($facility->images as $image) {
            if (! filled($image->image)) {
                continue;
            }

            $caption = filled($image->caption) ? (string) $image->caption : $facility->title;
            $this->pushImage(
                $items,
                $seenPaths,
                'facility_image:'.$image->id,
                (string) $image->image,
                $caption,
                $facilityLabel,
                'facility_image',
                (int) $image->id
            );
        }
    }

    protected function pushImage(
        Collection $items,
        array &$seenPaths,
        string $key,
        string $path,
        string $caption,
        string $sourceLabel,
        string $sourceType,
        int $sourceId
    ): void {
        $normalized = $this->normalizePath($path);
        if ($normalized === '' || isset($seenPaths[$normalized])) {
            return;
        }

        $seenPaths[$normalized] = true;

        $items->push([
            'key' => $key,
            'url' => $this->storageUrl($path),
            'caption' => $caption,
            'source_label' => $sourceLabel,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'image_path' => $normalized,
        ]);
    }

    public function storageUrl(string $path): string
    {
        $path = ltrim($path, '/');

        if ($path === '') {
            return asset('storage/rooms/default.jpg');
        }

        if (str_starts_with($path, 'gallery/') || str_contains($path, '/')) {
            return asset('storage/'.$path);
        }

        return asset('storage/images/gallery/'.$path);
    }

    protected function normalizePath(string $path): string
    {
        return ltrim($path, '/');
    }
}
