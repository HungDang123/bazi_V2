<?php

namespace App\Services;

use App\Models\Phan9bNoiLuc;
use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationProfiles;
use App\Services\Pdf\PdfTextSanitizer;

/**
 * PDF Mục II.1 — Nội lực Ngũ Hành (nền kim/moc/thuy/hoa/tho + coding layout).
 */
class Phan9bNoiLucPdfBuilder
{
    /**
     * @param  array<string, mixed>  $nl
     * @return array<int, array<string, mixed>>
     */
    public static function buildIntroPages(array $nl): array
    {
        $blocks = [];

        $blocks[] = [
            'type' => 'chapter_title',
            'text' => ($nl['tieu_de'] ?? null) ?: 'II. NỘI LỰC TỰ THÂN',
        ];

        foreach ($nl['intro'] ?? [] as $para) {
            self::appendParagraphs($blocks, (string) $para);
        }

        if (! empty($nl['muc'])) {
            $blocks[] = ['type' => 'sub_title', 'text' => (string) $nl['muc']];
        }

        if ($blocks === []) {
            return [];
        }

        $profile = PdfPaginationProfiles::phan9(Phan9bAssetService::contentBgPath());

        return Phan9bPdfService::compactFlatPages(
            PdfContentPaginator::paginate($blocks, $profile),
            $profile
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $hanhBlocks
     * @return array<int, array<string, mixed>>
     */
    public static function buildHanhPages(array $hanhBlocks): array
    {
        $datas = self::buildHanhCodingDatas($hanhBlocks);
        if ($datas === []) {
            return [];
        }

        return Phan8CodingPaginator::paginateMany($datas);
    }

    /**
     * @param  array<int, array<string, mixed>>  $hanhBlocks
     * @return array<int, array<string, mixed>>
     */
    public static function buildHanhCodingDatas(array $hanhBlocks): array
    {
        $datas = [];

        foreach ($hanhBlocks as $hanh) {
            if (! is_array($hanh)) {
                continue;
            }

            $slug = trim((string) ($hanh['ngu_hanh']['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $sections = [];
            foreach ($hanh['sections'] ?? [] as $sec) {
                if (! is_array($sec)) {
                    continue;
                }

                $label = trim((string) ($sec['tieu_de'] ?? ''));
                $lines = [];
                foreach ($sec['doan'] ?? [] as $para) {
                    $para = trim((string) $para);
                    if ($para !== '') {
                        $lines[] = $para;
                    }
                }

                $content = PdfTextSanitizer::trimMultiline(implode("\n", $lines));
                if ($label === '' && $content === '') {
                    continue;
                }

                $sections[] = [
                    'label' => $label,
                    'content' => $content,
                ];
            }

            if ($sections === []) {
                continue;
            }

            $ten = mb_strtoupper(trim((string) ($hanh['ngu_hanh']['ten'] ?? '')), 'UTF-8');
            $tagline = trim((string) ($hanh['tieu_de_chinh'] ?? ''));

            $titleImg = NguHanhTitleRenderer::goldTitleToFilePath($ten, 25, 162.0);
            $contImg = NguHanhTitleRenderer::goldTitleToFilePath($ten.' (tiếp)', 12, 162.0);

            $data = [
                'bgPath' => Phan9bAssetService::hanhBgPath($slug),
                'title' => $ten,
                'titleImagePath' => $titleImg['path'],
                'titleImageHeightMm' => $titleImg['heightMm'],
                'contTitleImagePath' => $contImg['path'],
                'contTitleImageHeightMm' => $contImg['heightMm'],
                'subtitle' => '',
                'sections' => $sections,
            ];

            if ($tagline !== '') {
                $subtitleImg = NguHanhTitleRenderer::goldTitleToFilePath($tagline, 16, 162.0);
                $data['subtitleImagePath'] = $subtitleImg['path'];
                $data['subtitleImageHeightMm'] = $subtitleImg['heightMm'];
            }

            $datas[] = PdfTextSanitizer::trimCodingData($data);
        }

        return $datas;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>|null  $tt
     */
    public static function appendThapThanBlocks(array &$blocks, ?array $tt, string $thapThanLabel): void
    {
        if ($tt === null) {
            return;
        }

        if ($thapThanLabel !== '') {
            $blocks[] = [
                'type' => 'sub_title',
                'text' => 'Thập Thần bản mệnh cao nhất: '.$thapThanLabel,
            ];
        }

        foreach ($tt['intro'] ?? [] as $para) {
            self::appendParagraphs($blocks, (string) $para);
        }

        if (! empty($tt['muc'])) {
            $blocks[] = ['type' => 'sub_title', 'text' => (string) $tt['muc']];
        }

        foreach ($tt['thap_than'] ?? [] as $item) {
            if (! empty($item['bo'])) {
                $blocks[] = ['type' => 'sub_title', 'text' => (string) $item['bo']];
            }
            if (! empty($item['thap_than']['ten'])) {
                $name = (string) $item['thap_than']['ten'];
                $blocks[] = ['type' => 'sub_title', 'text' => $name];
            }
            if (! empty($item['tagline'])) {
                $blocks[] = ['type' => 'sub_title', 'text' => (string) $item['tagline']];
            }
            if (! empty($item['intro'])) {
                self::appendParagraphs($blocks, (string) $item['intro']);
            }
            foreach ($item['sections'] ?? [] as $sec) {
                if (! empty($sec['tieu_de'])) {
                    $blocks[] = ['type' => 'sub_title', 'text' => (string) $sec['tieu_de']];
                }
                foreach ($sec['doan'] ?? [] as $para) {
                    self::appendParagraphs($blocks, (string) $para);
                }
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private static function appendParagraphs(array &$blocks, string $text): void
    {
        PdfTextSanitizer::appendParagraphBlocks($blocks, $text);
    }
}
