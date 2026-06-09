<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;
use App\Models\DongChayGioiThieu;
use App\Models\Phan9aNgoaiLuc;
use App\Models\Phan9aNoiLuc;

/**
 * Xây dựng trang PDF Phần 9 – Giải Pháp Tối Ưu Để Kiến Tạo Vận Mệnh.
 *
 * Bìa   : LBTV-236 (ảnh tĩnh) – xử lý ở PdfExportController.
 * Nội dung: LBTV-119 (page-content-bg.png) + la-so-phan-8-content (tái sử dụng).
 */
class Phan9PdfService
{
    public static function coverImagePath(): string
    {
        return resource_path('views/pdfs/phan-9/bia-phan-9.png');
    }

    public static function contentBgPath(): string
    {
        return resource_path('views/pdfs/phan-9/page-content-bg.png');
    }

    /**
     * Lấy data và phân trang Phần 9.
     *
     * @param  array<string, int>  $nguHanhDong  Ngũ hành bản mệnh đã chuẩn hóa
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    public static function buildPdfPages(array $nguHanhDong): array
    {
        $blocks = self::buildBlocks($nguHanhDong);
        if ($blocks === []) {
            return [];
        }

        $bgPath = self::contentBgPath();
        $pages = PdfContentPaginator::paginate($blocks, PdfPaginationProfiles::phan9($bgPath));

        if ($pages === []) {
            return [];
        }

        return [
            [
                'view' => 'pdfs.phan-8.la-so-phan-8-content',
                'data' => ['pages' => $pages],
            ],
        ];
    }

    /**
     * Xây dựng mảng blocks từ data DB.
     *
     * @param  array<string, int>  $nguHanhDong
     * @return array<int, array<string, mixed>>
     */
    private static function buildBlocks(array $nguHanhDong): array
    {
        $blocks = [];

        $yeuNhat = Phan9aService::resolveYeuNhatNguHanh($nguHanhDong);

        // ── I. NỘI LỰC TỰ THÂN ──────────────────────────────────────────────
        $blocks[] = ['type' => 'chapter_title', 'text' => 'I. NỘI LỰC TỰ THÂN'];

        $introRows = Phan9aNoiLuc::query()
            ->where('loai', 'intro')
            ->orderBy('sort_order')
            ->get();

        foreach ($introRows as $row) {
            $text = trim((string) ($row->noi_dung ?? ''));
            if ($text === '') {
                continue;
            }
            if ($yeuNhat !== null) {
                $text = Phan9aService::replaceIntroPlaceholders(
                    $text,
                    $yeuNhat['ten'],
                    $yeuNhat['phan_tram']
                );
            }
            $blocks[] = ['type' => 'para', 'text' => $text];
        }

        if ($yeuNhat !== null) {
            $hanhRows = Phan9aNoiLuc::query()
                ->where('loai', 'hanh')
                ->where('ngu_hanh', $yeuNhat['slug'])
                ->orderBy('sort_order')
                ->get();

            $display = Phan9aService::buildHanhDisplay($hanhRows);

            if ($display['tieu_de_chinh'] !== null) {
                $blocks[] = ['type' => 'sub_title', 'text' => $display['tieu_de_chinh']];
            }

            foreach ($display['sections'] as $section) {
                if (! empty($section['tieu_de'])) {
                    $blocks[] = ['type' => 'sub_title', 'text' => $section['tieu_de']];
                }
                foreach ($section['doan'] as $para) {
                    $para = trim($para);
                    if ($para !== '') {
                        $blocks[] = ['type' => 'para', 'text' => $para];
                    }
                }
            }
        }

        // ── Transition ───────────────────────────────────────────────────────
        $transition = DongChayGioiThieu::query()
            ->where('tru_loai', 'transition_phan9a')
            ->first();

        if ($transition !== null) {
            $transText = trim((string) ($transition->noi_dung ?? ''));
            if ($transText !== '') {
                $blocks[] = ['type' => 'sub_title', 'text' => $transText];
            }
        }

        // ── II. NGOẠI LỰC ───────────────────────────────────────────────────
        $ngoaiLuc = Phan9aNgoaiLuc::query()->orderBy('sort_order')->first();
        if ($ngoaiLuc !== null) {
            $tieude = trim((string) ($ngoaiLuc->tieu_de ?? ''));
            $blocks[] = ['type' => 'chapter_title', 'text' => $tieude !== '' ? $tieude : 'II. NGOẠI LỰC'];

            $paragraphs = array_values(array_filter(
                preg_split('/\r\n|\r|\n/', (string) ($ngoaiLuc->noi_dung ?? '')) ?: [],
                static fn (string $p): bool => trim($p) !== ''
            ));

            foreach ($paragraphs as $para) {
                $para = trim($para);
                if ($para !== '') {
                    $blocks[] = ['type' => 'para', 'text' => $para];
                }
            }
        }

        return $blocks;
    }
}
