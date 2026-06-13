<?php

namespace App\Services\Pdf;

/**
 * Tài nguyên trang Mục lục — thuyền rồng overlay PNG trong suốt.
 */
class PdfTocAssetService
{
    private const CACHE_VERSION = 'v1';

    /** Ngưỡng RGB coi là nền trắng → alpha 0. */
    private const WHITE_THRESHOLD = 248;

    public static function backgroundPath(int $pageIndex = 0): string
    {
        if ($pageIndex > 0) {
            return resource_path('views/pdfs/shared/lbtv-587-muc-luc-bg.png');
        }

        return resource_path('views/pdfs/shared/lbtv-586-muc-luc-bg.png');
    }

    /** PNG thuyền rồng góc dưới — nền trong suốt, cache GD. */
    public static function dragonOverlayPath(): string
    {
        $cacheDir = storage_path('app/pdf-cache');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cachePath = $cacheDir.DIRECTORY_SEPARATOR.'toc-dragon-overlay-'.self::CACHE_VERSION.'.png';
        if (is_file($cachePath)) {
            return $cachePath;
        }

        $source = resource_path('views/pdfs/shared/lbtv-587-muc-luc-bg.png');
        if (! is_file($source) || ! function_exists('imagecreatefrompng')) {
            return '';
        }

        $blob = @file_get_contents($source);
        if ($blob === false || $blob === '') {
            return '';
        }

        $src = @imagecreatefromstring($blob);
        if ($src === false) {
            return '';
        }

        $sw = imagesx($src);
        $sh = imagesy($src);

        // Vùng crop: góc dưới-phải chứa thuyền rồng (tỉ lệ từ PNG 723×1024).
        $cropX = (int) round($sw * 0.32);
        $cropY = (int) round($sh * 0.56);
        $cropW = $sw - $cropX;
        $cropH = $sh - $cropY;

        $dst = imagecreatetruecolor($cropW, $cropH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);

        imagecopy($dst, $src, 0, 0, $cropX, $cropY, $cropW, $cropH);
        imagedestroy($src);

        for ($y = 0; $y < $cropH; $y++) {
            for ($x = 0; $x < $cropW; $x++) {
                $rgba = imagecolorat($dst, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($r >= self::WHITE_THRESHOLD && $g >= self::WHITE_THRESHOLD && $b >= self::WHITE_THRESHOLD) {
                    imagesetpixel($dst, $x, $y, $transparent);
                }
            }
        }

        imagepng($dst, $cachePath);
        imagedestroy($dst);

        return is_file($cachePath) ? $cachePath : '';
    }
}
