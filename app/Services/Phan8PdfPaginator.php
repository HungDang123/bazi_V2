<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;

/**
 * @deprecated Delegate — logic tại PdfContentPaginator + PdfPaginationProfiles::phan8()
 */
class Phan8PdfPaginator
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $blocks, string $bgPath, float $contentHeightMm = 0.0): array
    {
        return PdfContentPaginator::paginate(
            $blocks,
            PdfPaginationProfiles::phan8($bgPath, $contentHeightMm)
        );
    }
}
