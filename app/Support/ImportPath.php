<?php

namespace App\Support;

class ImportPath
{
    public static function dir(): string
    {
        return base_path('imports');
    }

    public static function file(string $filename): string
    {
        return self::dir().DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * Ưu tiên: đường dẫn truyền vào → imports/ → thư mục gốc (legacy) → database/ (legacy).
     */
    public static function resolve(?string $explicit, string $filename): string
    {
        if ($explicit !== null && $explicit !== '' && is_file($explicit)) {
            return $explicit;
        }

        $candidates = [
            self::file($filename),
            base_path($filename),
            base_path('database/'.$filename),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return self::file($filename);
    }

    public static function exists(string $filename): bool
    {
        return is_file(self::resolve(null, $filename));
    }

    /**
     * Thử lần lượt nhiều tên file trong imports/ (và legacy).
     *
     * @param  array<int, string>  $filenames
     */
    public static function resolveFirst(?string $explicit, array $filenames): ?string
    {
        if ($explicit !== null && $explicit !== '' && is_file($explicit)) {
            return $explicit;
        }

        foreach ($filenames as $filename) {
            $path = self::resolve(null, $filename);
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
