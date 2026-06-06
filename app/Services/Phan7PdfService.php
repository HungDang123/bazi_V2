<?php

namespace App\Services;

/**
 * Phần 7 – Bài học cuộc sống (Quyển 2): trang tĩnh theo thứ tự đọc trong sách,
 * không sắp theo số LBTV trong tên file.
 */
class Phan7PdfService
{
    /** @var array<int, string> filename trong resources/views/pdfs/phan-7/ */
    private const PAGE_FILES = [
        'page-537.png', // 22 – bìa PHẦN 7
        'page-166.png', // 23 – mở đầu + sơ đồ 5 trung tâm
        'page-167.png', // 24 – 1. Huynh Đệ
        'page-168.png', // 25 – tiếp Huynh Đệ
        'page-169.png', // 26 – 2. Quan Quỷ
        'page-554.png', // 27 – tiếp Quan Quỷ
        'page-170.png', // 28 – 3. Phụ Mẫu
        'page-555.png', // 29 – tiếp Phụ Mẫu
        'page-171.png', // 30 – 4. Thê Tài
        'page-556.png', // 31 – tiếp Thê Tài
        'page-557.png', // 33 – kết Phần 7
    ];

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
        return 'phan7-pages-reading-order-v2';
    }
}
