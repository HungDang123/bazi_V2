<?php

namespace App\Services\Pdf;

use App\Services\Phan7PdfService;
use App\Services\Phan9PdfService;

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
                $top = self::contentZoneTopForLayout('tong_quan');

                return [
                    'layoutVariant'         => 'tong_quan',
                    'contentZoneHeightMm'   => PdfPaginationConfig::contentZoneHeightForTop($top),
                ];
            }

            $top = PdfPaginationConfig::CONTENT_ZONE_TOP_MM;

            return [
                'layoutVariant'       => 'page_content',
                'contentZoneTopMm'    => $top,
                'contentZoneHeightMm' => PdfPaginationConfig::contentZoneHeightForTop($top),
                'contentLeftMm'       => 28.0,
                'contentWidthMm'      => 154.0,
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
        $zoneHeight = PdfPaginationConfig::contentZoneHeightForTop($zoneTop);
        $isItemLayout = in_array($layoutVariant, ['su_nghiep_item', 'lbtv119', 'traits_su_nghiep', 'traits_lbtv119'], true);

        // 14px × 140% line-height = 19.6px × 0.75pt × 0.3528mm = 5.19mm — giá trị vật lý thật
        $lineMm = 5.3;

        $budgetRatio = $isItemLayout ? 0.93 : 0.96;

        return new PdfPaginationConfig([
            'contentZoneTopMm'    => $zoneTop,
            'contentHeightMm'     => round($zoneHeight * $budgetRatio, 1),
            'contentZoneHeightMm' => $zoneHeight,
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
                'chien_luoc_title'   => 12.0,
                'keywords'           => 70.0,
                'table'              => 92.0,
            ],
            'blockHeightResolver' => static function (array $block) use ($contentWidth): float {
                $type = (string) ($block['type'] ?? '');

                if ($type === 'traits') {
                    return Phan5TraitLayout::blockHeightMm(
                        (string) ($block['tichCuc'] ?? ''),
                        (string) ($block['tieuCuc'] ?? ''),
                        $contentWidth
                    );
                }

                if ($type === 'energy_traits') {
                    return Phan5TraitLayout::energyTraitsBlockHeightMm(
                        (string) ($block['giaiNghia'] ?? ''),
                        (string) ($block['tichCuc'] ?? ''),
                        (string) ($block['tieuCuc'] ?? ''),
                        $contentWidth
                    );
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
                'layoutVariant'         => $layoutVariant,
                'contentZoneHeightMm'   => $zoneHeight,
            ],
        ]);
    }

    public static function phan6(string $bgPath): PdfPaginationConfig
    {
        $base = self::phan68Base($bgPath);

        $zoneTop    = PdfPaginationConfig::CONTENT_ZONE_TOP_MM;
        $zoneHeight = PdfPaginationConfig::contentZoneHeightForTop($zoneTop);
        $base->contentZoneTopMm    = $zoneTop;
        $base->contentZoneHeightMm = $zoneHeight;
        $base->contentHeightMm     = round($zoneHeight * 0.97, 1);
        $base->contentLeftMm       = 24.0;
        $base->contentWidthMm      = 162.0;

        $base->clampImages = true;
        $base->blockHeightResolver = static function (array $block): float {
            $contentWidth = 162.0;
            if (($block['type'] ?? '') === 'table') {
                return self::phan6LaSoTableHeightMm($block, $contentWidth);
            }

            return self::phan68BlockHeight($block, $contentWidth);
        };

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
        $zoneTop    = PdfPaginationConfig::CONTENT_ZONE_TOP_MM;
        $zoneHeight = PdfPaginationConfig::contentZoneHeightForTop($zoneTop);
        $firstBg    = $firstPageBgPath ?? $bgPath;
        $hasIntroBg = $firstPageBgPath !== null && $firstPageBgPath !== $bgPath;
        $introTop   = Phan7PdfService::MUC1_FIRST_PAGE_TOP_MM;
        $introHeight = PdfPaginationConfig::contentZoneHeightForTop($introTop);
        $baseBudget = round($zoneHeight * 0.96, 1);

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
            'pageMetaResolver' => static function (int $pageIndex, array $page) use (
                $hasIntroBg,
                $introTop,
                $introHeight,
                $zoneTop,
                $zoneHeight
            ): array {
                if ($hasIntroBg && $pageIndex === 0) {
                    return [
                        'contentZoneTopMm'    => $introTop,
                        'contentZoneHeightMm' => $introHeight,
                    ];
                }

                return [
                    'contentZoneTopMm'    => $zoneTop,
                    'contentZoneHeightMm' => $zoneHeight,
                ];
            },
        ]);
    }

    /** Vùng nội dung Phần 8 trên page-content-bg — chừa ~39mm cho footer overlay. */
    public const PHAN8_CONTENT_ZONE_HEIGHT_MM = 240.0;

    /** Cuốn 2 — chapter paginated (tách hằng để sửa Phần 8 không đụng Quyển 2). */
    public const QUYEN2_CHAPTER_ZONE_HEIGHT_MM = 240.0;

    public const PHAN8_CONTENT_BUDGET_RATIO = 0.95;

    /** Cuốn 2 — NHẬT CHỦ chapter (trang 19–21): cùng vùng an toàn footer như Phần 8. */
    public static function quyen2NhatChuChapter(
        string $firstBgPath,
        string $contBgPath,
        string $chapterTitle
    ): PdfPaginationConfig {
        $zoneHeight   = self::QUYEN2_CHAPTER_ZONE_HEIGHT_MM;
        $zoneTop      = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM;
        $baseBudget   = round($zoneHeight * 0.96, 1);
        $chapterTitle = trim($chapterTitle);

        return new PdfPaginationConfig([
            'charsPerLine'        => 75,
            'lineMm'              => 5.3,
            'lineWidthThreshold'  => 1.0,
            'blockGapMm'          => 1.5,
            'paraLinePaddingMm'   => 0.8,
            'splitOversizedPara'  => true,
            'contentZoneTopMm'    => $zoneTop,
            'contentHeightMm'     => $baseBudget,
            'contentZoneHeightMm' => $zoneHeight,
            'contentLeftMm'       => 28.0,
            'contentWidthMm'      => 154.0,
            'fixedBlockHeights'   => [
                'sub_title'      => 10.0,
                'chapter_title'  => 14.0,
            ],
            'blockHeightResolver' => static function (array $block): float {
                return 0.0;
            },
            'bgResolver' => static fn (int $pageIndex): string => $pageIndex === 0
                ? $firstBgPath
                : $contBgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget): float {
                if ($pageIndex === 0) {
                    return $budget - 14.0;
                }

                return $budget;
            },
            'pageMetaResolver' => static function (int $pageIndex, array $page) use ($chapterTitle, $zoneTop, $zoneHeight): array {
                return [
                    'chapterTitle'        => $pageIndex === 0 ? $chapterTitle : '',
                    'contentZoneTopMm'    => $zoneTop,
                    'contentZoneHeightMm' => $zoneHeight,
                    'contentLeftMm'       => 28.0,
                    'contentWidthMm'      => 154.0,
                ];
            },
        ]);
    }

    /** Cuốn 2 — trang 17 Lý tổng quan (vùng trắng dưới scroll, top 66mm). */
    public const QUYEN2_TONGQUAN_ZONE_TOP_MM = 66.0;

    public const QUYEN2_TONGQUAN_ZONE_HEIGHT_MM = 192.0;

    public static function quyen2TongQuan(string $firstBgPath, string $contBgPath): PdfPaginationConfig
    {
        $firstZoneHeight = self::QUYEN2_TONGQUAN_ZONE_HEIGHT_MM;
        $contZoneHeight  = self::QUYEN2_CHAPTER_ZONE_HEIGHT_MM;
        $firstZoneTop    = self::QUYEN2_TONGQUAN_ZONE_TOP_MM;
        $contZoneTop     = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM;
        $contBudget      = round($contZoneHeight * 0.96, 1);

        return new PdfPaginationConfig([
            'charsPerLine'        => 75,
            'lineMm'              => 5.3,
            'lineWidthThreshold'  => 1.0,
            'blockGapMm'          => 1.5,
            'paraLinePaddingMm'   => 0.8,
            'splitOversizedPara'  => true,
            'contentZoneTopMm'    => $contZoneTop,
            'contentHeightMm'     => $contBudget,
            'contentZoneHeightMm' => $contZoneHeight,
            'contentLeftMm'       => 28.0,
            'contentWidthMm'      => 154.0,
            'fixedBlockHeights'   => [
                'sub_title'     => 10.0,
                'chapter_title' => 14.0,
            ],
            'blockHeightResolver' => static function (array $block): float {
                return 0.0;
            },
            'bgResolver' => static fn (int $pageIndex): string => $pageIndex === 0
                ? $firstBgPath
                : $contBgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget) use ($firstZoneHeight, $contBudget): float {
                if ($pageIndex === 0) {
                    return round($firstZoneHeight * 0.96, 1);
                }

                return $contBudget;
            },
            'pageMetaResolver' => static function (int $pageIndex, array $page) use ($firstZoneTop, $firstZoneHeight, $contZoneTop, $contZoneHeight): array {
                $isFirst = $pageIndex === 0;

                return [
                    'chapterTitle'        => '',
                    'contentZoneTopMm'    => $isFirst ? $firstZoneTop : $contZoneTop,
                    'contentZoneHeightMm' => $isFirst ? $firstZoneHeight : $contZoneHeight,
                    'contentLeftMm'       => 28.0,
                    'contentWidthMm'      => 154.0,
                ];
            },
        ]);
    }

    public static function phan8(string $bgPath, float $contentHeightMm = 0.0): PdfPaginationConfig
    {
        $base = self::phan68Base($bgPath);

        $zoneHeight = $contentHeightMm > 0
            ? $contentHeightMm
            : self::PHAN8_CONTENT_ZONE_HEIGHT_MM;

        $zoneTop = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM;

        $base->contentHeightMm     = round($zoneHeight * self::PHAN8_CONTENT_BUDGET_RATIO, 1);
        $base->contentZoneHeightMm = $zoneHeight;
        $base->contentZoneTopMm    = $zoneTop;
        $base->blockGapMm          = 2.0;
        $base->lineMm              = 5.3; // 15pt line-height = 5.29mm
        $base->paraLinePaddingMm   = 2.0;
        $base->fixedBlockHeights['sub_title'] = 12.0;
        $base->fixedBlockHeights['sub_ab']    = 12.0;
        $base->blockHeightResolver = static function (array $block): float {
            // para → 0.0: rơi về paraHeightMm (đo bằng font metrics thật)
            return self::phan68BlockHeight($block);
        };

        return $base;
    }

    /** LBTV-119 — nền page-content-bg.png, vùng nội dung giống Phần 5 mục III+. */
    public static function phan9(string $bgPath): PdfPaginationConfig
    {
        $base = self::phan68Base($bgPath);

        $zoneTop = self::contentZoneTopForLayout('lbtv119');
        [$contentLeft, $contentWidth] = self::contentBoxForLayout('lbtv119');
        $zoneHeight = PdfPaginationConfig::contentZoneHeightForTop($zoneTop);

        $base->contentHeightMm     = round($zoneHeight * 0.93, 1);
        $base->contentZoneHeightMm = $zoneHeight;
        $base->contentZoneTopMm    = $zoneTop;
        $base->contentLeftMm       = $contentLeft;
        $base->contentWidthMm      = $contentWidth;
        $base->blockHeightResolver = static function (array $block) use ($contentWidth): float {
            return self::phan68BlockHeight($block, $contentWidth);
        };

        return $base;
    }

    /**
     * Phần 9 — trang đầu: giai-phap-bg.png + mục I; các trang sau: page-content-bg.png.
     */
    public static function phan9WithIntro(string $contentBgPath, string $introBgPath): PdfPaginationConfig
    {
        $base = self::phan9($contentBgPath);
        $introTop = Phan9PdfService::INTRO_FIRST_PAGE_TOP_MM;
        $zoneTop = $base->contentZoneTopMm;
        $zoneHeight = $base->contentZoneHeightMm;
        $hasIntroBg = $introBgPath !== '' && $introBgPath !== $contentBgPath;
        $introZoneHeight = PdfPaginationConfig::contentZoneHeightForTop($introTop);
        $originalBudget = $base->contentHeightMm;

        $base->bgResolver = static fn (int $pageIndex): string => ($hasIntroBg && $pageIndex === 0)
            ? $introBgPath
            : $contentBgPath;

        $base->budgetAdjustResolver = static function (int $pageIndex, array $remaining, float $budget) use (
            $hasIntroBg,
            $introTop,
            $introZoneHeight,
            $originalBudget
        ) {
            if ($hasIntroBg && $pageIndex === 0) {
                $firstBudget = round($introZoneHeight * 0.90, 1);

                return self::chapterBudgetAdjust($pageIndex, $remaining, $firstBudget);
            }

            return self::chapterBudgetAdjust($pageIndex, $remaining, $originalBudget);
        };

        $base->pageMetaResolver = static function (int $pageIndex, array $page) use (
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
        };

        return $base;
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
    private static function phan6LaSoTableHeightMm(array $block, float $contentWidthMm): float
    {
        $table = is_array($block['table'] ?? null) ? $block['table'] : [];
        $cols  = is_array($table['columns'] ?? null) ? $table['columns'] : [];
        $rows  = is_array($table['rows'] ?? null) ? $table['rows'] : [];
        $colCount = max(1, count($cols));

        $labelW = $contentWidthMm * 0.14;
        $cellW  = max(28.0, ($contentWidthMm - $labelW) / $colCount);
        $lineMm = 4.6;
        $cellPadV = 4.4;

        $height = 7.5 + 2.0 + 9.0; // title + gap + header

        foreach ($rows as $row) {
            $rowH = 10.0;
            foreach ($cols as $col) {
                $key  = (string) ($col['key'] ?? '');
                $text = trim((string) ($row['cells'][$key] ?? ''));
                if ($text === '') {
                    continue;
                }
                $textH = PdfTextWrapHelper::renderedHeightMmByWidth(
                    $text,
                    max(20.0, $cellW - 5.0),
                    $lineMm,
                    1.2
                );
                $rowH = max($rowH, $textH + $cellPadV);
            }
            $height += $rowH;
        }

        return round($height + 2.0, 1);
    }

    /** @param array<string, mixed> $block */
    private static function phan68BlockHeight(array $block, float $contentWidthMm = 0.0): float
    {
        if (($block['type'] ?? '') === 'coding_box') {
            $cfg = new PdfPaginationConfig(['contentWidthMm' => 156.0, 'lineMm' => 5.3, 'paraLinePaddingMm' => 2.0, 'blockGapMm' => 2.0]);

            return PdfContentPaginator::paraHeightMm((string) ($block['text'] ?? ''), $cfg) + 3.0 + 2.0;
        }

        if ($contentWidthMm > 0) {
            $heading = self::phan68HeadingBlockHeight($block, $contentWidthMm);
            if ($heading > 0) {
                return $heading;
            }
        }

        return 0.0;
    }

    /** @param array<string, mixed> $block */
    private static function phan68HeadingBlockHeight(array $block, float $contentWidthMm): float
    {
        $type = (string) ($block['type'] ?? '');
        if (! in_array($type, ['chapter_title', 'sub_title', 'sub_ab', 'huong_label'], true)) {
            return 0.0;
        }

        $text = trim((string) ($block['text'] ?? ''));
        if ($text === '') {
            return 9.0;
        }

        // la-so-phan-8-content: chapter-title / red-title — 16px, line-height 130%
        $lineMm = 5.5;
        $extraMm = $type === 'chapter_title' ? 3.5 : 3.0;
        $lines = max(1, count(PdfTextWrapHelper::wrapByWidthMm($text, $contentWidthMm, 16.0)));

        return ($lines * $lineMm) + $extraMm;
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
}
