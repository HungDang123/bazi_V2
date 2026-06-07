<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

class PdfFooterService
{
    public const BANNER_WIDTH_MM = 33.0;

    public const BOTTOM_MARGIN_MM = 7.0;

    private const RENDER_BANNER_PX = 330;

    private const PAGE_NUMBER_FONT_PT = 10;

    private const NAME_FONT_PX = 12;

    public const FIRST_FOOTER_PAGE = 3;

    public const FIRST_DISPLAY_PAGE_NUMBER = 1;

    public static function bannerPath(): string
    {
        $path = resource_path('views/pdfs/partials/footer-banner.png');

        if (file_exists($path)) {
            return $path;
        }

        $fallback = base_path('08.png');

        return file_exists($fallback) ? $fallback : $path;
    }

    /**
     * Gắn footer (banner 08.png + số trang + tên) lên từng trang PDF đã merge.
     */
    public static function applyToMergedPdf(
        string $inputPdf,
        string $outputPdf,
        string $fullName,
        int $firstFooterPage = self::FIRST_FOOTER_PAGE,
        int $firstDisplayPage = self::FIRST_DISPLAY_PAGE_NUMBER
    ): bool {
        if (! file_exists($inputPdf)) {
            return false;
        }

        $opaqueBanner = self::opaqueBannerPath();
        if ($opaqueBanner === null) {
            return copy($inputPdf, $outputPdf);
        }

        $fullName = trim($fullName);
        $firstFooterPage = max(1, $firstFooterPage);
        $firstDisplayPage = max(1, $firstDisplayPage);

        try {
            /** @var Fpdi $pdf */
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($inputPdf);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pageW = (float) $size['width'];
                $pageH = (float) $size['height'];

                $pdf->AddPage($orientation, [$pageW, $pageH]);
                $pdf->useTemplate($tplId);

                if ($pageNo < $firstFooterPage) {
                    continue;
                }

                $displayPage = $firstDisplayPage + ($pageNo - $firstFooterPage);
                self::drawFooterOnPage($pdf, $pageW, $pageH, $displayPage, $fullName, $opaqueBanner);
            }

            $outputDir = dirname($outputPdf);
            if (! is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $pdf->Output('F', $outputPdf);

            return file_exists($outputPdf);
        } catch (\Throwable $e) {
            Log::error('PdfFooterService::applyToMergedPdf thất bại', [
                'error' => $e->getMessage(),
                'input' => $inputPdf,
            ]);

            return false;
        }
    }

    /**
     * Vẽ footer trực tiếp bằng FPDF (tránh PNG alpha — FPDF không hỗ trợ trong suốt).
     */
    private static function drawFooterOnPage(
        Fpdi $pdf,
        float $pageW,
        float $pageH,
        int $displayPage,
        string $fullName,
        string $opaqueBanner
    ): void {
        $bannerInfo = @getimagesize($opaqueBanner);
        if ($bannerInfo === false) {
            return;
        }

        $bannerWmm = self::BANNER_WIDTH_MM;
        $bannerHmm = $bannerWmm * ($bannerInfo[1] / max(1, $bannerInfo[0]));
        $nameGapMm = 1.2;
        $nameHmm = 3.8;

        $nameImage = $fullName !== '' ? self::renderNameImage($fullName) : null;
        if ($nameImage !== null) {
            $nameInfo = @getimagesize($nameImage);
            if (is_array($nameInfo) && $nameInfo[0] > 0) {
                $nameHmm = max(3.0, min(5.5, $bannerWmm * ($nameInfo[1] / $nameInfo[0])));
            }
        }

        $totalHmm = $bannerHmm + ($nameImage !== null ? $nameGapMm + $nameHmm : 0);
        $x = ($pageW - $bannerWmm) / 2;
        $y = $pageH - self::BOTTOM_MARGIN_MM - $totalHmm;

        $imageType = str_ends_with(strtolower($opaqueBanner), '.jpg') ? 'JPEG' : 'PNG';
        $pdf->Image($opaqueBanner, $x, $y, $bannerWmm, $bannerHmm, $imageType);

        $pdf->SetFont('Helvetica', 'B', self::PAGE_NUMBER_FONT_PT);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($x, $y + ($bannerHmm / 2) - 2.0);
        $pdf->Cell($bannerWmm, 4, (string) $displayPage, 0, 0, 'C');

        if ($nameImage !== null && file_exists($nameImage)) {
            $nameInfo = getimagesize($nameImage);
            if (is_array($nameInfo) && $nameInfo[0] > 0 && $nameInfo[1] > 0) {
                $nameWmm = $nameHmm * ($nameInfo[0] / $nameInfo[1]);
                $nameWmm = min($pageW - 16, $nameWmm);
                $nameX = ($pageW - $nameWmm) / 2;
                $nameY = $y + $bannerHmm + $nameGapMm;
                $nameType = str_ends_with(strtolower($nameImage), '.jpg') ? 'JPEG' : 'PNG';
                $pdf->Image($nameImage, $nameX, $nameY, $nameWmm, $nameHmm, $nameType);
            }
        }
    }

