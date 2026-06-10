<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;

/**
 * @deprecated Delegate — logic tại PdfContentPaginator + PdfPaginationProfiles::phan7()
 */
class Phan7PdfPaginator
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $blocks, string $bgPath): array
    {
        return self::filterEmptyPages(
            PdfContentPaginator::paginate($blocks, PdfPaginationProfiles::phan7($bgPath))
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginateMuc1(array $blocks, string $bgPath, bool $useMuc1IntroBg = true): array
    {
        $firstBg = $useMuc1IntroBg ? Phan7PdfService::muc1FirstBgPath() : $bgPath;

        return self::filterEmptyPages(
            PdfContentPaginator::paginate(
                $blocks,
                PdfPaginationProfiles::phan7Muc1($bgPath, $firstBg)
            )
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     * @return array<int, array<string, mixed>>
     */
    private static function filterEmptyPages(array $pages): array
    {
        return array_values(array_filter($pages, static function (array $page): bool {
            foreach ($page['blocks'] ?? [] as $block) {
                if (! is_array($block)) {
                    continue;
                }
                $type = (string) ($block['type'] ?? '');
                if ($type === 'image' && trim((string) ($block['path'] ?? '')) !== '') {
                    return true;
                }
                if (trim((string) ($block['text'] ?? '')) !== '') {
                    return true;
                }
                if ($type === 'thap_than_title' && trim((string) ($block['title'] ?? '')) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }
}
