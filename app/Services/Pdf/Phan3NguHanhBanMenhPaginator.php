<?php

namespace App\Services\Pdf;

use App\Services\NguHanhTitleRenderer;

/**
 * Phân trang riêng PHẦN 3 — Ngũ hành bản mệnh (HÀNH KIM/Mộc/…).
 *
 * Trang đầu mỗi hành: tiêu đề PNG + ảnh minh họa + text fill tới ~85% A4.
 * Trang tiếp: chỉ text, cùng ngưỡng chiều cao.
 */
class Phan3NguHanhBanMenhPaginator
{
    /** 842pt ≈ 297mm — vùng nội dung chiếm 85% trang. */
    public const PAGE_HEIGHT_MM = 297.0;

    public const CONTENT_FILL_RATIO = 0.85;

    public const CONTENT_ZONE_HEIGHT_MM = 252.45; // 297 × 0.85

    public const CONTENT_ZONE_TOP_MM = 18.0;

    public const CONTENT_LEFT_MM = 28.0;

    public const CONTENT_WIDTH_MM = 154.0;

    /** Ảnh hành cố định trong blade: 88mm + margin-bottom 4mm. */
    private const HANH_IMAGE_MM = 92.0;

    /** Buffer tránh tràn vùng overflow:hidden (DomPDF render cao hơn ước lượng). */
    private const BUDGET_BUFFER_RATIO = 0.92;

    /** 14px × 140% line-height ≈ 5.5mm/dòng trong DomPDF. */
    private const LINE_MM = 5.3;

    private const PARA_PADDING_MM = 0.8;

    /**
     * @param  array<int, array{type: string, text: string}>  $blocks
     * @param  array{hanh_name: string, percent: int, imagePath: string, titleImagePath: string}  $element
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $blocks, array $element, string $bgPath): array
    {
        if ($blocks === []) {
            return [];
        }

        $hanhName       = mb_strtoupper((string) ($element['hanh_name'] ?? ''), 'UTF-8');
        $percent        = (int) ($element['percent'] ?? 0);
        $imagePath      = (string) ($element['imagePath'] ?? '');
        $titleImagePath = (string) ($element['titleImagePath'] ?? '');

        $titleMm = NguHanhTitleRenderer::titleDisplayHeightMm(
            (string) ($element['hanh_name'] ?? ''),
            $percent
        );

        $baseBudget = round(self::CONTENT_ZONE_HEIGHT_MM * self::BUDGET_BUFFER_RATIO, 1);

        $config = new PdfPaginationConfig([
            'contentZoneTopMm'    => self::CONTENT_ZONE_TOP_MM,
            'contentZoneHeightMm' => self::CONTENT_ZONE_HEIGHT_MM,
            'contentLeftMm'       => self::CONTENT_LEFT_MM,
            'contentWidthMm'      => self::CONTENT_WIDTH_MM,
            'contentHeightMm'     => $baseBudget,
            'charsPerLine'        => 75,
            'lineMm'              => self::LINE_MM,
            'lineWidthThreshold'  => 1.0,
            'blockGapMm'          => 2.0,
            'paraLinePaddingMm'   => self::PARA_PADDING_MM,
            'splitOversizedPara'  => true,
            'fixedBlockHeights'   => [
                'item_title' => 12.0,
            ],
            'blockHeightResolver' => static function (array $block): float {
                // para → 0.0: rơi về paraHeightMm (đo bằng font metrics thật)
                return 0.0;
            },
            'bgResolver' => static fn (): string => $bgPath,
            'budgetAdjustResolver' => static function (int $pageIndex, array $remaining, float $budget) use ($titleMm): float {
                if ($pageIndex === 0) {
                    return max(30.0, $budget - $titleMm - self::HANH_IMAGE_MM);
                }

                return $budget;
            },
            'pageMetaResolver' => static function (int $pageIndex, array $page) use (
                $hanhName,
                $percent,
                $imagePath,
                $titleImagePath,
                $bgPath
            ): array {
                return [
                    'bgPath'               => $bgPath,
                    'showTitle'            => $pageIndex === 0,
                    'showImage'            => $pageIndex === 0,
                    'hanhName'             => $hanhName,
                    'percent'              => $percent,
                    'imagePath'            => $imagePath,
                    'titleImagePath'       => $pageIndex === 0 ? $titleImagePath : '',
                    'contentZoneTopMm'     => self::CONTENT_ZONE_TOP_MM,
                    'contentZoneHeightMm'  => self::CONTENT_ZONE_HEIGHT_MM,
                    'contentLeftMm'        => self::CONTENT_LEFT_MM,
                    'contentWidthMm'       => self::CONTENT_WIDTH_MM,
                ];
            },
        ]);

        $pages = PdfContentPaginator::paginate($blocks, $config);

        return array_map(static function (array $page): array {
            return [
                'bgPath'              => $page['bgPath'] ?? '',
                'showTitle'           => (bool) ($page['showTitle'] ?? false),
                'showImage'           => (bool) ($page['showImage'] ?? false),
                'hanhName'            => (string) ($page['hanhName'] ?? ''),
                'percent'             => (int) ($page['percent'] ?? 0),
                'imagePath'           => (string) ($page['imagePath'] ?? ''),
                'titleImagePath'      => (string) ($page['titleImagePath'] ?? ''),
                'blocks'              => $page['blocks'] ?? [],
                'contentZoneTopMm'    => (float) ($page['contentZoneTopMm'] ?? self::CONTENT_ZONE_TOP_MM),
                'contentZoneHeightMm' => (float) ($page['contentZoneHeightMm'] ?? self::CONTENT_ZONE_HEIGHT_MM),
                'contentLeftMm'       => (float) ($page['contentLeftMm'] ?? self::CONTENT_LEFT_MM),
                'contentWidthMm'      => (float) ($page['contentWidthMm'] ?? self::CONTENT_WIDTH_MM),
            ];
        }, $pages);
    }
}
