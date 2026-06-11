<?php

namespace App\Services;

class Phan6AssetService
{
    public const RESOURCE_PREFIX = 'resources/views/pdfs/phan-6';

    public const PUBLIC_PREFIX = 'images/pdfs/phan-6';

    public static function resourceDir(): string
    {
        return resource_path('views/pdfs/phan-6');
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

    public static function coverImagePath(): string
    {
        $path = self::resourceDir().'/bia-phan-6.png';

        return is_file($path) ? $path : $path;
    }

    public static function contentBgPath(): string
    {
        return Phan8AssetService::nienVanBgPath();
    }

    public static function resolveImagePath(?string $urlOrPath): ?string
    {
        if ($urlOrPath === null || trim($urlOrPath) === '') {
            return null;
        }

        $raw = trim($urlOrPath);
        if (is_file($raw)) {
            return str_replace('\\', '/', realpath($raw) ?: $raw);
        }

        $path = ltrim(parse_url($raw, PHP_URL_PATH) ?: $raw, '/');
        if (str_starts_with($path, 'images/')) {
            $full = public_path($path);

            return is_file($full) ? str_replace('\\', '/', realpath($full) ?: $full) : null;
        }

        if (str_starts_with($path, self::PUBLIC_PREFIX.'/')) {
            $full = public_path($path);

            return is_file($full) ? str_replace('\\', '/', realpath($full) ?: $full) : null;
        }

        $resolved = self::resolvePath($raw);

        return is_file($resolved) ? $resolved : null;
    }
}
