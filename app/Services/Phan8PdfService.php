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
    public static function buildPdfPages(Request $req, string $phanBan = '8a'): array
    {
        $controller = app(TongQuanKhiaCanhController::class);
        $phanBan = $phanBan === '8b' ? '8b' : '8a';

        $daiVan = null;
        $nienVan = null;
        $nhungNam = null;
        $duBao = null;

        if ($phanBan === '8a') {
            $daiVanRes = $controller->daiVan($req);
            $daiVan = $daiVanRes->getData(true)['data'] ?? null;

            $nhungNamRes = $controller->nhungNamCanChuY($req);
            $nhungNam = $nhungNamRes->getData(true)['data'] ?? null;
        } else {
            $req8b = $req->duplicate();
            $req8b->merge(['phan_ban' => '8b']);

            $nienVanRes = $controller->nienVan($req8b);
            $nienVan = $nienVanRes->getData(true)['data'] ?? null;

            $duBaoRes = $controller->duBaoKhiaCanh($req8b);
            $duBao = $duBaoRes->getData(true)['data'] ?? null;
        }

        $pageSpecs = Phan8ContentService::buildAllPageSpecs(
            is_array($daiVan) ? $daiVan : null,
            is_array($nienVan) ? $nienVan : null,
            $phanBan,
            is_array($nhungNam) ? $nhungNam : null,
            is_array($duBao) ? $duBao : null
        );

        if ($pageSpecs === []) {
            return [];
        }

        $pdfPages = [];
        $bufType = null;
        $bufBlocks = [];
        $ivTableBuf = [];
        $ivIntroBlocks = [];

        foreach ($pageSpecs as $spec) {
            $type = $spec['type'] ?? 'content';

            if ($type === 'nien_van_cover') {
                self::appendTextPages($pdfPages, $bufType, $bufBlocks);
                self::flushIvTables($pdfPages, $ivTableBuf);
                $bufType = null;
                $bufBlocks = [];
                $ivTableBuf = [];

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
                self::flushIvTables($pdfPages, $ivTableBuf);
                $bufType = null;
                $bufBlocks = [];
                $ivTableBuf = [];

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

            if ($type === 'iv_table') {
                if ($bufBlocks !== []) {
                    $ivIntroBlocks = array_merge($ivIntroBlocks, $bufBlocks);
                    $bufBlocks = [];
                    $bufType = null;
                }

                $tableData = $spec['data'] ?? null;
                if (is_array($tableData) && ($tableData['years'] ?? []) !== []) {
                    $ivTableBuf[] = $tableData;
                }

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
        self::flushIvTables($pdfPages, $ivTableBuf, $ivIntroBlocks);

        return $pdfPages;
    }

    /** Max iv_table rows per PDF page (each table ~50–55 mm tall on A4). */
    private const IV_TABLES_PER_PAGE = 4;

    /**
     * Batch accumulated iv_table data into pages (multiple tables per page).
     *
     * @param  array<int, array{view: string, data: array<string, mixed>}>  $pdfPages
     * @param  array<int, array<string, mixed>>  $ivTableBuf
     */
    private static function flushIvTables(array &$pdfPages, array $ivTableBuf, array $introBlocks = []): void
    {
        if ($ivTableBuf === []) {
            if ($introBlocks !== []) {
                self::appendTextPages($pdfPages, 'content', $introBlocks);
            }

            return;
        }

        $first = true;
        foreach (array_chunk($ivTableBuf, self::IV_TABLES_PER_PAGE) as $group) {
            $pdfPages[] = [
                'view' => 'pdfs.phan-8.la-so-phan-8-iv-table',
                'data' => [
                    'bgPath' => Phan8AssetService::contentBgPath(),
                    'tables' => $group,
                    'introBlocks' => $first ? $introBlocks : [],
                ],
            ];
            $first = false;
        }
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

        $pages = Phan8PdfPaginator::paginate($blocks, $bgPath);
        if ($pages === []) {
            return;
        }

        $pdfPages[] = [
            'view' => $view,
            'data' => ['pages' => $pages],
        ];
    }
}
