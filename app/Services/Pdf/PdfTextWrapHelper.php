<?php

namespace App\Services\Pdf;

use App\Services\PdfFontService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Tách dòng dài cho paginator (split trang) — render dùng một <p> tự xuống dòng.
 *
 * Đo độ rộng text bằng chính FontMetrics của DomPDF → số dòng ước lượng
 * khớp 100% với số dòng DomPDF render (không còn đoán theo ký tự).
 */
class PdfTextWrapHelper
{
    private const PT_TO_MM = 25.4 / 72;

    /** DomPDF: 1 CSS px = 0.75pt. */
    private const PX_TO_PT = 0.75;

    /** @var array{0: ?\Dompdf\FontMetrics, 1: ?string}|null */
    private static ?array $metrics = null;

    /** @var array<string, float> */
    private static array $wordWidthCache = [];

    /** @return array{0: ?\Dompdf\FontMetrics, 1: ?string} */
    private static function metrics(): array
    {
        if (self::$metrics !== null) {
            return self::$metrics;
        }

        try {
            $dompdf = Pdf::loadHTML('<html></html>')->getDomPDF();
            $dompdf->getOptions()->setFontDir(PdfFontService::fontDir());
            $dompdf->getOptions()->setFontCache(PdfFontService::fontDir());
            $fm = $dompdf->getFontMetrics();
            PdfFontService::registerWithDompdf($fm);
            $font = $fm->getFont('svn-poppins', 'normal');
            self::$metrics = [$fm, is_string($font) ? $font : null];
        } catch (\Throwable) {
            self::$metrics = [null, null];
        }

        return self::$metrics;
    }

    /** Độ rộng text (mm) đo bằng font metrics thật của DomPDF. */
    public static function textWidthMm(string $text, float $fontPx = 14.0): float
    {
        if ($text === '') {
            return 0.0;
        }

        $cacheKey = $fontPx.'|'.$text;
        if (isset(self::$wordWidthCache[$cacheKey])) {
            return self::$wordWidthCache[$cacheKey];
        }

        [$fm, $font] = self::metrics();

        if ($fm !== null && $font !== null) {
            $widthPt = (float) $fm->getTextWidth($text, $font, $fontPx * self::PX_TO_PT);
            $mm = $widthPt * self::PT_TO_MM;
        } else {
            // Fallback khi không nạp được metrics: ~2.1mm/ký tự cho 14px
            $mm = mb_strlen($text) * 2.1 * ($fontPx / 14.0);
        }

        if (count(self::$wordWidthCache) < 20000) {
            self::$wordWidthCache[$cacheKey] = $mm;
        }

        return $mm;
    }

    /**
     * Word-wrap theo độ rộng mm thật — khớp cách DomPDF wrap.
     *
     * @return array<int, string>
     */
    public static function wrapByWidthMm(string $text, float $maxWidthMm, float $fontPx = 14.0): array
    {
        $text = PdfTextSanitizer::trimString($text);
        if ($text === '') {
            return [];
        }

        $spaceW = self::textWidthMm(' ', $fontPx);
        $words = preg_split('/\s+/u', $text) ?: [];

        $lines = [];
        $current = '';
        $currentW = 0.0;

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $w = self::textWidthMm($word, $fontPx);

            if ($current === '') {
                $current = $word;
                $currentW = $w;

                continue;
            }

            if ($currentW + $spaceW + $w > $maxWidthMm) {
                $lines[] = $current;
                $current = $word;
                $currentW = $w;
            } else {
                $current .= ' '.$word;
                $currentW += $spaceW + $w;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines !== [] ? $lines : [$text];
    }

    /** Số dòng thực tế của một đoạn khi render với độ rộng cho trước. */
    public static function lineCountByWidth(string $text, float $maxWidthMm, float $fontPx = 14.0): int
    {
        return max(1, count(self::wrapByWidthMm($text, $maxWidthMm, $fontPx)));
    }

    /**
     * Chiều cao render (mm) đo theo độ rộng thật — một <p> mỗi đoạn (\n).
     */
    public static function renderedHeightMmByWidth(
        string $text,
        float $maxWidthMm,
        float $lineMm,
        float $pMarginBottomMm = 3.0,
        float $fontPx = 14.0
    ): float {
        $parts = array_values(array_filter(
            array_map(static fn (string $l): string => PdfTextSanitizer::trimString($l), preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $l): bool => $l !== ''
        ));

        if ($parts === []) {
            return $lineMm + $pMarginBottomMm;
        }

        $height = 0.0;
        foreach ($parts as $part) {
            $height += (self::lineCountByWidth($part, $maxWidthMm, $fontPx) * $lineMm) + $pMarginBottomMm;
        }

        return max($height, $lineMm + $pMarginBottomMm);
    }

    /**
     * @return array<int, string>
     */
    public static function wrapAtChars(string $text, int $maxChars): array
    {
        $text = PdfTextSanitizer::trimString($text);
        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= $maxChars) {
            return [$text];
        }

        $chunks  = [];
        $words   = preg_split('/\s+/u', $text) ?: [];
        $current = '';

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $test = $current === '' ? $word : $current.' '.$word;
            if (mb_strlen($test) > $maxChars && $current !== '') {
                $chunks[] = $current;
                $current  = $word;
            } else {
                $current = $test;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks !== [] ? $chunks : [$text];
    }

    /**
     * Chiều cao render (mm) — một <p> mỗi đoạn (\n), line-height + margin-bottom cuối đoạn.
     */
    public static function renderedHeightMm(
        string $text,
        int $maxChars,
        float $lineMm,
        float $pMarginBottomMm = 3.0
    ): float {
        $parts = array_values(array_filter(
            array_map(static fn (string $l): string => PdfTextSanitizer::trimString($l), preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $l): bool => $l !== ''
        ));

        if ($parts === []) {
            return $lineMm + $pMarginBottomMm;
        }

        $maxChars = max(1, $maxChars);
        $height   = 0.0;

        foreach ($parts as $part) {
            $lineCount = max(1, count(self::wrapAtChars($part, $maxChars)));
            $height += ($lineCount * $lineMm) + $pMarginBottomMm;
        }

        return max($height, $lineMm + $pMarginBottomMm);
    }

    /**
     * Số dòng ước lượng cho một đoạn văn (dùng split trang).
     */
    public static function estimatedLineCount(string $text, int $maxChars): int
    {
        $parts = array_values(array_filter(
            array_map(static fn (string $l): string => PdfTextSanitizer::trimString($l), preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $l): bool => $l !== ''
        ));

        if ($parts === []) {
            return 1;
        }

        $maxChars = max(1, $maxChars);
        $lines    = 0;

        foreach ($parts as $part) {
            $lines += max(1, (int) ceil(mb_strlen($part) / $maxChars));
        }

        return max(1, $lines);
    }
}
