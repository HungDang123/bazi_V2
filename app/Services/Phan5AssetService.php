<?php

namespace App\Services;

class Phan5AssetService
{
    public const RESOURCE_PREFIX = 'resources/views/pdfs/phan-5';

    public const PUBLIC_PREFIX = 'images/pdfs/phan-5';

    public static function resourceDir(): string
    {
        return resource_path('views/pdfs/phan-5');
    }

    public static function toResourceRelative(string $subpath): string
    {
        return self::RESOURCE_PREFIX.'/'.ltrim(str_replace('\\', '/', $subpath), '/');
    }

    public static function resolvePath(string $relative): string
    {
        $relative = str_replace('\\', '/', trim($relative));
        $candidates = [
            base_path($relative),
            resource_path(str_replace('resources/', '', $relative)),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return str_replace('\\', '/', realpath($path) ?: $path);
            }
        }

        return base_path($relative);
    }

    public static function publicRelative(string $markerPath): ?string
    {
        $relative = str_replace('\\', '/', trim($markerPath));
        if (str_starts_with($relative, self::PUBLIC_PREFIX.'/') || $relative === self::PUBLIC_PREFIX) {
            return $relative;
        }

        if (str_starts_with($relative, self::RESOURCE_PREFIX.'/')) {
            return self::PUBLIC_PREFIX.'/'.substr($relative, strlen(self::RESOURCE_PREFIX) + 1);
        }

        return null;
    }

    /** Copy ảnh từ resources sang public để web/API phục vụ được. */
    public static function syncPublicMirror(string $markerPath): bool
    {
        $source = self::resolvePath($markerPath);
        if (! is_file($source) || filesize($source) === 0) {
            return false;
        }

        $publicRelative = self::publicRelative($markerPath);
        if ($publicRelative === null) {
            return false;
        }

        $dest = public_path($publicRelative);
        $destDir = dirname($dest);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (is_file($dest) && filesize($dest) === filesize($source)) {
            return true;
        }

        return copy($source, $dest);
    }

    public static function publicUrl(string $markerPath): string
    {
        $relative = str_replace('\\', '/', trim($markerPath));
        if (str_starts_with($relative, 'images/')) {
            return '/'.$relative;
        }

        $publicRelative = self::publicRelative($relative);
        if ($publicRelative !== null) {
            self::syncPublicMirror($relative);

            return '/'.$publicRelative;
        }

        return DocxTextService::publicUrlForResourcePath($relative);
    }

    /**
     * @param  array<int, string>  $paths
     * @return array{synced: array<int, string>, missing: array<int, string>}
     */
    public static function verifyAndSync(array $paths): array
    {
        $synced = [];
        $missing = [];

        foreach (array_unique(array_filter(array_map('trim', $paths))) as $path) {
            if ($path === '') {
                continue;
            }

            $resolved = self::resolvePath($path);
            if (! is_file($resolved) || filesize($resolved) === 0) {
                $missing[] = $path;

                continue;
            }

            if (self::syncPublicMirror($path)) {
                $synced[] = $path;
            }
        }

        return ['synced' => $synced, 'missing' => $missing];
    }
}
