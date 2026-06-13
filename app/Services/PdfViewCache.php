<?php

namespace App\Services;

use App\Services\Pdf\PdfLayoutVersion;

/**
 * Cache PDF render từ blade khi dữ liệu không đổi giữa các request (vd. nội dung DB tĩnh).
 */
class PdfViewCache
{
    public static function saveView(string $view, array $data, string $outputPath, ?string $cacheSalt = null): string
    {
        $cacheDir = storage_path('app/pdf-cache/views');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $key    = hash('xxh128', PdfLayoutVersion::fingerprint().'|'.$view.'|'.($cacheSalt ?? '').'|'.self::dataFingerprint($data));
        $cached = $cacheDir . DIRECTORY_SEPARATOR . $key . '.pdf';

        if (file_exists($cached)) {
            if ($cached !== $outputPath && !copy($cached, $outputPath)) {
                PdfRenderService::saveView($view, $data, $outputPath);
            }

            return $cached;
        }

        PdfRenderService::saveView($view, $data, $cached);

        if ($cached !== $outputPath) {
            copy($cached, $outputPath);
        }

        return $cached;
    }

    private static function dataFingerprint(array $data): string
    {
        return hash('xxh128', serialize(self::normalize($data)));
    }

    /**
     * @return mixed
     */
    private static function normalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        ksort($value);
        foreach ($value as $k => $v) {
            $value[$k] = self::normalize($v);
        }

        return $value;
    }
}
