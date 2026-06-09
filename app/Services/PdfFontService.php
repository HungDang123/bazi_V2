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

        self::repairInstalledFontsCache();
    }

    /**
     * DomPDF lưu installed-fonts.json — nếu value là full path (Windows) sẽ gây lỗi
     * "Undefined array key". Chuẩn hóa về basename và xóa entry không có file .ufm.
     */
    public static function repairInstalledFontsCache(): void
    {
        $fontDir  = self::fontDir();
        $jsonPath = $fontDir.'/installed-fonts.json';

        if (! is_file($jsonPath)) {
            return;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data)) {
            @unlink($jsonPath);

            return;
        }

        $changed = false;

        foreach ($data as $family => $variants) {
            if (! is_array($variants)) {
                unset($data[$family]);
                $changed = true;

                continue;
            }

            foreach ($variants as $variant => $value) {
                if (! is_string($value) || $value === '') {
                    unset($data[$family][$variant]);
                    $changed = true;

                    continue;
                }

                $basename = basename(str_replace('\\', '/', $value));
                $basename = preg_replace('/\.(ufm|ttf|json)$/i', '', $basename) ?? $basename;

                if ($basename !== $value) {
                    $data[$family][$variant] = $basename;
                    $changed = true;
                }

                if (! is_file($fontDir.'/'.$basename.'.ufm')) {
                    unset($data[$family][$variant]);
                    $changed = true;
                }
            }

            if (empty($data[$family])) {
                unset($data[$family]);
                $changed = true;
            }
        }

        if ($changed) {
            file_put_contents(
                $jsonPath,
                json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
            );
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
