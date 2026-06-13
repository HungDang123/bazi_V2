<?php

namespace App\Services;

class NguHanhTitleRenderer
{
    private const FONT_SIZE_PX = 22;

    /** Supersampling 3x — cân bằng nét ảnh và tốc độ render. */
    private const RENDER_SCALE = 3;

    /** @var array<string, string> */
    private static array $pathCache = [];

    /** @var array<string, array{path: string, widthMm: float, heightMm: float}> */
    private static array $goldCache = [];

    /**
     * Render tiêu đề nhiều dòng bằng font UTM-Davida (y hệt tiêu đề HÀNH ở Phần 3),
     * tô gradient vàng để đọc rõ trên nền tối. Trả về đường dẫn PNG + kích thước hiển thị.
     *
     * @return array{path: string, widthMm: float, heightMm: float}
     */
    public static function goldTitleToFilePath(string $text, int $fontPx = 16, float $maxWidthMm = 162.0): array
    {
        // UTM-Davida thiếu glyph gạch ngang unicode (– — −) → GD vẽ .notdef
        // (dòng quảng cáo UTM). Thay bằng hyphen ASCII mà font có.
        $text = (string) preg_replace('/[\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}]/u', '-', $text);
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        $fallback = ['path' => '', 'widthMm' => $maxWidthMm, 'heightMm' => 0.0];

        if ($text === '' || ! function_exists('imagettfbbox')) {
            return $fallback;
        }

        $font = self::resolveFontPath();
        if ($font === null) {
            return $fallback;
        }

        $cacheKey = 'gold|'.$text.'|'.$fontPx.'|'.$maxWidthMm;
        if (isset(self::$goldCache[$cacheKey])) {
            return self::$goldCache[$cacheKey];
        }

        $scale      = self::RENDER_SCALE;
        $fontSize   = $fontPx * $scale;
        $maxWidthPx = (int) round($maxWidthMm / 25.4 * 96 * $scale);

        $lines = self::wrapLines($text, $fontSize, $font, $maxWidthPx);

        $sample     = imagettfbbox($fontSize, 0, $font, 'ÂĐQGẶàjgyÀ');
        $ascDesc    = max(1, abs($sample[7] - $sample[1]));
        $lineHeight = (int) round($ascDesc * 1.30);
        $padding    = (int) round($fontSize * 0.30);

        $w = $maxWidthPx;
        $h = $lineHeight * count($lines) + $padding * 2;

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagealphablending($im, false);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        $y = $padding + (int) round($ascDesc * 0.80);
        foreach ($lines as $line) {
            $bbox  = imagettfbbox($fontSize, 0, $font, $line);
            $lineW = abs($bbox[2] - $bbox[0]);
            $x     = (int) round(($w - $lineW) / 2) - $bbox[0];

            // Viền vàng (giống HÀNH Phần 3)
            self::drawGradientStrokeText(
                $im,
                $x,
                $y,
                $fontSize,
                $font,
                $line,
                self::STROKE_WIDTH,
                self::STROKE_ANGLE_DEG,
                [0xB4, 0x90, 0x44],
                [0xE5, 0xCA, 0x8E],
                [0xB8, 0x8C, 0x2D]
            );

            // Fill đỏ (giống HÀNH Phần 3)
            self::drawGradientText(
                $im,
                $x,
                $y,
                $fontSize,
                $font,
                $line,
                self::FILL_ANGLE_DEG,
                [0xB9, 0x00, 0x00],
                [0x3C, 0x00, 0x00]
            );

            $y += $lineHeight;
        }

        $cacheDir = storage_path('app/pdf-cache/titles');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir.DIRECTORY_SEPARATOR.hash('xxh128', $cacheKey).'.png';
        imagepng($im, $file);
        imagedestroy($im);

        $displayHeightMm = $h / $scale / 96 * 25.4;

        return self::$goldCache[$cacheKey] = [
            'path' => $file,
            'widthMm' => $maxWidthMm,
            'heightMm' => $displayHeightMm,
        ];
    }

