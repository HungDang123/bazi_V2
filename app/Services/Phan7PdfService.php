<?php

namespace App\Services;

/**
 * Phần 7 – Bài học cuộc sống (Quyển 2): chỉ trang bìa.
 */
class Phan7PdfService
{
    /** @var array<int, string> filename trong resources/views/pdfs/phan-7/ */
    private const PAGE_FILES = [
        'LBTV - 582.png', // bìa PHẦN 7
    ];

    public static function muc1FirstBgPath(): string
    {
        return self::pageDir().'/LBTV - 583.png';
    }

    public static function pageDir(): string
    {
        return resource_path('views/pdfs/phan-7');
    }

    /**
     * @return array<int, string> đường dẫn đầy đủ theo thứ tự ghép PDF
     */
    public static function staticPagePaths(): array
    {
        $dir = self::pageDir();
        $paths = [];
        foreach (self::PAGE_FILES as $file) {
            $paths[] = $dir.'/'.$file;
        }

        return $paths;
    }

    public static function bundleCacheKey(): string
    {
        return 'phan7-bia-v5';
    }
}
