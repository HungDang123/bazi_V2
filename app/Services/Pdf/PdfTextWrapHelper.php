<?php

namespace App\Services\Pdf;

/**
 * Tách dòng dài cho DomPDF — tránh chồng chữ trong một <p>.
 * Dùng chung cho blade render và paginator (Phần 5/8).
 */
class PdfTextWrapHelper
{
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
     * Chiều cao render (mm) khi mỗi chunk = một <p> với padding-bottom cố định.
     */
    public static function renderedHeightMm(
        string $text,
        int $maxChars,
        float $lineMm,
        float $pPaddingMm = 2.0
    ): float {
        $parts = array_values(array_filter(
            array_map(static fn (string $l): string => PdfTextSanitizer::trimString($l), preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $l): bool => $l !== ''
        ));

        if ($parts === []) {
            return $lineMm;
        }

        $height = 0.0;
        foreach ($parts as $part) {
            $chunks = self::wrapAtChars($part, $maxChars);
            if ($chunks === []) {
                continue;
            }
            $height += count($chunks) * ($lineMm + $pPaddingMm);
        }

        return max($height - $pPaddingMm, $lineMm);
    }
}