    /**
     * Render nút pill (Tích cực / Tiêu cực) — gradient + bóng đổ, chữ trắng đậm.
     * DomPDF không hỗ trợ box-shadow/linear-gradient nên phải dùng ảnh.
     *
     * @param array{0: int, 1: int, 2: int} $colorTop
     * @param array{0: int, 1: int, 2: int} $colorBottom
     */
    public static function pillImagePath(
        string $text,
        array $colorTop,
        array $colorBottom,
        int $fontPx = 12
    ): string {
        $text = trim($text);
        if ($text === '' || ! function_exists('imagettfbbox')) {
            return '';
        }

        $font = \App\Services\PdfFontService::boldFontPath();
        if ($font === '' || ! is_file($font)) {
            return '';
        }

        $cacheKey = 'pill|'.$text.'|'.implode(',', $colorTop).'|'.implode(',', $colorBottom).'|'.$fontPx;
        $cacheDir = storage_path('app/pdf-cache/titles');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir.DIRECTORY_SEPARATOR.hash('xxh128', $cacheKey).'.png';
        if (is_file($file)) {
            return $file;
        }

        $scale    = self::RENDER_SCALE;
        $fontSize = $fontPx * $scale;

        $bbox  = imagettfbbox($fontSize, 0, $font, $text);
        $textW = abs($bbox[2] - $bbox[0]);
        $textH = abs($bbox[7] - $bbox[1]);

        $padX = (int) round($fontSize * 1.6);
        $padY = (int) round($fontSize * 0.55);

        $pillW = $textW + $padX * 2;
        $pillH = $textH + $padY * 2;
        $radius = (int) floor($pillH / 2);

        // Lề cho bóng đổ
        $shadowOffset = (int) round($scale * 2.2);
        $margin = $shadowOffset * 3;

        $w = $pillW + $margin * 2;
        $h = $pillH + $margin * 2;

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagealphablending($im, false);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        $x0 = $margin;
        $y0 = $margin;

        // Bóng đổ: vẽ pill đen mờ lệch xuống, làm mềm bằng gaussian blur
        $shadow = imagecolorallocatealpha($im, 0, 0, 0, 88);
        self::filledRoundRect($im, $x0 + $shadowOffset, $y0 + $shadowOffset * 2, $pillW, $pillH, $radius, $shadow);
        for ($i = 0; $i < 6; $i++) {
            imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
        }

        // Pill gradient dọc
        for ($row = 0; $row < $pillH; $row++) {
            $t = $pillH > 1 ? $row / ($pillH - 1) : 0;
            $r = (int) round($colorTop[0] + ($colorBottom[0] - $colorTop[0]) * $t);
            $g = (int) round($colorTop[1] + ($colorBottom[1] - $colorTop[1]) * $t);
            $b = (int) round($colorTop[2] + ($colorBottom[2] - $colorTop[2]) * $t);
            $color = imagecolorallocate($im, $r, $g, $b);

            $dy = $row - $radius;
            if ($row < $radius) {
                $dx = (int) round($radius - sqrt(max(0, $radius ** 2 - $dy ** 2)));
            } elseif ($row >= $pillH - $radius) {
                $dy = $row - ($pillH - $radius - 1);
                $dx = (int) round($radius - sqrt(max(0, $radius ** 2 - $dy ** 2)));
            } else {
                $dx = 0;
            }

            imageline($im, $x0 + $dx, $y0 + $row, $x0 + $pillW - 1 - $dx, $y0 + $row, $color);
        }

        // Chữ trắng căn giữa
        $white = imagecolorallocate($im, 255, 255, 255);
        $tx = $x0 + (int) round(($pillW - $textW) / 2) - $bbox[0];
        $ty = $y0 + (int) round(($pillH - $textH) / 2) + (int) abs($bbox[7]);
        imagettftext($im, $fontSize, 0, $tx, $ty, $white, $font, $text);

        imagepng($im, $file);
        imagedestroy($im);

        return $file;
    }

