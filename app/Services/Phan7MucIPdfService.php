<?php

namespace App\Services;

class Phan7MucIPdfService
{
    public static function contentBgPath(): string
    {
        return resource_path('views/pdfs/phan-7/page-content-bg.png');
    }

    /**
     * @return array{view: string, data: array{pages: array<int, mixed>}}|null
     */
    public static function buildContentPageSpec(int $sheetIndex = 0): ?array
    {
        $blocks = Phan7ContentService::buildTamTheBlocks($sheetIndex);

        if ($blocks === []) {
            return null;
        }

        $rawPages = Phan7PdfPaginator::paginateMuc1($blocks, self::contentBgPath(), $sheetIndex === 0);

        if ($rawPages === []) {
            return null;
        }

        return [
            'view' => 'pdfs.phan-7.la-so-phan-7-muc2-content',
            'data' => ['pages' => $rawPages],
        ];
    }
}
