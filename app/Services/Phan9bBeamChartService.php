<?php

namespace App\Services;

/**
 * Biểu đồ beam Ngũ Hành chuyển hóa (Phần 9B) — khớp canvas trên web.
 */
class Phan9bBeamChartService
{
    private const SCALE = 2;

    private const PAD_TOP = 18;

    private const PAD_BOTTOM = 8;

    private const ROW_H = 36;

    private const ROW_GAP = 14;

    private const LBL_W = 82;

    private const BADGE_W = 108;

    private const GAP = 10;

    private const PAD_X = 4;

    private const THUMB_R_BIG = 13;

    private const THUMB_R_SMALL = 6;

    private const TRACK_H = 7;

    /** @var array<string, array{color: string, colorLight: string, trackBg: string, badgeUp: string, badgeDown: string}> */
    private const THEMES = [
        'moc'  => ['color' => '#3a9e20', 'colorLight' => '#a8dc6a', 'trackBg' => '#d8f0b4', 'badgeUp' => '#2e7d12', 'badgeDown' => '#c03018'],
        'hoa'  => ['color' => '#d63b1c', 'colorLight' => '#f5a088', 'trackBg' => '#fcd8d0', 'badgeUp' => '#c03018', 'badgeDown' => '#c03018'],
        'tho'  => ['color' => '#c98020', 'colorLight' => '#f0c060', 'trackBg' => '#f5e0b0', 'badgeUp' => '#a06010', 'badgeDown' => '#c03018'],
        'kim'  => ['color' => '#606060', 'colorLight' => '#b8b8b4', 'trackBg' => '#dcdcd8', 'badgeUp' => '#4a4a46', 'badgeDown' => '#c03018'],
        'thuy' => ['color' => '#1460a5', 'colorLight' => '#80bce8', 'trackBg' => '#cce4f8', 'badgeUp' => '#0d4a8a', 'badgeDown' => '#c03018'],
    ];

    /**
     * @param  array<string, mixed>  $chart
     */
    public static function toCachedPngPath(array $chart): string
    {
        $rows = is_array($chart['rows'] ?? null) ? $chart['rows'] : [];
        if ($rows === [] || ! function_exists('imagecreatetruecolor')) {
            return '';
        }

        $cacheDir = storage_path('app/pdf-cache/phan9b-beam');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cacheKey = hash('sha256', 'phan9b-beam-v3|'.json_encode($rows, JSON_UNESCAPED_UNICODE));
        $path = $cacheDir.DIRECTORY_SEPARATOR.$cacheKey.'.png';
        if (is_file($path)) {
            return $path;
        }

        $png = self::renderPng($rows);
        if ($png === '') {
            return '';
        }

        file_put_contents($path, $png);

        return $path;
    }

    public static function chartHeightMm(float $widthMm = 162.0): float
    {
        $rowCount = count(Phan9bService::CHART_HANH_ORDER);
        $pxH = self::PAD_TOP + ($rowCount * self::ROW_H) + (($rowCount - 1) * self::ROW_GAP) + self::PAD_BOTTOM;
        $pxW = self::chartWidthPx();

        return round($widthMm * ($pxH / $pxW), 1);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private static function renderPng(array $rows): string
    {
        $s = self::SCALE;
        $w = self::chartWidthPx() * $s;
        $h = (self::PAD_TOP + (count($rows) * self::ROW_H) + (max(0, count($rows) - 1) * self::ROW_GAP) + self::PAD_BOTTOM) * $s;

        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, true);
        imagesavealpha($im, true);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $white);

        $y = self::PAD_TOP;
        foreach ($rows as $row) {
            self::drawRow($im, $row, (int) round($y * $s), $s);
            $y += self::ROW_H + self::ROW_GAP;
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean() ?: '';
        imagedestroy($im);

        return $png;
    }