    /** Kích thước hiển thị pill (mm) — tỉ lệ từ PNG đã render. */
    public static function pillDisplaySizeMm(string $path, float $heightMm = 11.0): array
    {
        $info = @getimagesize($path);
        if ($info === false || ($info[1] ?? 0) <= 0) {
            return ['widthMm' => 30.0, 'heightMm' => $heightMm];
        }

        return [
            'widthMm' => round($heightMm * $info[0] / $info[1], 2),
            'heightMm' => $heightMm,
        ];
    }

    private static function filledRoundRect($im, int $x, int $y, int $w, int $h, int $radius, int $color): void
    {
        imagefilledrectangle($im, $x + $radius, $y, $x + $w - 1 - $radius, $y + $h - 1, $color);
        imagefilledrectangle($im, $x, $y + $radius, $x + $w - 1, $y + $h - 1 - $radius, $color);
        imagefilledellipse($im, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x + $w - 1 - $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x + $radius, $y + $h - 1 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($im, $x + $w - 1 - $radius, $y + $h - 1 - $radius, $radius * 2, $radius * 2, $color);
    }

    /**
     * Render 1 từ khóa bằng font UTM-Davida → PNG vàng trong suốt (căn giữa cả 2 chiều).
     * DomPDF không nạp được glyph tiếng Việt của Davida nên phải dùng ảnh.
     */
    public static function keywordImagePath(
        string $text,
        float $widthMm = 27.0,
        float $heightMm = 34.0,
        int $fontPx = 20,
        ?array $rgb = null
    ): string {
        $text = mb_strtoupper(trim($text), 'UTF-8');
        $text = (string) preg_replace('/[\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}]/u', '-', $text);

        if ($text === '' || ! function_exists('imagettfbbox')) {
            return '';
        }

        $font = self::resolveFontPath();
        if ($font === null) {
            return '';
        }

        $rgb = $rgb ?? [0xD4, 0xAF, 0x37];

        $cacheKey = 'kw|'.$text.'|'.$widthMm.'|'.$heightMm.'|'.$fontPx.'|'.implode(',', $rgb);
        $cacheDir = storage_path('app/pdf-cache/titles');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir.DIRECTORY_SEPARATOR.hash('xxh128', $cacheKey).'.png';
        if (is_file($file)) {
            return $file;
        }

        $scale    = self::RENDER_SCALE;
        $fontSize = $fontPx * $scale;
        $w        = (int) round($widthMm / 25.4 * 96 * $scale);
        $h        = (int) round($heightMm / 25.4 * 96 * $scale);

        $lines = self::wrapLinesMultiline($text, $fontSize, $font, $w);

        $sample     = imagettfbbox($fontSize, 0, $font, 'ÂĐQGẶàjgyÀ');
        $ascDesc    = max(1, abs($sample[7] - $sample[1]));
        $lineHeight = (int) round($ascDesc * 1.15);
        $blockH     = $lineHeight * count($lines);

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagealphablending($im, false);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        $gold = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);

        $y = (int) round(($h - $blockH) / 2 + $ascDesc * 0.85);
        foreach ($lines as $line) {
            $bbox  = imagettfbbox($fontSize, 0, $font, $line);
            $lineW = abs($bbox[2] - $bbox[0]);
            $x     = (int) round(($w - $lineW) / 2) - $bbox[0];
            imagettftext($im, $fontSize, 0, $x, $y, $gold, $font, $line);
            $y += $lineHeight;
        }

        imagepng($im, $file);
        imagedestroy($im);

        return $file;
    }

    /**
     * Nhãn cuộn lịch trang 2 — UTM-Davida, vàng (#D4AF37) như từ khóa Phần 5.
     */
    public static function scrollLabelImagePath(
        string $text,
        float $widthMm = 145.0,
        float $heightMm = 6.0,
        int $fontPx = 14
    ): string {
        return self::keywordImagePath($text, $widthMm, $heightMm, $fontPx, self::SCROLL_LABEL_RGB);
    }

