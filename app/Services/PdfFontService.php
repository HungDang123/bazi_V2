<?php

namespace App\Services;

use Dompdf\FontMetrics;

class PdfFontService
{
    private const FONT_BOLD = 'SVN-Poppins-Bold.ttf';

    private const FONT_DAVIDA = 'UTM-Davida.ttf';

    /**
     * Regular: SVN-Poppins.ttf (chữ thường 400). Không dùng Bold làm Regular.
     */
    private const REGULAR_CANDIDATES = [
        'SVN-Poppins.ttf',
        'SVN-Poppins-Regular.ttf',
        'SVN-Poppins-Medium.ttf',
        'Poppins-Regular.ttf',
    ];

    private const FONT_FAMILY = 'svn-poppins';

    private const FONT_FAMILY_DAVIDA = 'utm-davida';

    public static function fontDir(): string
    {
        return str_replace('\\', '/', storage_path('fonts'));
    }

    public static function ensureRegistered(): void
    {
        foreach (array_merge(self::REGULAR_CANDIDATES, [self::FONT_BOLD, self::FONT_DAVIDA]) as $file) {
            self::copyFontToStorage($file);
        }
    }

    private static function copyFontToStorage(string $file): void
    {
        $source = resource_path('fonts/' . $file);
        if (!file_exists($source)) {
            return;
        }

        $fontDir = self::fontDir();
        if (!is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $target = $fontDir . '/' . $file;
        if (!file_exists($target) || filemtime($source) > filemtime($target)) {
            copy($source, $target);
        }
    }

    public static function regularFontPath(): string
    {
        self::ensureRegistered();

        foreach (self::REGULAR_CANDIDATES as $file) {
            $path = self::fontDir() . '/' . $file;
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }

    public static function boldFontPath(): string
    {
        self::ensureRegistered();

        $path = self::fontDir() . '/' . self::FONT_BOLD;

        return file_exists($path) ? $path : '';
    }

    public static function davidaFontPath(): string
    {
        self::ensureRegistered();

        $path = self::fontDir() . '/' . self::FONT_DAVIDA;

        return file_exists($path) ? $path : '';
    }

    /** Đăng ký SVN-Poppins Regular (400) + Bold (700) với DomPDF. */
    public static function registerWithDompdf(FontMetrics $fontMetrics): bool
    {
        $registered = false;
        $regular    = self::regularFontPath();
        $bold       = self::boldFontPath();

        if ($regular !== '') {
            $registered = $fontMetrics->registerFont([
                'family' => self::FONT_FAMILY,
                'style'  => 'normal',
                'weight' => 'normal',
            ], $regular) || $registered;
        }

        if ($bold !== '') {
            $registered = $fontMetrics->registerFont([
                'family' => self::FONT_FAMILY,
                'style'  => 'normal',
                'weight' => 'bold',
            ], $bold) || $registered;
        }

        $davida = self::davidaFontPath();
        if ($davida !== '') {
            foreach (['normal', 'bold'] as $weight) {
                $registered = $fontMetrics->registerFont([
                    'family' => self::FONT_FAMILY_DAVIDA,
                    'style'  => 'normal',
                    'weight' => $weight,
                ], $davida) || $registered;
            }
        }

        return $registered;
    }
}
