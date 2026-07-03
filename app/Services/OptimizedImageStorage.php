<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OptimizedImageStorage
{
    public const MAX_BYTES = 716800; // 700 KB

    public const MIN_BYTES = 409600; // 400 KB target floor when compressing large files

    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        if (! $this->isOptimizableImage($file)) {
            return $file->store($directory, $disk);
        }

        if ($file->getSize() <= self::MAX_BYTES) {
            return $file->store($directory, $disk);
        }

        $binary = $this->optimizeToBinary($file);
        $filename = uniqid('img_', true).'.jpg';
        $path = trim($directory, '/').'/'.$filename;
        Storage::disk($disk)->put($path, $binary);

        return $path;
    }

    private function isOptimizableImage(UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true);
    }

    private function optimizeToBinary(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            return (string) file_get_contents($file->getRealPath());
        }

        $source = $this->loadImageResource($file);
        if ($source === null) {
            return (string) file_get_contents($file->getRealPath());
        }

        $width = imagesx($source);
        $height = imagesy($source);

        $bestUnderMax = null;
        $bestUnderMaxSize = PHP_INT_MAX;
        $fallbackSmallest = null;
        $fallbackSmallestSize = PHP_INT_MAX;

        foreach ([1.0, 0.85, 0.7, 0.55, 0.45, 0.35] as $scale) {
            $scaled = $this->resizeResource($source, $width, $height, $scale);

            for ($quality = 90; $quality >= 50; $quality -= 5) {
                $binary = $this->encodeJpeg($scaled, $quality);
                $size = strlen($binary);

                if ($size < $fallbackSmallestSize) {
                    $fallbackSmallest = $binary;
                    $fallbackSmallestSize = $size;
                }

                if ($size > self::MAX_BYTES) {
                    continue;
                }

                if ($size >= self::MIN_BYTES) {
                    imagedestroy($scaled);
                    imagedestroy($source);

                    return $binary;
                }

                if ($size < $bestUnderMaxSize) {
                    $bestUnderMax = $binary;
                    $bestUnderMaxSize = $size;
                }
            }

            imagedestroy($scaled);
        }

        imagedestroy($source);

        if ($bestUnderMax !== null) {
            return $bestUnderMax;
        }

        if ($fallbackSmallest !== null) {
            return $fallbackSmallest;
        }

        throw new RuntimeException('Unable to optimize image.');
    }

    /**
     * @return resource|null
     */
    private function loadImageResource(UploadedFile $file)
    {
        $path = $file->getRealPath();
        $mime = strtolower((string) $file->getMimeType());

        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    /**
     * @param  resource  $source
     * @return resource
     */
    private function resizeResource($source, int $width, int $height, float $scale)
    {
        $targetW = max(1, (int) round($width * $scale));
        $targetH = max(1, (int) round($height * $scale));

        if ($targetW === $width && $targetH === $height) {
            $canvas = imagecreatetruecolor($width, $height);
            imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

            return $canvas;
        }

        $canvas = imagecreatetruecolor($targetW, $targetH);
        imagefilledrectangle($canvas, 0, 0, $targetW, $targetH, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetW, $targetH, $width, $height);

        return $canvas;
    }

    /**
     * @param  resource  $image
     */
    private function encodeJpeg($image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);

        return (string) ob_get_clean();
    }
}
