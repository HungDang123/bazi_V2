<?php

namespace App\Services;

use App\Http\Controllers\TongQuanKhiaCanhController;
use Illuminate\Http\Request;

class Phan8PdfService
{
    public static function coverImagePath(): string
    {
        return Phan8AssetService::coverImagePath();
    }

    /**
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    public static function buildPdfPages(Request $req): array
    {
        $controller = app(TongQuanKhiaCanhController::class);

        $daiVanRes = $controller->daiVan($req);
        $daiVan = $daiVanRes->getData(true)['data'] ?? null;

        $nienVanRes = $controller->nienVan($req);
        $nienVan = $nienVanRes->getData(true)['data'] ?? null;

        if (! is_array($daiVan) && ! is_array($nienVan)) {
            return [];
        }

        $pageSpecs = Phan8ContentService::buildAllPageSpecs(
            is_array($daiVan) ? $daiVan : null,
            is_array($nienVan) ? $nienVan : null
        );

        if ($pageSpecs === []) {
            return [];
        }

        $pdfPages = [];
        $bufType = null;
        $bufBlocks = [];

        foreach ($pageSpecs as $spec) {
            $type = $spec['type'] ?? 'content';

            if ($type === 'nien_van_cover') {
                self::appendTextPages($pdfPages, $bufType, $bufBlocks);
                $bufType = null;
                $bufBlocks = [];

                $pdfPages[] = [
                    'view' => 'pdfs.phan-8.la-so-phan-8-nien-van-cover',
                    'data' => [
                        'bgPath' => Phan8AssetService::nienVanBgPath(),
                        'title' => $spec['title'] ?? '',
                        'subtitle' => $spec['subtitle'] ?? '',
                    ],
                ];

                continue;
            }

            if ($type === 'coding') {
                self::appendTextPages($pdfPages, $bufType, $bufBlocks);
                $bufType = null;
                $bufBlocks = [];

                $codingPages = Phan8CodingPaginator::paginate($spec['data'] ?? []);
                if ($codingPages === []) {
                    continue;
                }

                $pdfPages[] = [
                    'view' => 'pdfs.phan-8.la-so-phan-8-coding',
                    'data' => ['pages' => $codingPages],
                ];

                continue;
            }

            $blocks = $spec['blocks'] ?? [];
            if ($blocks === []) {
                continue;
            }

            // Gộp các block văn bản liên tiếp cùng loại nền để nội dung chảy liền,
            // tránh trang intro đứng riêng bị trống nửa dưới.
            if ($bufType !== null && $bufType !== $type) {
                self::appendTextPages($pdfPages, $bufType, $bufBlocks);
                $bufBlocks = [];
            }

            $bufType = $type;
            $bufBlocks = array_merge($bufBlocks, $blocks);
        }

        self::appendTextPages($pdfPages, $bufType, $bufBlocks);

        return $pdfPages;
    }

    /**
     * @param  array<int, array{view: string, data: array<string, mixed>}>  $pdfPages
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private static function appendTextPages(array &$pdfPages, ?string $type, array $blocks): void
    {
        if ($type === null || $blocks === []) {
            return;
        }

        $bgPath = $type === 'nien_van'
            ? Phan8AssetService::nienVanBgPath()
            : Phan8AssetService::contentBgPath();

        $view = $type === 'nien_van'
            ? 'pdfs.phan-8.la-so-phan-8-nien-van'
            : 'pdfs.phan-8.la-so-phan-8-content';

        $heightMm = $type === 'nien_van' ? 252.0 : 258.0;

        foreach (Phan8PdfPaginator::paginate($blocks, $bgPath, $heightMm) as $page) {
            $pdfPages[] = [
                'view' => $view,
                'data' => ['pages' => [$page]],
            ];
        }
    }
}
