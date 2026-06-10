<?php

namespace App\Services\Pdf;

use App\Services\Phan7PdfService;

/**
 * Preset cấu hình phân trang theo từng Phần PDF.
 */
class PdfPaginationProfiles
{
    public static function phan3(string $firstBgPath, string $contBgPath): PdfPaginationConfig
    {
        $zoneHeight = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_HEIGHT_MM;
        $zoneTop    = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM;
        $baseBudget = round($zoneHeight * 0.92, 1);
        $lineMm     = 5.3;
        $paraPad    = 0.8;

        return new PdfPaginationConfig([
            'charsPerLine'        => 75,
            'lineMm'              => $lineMm,
            'lineWidthThreshold'  => 1.0,
            'blockGapMm'          => 2.0,
            'paraLinePaddingMm'   => $paraPad,
            'splitOversizedPara'  => true,
            'contentZoneTopMm'    => $zoneTop,
            'contentHeightMm'     => $baseBudget,
            'contentZoneHeightMm' => $zoneHeight,
            'contentLeftMm'       => Phan3NguHanhBanMenhPaginator::CONTENT_LEFT_MM,
            'contentWidthMm'      => Phan3NguHanhBanMenhPaginator::CONTENT_WIDTH_MM,
            'fixedBlockHeights'   => [
                'sub_title' => 11.0,
            ],
            'blockHeightResolver' => static function (array $block): float {
                // para → 0.0: rơi về paraHeightMm (đo bằng font metrics thật)
                return 0.0;
            },
            'bgResolver'           => static fn (int $i): string => $i === 0 ? $firstBgPath : $contBgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget): float {
                if ($pageIndex === 0) {
                    return $budget - 14.0;
                }

                return $budget;
            },
            'pageMetaResolver'     => static function (int $pageIndex, array $page) use ($zoneTop, $zoneHeight): array {
                return [
                    'chapterTitle'        => $pageIndex === 0 ? (string) ($page['chapterTitle'] ?? '') : '',
                    'contentZoneTopMm'    => $zoneTop,
                    'contentZoneHeightMm' => $zoneHeight,
                    'contentLeftMm'       => Phan3NguHanhBanMenhPaginator::CONTENT_LEFT_MM,
                    'contentWidthMm'      => Phan3NguHanhBanMenhPaginator::CONTENT_WIDTH_MM,
                ];
            },
        ]);
    }

    public static function phan3WithChapter(string $firstBgPath, string $contBgPath, string $chapterTitle): PdfPaginationConfig
    {
        $config       = self::phan3($firstBgPath, $contBgPath);
        $chapterTitle = trim($chapterTitle);
        $zoneTop      = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM;
        $zoneHeight   = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_HEIGHT_MM;

        $config->pageMetaResolver = static function (int $pageIndex, array $page) use ($chapterTitle, $zoneTop, $zoneHeight): array {
            return [
                'chapterTitle'        => $pageIndex === 0 ? $chapterTitle : '',
                'contentZoneTopMm'    => $zoneTop,
                'contentZoneHeightMm' => $zoneHeight,
                'contentLeftMm'       => Phan3NguHanhBanMenhPaginator::CONTENT_LEFT_MM,
                'contentWidthMm'      => Phan3NguHanhBanMenhPaginator::CONTENT_WIDTH_MM,
            ];
        };

        return $config;
    }

    /** I. Tổng quan: trang 1 = tong-quan-bg, trang tiếp = page-content-bg. */
    public static function phan5TongQuan(string $firstBgPath, string $contBgPath): PdfPaginationConfig
    {
        $config = self::phan5($firstBgPath, 'tong_quan', null);

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

        // 14px × 140% line-height = 19.6px × 0.75pt × 0.3528mm = 5.19mm — giá trị vật lý thật
        $lineMm = 5.3;

        return new PdfPaginationConfig([
            'contentZoneTopMm'    => $zoneTop,
            'contentHeightMm'     => round(PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM * 0.96, 1),
            'contentZoneHeightMm' => PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM,
            'contentLeftMm'       => $contentLeft,
            'charsPerLine'        => 72,
            'lineMm'              => $lineMm,
            'lineWidthThreshold'  => 1.0,
            'paraLinePaddingMm'   => 2.5,
            'contentWidthMm'      => $contentWidth,
            'blockGapMm'          => $isItemLayout ? 2.0 : 2.5,
            'imageGapMm'          => 3.0,
            'skipOversizedTraits' => true,
            'clampImages'         => true,
            'maxImageMm'          => $isItemLayout ? 110.0 : 120.0,
            'fixedBlockHeights'   => [
                'item_title'         => 12.0,
                'section_title'      => 10.0,
                'sub_title'          => 11.0,
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

                // para → 0.0: rơi về paraHeightMm (đo bằng font metrics thật)
                return 0.0;
            },
            'bgResolver'          => static fn (): string => $bgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget) use ($continuationHeader, $contentWidth, $lineMm): float {
                if ($pageIndex > 0 && $continuationHeader !== null) {
                    $cfg = new PdfPaginationConfig(['blockGapMm' => 2.5, 'contentWidthMm' => $contentWidth, 'lineMm' => $lineMm, 'paraLinePaddingMm' => 2.5]);

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
        $config = self::phan7Muc1($bgPath);
        $config->forceNewPageBefore = ['thap_than_title'];
        $config->fixedBlockHeights['thap_than_title'] = 22.0;
        $config->maxImageMm = 100.0;

        return $config;
    }

    public static function phan7Muc1(string $bgPath, ?string $firstPageBgPath = null): PdfPaginationConfig
    {
        $zoneHeight = PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM;
        $zoneTop    = PdfPaginationConfig::CONTENT_ZONE_TOP_MM;
        $firstBg    = $firstPageBgPath ?? $bgPath;
        $hasIntroBg = $firstPageBgPath !== null && $firstPageBgPath !== $bgPath;
        $introTop   = Phan7PdfService::MUC1_FIRST_PAGE_TOP_MM;
        $introOffset = $hasIntroBg ? ($introTop - $zoneTop) : 0.0;
        $baseBudget  = round($zoneHeight * 0.96, 1);
        $introBudget = round($baseBudget - $introOffset, 1);
        $introZoneHeight = $zoneHeight - $introOffset;

        return new PdfPaginationConfig([
            'contentHeightMm'     => $baseBudget,
            'contentZoneHeightMm' => $zoneHeight,
            'contentZoneTopMm'    => $zoneTop,
            'contentLeftMm'       => 24.0,
            'contentWidthMm'      => 162.0,
            'lineMm'              => 5.3,
            'paraLinePaddingMm'   => 2.0,
            'blockGapMm'          => 2.0,
            'splitOversizedPara'  => true,
            'clampImages'         => true,
            'maxImageMm'          => 135.0,
            'minImagePageMm'      => 35.0,
            'fixedBlockHeights'   => [
                'section_label' => 9.0,
            ],
            'blockHeightResolver' => static fn (array $block): float => 0.0,
            'bgResolver'          => static fn (int $pageIndex): string => $pageIndex === 0 ? $firstBg : $bgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget) use ($hasIntroBg, $introBudget): float {
                if ($hasIntroBg && $pageIndex === 0) {
                    return $introBudget;
                }

                return $budget;
            },
            'pageMetaResolver' => static function (int $pageIndex, array $page) use (
                $hasIntroBg,
                $introTop,
                $introZoneHeight,
                $zoneTop,
                $zoneHeight
            ): array {
                if ($hasIntroBg && $pageIndex === 0) {
                    return [
                        'contentZoneTopMm'    => $introTop,
                        'contentZoneHeightMm' => $introZoneHeight,
                    ];
                }

                return [
                    'contentZoneTopMm'    => $zoneTop,
                    'contentZoneHeightMm' => $zoneHeight,
                ];
            },
        ]);
    }

    public static function phan8(string $bgPath, float $contentHeightMm = 0.0): PdfPaginationConfig
    {
        $base = self::phan68Base($bgPath);

        $zoneHeight = $contentHeightMm > 0
            ? $contentHeightMm
            : Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_HEIGHT_MM;

        $zoneTop = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM;

        $base->contentHeightMm     = round($zoneHeight * 0.96, 1);
        $base->contentZoneHeightMm = $zoneHeight;
        $base->contentZoneTopMm    = $zoneTop;
        $base->blockGapMm          = 2.0;
        $base->lineMm              = 5.3; // 15pt line-height = 5.29mm
        $base->paraLinePaddingMm   = 2.0;
        $base->blockHeightResolver = static function (array $block): float {
            // para → 0.0: rơi về paraHeightMm (đo bằng font metrics thật)
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
            'su_nghiep' => 38.0,
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
            // 14px × 140% = 5.19mm/dòng — đo dòng bằng font metrics thật (paraHeightMm)
            'lineMm'              => 5.3,
            'paraLinePaddingMm'   => 2.0,
            'blockGapMm'          => 2.0,
            'fixedBlockHeights'   => [
                'chapter_title' => 9.0,
                'sub_title'     => 9.0,
                'sub_ab'        => 9.0,
                'huong_label'   => 9.0,
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

        $cfg = new PdfPaginationConfig(['contentWidthMm' => 156.0, 'lineMm' => 5.3, 'paraLinePaddingMm' => 2.0, 'blockGapMm' => 2.0]);

        return PdfContentPaginator::paraHeightMm((string) ($block['text'] ?? ''), $cfg) + 3.0 + 2.0;
    }

    /** @param array<string, mixed> $block */
    private static function keywordsHeightMm(array $block): float
    {
        // kw-grid 59.71mm + kw-section margin-bottom 6mm + muc-label ~8mm
        $h = 59.71 + 6.0;
        $label = trim((string) ($block['label'] ?? ''));
        if ($label !== '') {
            $h += 8.0;
        }

        return $h;
    }

    /** @param array<string, mixed> $block */
    private static function traitsHeightMm(array $block): float
    {
        return Phan5TraitLayout::blockHeightMm(
            (string) ($block['tichCuc'] ?? ''),
            (string) ($block['tieuCuc'] ?? '')
        );
    }
}