    /** PNG/JPEG banner 08.png đã phẳng (không alpha) cho FPDF. */
    public static function opaqueBannerPath(): ?string
    {
        $source = self::bannerPath();
        if (! file_exists($source)) {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-assets');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cachePath = $cacheDir.'/banner-opaque-'.self::RENDER_BANNER_PX.'-'.filemtime($source).'.jpg';
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $blob = @file_get_contents($source);
        if ($blob === false) {
            return null;
        }

        $img = @imagecreatefromstring($blob);
        if ($img === false) {
            return null;
        }

        $img = self::makeNearBlackTransparent($img);

        $srcW = imagesx($img);
        $srcH = imagesy($img);
        $targetW = self::RENDER_BANNER_PX;
        $targetH = max(1, (int) round($srcH * ($targetW / max(1, $srcW))));
        $scaled = imagescale($img, $targetW, $targetH);
        imagedestroy($img);

        if ($scaled === false) {
            return null;
        }

        $flat = imagecreatetruecolor($targetW, $targetH);
        imagesavealpha($flat, false);
        $cream = imagecolorallocate($flat, 252, 250, 245);
        imagefill($flat, 0, 0, $cream);
        self::copyWithAlpha($flat, $scaled, 0, 0);
        imagedestroy($scaled);

        imagejpeg($flat, $cachePath, 92);
        imagedestroy($flat);

        return file_exists($cachePath) ? $cachePath : null;
    }

    /** Render tên HOA thành ảnh opaque (chữ đen, nền kem). */
    public static function renderNameImage(string $fullName): ?string
    {
        if (! function_exists('imagettftext')) {
            return null;
        }

        $font = PdfFontService::regularFontPath() ?: PdfFontService::boldFontPath();
        if ($font === '') {
            return null;
        }

        $displayName = mb_strtoupper(trim($fullName), 'UTF-8');
        if ($displayName === '') {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-assets');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $fontSize = self::NAME_FONT_PX;
        $cachePath = $cacheDir.'/name-'.hash('sha256', 'v6|'.$displayName.'|'.$fontSize).'.jpg';
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        $box = imagettfbbox($fontSize, 0, $font, $displayName);
        if (! is_array($box)) {
            return null;
        }

        $textW = $box[2] - $box[0];
        $textH = $box[1] - $box[7];
        $padX = 4;
        $padY = 2;
        $canvasW = $textW + $padX * 2;
        $canvasH = $textH + $padY * 2;

        $canvas = imagecreatetruecolor($canvasW, $canvasH);
        imagesavealpha($canvas, false);
        $cream = imagecolorallocate($canvas, 252, 250, 245);
        $black = imagecolorallocate($canvas, 26, 26, 26);
        imagefill($canvas, 0, 0, $cream);

        $textX = $padX - $box[0];
        $textY = $padY + $textH;
        imagettftext($canvas, $fontSize, 0, (int) $textX, (int) $textY, $black, $font, $displayName);

        imagejpeg($canvas, $cachePath, 92);
        imagedestroy($canvas);

        return file_exists($cachePath) ? $cachePath : null;
    }

    private static function copyWithAlpha(\GdImage $dst, \GdImage $src, int $dstX, int $dstY): void
    {
        $w = imagesx($src);
        $h = imagesy($src);

        imagealphablending($dst, true);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($src, $x, $y);
                $a = ($rgba & 0x7F000000) >> 24;
                if ($a >= 127) {
                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $color = imagecolorallocate($dst, $r, $g, $b);
                imagesetpixel($dst, $dstX + $x, $dstY + $y, $color);
            }
        }
    }

    private static function makeNearBlackTransparent(\GdImage $img): \GdImage
    {
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $w = imagesx($img);
        $h = imagesy($img);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($r < 28 && $g < 28 && $b < 28) {
                    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
             