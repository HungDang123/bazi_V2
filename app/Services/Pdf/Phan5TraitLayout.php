<?php

namespace App\Services\Pdf;

/**
 * Chiều cao khối Tích cực / Tiêu cực — đo bằng font metrics DomPDF (cùng helper với para Phần 5).
 */
class Phan5TraitLayout
{
    /** Khớp phan5 profile — 14px × 140% line-height. */
    public const LINE_MM = 5.3;

    /** margin-bottom 2.5mm giữa các <p> — khớp .trait-body-cell p */
    public const P_MARGIN_MM = 2.5;

    /** padding-bottom 4mm + buffer nhỏ dưới body. */
    public const BODY_PADDING_MM = 12.0;

    /** Đệm chống clip + slack render DomPDF (justify wrap thực tế cao hơn ước lượng). */
    public const BOX_SAFETY_MM = 8.0;

    public const DOM_RENDER_SLACK_MM = 6.0;

    public const ROW_MARGIN_MM = 6.0;

    /** border-spacing giữa 2 cột traits-row. */
    public const COL_SPACING_MM = 4.0;

    /** padding ngang .trait-body-cell (4mm × 2). */
    public const COL_BODY_PADDING_H_MM = 8.0;

    /** border 0.5mm × 2 mỗi cột. */
    public const COL_BORDER_MM = 1.0;

    public const DEFAULT_CONTENT_WIDTH_MM = 166.0;

    /**
     * Độ rộng vùng text 1 cột — khớp CSS traits-row (table 166mm, 50% col, padding, border).
     */
    public static function colTextWidthMm(float $contentWidthMm = self::DEFAULT_CONTENT_WIDTH_MM): float
    {
        $colInner = ($contentWidthMm - self::COL_SPACING_MM) / 2.0;

        return max(50.0, $colInner - self::COL_BODY_PADDING_H_MM - self::COL_BORDER_MM);
    }

    /** Chiều cao phần body 1 cột (DomPDF font metrics). */
    public static function bodyHeightMm(?string $text, float $contentWidthMm = self::DEFAULT_CONTENT_WIDTH_MM): float
    {
        if ($text === null || trim($text) === '') {
            return 0.0;
        }

        $colWidth = self::colTextWidthMm($contentWidthMm);
        $lines    = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^[-–•]\s*/u', $line)) {
                $line = preg_replace('/^[-–•]\s*/u', '– ', $line);
            } else {
                $line = '– '.$line;
            }
            $lines[] = $line;
        }

        if ($lines === []) {
            return 0.0;
        }

        $height = 0.0;
        foreach ($lines as $line) {
            $height += PdfTextWrapHelper::renderedHeightMmByWidth(
                $line,
                $colWidth,
                self::LINE_MM,
                self::P_MARGIN_MM
            );
        }

        return $height;
    }

    public static function pillSectionHeightMm(float $pillHeightMm = 11.0): float
    {
        return round(2.5 + $pillHeightMm + 2.0 + 1.5, 2);
    }

    public static function boxHeightMm(
        ?string $tichCuc,
        ?string $tieuCuc,
        float $pillHeightMm = 11.0,
        float $contentWidthMm = self::DEFAULT_CONTENT_WIDTH_MM
    ): float {
        $maxBody = max(
            self::bodyHeightMm($tichCuc, $contentWidthMm),
            self::bodyHeightMm($tieuCuc, $contentWidthMm),
            self::LINE_MM
        );

        return round(
            self::pillSectionHeightMm($pillHeightMm) + $maxBody + self::BODY_PADDING_MM + self::BOX_SAFETY_MM + self::DOM_RENDER_SLACK_MM,
            2
        );
    }

    public static function blockHeightMm(
        ?string $tichCuc,
        ?string $tieuCuc,
        float $contentWidthMm = self::DEFAULT_CONTENT_WIDTH_MM
    ): float {
        return self::boxHeightMm($tichCuc, $tieuCuc, 11.0, $contentWidthMm) + self::ROW_MARGIN_MM;
    }

    /**
     * Tách traits theo budget chiều cao (mm) — phần đầu vừa trang hiện tại,
     * phần còn lại qua trang sau (tránh box cao hơn trang bị clip chữ).
     *
     * @return array{0: string, 1: string, 2: string, 3: string}|null
     *         [headTich, headTieu, tailTich, tailTieu] — null nếu không cần/không thể tách
     */
    public static function splitByHeight(
        ?string $tichCuc,
        ?string $tieuCuc,
        float $availableMm,
        float $contentWidthMm = self::DEFAULT_CONTENT_WIDTH_MM
    ): ?array {
        $bodyBudget = $availableMm
            - self::pillSectionHeightMm()
            - self::BODY_PADDING_MM
            - self::BOX_SAFETY_MM
            - self::DOM_RENDER_SLACK_MM
            - self::ROW_MARGIN_MM;

        // Dưới ~2 dòng thì không tách — paginator sẽ thử đặt cả khối hoặc sang trang sau
        if ($bodyBudget < self::LINE_MM * 2 + self::P_MARGIN_MM) {
            return null;
        }

        [$headTich, $tailTich] = self::takeParas($tichCuc, $bodyBudget, $contentWidthMm);
        [$headTieu, $tailTieu] = self::takeParas($tieuCuc, $bodyBudget, $contentWidthMm);

        if ($tailTich === '' && $tailTieu === '') {
            return null;
        }
        if ($headTich === '' && $headTieu === '') {
            return null;
        }

        return [$headTich, $headTieu, $tailTich, $tailTieu];
    }

    /**
     * Lấy các đoạn đầu của 1 cột vừa với budget; phần dư trả về tail.
     *
     * @return array{0: string, 1: string}
     */
    protected static function takeParas(
        ?string $text,
        float $budgetMm,
        float $contentWidthMm = self::DEFAULT_CONTENT_WIDTH_MM
    ): array {
        if ($text === null || trim($text) === '') {
            return ['', ''];
        }

        $colWidth = self::colTextWidthMm($contentWidthMm);
        $head     = [];
        $tail     = [];
        $height   = 0.0;

        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($tail !== []) {
                $tail[] = $line;

                continue;
            }

            $formatted = preg_match('/^[-–•]\s*/u', $line)
                ? preg_replace('/^[-–•]\s*/u', '– ', $line)
                : '– '.$line;
            $h = PdfTextWrapHelper::renderedHeightMmByWidth(
                $formatted,
                $colWidth,
                self::LINE_MM,
                self::P_MARGIN_MM
            );

            if ($height + $h > $budgetMm + 0.01) {
                $tail[] = $line;
            } else {
                $head[] = $line;
                $height += $h;
            }
        }

        return [implode("\n", $head), implode("\n", $tail)];
    }
}
