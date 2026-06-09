<?php

namespace App\Services\Pdf;

/**
 * Cấu hình phân trang PDF — vùng nội dung 70% A4, nội dung xếp từ trên xuống.
 */
class PdfPaginationConfig
{
    public const PAGE_HEIGHT_MM = 297.0;

    public const CONTENT_RATIO = 0.7;

    public const CONTENT_ZONE_HEIGHT_MM = 207.9; // 297 × 0.7

    public const CONTENT_ZONE_TOP_MM = 15.0; // vùng 70% bắt đầu từ trên, nội dung xếp từ trên xuống

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
        $this->contentHeightMm      = (float) ($overrides['contentHeightMm'] ?? self::CONTENT_ZONE_HEIGHT_MM);
        $this->contentZoneTopMm     = (float) ($overrides['contentZoneTopMm'] ?? self::CONTENT_ZONE_TOP_MM);
        $this->contentZoneHeightMm  = (float) ($overrides['contentZoneHeightMm'] ?? self::CONTENT_ZONE_HEIGHT_MM);
        $this->contentLeftMm        = (float) ($overrides['contentLeftMm'] ?? 24.0);
        $this->contentWidthMm       = (float) ($overrides['contentWidthMm'] ?? 162.0);
        $this->charsPerLine         = (int) ($overrides['charsPerLine'] ?? 72);
        $this->lineMm               = (float) ($overrides['lineMm'] ?? 5.2);
        $this->blockGapMm           = (float) ($overrides['blockGapMm'] ?? 2.0);
        $this->imageGapMm           = (float) ($overrides['imageGapMm'] ?? 5.0);
        $this->fixedBlockHeights    = (array) ($overrides['fixedBlockHeights'] ?? []);
        $this->maxImageMm           = isset($overrides['maxImageMm']) ? (float) $overrides['maxImageMm'] : null;
        $this->splitOversizedPara   = (bool) ($overrides['splitOversizedPara'] ?? true);
        $this->clampImages          = (bool) ($overrides['clampImages'] ?? true);
        $this->skipOversizedTraits  = (bool) ($overrides['skipOversizedTraits'] ?? false);
        $this->forceNewPageBefore   = (array) ($overrides['forceNewPageBefore'] ?? []);
        $this->bgResolver           = $overrides['bgResolver'] ?? null;
        $this->blockHeightResolver  = $overrides['blockHeightResolver'] ?? null;
        $this->budgetAdjustResolver = $overrides['budgetAdjustResolver'] ?? null;
        $this->chunkAdjustResolver  = $overrides['chunkAdjustResolver'] ?? null;
        $this->pageMetaResolver     = $overrides['pageMetaResolver'] ?? null;
    }
}
