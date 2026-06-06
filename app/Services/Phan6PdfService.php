<?php

namespace App\Services;

use App\Http\Controllers\TongQuanKhiaCanhController;
use Illuminate\Http\Request;

class Phan6PdfService
{
    public static function coverImagePath(): string
    {
        return Phan6AssetService::coverImagePath();
    }

    public static function contentBgPath(): string
    {
        return Phan6AssetService::contentBgPath();
    }

    /**
     * @return array{view: string, data: array{pages: array<int, array<string, mixed>>}}|null
     */
    public static function buildContentPageSpec(Request $req): ?array
    {
        $data = app(TongQuanKhiaCanhController::class)->buildPhan6ApiDataFromRequest($req);
        if ($data === null) {
            return null;
        }

        $blocks = Phan6ContentService::buildAllBlocks($data);
        if ($blocks === []) {
            return null;
        }

        $pages = Phan6PdfPaginator::paginate($blocks, self::contentBgPath());
        if ($pages === []) {
            return null;
        }

        return [
            'view' => 'pdfs.phan-6.la-so-phan-6-content',
            'data' => ['pages' => $pages],
        ];
    }
}
