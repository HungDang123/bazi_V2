<?php

namespace App\Services;

/**
 * Render biểu đồ radar Ngũ Hành (Bản Mệnh + Niên Vận) — khớp Chart.js trên web.
 */
class NguHanhRadarChartService
{
    private const WIDTH = 900;

    private const HEIGHT = 600;

    private const CX = 450;

    private const CY = 265;

    /** Bán kính vùng plot (px) — chừa chỗ pointLabels + legend. */
    private const RADIUS = 185;

    /** @var array<string, array{0: int, 1: int, 2: int}> */
    private const ELEMENT_RGB = [
        'kim'  => [156, 163, 175],
        'moc'  => [34, 197, 94],
        'thuy' => [59, 130, 246],
        'hoa'  => [239, 68, 68],
        'tho'  => [234, 179, 8],
    ];

    /** @var array<string, string> */
    private const NAME_TO_KEY = [
        'Kim'  => 'kim',
        'Mộc'  => 'moc',
        'Thủy' => 'thuy',
        'Hỏa'  => 'hoa',
        'Thổ'  => 'tho',
    ];

    /**
     * @param  array<int, array{ten_ngu_hanh?: string, diem_ngu_hanh?: float|int, diem_nien_van?: float|int, luc_than?: string}>  $bieuDoNguHanh
     */
    public static function toDataUri(array $bieuDoNguHanh): string
    {
        if (! function_exists('imagecreatetruecolor') || count($bieuDoNguHanh) < 3) {
            return self::emptyChartDataUri();
        }

        $im = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagealphablending($im, true);
        imagesavealpha($im, true);

        $white = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $white);

        $gridColor   = imagecolorallocate($im, 229, 229, 229);
        $labelColor  = imagecolorallocate($im, 31, 41, 55);
        $tickColor   = imagecolorallocate($im, 55, 65, 81);
        $bmFill      = imagecolorallocatealpha($im, 79, 70, 229, 102);
        $bmStroke    = imagecolorallocate($im, 79, 70, 229);
        $nvFill      = imagecolorallocatealpha($im, 139, 69, 19, 102);
        $nvStroke    = imagecolorallocate($im, 139, 69, 19);

        $axes = self::axisAngles(count($bieuDoNguHanh));
        $font = self::resolveFontPath();
        $fontBold = self::resolveBoldFontPath() ?? $font;

        // Spokes + grid rings (step 20 — y hệt web)
        imagesetthickness($im, 1);
        foreach ($axes as $angle) {
            [$ex, $ey] = self::polar(self::RADIUS, $angle);
            imageline($im, self::CX, self::CY, $ex, $ey, $gridColor);
        }

        foreach ([20, 40, 60, 80] as $pct) {
            self::drawRing($im, self::RADIUS * $pct / 100, $axes, $gridColor);
        }

        // Tick labels trên trục trên (trục 0)
        if ($font !== null) {
            foreach ([20, 40, 60, 80, 100] as $pct) {
                $r = self::RADIUS * $pct / 100;
                [$tx, $ty] = self::polar($r, $axes[0]);
                $lbl = (string) $pct;
                $box = imagettfbbox(11, 0, $fontBold, $lbl);
                $tw  = ($box[2] ?? 0) - ($box[0] ?? 0);
                imagettftext($im, 11, 0, (int) ($tx + 4), (int) ($ty + 4), $tickColor, $fontBold, $lbl);
            }
        }

        $hasNv = false;
        foreach ($bieuDoNguHanh as $ax) {
            if ((float) ($ax['diem_nien_van'] ?? 0) > 0) {
                $hasNv = true;
                break;
            }
        }

        $bmValues = [];
        $nvValues = [];
        foreach (array_values($bieuDoNguHanh) as $idx => $ax) {
            $bmValues[$idx] = min(100, max(0, (float) ($ax['diem_ngu_hanh'] ?? 0)));
            $nvValues[$idx] = min(100, max(0, (float) ($ax['diem_nien_van'] ?? 0)));
        }

        if ($hasNv) {
            self::drawDataset($im, $nvValues, $axes, $nvFill, $nvStroke, 2);
        }
        self::drawDataset($im, $bmValues, $axes, $bmFill, $bmStroke, 2);

        // Points + point labels
        foreach (array_values($bieuDoNguHanh) as $idx => $ax) {
            $key = self::elementKey($ax);
            [$pr, $pg, $pb] = self::ELEMENT_RGB[$key] ?? [128, 128, 128];
            $pointColor = imagecolorallocate($im, $pr, $pg, $pb);
            $pointBorder = imagecolorallocate($im, 255, 255, 255);

            foreach (['bm' => $bmValues[$idx] ?? 0, 'nv' => $nvValues[$idx] ?? 0] as $kind => $v) {
                if ($kind === 'nv' && ! $hasNv) {
                    continue;
                }
                [$px, $py] = self::polar(self::RADIUS * (float) $v / 100, $axes[$idx]);
                imagefilledellipse($im, $px, $py, 12, 12, $pointBorder);
                imagefilledellipse($im, $px, $py, 8, 8, $pointColor);
            }

            $tenNh   = trim((string) ($ax['ten_ngu_hanh'] ?? ''));
            $lucThan = trim((string) ($ax['luc_than'] ?? ''));
            $label   = $lucThan !== '' ? $tenNh.' ('.$lucThan.')' : $tenNh;

            if ($fontBold !== null && $label !== '') {
                $lr     = self::RADIUS + 28;
                [$lx, $ly] = self::polar($lr, $axes[$idx]);
                $box    = imagettfbbox(13, 0, $fontBold, $label);
                $tw     = ($box[2] ?? 0) - ($box[0] ?? 0);
                $th     = ($box[1] ?? 0) - ($box[7] ?? 0);
                $textX  = (int) round($lx - $tw / 2);
                $textY  = (int) round($ly + $th / 2);
                imagettftext($im, 13, 0, $textX, $textY, $labelColor, $fontBold, $label);
            }
        }

