<?php

namespace App\Models\Concerns;

trait HasPublicThumbnail
{
    public function publicThumbnailUrl(?string $default = null): string
    {
        if (filled($this->cover_image ?? null)) {
            return asset('storage/'.ltrim((string) $this->cover_image, '/'));
        }

        if (filled($this->image ?? null)) {
            return asset('storage/'.ltrim($this->normalizeThumbnailPath((string) $this->image), '/'));
        }

        if (method_exists($this, 'images')) {
            $first = $this->relationLoaded('images')
                ? $this->images->sortBy('id')->first()
                : $this->images()->orderBy('id')->first();

            if ($first && filled($first->image ?? null)) {
                return asset('storage/'.ltrim($this->normalizeThumbnailPath((string) $first->image), '/'));
            }
        }

        return asset($default ?? 'storage/rooms/default.jpg');
    }

    protected function normalizeThumbnailPath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_contains($path, '/')) {
            return $path;
        }

        return 'images/rooms/'.$path;
    }
}
