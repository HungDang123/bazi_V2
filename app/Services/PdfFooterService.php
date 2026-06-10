<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

class PdfFooterService
{
    public const BANNER_WIDTH_MM = 42.0;

    public const BOTTOM_MARGIN_MM = 4.5;

    /** 20px/mm — badge 33mm = 660px */
    private const RENDER_BANNER_PX = 660;

    private const PAGE_NUMBER_FONT_PX = 72;

    private const NAME_FONT_PX = 48;

    /** 1.4mm @ 20px/mm */
    private const NAME_GAP_PX = 28;

    private const PAGE_LETTER_SPACING_PX = 4;

    private const NAME_LETTER_SPACING_PX = 3;

    public const FIRST_FOOTER_PAGE = 3;

    public const FIRST_DISPLAY_PAGE_NUMBER = 1;

    public static function svgBadgePath(): string
    {
        return resource_path('views/pdfs/partials/footer-badge.svg');
    }

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
                $isLastPage  = $pageNo === $pageCount;
                self::drawFooterOnPage($pdf, $pageW, $pageH, $displayPage, $fullName, $opaqueBanner, $isLastPage);
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
     * Đặt badge (SVG → Imagick transparent PNG) + tên (Imagick transparent PNG) lên trang PDF.
     */
    private static function drawFooterOnPage(
        Fpdi $pdf,
        float $pageW,
        float $pageH,
        int $displayPage,
        string $fullName,
        string $opaqueBanner,
        bool $isLastPage = false
    ): void {
        $badgePath = self::renderBadgeStrip($displayPage, $opaqueBanner);
        if ($badgePath === null || ! file_exists($badgePath)) {
            return;
        }

        $badgeInfo = @getimagesize($badgePath);
        if ($badgeInfo === false) {
            return;
        }

        $badgeWmm = self::BANNER_WIDTH_MM;
        $badgeHmm = $badgeWmm * ($badgeInfo[1] / max(1, $badgeInfo[0]));
        $badgeX   = ($pageW - $badgeWmm) / 2;

        $displayName = trim($fullName) !== '' ? mb_strtoupper(trim($fullName), 'UTF-8') : '';

        // Trang đầu + trang cuối thường nền tối → chữ trắng; các trang giữa → chữ đen
        $whiteText = $isLastPage || ($displayPage === self::FIRST_DISPLAY_PAGE_NUMBER);
        $namePath  = $displayName !== '' ? self::renderNameStrip($displayName, $whiteText) : null;
        $nameWmm  = 0.0;
        $nameHmm  = 0.0;
        if ($namePath !== null && file_exists($namePath)) {
            $nameInfo = @getimagesize($namePath);
            if (is_array($nameInfo) && $nameInfo[0] > 0 && $nameInfo[1] > 0) {
                // scale theo cùng px/mm như badge (RENDER_BANNER_PX / BANNER_WIDTH_MM)
                $pxPerMm = self::RENDER_BANNER_PX / self::BANNER_WIDTH_MM;
                $nameWmm = $nameInfo[0] / $pxPerMm;
                $nameHmm = $nameInfo[1] / $pxPerMm;
                $nameWmm = min($pageW - 10, $nameWmm);
            }
        }

        $gapMm    = 1.8;
        $totalHmm = $badgeHmm + ($namePath !== null ? $gapMm + $nameHmm : 0.0);
        $badgeY   = $pageH - self::BOTTOM_MARGIN_MM - $totalHmm;

        $pdf->Image(self::fpdfImagePath($badgePath), $badgeX, $badgeY, $badgeWmm, $badgeHmm, 'PNG');

        if ($namePath !== null && $nameWmm > 0 && $nameHmm > 0) {
            $nameX = ($pageW - $nameWmm) / 2;
            $nameY = $badgeY + $badgeHmm + $gapMm;
            $pdf->Image(self::fpdfImagePath($namePath), $nameX, $nameY, $nameWmm, $nameHmm, 'PNG');
        }
    }

