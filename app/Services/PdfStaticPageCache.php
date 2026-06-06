<?php

namespace App\Services;

/**
 * Cache PDF tĩnh (PNG→PDF, file PDF gốc) — tránh convert lại mỗi request.
 */
class PdfStaticPageCache
{
    public static function resolve(string $sourcePath, bool $fullPage = true): ?string
    {
        if (!file_exists($sourcePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return $sourcePath;
        }

        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/static');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $mtime = filemtime($sourcePath) ?: 0;
        $size  = filesize($sourcePath) ?: 0;
        $key   = hash('xxh128', $sourcePath . '|' . $mtime . '|' . $size . '|' . ($fullPage ? 'full' : 'fit') . '|raster-v4');
        $cached = $cacheDir . DIRECTORY_SEPARATOR . $key . '.pdf';

        if (file_exists($cached)) {
            return $cached;
        }

        $ok = $fullPage
            ? PdfMergeService::convertImageToPdfFullPage($sourcePath, $cached)
            : PdfMergeService::convertImageToPdf($sourcePath, $cached);

        return $ok && file_exists($cached) ? $cached : null;
    }

    /**
     * @param  array<int, string>  $sourcePaths
     * @return array<int, string>
     */
    public static function resolveMany(array $sourcePaths, bool $fullPage = true): array
    {
        $out = [];
        foreach ($sourcePaths as $path) {
            $resolved = self::resolve($path, $fullPage);
            if ($resolved !== null) {
                $out[] = $resolved;
            }
        }

        return $out;
    }

    /**
     * Merge nhiều trang tĩnh thành 1 PDF cache (giảm số file khi FPDI merge).
     *
     * @param  array<int, string>  $sourcePaths
     */
    public static function resolveBundle(string $bundleKey, array $sourcePaths, bool $fullPage = true): ?string
    {
        $resolved = self::resolveMany($sourcePaths, $fullPage);
        if ($resolved === []) {
            return null;
        }

        if (count($resolved) === 1) {
            return $resolved[0];
        }

        $cacheDir = storage_path('app/pdf-cache/bundles');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $parts = [];
        foreach ($sourcePaths as $path) {
            $parts[] = $path . '|' . (file_exists($path) ? (filemtime($path) ?: 0) : 0) . '|' . (file_exists($path) ? (filesize($path) ?: 0) : 0);
        }

        $key    = hash('xxh128', $bundleKey . '|' . implode(';', $parts) . '|' . ($fullPage ? 'full' : 'fit') . '|raster-v4');
        $cached = $cacheDir . DIRECTORY_SEPARATOR . $key . '.pdf';

        if (file_exists($cached)) {
            return $cached;
        }

        if (!PdfMergeService::mergeMultiple($resolved, $cached)) {
            return null;
        }

        return file_exists($cached) ? $cached : null;
    }
}
