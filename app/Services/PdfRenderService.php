<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfRenderService
{
    /**
     * Render blade → PDF file (DomPDF options tối ưu cho tốc độ).
     */
    public static function saveView(string $view, array $data, string $outputPath): void
    {
        PdfFontService::ensureRegistered();

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fontDir = PdfFontService::fontDir();

        $pdf = Pdf::loadView($view, self::normalizeViewData($data));
        PdfFontService::registerWithDompdf($pdf->getDomPDF()->getFontMetrics());

        $pdf->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('fontDir', $fontDir)
            ->setOption('fontCache', $fontDir)
            ->setOption('defaultFont', 'svn-poppins')
            ->setOption('convertEntities', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', false)
            ->setOption('isJavascriptEnabled', false)
            ->setOption('enable_font_subsetting', true)
            ->setOption('debugKeepTemp', false)
            ->setOption('debugCss', false)
            ->setOption('debugLayout', false)
            ->save($outputPath);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function normalizeViewData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = self::normalizeUnicode($value);
                $data[$key] = self::optimizeImagePathIfNeeded($value);
            } elseif (is_array($value)) {
                $data[$key] = self::normalizeViewData($value);
            }
        }

        return $data;
    }

    private static function optimizeImagePathIfNeeded(string $value): string
    {
        if ($value === '' || !preg_match('/\.(png|jpe?g|webp)$/i', $value)) {
            return $value;
        }

        if (!is_file($value)) {
            return $value;
        }

        return PdfMergeService::optimizedRasterPath($value);
    }

    private static function normalizeUnicode(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($normalized !== false) {
                return $normalized;
            }
        }

        return $text;
    }
}
