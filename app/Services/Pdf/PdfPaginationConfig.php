<?php

namespace App\Services\Pdf;

/**
 * Cấu hình phân trang PDF — vùng nội dung 70% A4, nội dung xếp từ trên xuống.
 */
class PdfPaginationConfig
{
    public const PAGE_HEIGHT_MM = 297.0;

    public const CONTENT_RATIO = 0.63;

    public const CONTENT_ZONE_HEIGHT_MM = 187.1; // 297 × 0.63 — chiều cao container CSS

    public const CONTENT_ZONE_TOP_MM = 18.0; // nội dung bắt đầu từ 18mm, xếp từ trên xuống

    /** Budget thực tế cho paginator = 92% container — giữ buffer ~15mm để không bao giờ tràn */
    public const CONTENT_BUDGET_MM = 172.1; // 187.1 × 0.92

    public float $contentHeightMm;

    public float $contentZoneTopMm;

    public float $contentZoneHeightMm;

    public float $contentLeftMm;

    public float $contentWidthMm;

    public int $charsPerLine;

    public float $lineMm;

    public float $blockGapMm;

    public float $imageGapMm;

    /** @var array<string, float> */
    public array $fixedBlockHeights;

    public ?float $maxImageMm;

    public bool $splitOversizedPara;

    public bool $clampImages;

    public bool $skipOversizedTraits;

    /** @var array<int, string> */
    public array $forceNewPageBefore;

    /** Tỉ lệ fill thực tế của text trên 1 dòng (mặc định 0.95 = 95% chiều rộng). */
    public float $lineWidthThreshold;

    /** Min không gian còn lại (mm) để đặt ảnh trên trang hiện tại; dưới ngưỡng → sang trang mới. */
    public float $minImagePageMm;

    /** @var null|\Closure(int $pageIndex): string */
    public $bgResolver;

    /** @var null|\Closure(array<string, mixed> $block): float */
    public $blockHeightResolver;

    /**
     * Trừ budget đầu trang (chapter title, continuation header…).
     *
     * @var null|\Closure(int $pageIndex, array<int, array<string, mixed>> $remaining, float $budget): float
     */
    public $budgetAdjustResolver;

    /**
     * Sau khi lấy chunk — thêm header, bỏ chapter_title khỏi blocks…
     *
     * @var null|\Closure(int $pageIndex, array<int, array<string, mixed>> $chunk, array<int, array<string, mixed>> $remaining): array<int, array<string, mixed>>
     */
    public $chunkAdjustResolver;

    /**
     * Meta bổ sung mỗi trang (chapterTitle, layoutVariant…).
     *
     * @var null|\Closure(int $pageIndex, array<string, mixed> $page): array<string, mixed>
     */
    public $pageMetaResolver;

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function __construct(array $overrides = [])
    {
        $this->contentHeightMm      = (float) ($overrides['contentHeightMm'] ?? self::CONTENT_BUDGET_MM);
        $this->contentZoneTopMm     = (float) ($overrides['contentZoneTopMm'] ?? self::CONTENT_ZONE_TOP_MM);
        $this->contentZoneHeightMm  = (float) ($overrides['contentZoneHeightMm'] ?? self::CONTENT_ZONE_HEIGHT_MM);
        $this->contentLeftMm        = (float) ($overrides['contentLeftMm'] ?? 24.0);
        $this->contentWidthMm       = (float) ($overrides['contentWidthMm'] ?? 162.0);
        $this->charsPerLine         = (int) ($overrides['charsPerLine'] ?? 72);
        $this->lineMm               = (float) ($overrides['lineMm'] ?? 4.5);
        $this->blockGapMm           = (float) ($overrides['blockGapMm'] ?? 1.5);
        $this->imageGapMm           = (float) ($overrides['imageGapMm'] ?? 5.0);
        $this->fixedBlockHeights    = (array) ($overrides['fixedBlockHeights'] ?? []);
        $this->maxImageMm           = isset($overrides['maxImageMm']) ? (float) $overrides['maxImageMm'] : null;
        $this->splitOversizedPara   = (bool) ($overrides['splitOversizedPara'] ?? true);
        $this->clampImages          = (bool) ($overrides['clampImages'] ?? true);
        $this->skipOversizedTraits  = (bool) ($overrides['skipOversizedTraits'] ?? false);
        $this->forceNewPageBefore   = (array) ($overrides['forceNewPageBefore'] ?? []);
        $this->lineWidthThreshold   = (float) ($overrides['lineWidthThreshold'] ?? 0.95);
        $this->minImagePageMm       = (float) ($overrides['minImagePageMm'] ?? 50.0);
        $this->bgResolver           = $overrides['bgResolver'] ?? null;
        $this->blockHeightResolver  = $overrides['blockHeightResolver'] ?? null;
        $this->budgetAdjustResolver = $overrides['budgetAdjustResolver'] ?? null;
        $this->chunkAdjustResolver  = $overrides['chunkAdjustResolver'] ?? null;
        $this->pageMetaResolver     = $overrides['pageMetaResolver'] ?? null;
    }
}
