<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;

/**
 * @deprecated Delegate — logic tại PdfContentPaginator + PdfPaginationProfiles::phan6()
 */
class Phan6PdfPaginator
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $blocks, string $bgPath): array
    {
        return PdfContentPaginator::paginate($blocks, PdfPaginationProfiles::phan6($bgPath));
    }
}
