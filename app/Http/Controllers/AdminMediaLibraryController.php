<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMediaLibraryController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function index(Request $request): JsonResponse
    {
        $disk = Storage::disk('public');
        $q = strtolower(trim((string) $request->query('q', '')));
        $limit = min(300, max(24, (int) $request->query('limit', 120)));

        $files = collect($disk->allFiles())
            ->filter(function (string $path) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return in_array($ext, self::IMAGE_EXTENSIONS, true)
                    && ! str_contains($path, '/.')
                    && ! str_starts_with(basename($path), '.');
            })
            ->when($q !== '', function ($collection) use ($q) {
                return $collection->filter(fn (string $path) => str_contains(strtolower($path), $q));
            })
            ->sortByDesc(function (string $path) use ($disk) {
                try {
                    return $disk->lastModified($path);
                } catch (\Throwable) {
                    return 0;
                }
            })
            ->take($limit)
            ->values()
            ->map(function (string $path) use ($disk) {
                $size = 0;
                try {
                    $size = (int) $disk->size($path);
                } catch (\Throwable) {
                    $size = 0;
                }

                return [
                    'path' => $path,
                    'name' => basename($path),
                    'folder' => trim(dirname($path), '.') ?: '/',
                    'url' => asset('storage/'.$path),
                    'size' => $size,
                    'size_label' => $this->formatBytes($size),
                ];
            });

        return response()->json([
            'data' => $files,
            'count' => $files->count(),
        ]);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), $i === 0 ? 0 : 1).' '.$units[$i];
    }
}