        self::drawLegend($im, $fontBold, $bmStroke, $nvStroke, $hasNv);

        ob_start();
        imagepng($im);
        $png = ob_get_clean() ?: '';
        imagedestroy($im);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * @param  array{ten_ngu_hanh?: string, luc_than?: string}  $ax
     */
    private static function elementKey(array $ax): string
    {
        $name = trim((string) ($ax['ten_ngu_hanh'] ?? ''));

        if (isset(self::NAME_TO_KEY[$name])) {
            return self::NAME_TO_KEY[$name];
        }

        $lower = mb_strtolower($name, 'UTF-8');

        return match ($lower) {
            'kim'  => 'kim',
            'moc', 'mộc' => 'moc',
            'thuy', 'thủy' => 'thuy',
            'hoa', 'hỏa'  => 'hoa',
            'tho', 'thổ'  => 'tho',
            default => 'kim',
        };
    }

    /**
     * @param  array<int, float>  $values
     * @param  array<int, float>  $angles
     */
    private static function drawDataset(
        \GdImage $im,
        array $values,
        array $angles,
        int $fillColor,
        int $strokeColor,
        int $strokeWidth
    ): void {
        $points = [];
        foreach ($values as $idx => $v) {
            [$px, $py] = self::polar(self::RADIUS * $v / 100, $angles[$idx] ?? -M_PI_2);
            $points[] = $px;
            $points[] = $py;
        }

        $n = count($values);
        if ($n < 3) {
            return;
        }

        if (PHP_MAJOR_VERSION >= 8) {
            imagefilledpolygon($im, $points, $fillColor);
            imagesetthickness($im, $strokeWidth);
            imagepolygon($im, $points, $strokeColor);
        } else {
            imagefilledpolygon($im, $points, $n, $fillColor);
            imagesetthickness($im, $strokeWidth);
            imagepolygon($im, $points, $n, $strokeColor);
        }
        imagesetthickness($im, 1);
    }

    /**
     * @param  array<int, float>  $angles
     */
    private static function drawRing(\GdImage $im, float $r, array $angles, int $color): void
    {
        $points = [];
        foreach ($angles as $angle) {
            [$px, $py] = self::polar($r, $angle);
            $points[] = $px;
            $points[] = $py;
        }
        $n = count($angles);
        if (PHP_MAJOR_VERSION >= 8) {
            imagepolygon($im, $points, $color);
        } else {
            imagepolygon($im, $points, $n, $color);
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function polar(float $r, float $angle): array
    {
        return [
            (int) round(self::CX + $r * cos($angle)),
            (int) round(self::CY + $r * sin($angle)),
        ];
    }

    /**
     * @return array<int, float>
     */
    private static function axisAngles(int $count): array
    {
        $angles = [];
        for ($i = 0; $i < max(3, $count); $i++) {
            $angles[] = -M_PI_2 + $i * 2 * M_PI / max(3, $count);
        }

        return $angles;
    }

    private static function drawLegend(
        \GdImage $im,
        ?string $font,
        int $bmColor,
        int $nvColor,
        bool $showNv
    ): void {
        if ($font === null) {
            return;
        }

        $y     = self::HEIGHT - 42;
        $items = [
            ['color' => $bmColor, 'label' => 'Bản Mệnh (%)'],
        ];
        if ($showNv) {
            $items[] = ['color' => $nvColor, 'label' => 'Niên Vận (%)'];
        }

        $fontSize = 13;
        $gap      = 48;
        $totalW   = 0;
        foreach ($items as $item) {
            $box = imagettfbbox($fontSize, 0, $font, $item['label']);
            $totalW += 40 + 8 + (($box[2] ?? 0) - ($box[0] ?? 0)) + $gap;
        }
        $totalW -= $gap;
        $x = (int) ((self::WIDTH - $totalW) / 2);

        foreach ($items as $item) {
            imagefilledrectangle($im, $x, $y, $x + 40, $y + 12, $item['color']);
            imagerectangle($im, $x, $y, $x + 40, $y + 12, $item['color']);
            imagettftext($im, $fontSize, 0, $x + 48, $y + 12, imagecolorallocate($im, 31, 41, 55), $font, $item['label']);
            $box = imagettfbbox($fontSize, 0, $font, $item['label']);
            $x  += 40 + 8 + (($box[2] ?? 0) - ($box[0] ?? 0)) + $gap;
        }
    }

    private static function resolveFontPath(): ?string
    {
        $candidates = [
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

    private static function resolveBoldFontPath(): ?string
    {
        $candidates = [
            storage_path('fonts/svn_poppins_bold_6b0699b50613bf17ff96dd5c61fc8bf1.ttf'),
            storage_path('fonts/svn_poppins_normal_f04cd139384b20765736e6f179adefb3.ttf'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function emptyChartDataUri(): string
    {
        $im = imagecreatetruecolor(4, 4);
        $bg = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $bg);
        ob_start();
        imagepng($im);
        $png = ob_get_clean() ?: '';
        imagedestroy($im);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
