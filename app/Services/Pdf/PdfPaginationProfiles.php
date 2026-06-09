<?php

namespace App\Services\Pdf;

/**
 * Preset cấu hình phân trang theo từng Phần PDF.
 */
class PdfPaginationProfiles
{
    public static function phan3(string $firstBgPath, string $contBgPath): PdfPaginationConfig
    {
        return new PdfPaginationConfig([
            'charsPerLine'        => 75,
            'lineMm'              => 5.5,
            'blockGapMm'          => 2.5,
            'contentZoneTopMm'    => 22.0,
            'contentHeightMm'     => 220.0,
            'contentZoneHeightMm' => 240.0,
            'contentLeftMm'       => 28.0,
            'contentWidthMm'      => 154.0,
            'fixedBlockHeights'   => [
                'sub_title' => 9.0,
            ],
            'bgResolver'         => static fn (int $i): string => $i === 0 ? $firstBgPath : $contBgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget): float {
                if ($pageIndex === 0) {
                    return $budget - 9.0;
                }

                return $budget;
            },
            'pageMetaResolver'   => static function (int $pageIndex, array $page): array {
                return [
                    'chapterTitle' => $pageIndex === 0 ? (string) ($page['chapterTitle'] ?? '') : '',
                ];
            },
        ]);
    }

    public static function phan3WithChapter(string $firstBgPath, string $contBgPath, string $chapterTitle): PdfPaginationConfig
    {
        $config = self::phan3($firstBgPath, $contBgPath);
        $chapterTitle = trim($chapterTitle);

        $config->pageMetaResolver = static function (int $pageIndex, array $page) use ($chapterTitle): array {
            return [
                'chapterTitle' => $pageIndex === 0 ? $chapterTitle : '',
            ];
        };

        return $config;
    }

    /** I. Tổng quan: trang 1 = tong-quan-bg, trang tiếp = page-content-bg. */
    public static function phan5TongQuan(string $firstBgPath, string $contBgPath): PdfPaginationConfig
    {
        $continuationHeader = ['type' => 'section_title', 'text' => 'I. TỔNG QUAN'];
        $config             = self::phan5($firstBgPath, 'tong_quan', $continuationHeader);

        $config->bgResolver = static fn (int $pageIndex): string => $pageIndex === 0
            ? $firstBgPath
            : $contBgPath;

        $config->pageMetaResolver = static function (int $pageIndex, array $page): array {
            if ($pageIndex === 0) {
                return ['layoutVariant' => 'tong_quan'];
            }

            return [
                'layoutVariant'    => 'page_content',
                'contentZoneTopMm' => PdfPaginationConfig::CONTENT_ZONE_TOP_MM,
                'contentLeftMm'    => 28.0,
                'contentWidthMm'   => 154.0,
            ];
        };

        return $config;
    }

    public static function phan5(
        string $bgPath,
        string $layoutVariant = '',
        ?array $continuationHeader = null
    ): PdfPaginationConfig {
        $zoneTop = self::contentZoneTopForLayout($layoutVariant);
        [$contentLeft, $contentWidth] = self::contentBoxForLayout($layoutVariant);
        $isItemLayout = in_array($layoutVariant, ['su_nghiep_item', 'lbtv119', 'traits_su_nghiep', 'traits_lbtv119'], true);

        return new PdfPaginationConfig([
            'contentZoneTopMm'    => $zoneTop,
            'contentHeightMm'     => $isItemLayout
                ? round(PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM * 0.88, 1)
                : round(PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM * 0.92, 1),
            'contentZoneHeightMm' => PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM,
            'contentLeftMm'       => $contentLeft,
            'charsPerLine'        => 68,
            'lineMm'              => 5.5,
            'contentWidthMm'      => $contentWidth,
            'blockGapMm'          => $isItemLayout ? 2.0 : 2.5,
            'imageGapMm'          => 3.0,
            'skipOversizedTraits' => true,
            'clampImages'         => true,
            'maxImageMm'          => $isItemLayout ? 88.0 : 120.0,
            'fixedBlockHeights'   => [
                'item_title'         => 12.0,
                'section_title'      => 12.0,
                'sub_title'          => 7.0,
                'muc_label'          => 8.0,
                'chien_luoc_title'   => 8.0,
                'keywords'           => 70.0,
                'table'              => 92.0,
            ],
            'blockHeightResolver' => static function (array $block): float {
                $type = (string) ($block['type'] ?? '');

                if ($type === 'traits') {
                    return self::traitsHeightMm($block);
                }

                if ($type === 'keywords') {
                    return self::keywordsHeightMm($block);
                }

                if ($type === 'para' || $type === '') {
                    return PdfTextWrapHelper::renderedHeightMm(
                        (string) ($block['text'] ?? ''),
                        68,
                        5.5,
                        2.0
                    ) + 2.0;
                }

                return 0.0;
            },
            'bgResolver'          => static fn (): string => $bgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget) use ($continuationHeader): float {
                if ($pageIndex > 0 && $continuationHeader !== null) {
                    $cfg = new PdfPaginationConfig(['blockGapMm' => 2.5, 'charsPerLine' => 68, 'lineMm' => 5.5]);

                    return $budget - PdfContentPaginator::blockHeightMm($continuationHeader, $cfg);
                }

                return $budget;
            },
            'chunkAdjustResolver' => static function (int $pageIndex, array $chunk, array $remaining) use ($continuationHeader): array {
                if ($pageIndex > 0 && $continuationHeader !== null) {
                    $contType = $continuationHeader['type'] ?? '';
                    if (($chunk[0]['type'] ?? '') !== $contType) {
                        array_unshift($chunk, $continuationHeader);
                    }
                }

                return $chunk;
            },
            'pageMetaResolver'    => static fn (int $pageIndex, array $page): array => [
                'layoutVariant' => $layoutVariant,
            ],
        ]);
    }

    public static function phan6(string $bgPath): PdfPaginationConfig
    {
        $base = self::phan68Base($bgPath);

        $base->fixedBlockHeights['table'] = 48.0;
        $base->clampImages                = true;
        $base->blockHeightResolver        = self::phan68BlockHeight(...);

        return $base;
    }

    public static function phan7(string $bgPath): PdfPaginationConfig
    {
        // line-height: 140% → ~5.9mm/dòng ở 72dpi → dùng 6.0mm để ước tính chính xác
        return new PdfPaginationConfig([
            'charsPerLine'        => 70,
            'lineMm'              => 6.0,
            'blockGapMm'          => 2.0,
            'imageGapMm'          => 3.0,
            'maxImageMm'          => 95.0,
            'forceNewPageBefore'  => ['thap_than_title'],
            'fixedBlockHeights'   => [
                'thap_than_title' => 22.0,
                'section_label'   => 8.0,
            ],
            'clampImages'         => true,
            'bgResolver'          => static fn (): string => $bgPath,
        ]);
    }

    public static function phan7Muc1(string $bgPath): PdfPaginationConfig
    {
        // Cùng blade la-so-phan-7-muc2-content → line-height: 140% → lineMm=6.0
        return new PdfPaginationConfig([
            'charsPerLine'      => 70,
            'lineMm'            => 6.0,
            'blockGapMm'        => 2.0,
            'imageGapMm'        => 3.0,
            'maxImageMm'        => 120.0,
            'fixedBlockHeights' => [
                'section_label' => 8.0,
            ],
            'clampImages'       => true,
            'bgResolver'        => static fn (): string => $bgPath,
        ]);
    }

    public static function phan8(string $bgPath, float $contentHeightMm = 0.0): PdfPaginationConfig
    {
        $base = self::phan68Base($bgPath);

        $zoneHeight = $contentHeightMm > 0
            ? $contentHeightMm
            : PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM;

        $base->contentHeightMm     = round(PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM * 0.88, 1);
        $base->contentZoneHeightMm = $zoneHeight;
        $base->contentZoneTopMm    = PdfPaginationConfig::CONTENT_ZONE_TOP_MM;
        $base->blockGapMm          = 2.0;
        $base->charsPerLine        = 72;
        $base->lineMm              = 5.5;
        $base->blockHeightResolver = static function (array $block): float {
            $type = (string) ($block['type'] ?? '');

            if ($type === 'para' || $type === '') {
                return PdfTextWrapHelper::renderedHeightMm(
                    (string) ($block['text'] ?? ''),
                    72,
                    5.5,
                    2.0
                ) + 2.0;
            }

            return self::phan68BlockHeight($block);
        };

        return $base;
    }

    public static function phan9(string $bgPath): PdfPaginationConfig
    {
        return self::phan8($bgPath);
    }

    /** @deprecated Dùng CONTENT_ZONE_HEIGHT_MM — giữ để tương thích Phan5PdfPaginator. */
    public static function contentHeightForLayout(string $layout): float
    {
        return PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM;
    }

    private static function contentZoneTopForLayout(string $layout): float
    {
        return match ($layout) {
            'tong_quan' => 79.0,
            'su_nghiep' => 34.0,
            'su_nghiep_item' => 28.0,
            'traits_su_nghiep' => 32.0,
            'lbtv119', 'traits_lbtv119' => 22.0,
            'page_content' => PdfPaginationConfig::CONTENT_ZONE_TOP_MM,
            default => PdfPaginationConfig::CONTENT_ZONE_TOP_MM,
        };
    }

    /** @return array{0: float, 1: float} */
    private static function contentBoxForLayout(string $layout): array
    {
        return match ($layout) {
            'tong_quan', 'page_content' => [28.0, 154.0],
            'su_nghiep', 'su_nghiep_item', 'traits_su_nghiep', 'traits_lbtv119' => [22.0, 166.0],
            'lbtv119' => [24.0, 162.0],
            default => [24.0, 162.0],
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $remaining
     */
    private static function chapterBudgetAdjust(int $pageIndex, array $remaining, float $budget): float
    {
        $firstBlock = $remaining[0] ?? null;
        $showChapter = $pageIndex === 0
            || (is_array($firstBlock) && ($firstBlock['type'] ?? '') === 'chapter_title');

        if ($showChapter && is_array($firstBlock) && ($firstBlock['type'] ?? '') === 'chapter_title') {
            return $budget - 9.0;
        }

        return $budget;
    }

    /**
     * @param  array<int, array<string, mixed>>  $chunk
     * @param  array<int, array<string, mixed>>  $remaining
     * @return array<int, array<string, mixed>>
     */
    private static function chapterChunkAdjust(int $pageIndex, array $chunk, array $remaining): array
    {
        $firstBlock = $chunk[0] ?? null;
        $showChapter = $pageIndex === 0
            || (is_array($firstBlock) && ($firstBlock['type'] ?? '') === 'chapter_title');

        if ($showChapter && is_array($firstBlock) && ($firstBlock['type'] ?? '') === 'chapter_title') {
            array_shift($chunk);
        }

        return $chunk;
    }

    private static function phan68Base(string $bgPath): PdfPaginationConfig
    {
        return new PdfPaginationConfig([
            'lineMm'            => 5.5,  // 14px × 140% × 0.2646mm/px ≈ 5.19mm → dùng 5.5 cho an toàn
            'blockGapMm'        => 2.0,
            'fixedBlockHeights' => [
                'chapter_title' => 9.0,
                'sub_title'     => 7.0,
                'sub_ab'        => 7.0,
                'huong_label'   => 7.0,
            ],
            'bgResolver'           => static fn (): string => $bgPath,
            'budgetAdjustResolver' => self::chapterBudgetAdjust(...),
            'chunkAdjustResolver'  => self::chapterChunkAdjust(...),
        ]);
    }

    /** @param array<string, mixed> $block */
    private static function phan68BlockHeight(array $block): float
    {
        if (($block['type'] ?? '') !== 'coding_box') {
            return 0.0;
        }

        $cfg = new PdfPaginationConfig(['charsPerLine' => 72, 'lineMm' => 5.5, 'blockGapMm' => 2.0]);

        return PdfContentPaginator::paraHeightMm((string) ($block['text'] ?? ''), $cfg) + 3.0 + 2.0;
    }

    /** @param array<string, mixed> $block */
    private static function keywordsHeightMm(array $block): float
    {
        // khung 59.71mm + label 8mm + margin 5mm + buffer 2mm
        $h = 59.71 + 5.0 + 2.0;
        $label = trim((string) ($block['label'] ?? ''));
        if ($label !== '') {
            $h += 8.0;
        }

        return $h + 2.0;
    }

    /** @param array<string, mixed> $block */
    private static function traitsHeightMm(array $block): float
    {
        $cfg = new PdfPaginationConfig([
            'charsPerLine'       => 32,
            'lineMm'             => 5.5,
            'lineWidthThreshold' => 0.95,
        ]);
        $tichH = self::traitsColumnBodyMm((string) ($block['tichCuc'] ?? ''), $cfg);
        $tieuH = self::traitsColumnBodyMm((string) ($block['tieuCuc'] ?? ''), $cfg);

        // pill 12mm + body padding 3.5mm + row margin-bottom 6mm + block gap 2mm
        return 12.0 + 3.5 + max($tichH, $tieuH) + 6.0 + 2.0;
    }

    private static function traitsColumnBodyMm(string $text, PdfPaginationConfig $cfg): float
    {
        $height = 0.0;
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $height += PdfContentPaginator::paraHeightMm($line, $cfg);
        }

        return max($height, 5.5);
    }
}