    /** Tiêu đề trường (Họ & Tên, Giới tính…). */
    public const SCROLL_LABEL_RGB = [0xD4, 0xAF, 0x37];

    /** Giá trị nhập liệu — đỏ #6E0101. */
    public const SCROLL_VALUE_RGB = [0x6E, 0x01, 0x01];

    /**
     * Giá trị cuộn lịch trang 2 — UTM-Davida đỏ, tự tính chiều cao theo số dòng wrap.
     *
     * @return array{path: string, widthMm: float, heightMm: float}
     */
    public static function scrollValueImageMetrics(
        string $text,
        float $widthMm = 128.0,
        int $fontPx = 16,
        float $minHeightMm = 12.0
    ): array {
        $text = mb_strtoupper(trim($text), 'UTF-8');
        $text = (string) preg_replace('/[\x{2012}\x{2013}\x{2014}\x{2015}\x{2212}]/u', '-', $text);

        $empty = ['path' => '', 'widthMm' => $widthMm, 'heightMm' => $minHeightMm];
        if ($text === '' || ! function_exists('imagettfbbox')) {
            return $empty;
        }

        $font = self::resolveFontPath();
        if ($font === null) {
            return $empty;
        }

        $rgb   = self::SCROLL_VALUE_RGB;
        $scale = self::RENDER_SCALE;
        $fontSize = $fontPx * $scale;
        $wPx      = (int) round($widthMm / 25.4 * 96 * $scale);

        $lines = self::wrapLinesMultiline($text, $fontSize, $font, $wPx);

        $sample     = imagettfbbox($fontSize, 0, $font, 'ÂĐQGẶàjgyÀ');
        $ascDesc    = max(1, abs($sample[7] - $sample[1]));
        $lineHeight = (int) round($ascDesc * 1.15);
        $blockH     = $lineHeight * count($lines);
        $padPx      = (int) round(1.2 / 25.4 * 96 * $scale);
        $hPx        = max((int) round($minHeightMm / 25.4 * 96 * $scale), $blockH + $padPx * 2);
        $heightMm   = round($hPx / $scale / 96 * 25.4, 1);

        $cacheKey = 'scroll-val|'.$text.'|'.$widthMm.'|'.$heightMm.'|'.$fontPx.'|'.implode(',', $rgb);
        $cacheDir = storage_path('app/pdf-cache/titles');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir.DIRECTORY_SEPARATOR.hash('xxh128', $cacheKey).'.png';
        if (! is_file($file)) {
            $im = imagecreatetruecolor($wPx, $hPx);
            imagesavealpha($im, true);
            imagealphablending($im, false);
            imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
            imagealphablending($im, true);

            $color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
            $y     = (int) round(($hPx - $blockH) / 2 + $ascDesc * 0.85);
            foreach ($lines as $line) {
                $bbox  = imagettfbbox($fontSize, 0, $font, $line);
                $lineW = abs($bbox[2] - $bbox[0]);
                $x     = (int) round(($wPx - $lineW) / 2) - $bbox[0];
                imagettftext($im, $fontSize, 0, $x, $y, $color, $font, $line);
                $y += $lineHeight;
            }

            imagepng($im, $file);
            imagedestroy($im);
        }

        return [
            'path'      => is_file($file) ? $file : '',
            'widthMm'   => $widthMm,
            'heightMm'  => $heightMm,
        ];
    }

