<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;

/**
 * @deprecated Delegate — logic tại PdfContentPaginator + PdfPaginationProfiles::phan5()
 */
class Phan5PdfPaginator
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  ?array{type: string, text: string}  $continuationHeader
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(
        array $blocks,
        string $bgPath,
        float $contentHeightMm,
        string $layoutVariant = '',
        ?array $continuationHeader = null
    ): array {
        return PdfContentPaginator::paginate(
            $blocks,
            PdfPaginationProfiles::phan5($bgPath, $layoutVariant, $continuationHeader)
        );
    }

    public static function contentHeightForLayout(string $layout): float
    {
        return PdfPaginationProfiles::contentHeightForLayout($layout);
    }
}