    private static function chartWidthPx(): int
    {
        return self::PAD_X * 2 + self::LBL_W + self::GAP + 420 + self::GAP + self::BADGE_W;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function drawRow(\GdImage $im, array $row, int $y, int $s): void
    {
        $slug = strtolower(trim((string) ($row['slug'] ?? 'kim')));
        $theme = self::THEMES[$slug] ?? self::THEMES['kim'];

        $before = max(0, min(100, (int) ($row['before'] ?? 0)));
        $after = max(0, min(100, (int) ($row['after'] ?? 0)));
        $dir = (string) ($row['direction'] ?? 'stable');
        $isStable = $dir === 'stable' || $before === $after;
        $isGiam = $dir === 'giam';

        $x = self::PAD_X * $s;
        $lblW = self::LBL_W * $s;
        $gap = self::GAP * $s;
        $badgeW = self::BADGE_W * $s;
        $rowH = self::ROW_H * $s;
        $trackW = (self::chartWidthPx() - self::PAD_X * 2 - self::LBL_W - self::GAP - self::BADGE_W - self::GAP) * $s;

        $label = self::loadNguHanhLabel($slug);
        if ($label !== null) {
            $lw = imagesx($label);
            $lh = imagesy($label);
            $targetH = (int) round(30 * $s);
            $targetW = $lw > 0 ? (int) round($lw * $targetH / max(1, $lh)) : $lblW;
            $ly = $y + (int) round(($rowH - $targetH) / 2);
            imagecopyresampled($im, $label, $x, $ly, 0, 0, min($targetW, $lblW), $targetH, $lw, $lh);
            imagedestroy($label);
        }

        $trackX = $x + $lblW + $gap;
        $cy = $y + (int) round($rowH / 2);

        self::drawSlider(
            $im,
            $trackX,
            $cy,
            $trackW,
            $before,
            $after,
            $isStable,
            $isGiam,
            $theme,
            $s
        );

        $delta = $after - $before;
        $deltaAbs = abs($delta);
        $arrow = null;
        if ($isStable) {
            $badgeText = '- 0%';
            $badgeColor = '#9ca3af';
        } elseif ($dir === 'tang') {
            $badgeText = '(Tăng) +'.$deltaAbs.'%';
            $badgeColor = $theme['badgeUp'];
            $arrow = 'up';
        } else {
            $badgeText = '(Giảm) -'.$deltaAbs.'%';
            $badgeColor = $theme['badgeDown'];
            $arrow = 'down';
        }

        self::drawBadge($im, $trackX + $trackW + $gap, $y, $badgeW, $rowH, $badgeText, $badgeColor, $s, $arrow);
    }

    /**
     * @param  array{color: string, colorLight: string, trackBg: string}  $theme
     */
    private static function drawSlider(
        \GdImage $im,
        int $trackX,
        int $cy,
        int $trackW,
        int $before,
        int $after,
        bool $isStable,
        bool $isGiam,
        array $theme,
        int $s
    ): void {
        $padL = (self::THUMB_R_BIG + 2) * $s;
        $padR = (self::THUMB_R_BIG + 2) * $s;
        $xLeft = $trackX + $padL;
        $innerW = max(1, $trackW - $padL - $padR);
        $trackH = self::TRACK_H * $s;
        $trackY = $cy - (int) round($trackH / 2);
        $radius = (int) round($trackH / 2);

        self::fillRoundRect($im, $xLeft, $trackY, $innerW, $trackH, $radius, self::color($im, $theme['trackBg']));

        $initX = $xLeft + (int) round($innerW * ($before / 100));
        $valX = $xLeft + (int) round($innerW * ($after / 100));
        $smallX = $isGiam ? $valX : $initX;
        $bigX = $isGiam ? $initX : $valX;

        if (! $isStable) {
            $fillLeft = min($initX, $valX);
            $fillW = abs($valX - $initX);
            if ($fillW > (int) round(0.5 * $s)) {
                self::fillHorizontalGradientRoundRect(
                    $im,
                    $fillLeft,
                    $trackY,
                    $fillW,
                    $trackH,
                    $radius,
                    self::hexToRgb($theme['colorLight']),
                    self::hexToRgb($theme['color'])
                );
            }

            $coneW = (int) min(abs($bigX - $smallX) * 0.85, $innerW * 0.55);
            $coneH = 22 * $s;
            if ($coneW > 4 * $s) {
                self::drawCone($im, $bigX, $smallX, $cy, $coneW, $coneH, $theme['color']);
            }

            if (abs($smallX - $bigX) > $s) {
                self::drawSmallThumb($im, $smallX, $cy, $theme['color'], $s);
            }
            self::drawBigThumb($im, $bigX, $cy, $theme['color'], $theme['colorLight'], $s);
        } else {
            self::drawBigThumb($im, $initX, $cy, $theme['color'], $theme['colorLight'], $s);
        }
    }

    private static function drawSmallThumb(\GdImage $im, int $x, int $cy, string $color, int $s): void
    {
        $r = self::THUMB_R_SMALL * $s;
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledellipse($im, $x, $cy, $r * 2, $r * 2, $white);
        imageellipse($im, $x, $cy, $r * 2, $r * 2, self::color($im, $color, 0.6));
        imagefilledellipse($im, $x, $cy, max(2, ($r - 3 * $s) * 2), max(2, ($r - 3 * $s) * 2), self::color($im, $color, 0.33));
    }

    private static function drawBigThumb(\GdImage $im, int $x, int $cy, string $color, string $colorLight, int $s): void
    {
        $r = self::THUMB_R_BIG * $s;
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledellipse($im, $x, $cy, $r * 2, $r * 2, $white);
        imageellipse($im, $x, $cy, $r * 2, $r * 2, self::color($im, $color, 0.33));

        $innerR = max(2, $r - 4 * $s);
        $outer = self::color($im, $color);
        for ($i = $innerR; $i >= 1; $i--) {
            $t = 1 - ($i / max(1, $innerR));
            $rgb = self::lerpRgb(self::hexToRgb($colorLight), self::hexToRgb($color), $t);
            $col = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledellipse($im, $x - (int) round(2 * $s * $t), $cy - (int) round(2 * $s * $t), $i * 2, $i * 2, $col);
        }
        imagefilledellipse($im, $x, $cy, $innerR * 2, $innerR * 2, $outer);
    }

    private static function drawCone(\GdImage $im, int $bigX, int $smallX, int $cy, int $coneW, int $coneH, string $color): void
    {
        $rgb = self::hexToRgb($color);
        $pts = [];
        if ($bigX >= $smallX) {
            $pts = [
                $bigX - $coneW, $cy - 2,
                $bigX - $coneW, $cy + 2,
                $bigX, $cy + (int) round($coneH / 2),
                $bigX, $cy - (int) round($coneH / 2),
            ];
            for ($i = 0; $i < $coneW; $i++) {
                $alpha = (int) round(127 * (1 - ($i / max(1, $coneW)) * 0.6));
                $col = imagecolorallocatealpha($im, $rgb[0], $rgb[1], $rgb[2], $alpha);
                imageline($im, $bigX - $coneW + $i, $cy - (int) round($coneH / 2), $bigX - $coneW + $i, $cy + (int) round($coneH / 2), $col);
            }
        } else {
            for ($i = 0; $i < $coneW; $i++) {
                $alpha = (int) round(127 * ($i / max(1, $coneW)) * 0.6);
                $col = imagecolorallocatealpha($im, $rgb[0], $rgb[1], $rgb[2], $alpha);
                imageline($im, $bigX + $i, $cy - (int) round($coneH / 2), $bigX + $i, $cy + (int) round($coneH / 2), $col);
            }
        }
    }

    private static function drawBadge(
        \GdImage $im,
        int $x,
        int $y,
        int $w,
        int $h,
        string $text,
        string $color,
        int $s,
        ?string $arrow = null
    ): void {
        $font = self::resolveFontPath();
        if ($font === null) {
            return;
        }

        $size = 13 * $s * 0.72;
        $rgb = self::hexToRgb($color);
        $col = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        $box = imagettfbbox($size, 0, $font, $text);
        $tw = ($box[2] ?? 0) - ($box[0] ?? 0);
        $th = ($box[1] ?? 0) - ($box[7] ?? 0);

        $arrowW = 0;
        $arrowGap = (int) round(3 * $s);
        if ($arrow === 'up' || $arrow === 'down') {
            $arrowW = (int) round(7 * $s);
        }

        $totalW = $tw + ($arrowW > 0 ? $arrowW + $arrowGap : 0);
        $startX = $x + $w - $totalW;
        $ty = $y + (int) round(($h + $th) / 2);

        if ($arrowW > 0) {
            $ax = $startX + (int) round($arrowW / 2);
            $acy = $y + (int) round($h / 2);
            self::drawArrow($im, $ax, $acy, $arrow, $col, $s);
        }

        $tx = $startX + $arrowW + ($arrowW > 0 ? $arrowGap : 0);
        imagettftext($im, $size, 0, $tx, $ty, $col, $font, $text);
    }

    private static function drawArrow(\GdImage $im, int $cx, int $cy, string $dir, int $col, int $s): void
    {
        $halfW = max(3, (int) round(3.5 * $s));
        $halfH = max(4, (int) round(4.5 * $s));

        if ($dir === 'up') {
            $pts = [
                $cx, $cy - $halfH,
                $cx - $halfW, $cy + (int) round($halfH * 0.65),
                $cx + $halfW, $cy + (int) round($halfH * 0.65),
            ];
        } else {
            $pts = [
                $cx, $cy + $halfH,
                $cx - $halfW, $cy - (int) round($halfH * 0.65),
                $cx + $halfW, $cy - (int) round($halfH * 0.65),
            ];
        }

        if (PHP_MAJOR_VERSION >= 8) {
            imagefilledpolygon($im, $pts, $col);
        } else {
            imagefilledpolygon($im, $pts, 3, $col);
        }
    }

    private static function loadNguHanhLabel(string $slug): ?\GdImage
    {
        $svg = public_path('images/ngu-hanh/'.$slug.'.svg');
        if (! is_file($svg)) {
            return null;
        }

        if (! class_exists(\Imagick::class)) {
            return null;
        }

        try {
            $im = new \Imagick();
            $im->setBackgroundColor(new \ImagickPixel('transparent'));
            $im->readImage($svg);
            $im->setImageFormat('png');
            $targetH = 60;
            $w = $im->getImageWidth();
            $h = max(1, $im->getImageHeight());
            $targetW = (int) round($w * $targetH / $h);
            $im->resizeImage($targetW, $targetH, \Imagick::FILTER_LANCZOS, 1);
            $blob = $im->getImageBlob();
            $im->destroy();
            $gd = @imagecreatefromstring($blob);

            return $gd instanceof \GdImage ? $gd : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function fillRoundRect(\GdImage $im, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        $r = min($r, (int) floor($w / 2), (int) floor($h / 2));
        imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     */
    private static function fillHorizontalGradientRoundRect(
        \GdImage $im,
        int $x,
        int $y,
        int $w,
        int $h,
        int $r,
        array $from,
        array $to
    ): void {
        $r = min($r, (int) floor($w / 2), (int) floor($h / 2));
        for ($i = 0; $i < $w; $i++) {
            $t = $w <= 1 ? 0 : $i / ($w - 1);
            $rgb = self::lerpRgb($from, $to, $t);
            $col = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
            imageline($im, $x + $i, $y + $r, $x + $i, $y + $h - $r, $col);
        }

        $leftCol = imagecolorallocate($im, ...self::lerpRgb($from, $to, 0));
        $rightCol = imagecolorallocate($im, ...self::lerpRgb($from, $to, 1));
        imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $leftCol);
        imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $rightCol);
        imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $leftCol);
        imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $rightCol);
    }

