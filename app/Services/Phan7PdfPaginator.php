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
        return PdfContentPaginator::paginate($blocks, PdfPaginationProfiles::phan7($bgPath));
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginateMuc1(array $blocks, string $bgPath): array
    {
        return PdfContentPaginator::paginate($blocks, PdfPaginationProfiles::phan7Muc1($bgPath));
    }
}
