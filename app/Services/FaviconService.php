<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaviconService
{
    /**
     * Convert an uploaded image (PNG, JPG, WebP, SVG) or local file into a crisp square favicon.
     *
     * @return string|null Relative path in public storage disk, or null on failure.
     */
    public static function generateFavicon(UploadedFile|string $file): ?string
    {
        try {
            $isUploaded = $file instanceof UploadedFile;
            $extension = strtolower($isUploaded ? $file->getClientOriginalExtension() : pathinfo($file, PATHINFO_EXTENSION));
            $contents = $isUploaded ? file_get_contents($file->getRealPath()) : file_get_contents($file);

            if (empty($contents)) {
                return null;
            }

            // Ensure directory exists in public storage
            Storage::disk('public')->makeDirectory('settings/favicons');
            $timestamp = time();

            // Handle SVG format directly
            if ($extension === 'svg' || str_contains($contents, '<svg')) {
                $storagePath = "settings/favicons/favicon_{$timestamp}.svg";
                Storage::disk('public')->put($storagePath, $contents);

                // Also update public/favicon.svg for fallback
                try {
                    File::put(public_path('favicon.svg'), $contents);
                } catch (\Throwable $e) {
                    Log::warning('Could not overwrite public/favicon.svg: '.$e->getMessage());
                }

                return $storagePath;
            }

            // Handle Raster Images (PNG, JPG, WEBP, GIF) via GD
            if (! extension_loaded('gd') || ! function_exists('imagecreatefromstring')) {
                // Fallback: save as raw png/jpg without GD resize
                $storagePath = "settings/favicons/favicon_{$timestamp}.".$extension;
                Storage::disk('public')->put($storagePath, $contents);

                return $storagePath;
            }

            $src = @imagecreatefromstring($contents);
            if (! $src) {
                Log::warning('FaviconService: imagecreatefromstring failed for uploaded file.');

                return null;
            }

            $origW = imagesx($src);
            $origH = imagesy($src);

            // Generate crisp square canvases (16, 32, 64)
            $png64 = self::renderSquarePng($src, $origW, $origH, 64);
            $png32 = self::renderSquarePng($src, $origW, $origH, 32);
            $png16 = self::renderSquarePng($src, $origW, $origH, 16);

            imagedestroy($src);

            if (! $png64) {
                return null;
            }

            // Save 64x64 PNG as the primary modern web favicon in public storage
            $storagePath = "settings/favicons/favicon_{$timestamp}.png";
            Storage::disk('public')->put($storagePath, $png64);

            // Build standard multi-resolution ICO file (16x16, 32x32, 64x64)
            $icoData = self::packIco([
                [16, $png16],
                [32, $png32],
                [64, $png64],
            ]);

            // Overwrite public/favicon.ico so direct browser requests /favicon.ico use the school logo
            try {
                File::put(public_path('favicon.ico'), $icoData);
            } catch (\Throwable $e) {
                Log::warning('Could not overwrite public/favicon.ico: '.$e->getMessage());
            }

            return $storagePath;
        } catch (\Throwable $e) {
            Log::error('FaviconService::generateFavicon error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Fit and center an image into a transparent square canvas of the given size.
     */
    protected static function renderSquarePng($src, int $origW, int $origH, int $targetSize): ?string
    {
        $canvas = imagecreatetruecolor($targetSize, $targetSize);
        if (! $canvas) {
            return null;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagealphablending($canvas, true);

        // Proportional scale calculation to fit within target square without distortion
        $scale = min($targetSize / $origW, $targetSize / $origH);
        $dstW = max(1, (int) round($origW * $scale));
        $dstH = max(1, (int) round($origH * $scale));
        $dstX = (int) round(($targetSize - $dstW) / 2);
        $dstY = (int) round(($targetSize - $dstH) / 2);

        imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $dstW, $dstH, $origW, $origH);

        ob_start();
        imagepng($canvas, null, 9);
        $pngData = ob_get_clean();
        imagedestroy($canvas);

        return $pngData;
    }

    /**
     * Pack PNG images into a standard Windows ICO binary stream.
     */
    public static function packIco(array $images): string
    {
        $count = count($images);
        $header = pack('vvv', 0, 1, $count);
        $entryOffset = 6 + (16 * $count);
        $entries = '';
        $dataBlock = '';

        foreach ($images as $img) {
            [$size, $data] = $img;
            $length = strlen($data);
            $w = ($size >= 256) ? 0 : $size;
            $h = ($size >= 256) ? 0 : $size;
            $entries .= pack('CCCCvvVV', $w, $h, 0, 0, 1, 32, $length, $entryOffset);
            $dataBlock .= $data;
            $entryOffset += $length;
        }

        return $header.$entries.$dataBlock;
    }

    /**
     * Restore default institutional favicon to public/favicon.ico and public/favicon.svg.
     */
    public static function restoreDefaultFavicon(): void
    {
        // Re-generate default 16x16, 32x32, 48x48 ICO
        try {
            $images = [];
            foreach ([16, 32, 48] as $size) {
                $img = imagecreatetruecolor($size, $size);
                imagealphablending($img, false);
                imagesavealpha($img, true);
                $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
                imagefill($img, 0, 0, $transparent);
                imagealphablending($img, true);

                $emerald = imagecolorallocate($img, 5, 150, 105);
                $white = imagecolorallocate($img, 255, 255, 255);
                $gold = imagecolorallocate($img, 251, 191, 36);

                $r = (int) ($size * 0.25);
                imagefilledellipse($img, $r, $r, $r * 2, $r * 2, $emerald);
                imagefilledellipse($img, $size - $r - 1, $r, $r * 2, $r * 2, $emerald);
                imagefilledellipse($img, $r, $size - $r - 1, $r * 2, $r * 2, $emerald);
                imagefilledellipse($img, $size - $r - 1, $size - $r - 1, $r * 2, $r * 2, $emerald);
                imagefilledrectangle($img, $r, 0, $size - $r - 1, $size - 1, $emerald);
                imagefilledrectangle($img, 0, $r, $size - 1, $size - $r - 1, $emerald);

                $midX = (int) ($size / 2);
                $bookY = (int) ($size * 0.58);
                $bookH = (int) ($size * 0.22);
                $wingW = (int) ($size * 0.32);

                imagefilledpolygon($img, [
                    $midX, $bookY,
                    $midX - $wingW, $bookY - (int) ($bookH * 0.2),
                    $midX - $wingW, $bookY + $bookH - (int) ($bookH * 0.2),
                    $midX, $bookY + $bookH,
                ], $white);

                imagefilledpolygon($img, [
                    $midX, $bookY,
                    $midX + $wingW, $bookY - (int) ($bookH * 0.2),
                    $midX + $wingW, $bookY + $bookH - (int) ($bookH * 0.2),
                    $midX, $bookY + $bookH,
                ], $white);

                $dotR = max(1, (int) ($size * 0.06));
                imagefilledellipse($img, $midX, (int) ($size * 0.28), $dotR * 2, $dotR * 2, $gold);

                ob_start();
                imagepng($img);
                $data = ob_get_clean();
                imagedestroy($img);
                $images[] = [$size, $data];
            }

            File::put(public_path('favicon.ico'), self::packIco($images));
        } catch (\Throwable $e) {
            Log::warning('FaviconService::restoreDefaultFavicon failed: '.$e->getMessage());
        }
    }
}