    private static function color(\GdImage $im, string $hex, float $alpha = 1.0): int
    {
        $rgb = self::hexToRgb($hex, $alpha);

        if ($alpha >= 0.999) {
            return imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        }

        return imagecolorallocatealpha(
            $im,
            $rgb[0],
            $rgb[1],
            $rgb[2],
            (int) round((1 - $alpha) * 127)
        );
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hexToRgb(string $hex, float $alpha = 1.0): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 8) {
            $alpha = hexdec(substr($hex, 6, 2)) / 255;
            $hex = substr($hex, 0, 6);
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $a
     * @param  array{0: int, 1: int, 2: int}  $b
     * @return array{0: int, 1: int, 2: int}
     */
    private static function lerpRgb(array $a, array $b, float $t): array
    {
        return [
            (int) round($a[0] + ($b[0] - $a[0]) * $t),
            (int) round($a[1] + ($b[1] - $a[1]) * $t),
            (int) round($a[2] + ($b[2] - $a[2]) * $t),
        ];
    }

    private static function resolveFontPath(): ?string
    {
        $candidates = [
            storage_path('fonts/svn_poppins_bold_6b0699b50613bf17ff96dd5c61fc8bf1.ttf'),
            storage_path('fonts/svn_poppins_normal_f04cd139384b20765736e6f179adefb3.ttf'),
            resource_path('fonts/svn-poppins.ttf'),
            public_path('fonts/svn-poppins.ttf'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
