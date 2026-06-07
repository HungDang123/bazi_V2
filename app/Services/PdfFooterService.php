<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

class PdfFooterService
{
    public const BANNER_WIDTH_MM = 33.0;

    public const BOTTOM_MARGIN_MM = 7.0;

    /** ~20px/mm để badge sắc khi in PDF */
    private const RENDER_BANNER_PX = 660;

    private const PAGE_NUMBER_FONT_PX = 72;

    private const NAME_FONT_PX = 48;

    private const NAME_GAP_PX = 14;

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
     * Dán một ảnh footer hoàn chỉnh (badge + số trang + tên đã bake bằng GD).
     * FPDF không vẽ chữ đè lên ảnh ổn định — gộp hết vào một PNG opaque.
     */
    private static function drawFooterOnPage(
        Fpdi $pdf,
        float $pageW,
        float $pageH,
        int $displayPage,
        string $fullName,
        string $opaqueBanner
    ): void {
        $stripPath = self::renderFooterStrip($displayPage, $fullName, $opaqueBanner);
        if ($stripPath === null || ! file_exists($stripPath)) {
            return;
        }

        $stripInfo = @getimagesize($stripPath);
        if ($stripInfo === false) {
            return;
        }

        $stripWmm = self::BANNER_WIDTH_MM;
        $stripHmm = $stripWmm * ($stripInfo[1] / max(1, $stripInfo[0]));
        $x = ($pageW - $stripWmm) / 2;
        $y = $pageH - self::BOTTOM_MARGIN_MM - $stripHmm;

        $pdf->Image(self::fpdfImagePath($stripPath), $x, $y, $stripWmm, $stripHmm, 'PNG');
    }

    /** Footer hoàn chỉnh: badge + số trang trắng + tên HOA đen. */
    private static function renderFooterStrip(int $displayPage, string $fullName, string $bannerPath): ?string
    {
        if (! function_exists('imagecreatefrompng') || ! function_exists('imagettftext')) {
            return null;
        }

        $fullName = trim($fullName);
        $cacheDir = storage_path('app/pdf-cache/footer-strips');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheKey = hash('sha256', 'v9|'.$displayPage.'|'.$fullName.'|'.self::RENDER_BANNER_PX);
        $cachePath = $cacheDir.'/'.$cacheKey.'.png';
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        $banner = @imagecreatefrompng($bannerPath);
        if ($banner === false) {
            return null;
        }

        $bannerW = imagesx($banner);
        $bannerH = imagesy($banner);

        $boldFont = PdfFontService::boldFontPath();
        $regularFont = PdfFontService::regularFontPath() ?: $boldFont;
        if ($boldFont === '' || $regularFont === '') {
            imagedestroy($banner);

            return null;
        }

        $nameH = 0;
        $nameCanvas = null;
        if ($fullName !== '') {
            $nameCanvas = self::renderNameCanvas(mb_strtoupper($fullName, 'UTF-8'), $regularFont);
            if ($nameCanvas !== null) {
                $nameH = imagesy($nameCanvas) + self::NAME_GAP_PX;
            }
        }

        $totalH = $bannerH + $nameH;
        $canvas = imagecreatetruecolor($bannerW, $totalH);
        imagesavealpha($canvas, false);
        $cream = imagecolorallocate($canvas, 252, 250, 245);
        imagefill($canvas, 0, 0, $cream);

        imagecopy($canvas, $banner, 0, 0, 0, 0, $bannerW, $bannerH);
        imagedestroy($banner);

        self::drawCenteredText(
            $canvas,
            (string) $displayPage,
            $boldFont,
            self::PAGE_NUMBER_FONT_PX,
            (int) round($bannerW / 2),
            (int) round($bannerH / 2),
            imagecolorallocate($canvas, 255, 255, 255)
        );

        if ($nameCanvas !== null) {
            $nameW = imagesx($nameCanvas);
            $nameImgH = imagesy($nameCanvas);
            $nameX = (int) round(($bannerW - $nameW) / 2);
            $nameY = $bannerH + self::NAME_GAP_PX;
            imagecopy($canvas, $nameCanvas, $nameX, $nameY, 0, 0, $nameW, $nameImgH);
            imagedestroy($nameCanvas);
        }

        imagepng($canvas, $cachePath, 6);
        imagedestroy($canvas);

        return file_exists($cachePath) ? $cachePath : null;
    }

