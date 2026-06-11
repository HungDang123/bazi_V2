<?php

namespace App\Services\Pdf;

/**
 * Chiều cao khối Tích cực / Tiêu cực — đo bằng font metrics thật (khớp DomPDF).
 */
class Phan5TraitLayout
{
    /**
     * Độ rộng text 1 cột — hẹp hơn thực tế một chút để ước lượng đủ dòng (tránh clip).
     * (166mm − spacing)/2 − border − padding ≈ 66–68mm; dùng 66mm an toàn.
     */
    public const COL_TEXT_WIDTH_MM = 66.0;

    /** 14px × 140% line-height ≈ 5.2mm + buffer DomPDF justify. */
    public const LINE_MM = 5.85;

    /** margin-bottom 2.5mm giữa các <p>. */
    public const P_MARGIN_MM = 2.5;

    /** padding-bottom 4mm + buffer dưới cùng. */
    public const BODY_PADDING_MM = 10.0;

    /** Đệm cuối — box ép height, thiếu 2–3mm sẽ clip dòng cuối. */
    public const BOX_SAFETY_MM = 3.0;

    public const ROW_MARGIN_MM = 6.0;

    /** Chiều cao phần body 1 cột (đo từng đoạn bằng độ rộng thật). */
    public static function bodyHeightMm(?string $text): float
    {
        if ($text === null || trim($text) === '') {
            return 0.0;
        }

        $paras = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $paras[] = $line;
            }
        }

        if ($paras === []) {
            return 0.0;
        }

        $height = 0.0;
        $lastIdx = count($paras) - 1;
        foreach ($paras as $i => $line) {
            if (preg_match('/^[-–•]\s*/u', $line)) {
                $line = preg_replace('/^[-–•]\s*/u', '– ', $line);
            } else {
                $line = '– '.$line;
            }
            $lines = PdfTextWrapHelper::lineCountByWidth($line, self::COL_TEXT_WIDTH_MM);
            $height += $lines * self::LINE_MM;
            if ($i < $lastIdx) {
                $height += self::P_MARGIN_MM;
            }
        }

        return $height;
    }

    public static function pillSectionHeightMm(float $pillHeightMm = 11.0): float
    {
        return round(2.5 + $pillHeightMm + 2.0 + 1.5, 2);
    }

    public static function boxHeightMm(?string $tichCuc, ?string $tieuCuc, float $pillHeightMm = 11.0): float
    {
        $maxBody = max(self::bodyHeightMm($tichCuc), self::bodyHeightMm($tieuCuc), self::LINE_MM);

        return round(
            self::pillSectionHeightMm($pillHeightMm) + $maxBody + self::BODY_PADDING_MM + self::BOX_SAFETY_MM,
            2
        );
    }

    public static function blockHeightMm(?string $tichCuc, ?string $tieuCuc): float
    {
        return self::boxHeightMm($tichCuc, $tieuCuc) + self::ROW_MARGIN_MM;
    }

    /**
     * Tách traits theo budget chiều cao (mm) — phần đầu vừa trang hiện tại,
     * phần còn lại qua trang sau (tránh box cao hơn trang bị clip chữ).
     *
     * @return array{0: string, 1: string, 2: string, 3: string}|null
     *         [headTich, headTieu, tailTich, tailTieu] — null nếu không cần/không thể tách
     */
    public static function splitByHeight(?string $tichCuc, ?string $tieuCuc, float $availableMm): ?array
    {
        $bodyBudget = $availableMm - self::pillSectionHeightMm() - self::BODY_PADDING_MM - self::BOX_SAFETY_MM - self::ROW_MARGIN_MM;

        // Dưới ~3 dòng thì không đáng tách — đẩy nguyên khối sang trang sau
        if ($bodyBudget < self::LINE_MM * 3 + self::P_MARGIN_MM) {
            return null;
        }

        [$headTich, $tailTich] = self::takeParas($tichCuc, $bodyBudget);
        [$headTieu, $tailTieu] = self::takeParas($tieuCuc, $bodyBudget);

        if ($tailTich === '' && $tailTieu === '') {
            return null; // vừa nguyên khối, không cần tách
        }
        if ($headTich === '' && $headTieu === '') {
            return null; // không nhét được đoạn nào
        }

        return [$headTich, $headTieu, $tailTich, $tailTieu];
    }

    /**
     * Lấy các đoạn đầu của 1 cột vừa với budget; phần dư trả về tail.
     *
     * @return array{0: string, 1: string}
     */
    protected static function takeParas(?string $text, float $budgetMm): array
    {
        if ($text === null || trim($text) === '') {
            return ['', ''];
        }

        $head = [];
        $tail = [];
        $height = 0.0;
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($tail !== []) {
                $tail[] = $line;

                continue;
            }
            $lines = PdfTextWrapHelper::lineCountByWidth(
                preg_match('/^[-–•]\s*/u', $line) ? preg_replace('/^[-–•]\s*/u', '– ', $line) : '– '.$line,
                self::COL_TEXT_WIDTH_MM
            );
            $h = ($lines * self::LINE_MM) + self::P_MARGIN_MM;
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
