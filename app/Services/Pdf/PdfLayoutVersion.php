<?php

namespace App\Services\Pdf;

/**
 * Fingerprint layout/CSS/pagination — PdfViewCache tự bust khi file liên quan đổi.
 * Bump VERSION khi đổi logic phân trang không nằm trong watched files.
 */
class PdfLayoutVersion
{
    public const VERSION = '2026-06-13-toc-dragon';

    public static function fingerprint(): string
    {
        $parts = [self::VERSION];

        $partialsDir = resource_path('views/pdfs/partials');
        if (is_dir($partialsDir)) {
            $mtimes = [];
            foreach (glob($partialsDir.DIRECTORY_SEPARATOR.'*.blade.php') ?: [] as $file) {
                $mtimes[] = (string) filemtime($file);
            }
            sort($mtimes);
            $parts[] = implode(',', $mtimes);
        }

        foreach (self::watchedServiceFiles() as $file) {
            $parts[] = is_file($file) ? (string) filemtime($file) : '0';
        }

        return hash('xxh128', implode('|', $parts));
    }

    /** @return array<int, string> */
    private static function watchedServiceFiles(): array
    {
        return [
            app_path('Services/Pdf/PdfPaginationProfiles.php'),
            app_path('Services/Pdf/PdfContentPaginator.php'),
            app_path('Services/Pdf/Phan3NguHanhBanMenhPaginator.php'),
            app_path('Services/Pdf/Phan5TraitLayout.php'),
            app_path('Services/PdfRenderService.php'),
            app_path('Services/NguHanhTitleRenderer.php'),
            app_path('Services/PdfViewCache.php'),
        ];
    }
}