    private static function renderNameCanvas(string $displayName, string $font): ?\GdImage
    {
        $box = imagettfbbox(self::NAME_FONT_PX, 0, $font, $displayName);
        if (! is_array($box)) {
            return null;
        }

        $textW = $box[2] - $box[0];
        $textH = $box[1] - $box[7];
        $padX = 8;
        $padY = 4;
        $canvasW = $textW + $padX * 2;
        $canvasH = $textH + $padY * 2;

        $canvas = imagecreatetruecolor($canvasW, $canvasH);
        imagesavealpha($canvas, false);
        $cream = imagecolorallocate($canvas, 252, 250, 245);
        $black = imagecolorallocate($canvas, 10, 10, 10);
        imagefill($canvas, 0, 0, $cream);

        $textX = (int) ($padX - $box[0]);
        $textY = (int) ($padY + $textH);
        imagettftext($canvas, self::NAME_FONT_PX, 0, $textX, $textY, $black, $font, $displayName);

        return $canvas;
    }

    private static function drawCenteredText(
        \GdImage $canvas,
        string $text,
        string $font,
        int $fontSize,
        int $centerX,
        int $centerY,
        int $color
    ): void {
        $box = imagettfbbox($fontSize, 0, $font, $text);
        if (! is_array($box)) {
            return;
        }

        $textW = $box[2] - $box[0];
        $textH = $box[1] - $box[7];
        $x = (int) ($centerX - ($textW / 2) - $box[0]);
        $y = (int) ($centerY + ($textH / 2) - $box[1]);

        imagettftext($canvas, $fontSize, 0, $x, $y, $color, $font, $text);
    }

    private static function fpdfImagePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Badge footer sắc nét (vẽ vector GD).
     * 08.png gốc chỉ 70×22px — upscale sẽ luôn mờ nên vẽ lại theo màu LBTV.
     */
    public static function opaqueBannerPath(): ?string
    {
        $sharp = self::sharpBannerPath();
        if ($sharp !== null) {
            return $sharp;
        }

        return self::legacyUpscaledBannerPath();
    }