    /** Badge PNG (tên badge gốc + số trang) — chỉ rộng bằng badge, không có nền trắng thừa. */
    private static function renderBadgeStrip(int $displayPage, string $bannerPath): ?string
    {
        if (! function_exists('imagecreatefrompng') || ! function_exists('imagettftext')) {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-strips');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheKey = hash('sha256', 'badge-v3|'.$displayPage.'|'.self::RENDER_BANNER_PX);
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
        if ($boldFont === '') {
            imagedestroy($banner);

            return null;
        }

        // Giữ alpha channel của SVG (nền trong suốt)
        imagealphablending($banner, true);
        imagesavealpha($banner, true);

        $white = imagecolorallocatealpha($banner, 255, 255, 255, 0);
        self::drawCenteredSpacedText(
            $banner,
            (string) $displayPage,
            $boldFont,
            self::PAGE_NUMBER_FONT_PX,
            (int) round($bannerW / 2),
            (int) round($bannerH / 2),
            $white,
            self::PAGE_LETTER_SPACING_PX
        );

        imagepng($banner, $cachePath, 6);
        imagedestroy($banner);

        return file_exists($cachePath) ? $cachePath : null;
    }

    /**
     * Tên HOA → PNG dùng Imagick.
     * $whiteText=true  → chữ trắng, nền trong suốt (trang nền tối).
     * $whiteText=false → chữ đen, nền trong suốt (trang nền sáng).
     */
    private static function renderNameStrip(string $displayName, bool $whiteText = false): ?string
    {
        if (! class_exists(\Imagick::class) || ! class_exists(\ImagickDraw::class)) {
            return self::renderNameStripGd($displayName, $whiteText);
        }

        $font = PdfFontService::boldFontPath() ?: PdfFontService::regularFontPath();
        if ($font === '') {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-strips');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $variant  = $whiteText ? 'white' : 'dark';
        $cacheKey = hash('sha256', 'name-imagick-v4|'.$variant.'|'.$displayName.'|'.self::NAME_FONT_PX);
        $cachePath = $cacheDir.'/'.$cacheKey.'.png';
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        try {
            $draw = new \ImagickDraw();
            $draw->setFont($font);
            $draw->setFontSize(self::NAME_FONT_PX);
            $draw->setFillColor(new \ImagickPixel($whiteText ? 'rgb(255,255,255)' : 'rgb(10,10,10)'));
            $draw->setTextAntialias(true);

            $probe   = new \Imagick();
            $metrics = $probe->queryFontMetrics($draw, $displayName, false);
            $probe->destroy();
            $textW = (int) ceil($metrics['textWidth'] ?? (strlen($displayName) * self::NAME_FONT_PX * 0.6));
            $textH = (int) ceil(($metrics['ascender'] ?? self::NAME_FONT_PX) + abs($metrics['descender'] ?? 0));
            $padX  = 16;
            $padY  = 10;

            $img = new \Imagick();
            // Cả hai variant đều dùng transparent PNG32 (giống badge SVG — FPDF xử lý đúng)
            $img->newImage($textW + $padX * 2, $textH + $padY * 2, new \ImagickPixel('none'));
            $img->setImageFormat('png32');
            $img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

            $draw->setGravity(\Imagick::GRAVITY_CENTER);
            $img->annotateImage($draw, 0, 0, 0, $displayName);

            $img->writeImage($cachePath);
            $img->destroy();

            return file_exists($cachePath) ? $cachePath : null;
        } catch (\Throwable $e) {
            Log::warning('PdfFooterService renderNameStrip Imagick thất bại', ['error' => $e->getMessage()]);

            return self::renderNameStripGd($displayName, $whiteText);
        }
    }

    /**
     * Tên HOA → PNG (GD) — nền trong suốt, giống Imagick khi server không có extension.
     */
    private static function renderNameStripGd(string $displayName, bool $whiteText = false): ?string
    {
        if (! function_exists('imagettftext')) {
            return null;
        }

        $font = PdfFontService::boldFontPath() ?: PdfFontService::regularFontPath();
        if ($font === '') {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-strips');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $variant  = $whiteText ? 'white' : 'dark';
        $cacheKey = hash('sha256', 'name-gd-v3|'.$variant.'|'.$displayName.'|'.self::NAME_FONT_PX);
        $cachePath = $cacheDir.'/'.$cacheKey.'.png';
        if (file_exists($cachePath)) {
            return $cachePath;
        }

        $box = imagettfbbox(self::NAME_FONT_PX, 0, $font, $displayName);
        if (! is_array($box)) {
            return null;
        }

        $textW = $box[2] - $box[0];
        $textH = $box[1] - $box[7];
        $padX  = 16;
        $padY  = 10;
        $canvasW = $textW + $padX * 2;
        $canvasH = $textH + $padY * 2;

        $canvas = imagecreatetruecolor($canvasW, $canvasH);
        imagesavealpha($canvas, true);
        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagealphablending($canvas, true);

        $textColor = $whiteText
            ? imagecolorallocate($canvas, 255, 255, 255)
            : imagecolorallocate($canvas, 10, 10, 10);
        $textX = (int) ($padX - $box[0]);
        $textY = (int) ($padY + $textH);
        imagettftext($canvas, self::NAME_FONT_PX, 0, $textX, $textY, $textColor, $font, $displayName);

        imagepng($canvas, $cachePath, 6);
        imagedestroy($canvas);

        return file_exists($cachePath) ? $cachePath : null;
    }

    /** @return array<int, string> */
    private static function splitChars(string $text): array
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return is_array($chars) ? $chars : [$text];
    }

    private static function spacedTextWidth(string $text, string $font, int $fontSize, int $letterSpacingPx): float
    {
        $chars = self::splitChars($text);
        $width = 0.0;

        foreach ($chars as $index => $char) {
            $box = imagettfbbox($fontSize, 0, $font, $char);
            if (! is_array($box)) {
                continue;
            }

            $width += ($box[2] - $box[0]);
            if ($index < count($chars) - 1) {
                $width += $letterSpacingPx;
            }
        }

        return $width;
    }

    private static function drawCenteredSpacedText(
        \GdImage $canvas,
        string $text,
        string $font,
        int $fontSize,
        int $centerX,
        int $centerY,
        int $color,
        int $letterSpacingPx = 0
    ): void {
        $chars = self::splitChars($text);
        if ($chars === []) {
            return;
        }

        $totalW = self::spacedTextWidth($text, $font, $fontSize, $letterSpacingPx);
        $cursorX = $centerX - ($totalW / 2);

        foreach ($chars as $index => $char) {
            $box = imagettfbbox($fontSize, 0, $font, $char);
            if (! is_array($box)) {
                continue;
            }

            $x = (int) round($cursorX - $box[0]);
            $y = (int) round($centerY - (($box[7] + $box[1]) / 2));
            imagettftext($canvas, $fontSize, 0, $x, $y, $color, $font, $char);

            $cursorX += ($box[2] - $box[0]);
            if ($index < count($chars) - 1) {
                $cursorX += $letterSpacingPx;
            }
        }
    }

    private static function drawTopCenteredSpacedText(
        \GdImage $canvas,
        string $text,
        string $font,
        int $fontSize,
        int $centerX,
        int $topY,
        int $color,
        int $letterSpacingPx = 0
    ): void {
        $chars = self::splitChars($text);
        if ($chars === []) {
            return;
        }

        $totalW = self::spacedTextWidth($text, $font, $fontSize, $letterSpacingPx);
        $cursorX = $centerX - ($totalW / 2);

        foreach ($chars as $index => $char) {
            $box = imagettfbbox($fontSize, 0, $font, $char);
            if (! is_array($box)) {
                continue;
            }

            $x = (int) round($cursorX - $box[0]);
            $y = (int) round($topY - $box[7]);
            imagettftext($canvas, $fontSize, 0, $x, $y, $color, $font, $char);

            $cursorX += ($box[2] - $box[0]);
            if ($index < count($chars) - 1) {
                $cursorX += $letterSpacingPx;
            }
        }
    }

    private static function fpdfImagePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Rasterize SVG badge → PNG với nền TRONG SUỐT (transparent).
     * FPDF/FPDI hỗ trợ PNG-32 alpha — badge nổi trên nền trang, không che nội dung.
     */
    public static function opaqueBannerPath(): ?string
    {
        $cacheDir = storage_path('app/pdf-cache/footer-assets');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $svg = self::svgBadgePath();
        if (file_exists($svg) && class_exists(\Imagick::class)) {
            $cacheKey = 'svg-transparent-v2-'.self::RENDER_BANNER_PX.'-'.filemtime($svg);
            $cachePath = $cacheDir.'/'.$cacheKey.'.png';
            if (file_exists($cachePath)) {
                return $cachePath;
            }

            try {
                $im = new \Imagick();
                $im->setBackgroundColor(new \ImagickPixel('none'));
                $im->setResolution(300, 300);
                $im->readImage('svg:'.$svg);
                $im->setImageFormat('png32');
                $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                $srcW = $im->getImageWidth();
                $targetW = self::RENDER_BANNER_PX;
                $targetH = max(1, (int) round($im->getImageHeight() * ($targetW / max(1, $srcW))));
                $im->resizeImage($targetW, $targetH, \Imagick::FILTER_LANCZOS, 1);
                $im->writeImage($cachePath);
                $im->destroy();

                if (file_exists($cachePath)) {
                    return $cachePath;
                }
            } catch (\Throwable $e) {
                Log::warning('PdfFooterService: SVG→PNG thất bại', ['error' => $e->getMessage()]);
            }
        }

        return self::gdTransparentBannerPath();
    }

    /**
     * Upscale footer-banner.png bằng GD — giữ alpha (PNG-32), không cần Imagick.
     */
    private static function gdTransparentBannerPath(): ?string
    {
        $source = self::bannerPath();
        if (! file_exists($source) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $cacheDir = storage_path('app/pdf-cache/footer-assets');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cachePath = $cacheDir.'/banner-gd-alpha-v1-'.self::RENDER_BANNER_PX.'-'.filemtime($source).'.png';
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

        imagepng($scaled, $cachePath, 6);
        imagedestroy($scaled);

        return file_exists($cachePath) ? $cachePath : null;
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
