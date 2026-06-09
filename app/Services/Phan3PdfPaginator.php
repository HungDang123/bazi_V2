<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;

/**
 * @deprecated Delegate — logic tại PdfContentPaginator + PdfPaginationProfiles::phan3()
 */
class Phan3PdfPaginator
{
    /**
     * @param  array{chapterTitle?: string, subSections?: array<int, array{sub_title?: string, content?: array<int, array<string, mixed>>}>}  $section
     * @return array<int, array<string, mixed>>
     */
    public static function paginateBocucSection(
        array $section,
        string $firstBgPath,
        string $contBgPath
    ): array {
        return self::paginateFlatBlocks(
            trim((string) ($section['chapterTitle'] ?? '')),
            self::flattenSubSections($section['subSections'] ?? []),
            $firstBgPath,
            $contBgPath
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginateFlatBlocks(
        string $chapterTitle,
        array $blocks,
        string $firstBgPath,
        string $contBgPath
    ): array {
        if ($blocks === []) {
            return [];
        }

        return PdfContentPaginator::paginate(
            $blocks,
            PdfPaginationProfiles::phan3WithChapter($firstBgPath, $contBgPath, $chapterTitle)
        );
    }

    /**
     * @param  array<int, array{sub_title?: string, content?: array<int, array<string, mixed>>}>  $subSections
     * @return array<int, array<string, mixed>>
     */
    private static function flattenSubSections(array $subSections): array
    {
        $flat = [];

        foreach ($subSections as $sub) {
            $title = trim((string) ($sub['sub_title'] ?? ''));
            if ($title !== '') {
                $flat[] = ['type' => 'sub_title', 'text' => $title];
            }

            foreach ($sub['content'] ?? [] as $block) {
                $flat[] = $block;
            }
        }

        return $flat;
    }
}