    private static function sharpBannerPath(): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-assets');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cachePath = $cacheDir.'/banner-sharp-v1-'.self::RENDER_BANNER_PX.'.png';
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        $w = self::RENDER_BANNER_PX;
        $h = max(40, (int) round($w * (22 / 70)));

        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, false);

        $cream = imagecolorallocate($img, 252, 250, 245);
        $maroon = imagecolorallocate($img, 110, 1, 1);
        $gold = imagecolorallocate($img, 201, 162, 39);
        $goldDark = imagecolorallocate($img, 145, 105, 28);
        $goldLight = imagecolorallocate($img, 228, 195, 95);

        imagefill($img, 0, 0, $cream);

        $capW = (int) round($w * 0.14);
        $lineY1 = (int) round($h * 0.10);
        $lineY2 = (int) round($h * 0.90);
        $bodyTop = (int) round($h * 0.20);
        $bodyBot = (int) round($h * 0.80);

        imagefilledrectangle($img, $capW, $bodyTop, $w - $capW, $bodyBot, $maroon);

        $lineH = max(2, (int) round($h * 0.05));
        imagefilledrectangle($img, 0, $lineY1, $w, $lineY1 + $lineH, $gold);
        imagefilledrectangle($img, 0, $lineY2 - $lineH, $w, $lineY2, $gold);

        self::drawBannerCap($img, 0, $h, $capW, $gold, $goldDark, $goldLight, true);
        self::drawBannerCap($img, $w - $capW, $h, $capW, $gold, $goldDark, $goldLight, false);

        imagepng($img, $cachePath, 6);
        imagedestroy($img);

        return file_exists($cachePath) ? $cachePath : null;
    }

    private static function drawBannerCap(
        \GdImage $img,
        int $x,
        int $h,
        int $capW,
        int $gold,
        int $goldDark,
        int $goldLight,
        bool $isLeft
    ): void {
        $cy = (int) round($h / 2);
        $spineX = $isLeft ? $x + (int) round($capW * 0.72) : $x + (int) round($capW * 0.28);
        $spineW = max(3, (int) round($capW * 0.10));

        imagefilledrectangle($img, $spineX - (int) ($spineW / 2), (int) round($h * 0.12), $spineX + (int) ($spineW / 2), (int) round($h * 0.88), $goldDark);

        $curls = [
            ['rx' => 0.42, 'ry' => 0.34, 'ox' => 0.18, 'oy' => 0.30],
            ['rx' => 0.34, 'ry' => 0.28, 'ox' => 0.34, 'oy' => 0.52],
            ['rx' => 0.28, 'ry' => 0.24, 'ox' => 0.12, 'oy' => 0.68],
        ];

        foreach ($curls as $i => $curl) {
            $rx = (int) round($capW * $curl['rx']);
            $ry = (int) round($h * $curl['ry']);
            $cx = $isLeft
                ? $x + (int) round($capW * $curl['ox'])
                : $x + $capW - (int) round($capW * $curl['ox']);
            $cy2 = (int) round($h * $curl['oy']);
            $color = $i === 1 ? $goldLight : $gold;
            imagefilledellipse($img, $cx, $cy2, $rx, $ry, $color);
        }

        imagefilledellipse($img, $spineX, $cy, (int) round($capW * 0.22), (int) round($h * 0.55), $gold);
    }

    /** Fallback: upscale 08.png nếu GD vector thất bại. */
    private static function legacyUpscaledBannerPath(): ?string
    {
        $source = self::bannerPath();
        if (! file_exists($source) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-assets');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cachePath = $cacheDir.'/banner-legacy-'.self::RENDER_BANNER_PX.'-'.filemtime($source).'.png';
        if (file_exists($cachePath)) {
            return $cachePath;
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

        $scaled = imagecreatetruecolor($targetW, $targetH);
        imagesavealpha($scaled, true);
        imagealphablending($scaled, false);
        $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefill($scaled, 0, 0, $transparent);
        imagealphablending($scaled, true);
        imagecopyresampled($scaled, $img, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
        imagedestroy($img);

        $flat = imagecreatetruecolor($targetW, $targetH);
        imagesavealpha($flat, false);
        imagefill($flat, 0, 0, imagecolorallocate($flat, 252, 250, 245));
        self::compositeWithAlpha($flat, $scaled, 0, 0, 252, 250, 245);
        imagedestroy($scaled);

        imagepng($flat, $cachePath, 6);
        imagedestroy($flat);

        return file_exists($cachePath) ? $cachePath : null;
    }

    /** Ghép ảnh có alpha lên nền kem — blend đúng viền anti-alias, không bị mờ. */
    private static function compositeWithAlpha(
        \GdImage $dst,
        \GdImage $src,
        int $dstX,
        int $dstY,
        int $bgR,
        int $bgG,
        int $bgB
    ): void {
        $w = imagesx($src);
        $h = imagesy($src);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($src, $x, $y);
                $a = ($rgba & 0x7F000000) >> 24;
                if ($a >= 127) {
                    continue;
                }

                $alpha = (127 - $a) / 127.0;
                $r = (int) round((($rgba >> 16) & 0xFF) * $alpha + $bgR * (1 - $alpha));
                $g = (int) round((($rgba >> 8) & 0xFF) * $alpha + $bgG * (1 - $alpha));
                $b = (int) round(($rgba & 0xFF) * $alpha + $bgB * (1 - $alpha));
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
                    imagesetpixel($img, $x, $y, $transparent);
                }
            }
        }

        return $img;
    }
}