    /**
     * Ngắt dòng — ưu tiên xuống hàng sau dấu phẩy, rồi wrap theo chiều ngang.
     *
     * @return array<int, string>
     */
    private static function wrapLinesMultiline(string $text, int $fontSize, string $font, int $maxWidthPx): array
    {
        $text = (string) preg_replace('/\s*,\s*/u', ",\n", trim($text));
        $segments = preg_split('/\r\n|\r|\n/u', $text) ?: [];
        $lines    = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }
            foreach (self::wrapLines($segment, $fontSize, $font, $maxWidthPx) as $line) {
                $lines[] = $line;
            }
        }

        return $lines === [] ? [trim($text)] : $lines;
    }

    /**
     * Ngắt dòng theo chiều rộng tối đa (px) dựa trên bbox font thực tế.
     *
     * @return array<int, string>
     */
    private static function wrapLines(string $text, int $fontSize, string $font, int $maxWidthPx): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $cur   = '';

        foreach ($words as $word) {
            $try  = $cur === '' ? $word : $cur.' '.$word;
            $bbox = imagettfbbox($fontSize, 0, $font, $try);
            $wpx  = abs($bbox[2] - $bbox[0]);

            if ($cur !== '' && $wpx > $maxWidthPx) {
                $lines[] = $cur;
                $cur = $word;
                $bboxWord = imagettfbbox($fontSize, 0, $font, $cur);
                if (is_array($bboxWord) && abs($bboxWord[2] - $bboxWord[0]) > $maxWidthPx) {
                    foreach (self::wrapLongToken($cur, $fontSize, $font, $maxWidthPx) as $chunk) {
                        $lines[] = $chunk;
                    }
                    $cur = '';
                }
            } elseif ($cur === '' && $wpx > $maxWidthPx) {
                foreach (self::wrapLongToken($word, $fontSize, $font, $maxWidthPx) as $chunk) {
                    $lines[] = $chunk;
                }
                $cur = '';
            } else {
                $cur = $try;
            }
        }

        if ($cur !== '') {
            $lines[] = $cur;
        }

        return $lines === [] ? [$text] : $lines;
    }

    /**
     * @return array<int, string>
     */
    private static function wrapLongToken(string $word, int $fontSize, string $font, int $maxWidthPx): array
    {
        $chunks = [];
        $cur    = '';

        foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            $try  = $cur.$ch;
            $bbox = imagettfbbox($fontSize, 0, $font, $try);
            $wpx  = abs($bbox[2] - $bbox[0]);

            if ($cur !== '' && $wpx > $maxWidthPx) {
                $chunks[] = $cur;
                $cur = $ch;
            } else {
                $cur = $try;
            }
        }

        if ($cur !== '') {
            $chunks[] = $cur;
        }

        return $chunks === [] ? [$word] : $chunks;
    }

    /**
     * Đường dẫn PNG tiêu đề (cache disk) — DomPDF nhanh hơn base64.
     */
    public static function toFilePath(string $hanhName, int $percent): string
    {
        $cacheKey = mb_strtoupper(trim($hanhName), 'UTF-8') . '|' . $percent;
        if (isset(self::$pathCache[$cacheKey])) {
            return self::$pathCache[$cacheKey];
        }

        $cacheDir = storage_path('app/pdf-cache/titles');
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $file = $cacheDir . DIRECTORY_SEPARATOR . hash('xxh128', $cacheKey . '|v4') . '.png';
        if (! is_file($file) || filesize($file) < 64) {
            if (is_file($file)) {
                @unlink($file);
            }
            self::renderToFile($hanhName, $percent, $file);
        }

        if (! is_file($file) || filesize($file) < 64 || @getimagesize($file) === false) {
            if (is_file($file)) {
                @unlink($file);
            }

            return self::$pathCache[$cacheKey] = '';
        }

        $real = realpath($file);

        return self::$pathCache[$cacheKey] = $real !== false
            ? str_replace('\\', '/', $real)
            : str_replace('\\', '/', $file);
    }

    /** Chiều cao hiển thị tiêu đề HÀNH X XX% (mm) — khớp blade + paginator. */
    public static function titleDisplayHeightMm(string $hanhName, int $percent): float
    {
        $path = self::toFilePath($hanhName, $percent);
        if ($path === '' || ! is_file($path)) {
            return 10.0;
        }

        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) <= 0) {
            return 12.0;
        }

        $displayW = 154.0;

        return (($info[1] / $info[0]) * $displayW) + 3.0;
    }

    /**
     * Nhúng PNG raster (tiêu đề UTM-Davida, pill, keyword…) thành data URI cho DomPDF.
     * DomPDF không tải được đường dẫn file cục bộ (đặc biệt path có khoảng trắng).
     */
    public static function embedPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:image/')) {
            return $path;
        }

        if (! is_file($path)) {
            return '';
        }

        $blob = @file_get_contents($path);
        if ($blob === false || $blob === '') {
            return '';
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($blob);
    }

    /**
     * Tiêu đề vàng nhiều dòng — PNG nhúng sẵn cho DomPDF.
     *
     * @return array{src: string, widthMm: float, heightMm: float}
     */
    public static function goldTitleEmbedded(string $text, int $fontPx = 16, float $maxWidthMm = 162.0): array
    {
        $img = self::goldTitleToFilePath($text, $fontPx, $maxWidthMm);

        return [
            'src' => self::embedPath($img['path']),
            'widthMm' => $img['widthMm'],
            'heightMm' => $img['heightMm'],
        ];
    }

    /**
     * PNG tiêu đề HÀNH X XX% nhúng base64 an toàn cho DomPDF.
     */
    public static function toEmbeddedSrc(string $hanhName, int $percent): string
    {
        return self::embedPath(self::toFilePath($hanhName, $percent));
    }

    /**
     * @deprecated Dùng toFilePath() — giữ cho tương thích.
     */
    public static function toDataUri(string $hanhName, int $percent): string
    {
        return self::toEmbeddedSrc($hanhName, $percent);
    }

    private static function renderToFile(string $hanhName, int $percent, string $outputPath): void
    {
        if (!function_exists('imagettfbbox')) {
            return;
        }

        $text     = 'HÀNH ' . mb_strtoupper(trim($hanhName), 'UTF-8') . ' ' . $percent . '%';
        $font     = self::resolveFontPath();
        $fontSize = self::FONT_SIZE_PX * self::RENDER_SCALE;

        if ($font === null) {
            return;
        }

        $bbox  = imagettfbbox($fontSize, 0, $font, $text);
        $textW = abs($bbox[2] - $bbox[0]);
        $textH = abs($bbox[7] - $bbox[1]);
        $stroke = self::STROKE_WIDTH;
        $w     = self::renderWidthPx();
        $h     = max(self::FONT_SIZE_PX * self::RENDER_SCALE, $textH) + $stroke * 2;

        $baseX = (int) round(($w - $textW) / 2) - $bbox[0];
        $centerY = $stroke + ($h - $stroke * 2) / 2;
        $baseY = (int) round($centerY - (($bbox[7] + $bbox[1]) / 2));

        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagealphablending($im, false);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagealphablending($im, true);

        self::drawGradientStrokeText(
            $im,
            $baseX,
            $baseY,
            $fontSize,
            $font,
            $text,
            $stroke,
            self::STROKE_ANGLE_DEG,
            [0xB4, 0x90, 0x44],
            [0xE5, 0xCA, 0x8E],
            [0xB8, 0x8C, 0x2D]
        );

        self::drawGradientText(
            $im,
            $baseX,
            $baseY,
            $fontSize,
            $font,
            $text,
            self::FILL_ANGLE_DEG,
            [0xB9, 0x00, 0x00],
            [0x3C, 0x00, 0x00]
        );

        imagepng($im, $outputPath);
        imagedestroy($im);
    }

    /** Fill: linear-gradient(107.57deg, #B90000 0%, #3C0000 100%) */
    private const FILL_ANGLE_DEG = 107.57;

    /** Viền 1px ở kích thước hiển thị. */
    private const STROKE_WIDTH = 3;

    /** Viền: linear-gradient(98.33deg, #B49044 -2.2%, #E5CA8E 50.52%, #B88C2D 102.3%) */
    private const STROKE_ANGLE_DEG = 98.33;

    private static function renderWidthPx(): int
    {
        $displayW = (int) round((154 / 25.4) * 96);

        return (int) round($displayW * self::RENDER_SCALE / 2);
    }

    private static function cssAngleVector(float $deg): array
    {
        $rad = deg2rad($deg);

        return [sin($rad), -cos($rad)];
    }

    private static function gradientRatio(int $px, int $py, int $width, int $height, float $angleDeg): float
    {
        if ($width <= 1 && $height <= 1) {
            return 0.0;
        }

        [$gx, $gy] = self::cssAngleVector($angleDeg);

        $corners = [
            [0, 0],
            [$width - 1, 0],
            [0, $height - 1],
            [$width - 1, $height - 1],
        ];

        $min = PHP_FLOAT_MAX;
        $max = -PHP_FLOAT_MAX;

        foreach ($corners as [$cx, $cy]) {
            $proj = $cx * $gx + $cy * $gy;
            $min  = min($min, $proj);
            $max  = max($max, $proj);
        }

        $proj = $px * $gx + $py * $gy;
        $span = $max - $min;

        if ($span <= 0.00001) {
            return 0.0;
        }

        return max(0.0, min(1.0, ($proj - $min) / $span));
    }

    private static function lerpColor(array $from, array $to, float $ratio): array
    {
        return [
            (int) round($from[0] + ($to[0] - $from[0]) * $ratio),
            (int) round($from[1] + ($to[1] - $from[1]) * $ratio),
            (int) round($from[2] + ($to[2] - $from[2]) * $ratio),
        ];
    }

    private static function triStopColor(float $ratio, array $start, array $mid, array $end): array
    {
        if ($ratio <= 0.5052) {
            $local = $ratio <= 0.0 ? 0.0 : $ratio / 0.5052;

            return self::lerpColor($start, $mid, $local);
        }

        $local = ($ratio - 0.5052) / (1.0 - 0.5052);

        return self::lerpColor($mid, $end, max(0.0, min(1.0, $local)));
    }

    /** Độ đặc từ mask trắng/nền (0–1), giữ anti-aliasing FreeType. */
    private static function maskCoverage(int $rgb): float
    {
        return max(0.0, min(1.0, (255 - ($rgb & 0xFF)) / 255.0));
    }

    /** @return array<int, array<int, float>> */
    private static function buildCoverageMask(
        int $fontSize,
        string $font,
        string $text,
        int $tw,
        int $th,
        int $ox,
        int $oy
    ): array {
        $mask = imagecreatetruecolor($tw, $th);
        imagefill($mask, 0, 0, imagecolorallocate($mask, 255, 255, 255));
        if (function_exists('imageantialias')) {
            imageantialias($mask, true);
        }

        $black = imagecolorallocate($mask, 0, 0, 0);
        imagettftext($mask, $fontSize, 0, $ox, $oy, $black, $font, $text);

        $coverage = [];
        for ($py = 0; $py < $th; $py++) {
            $coverage[$py] = [];
            for ($px = 0; $px < $tw; $px++) {
                $coverage[$py][$px] = self::maskCoverage(imagecolorat($mask, $px, $py));
            }
        }

        imagedestroy($mask);

        return $coverage;
    }

    private static function compositePixel(
        \GdImage $target,
        int $tx,
        int $ty,
        int $r,
        int $g,
        int $b,
        float $alpha
    ): void {
        if ($alpha <= 0.001) {
            return;
        }

        $maxX = imagesx($target) - 1;
        $maxY = imagesy($target) - 1;
        if ($tx < 0 || $ty < 0 || $tx > $maxX || $ty > $maxY) {
            return;
        }

        $existing = imagecolorat($target, $tx, $ty);
        $dstA = (127 - (($existing >> 24) & 0x7F)) / 127.0;
        $dstR = ($existing >> 16) & 0xFF;
        $dstG = ($existing >> 8) & 0xFF;
        $dstB = $existing & 0xFF;

        $outA = $alpha + $dstA * (1.0 - $alpha);
        if ($outA <= 0.001) {
            return;
        }

        $outR = (int) round(($r * $alpha + $dstR * $dstA * (1.0 - $alpha)) / $outA);
        $outG = (int) round(($g * $alpha + $dstG * $dstA * (1.0 - $alpha)) / $outA);
        $outB = (int) round(($b * $alpha + $dstB * $dstA * (1.0 - $alpha)) / $outA);
        $alphaByte = (int) round((1.0 - $outA) * 127);

        $color = imagecolorallocatealpha($target, $outR, $outG, $outB, $alphaByte);
        imagesetpixel($target, $tx, $ty, $color);
    }

    private static function drawGradientText(
        \GdImage $target,
        int $x,
        int $y,
        int $fontSize,
        string $font,
        string $text,
        float $angleDeg,
        array $startRgb,
        array $endRgb
    ): void {
        $bbox = imagettfbbox($fontSize, 0, $font, $text);
        $tw   = abs($bbox[2] - $bbox[0]) + 4;
        $th   = abs($bbox[7] - $bbox[1]) + 4;
        $fillOx = 2 - $bbox[0];
        $fillOy = 2 - $bbox[7];
        $coverage = self::buildCoverageMask($fontSize, $font, $text, $tw, $th, $fillOx, $fillOy);

        for ($py = 0; $py < $th; $py++) {
            for ($px = 0; $px < $tw; $px++) {
                $alpha = $coverage[$py][$px];
                if ($alpha <= 0.001) {
                    continue;
                }

                $ratio = self::gradientRatio($px, $py, $tw, $th, $angleDeg);
                [$r, $g, $b] = self::lerpColor($startRgb, $endRgb, $ratio);
                self::compositePixel($target, $x + $px - $fillOx, $y + $py - $fillOy, $r, $g, $b, $alpha);
            }
        }
    }

    private static function drawGradientStrokeText(
        \GdImage $target,
        int $x,
        int $y,
        int $fontSize,
        string $font,
        string $text,
        int $strokePx,
        float $angleDeg,
        array $startRgb,
        array $midRgb,
        array $endRgb
    ): void {
        $bbox = imagettfbbox($fontSize, 0, $font, $text);
        $tw   = abs($bbox[2] - $bbox[0]) + $strokePx * 2 + 6;
        $th   = abs($bbox[7] - $bbox[1]) + $strokePx * 2 + 6;
        $ox   = $strokePx + 3 - $bbox[0];
        $oy   = $strokePx + 3 - $bbox[7];
        $coverage = self::buildCoverageMask($fontSize, $font, $text, $tw, $th, $ox, $oy);

        for ($py = 0; $py < $th; $py++) {
            for ($px = 0; $px < $tw; $px++) {
                $fillAlpha = $coverage[$py][$px];
                $ringAlpha = 0.0;

                for ($dy = -$strokePx; $dy <= $strokePx; $dy++) {
                    for ($dx = -$strokePx; $dx <= $strokePx; $dx++) {
                        if ($dx === 0 && $dy === 0) {
                            continue;
                        }
                        if (max(abs($dx), abs($dy)) > $strokePx) {
                            continue;
                        }

                        $nx = $px + $dx;
                        $ny = $py + $dy;
                        if ($nx < 0 || $ny < 0 || $nx >= $tw || $ny >= $th) {
                            continue;
                        }

                        $neighbor = $coverage[$ny][$nx];
                        $ringAlpha = max($ringAlpha, max(0.0, $neighbor - $fillAlpha));
                    }
                }

                if ($ringAlpha <= 0.001) {
                    continue;
                }

                $ratio = self::gradientRatio($px, $py, $tw, $th, $angleDeg);
                [$r, $g, $b] = self::triStopColor($ratio, $startRgb, $midRgb, $endRgb);
                self::compositePixel($target, $x + $px - $ox, $y + $py - $oy, $r, $g, $b, $ringAlpha);
            }
        }
    }

    private static function resolveFontPath(): ?string
    {
        $root = dirname(__DIR__, 2);

        $candidates = [
            $root . '/public/fonts/UTM-Davida.ttf',
            $root . '/resources/fonts/UTM-Davida.ttf',
            $root . '/resources/fonts/UTMDavida.ttf',
            $root . '/resources/fonts/UTM Davida.ttf',
            $root . '/vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
