<?php

namespace App\Services;

class DocxTextService
{
    /** Vòng Tương Sinh / Tương Khắc – mục II (placeholder [example_image_ngu_hanh]). */
    public const BO_CUC_IMAGE_NGU_HANH = 'resources/views/pdfs/quyen-1/example-ngu-hanh.png';

    /** Bảng 5 hành (dòng 2) – mục II (placeholder [example_image_ban_menh]). */
    public const BO_CUC_IMAGE_BAN_MENH = 'resources/views/pdfs/quyen-1/example-ban-menh.png';

    /** Ảnh minh họa Phần 6 – Mã 2–4 (placeholder [image] trong DOCX / Excel). */
    public const PHAN6_IMAGE_NAM_THANG = 'images/pdfs/phan-6/tru-nam-tru-thang.png';

    public const PHAN6_IMAGE_THANG_NGAY = 'images/pdfs/phan-6/tru-thang-tru-ngay.png';

    public const PHAN6_IMAGE_NGAY_GIO = 'images/pdfs/phan-6/tru-ngay-tru-gio.png';

    /** Ảnh mẫu gốc (project root) — dùng khi DOCX chỉ có placeholder [image]. */
    public const PHAN6_SAMPLE_SOURCE = 'image.png';

    /** @var array<string, string> */
    public const PLACEHOLDER_IMAGES = [
        'example_image_ngu_hanh'  => self::BO_CUC_IMAGE_NGU_HANH,
        'example_image_ban_menh'  => self::BO_CUC_IMAGE_BAN_MENH,
    ];

    /** @var array<string, string> */
    public const PHAN6_TRU_LOAI_IMAGES = [
        'tru_nam_tru_thang'  => self::PHAN6_IMAGE_NAM_THANG,
        'tru_thang_tru_ngay' => self::PHAN6_IMAGE_THANG_NGAY,
        'tru_ngay_tru_gio'   => self::PHAN6_IMAGE_NGAY_GIO,
    ];

    public static function yNghiaImagePath(string $slug): string
    {
        $safe = preg_replace('/[^a-z0-9_-]+/', '-', mb_strtolower($slug, 'UTF-8')) ?: 'section';

        return 'images/pdfs/phan-6/y-nghia-' . trim($safe, '-') . '.png';
    }

    /**
     * @return array<int, string>|null
     */
    public static function extractParagraphs(string $path): ?array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return null;
        }

        $dom = new \DOMDocument();
        @$dom->loadXML($xml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $lines = [];
        foreach ($xpath->query('//w:p') as $p) {
            $text = '';
            foreach ($xpath->query('.//w:t', $p) as $t) {
                $text .= $t->textContent;
            }
            $line = trim($text);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    public static function isSkippableLine(string $line): bool
    {
        if ($line === '') {
            return true;
        }

        if (preg_match('/^PHẦN\s*3\b/iu', $line)) {
            return true;
        }

        if (preg_match('/^\[CHÈN HÌNH ẢNH\]$/iu', $line)) {
            return true;
        }

        return false;
    }

    public static function isImagePlaceholder(string $line): bool
    {
        return self::placeholderImagePath($line) !== null;
    }

    /** [image], [CHÈN HÌNH ẢNH…] (kể cả dòng đỏ trong Excel mẫu Mã 2–4). */
    public static function isPhan6ImagePlaceholder(string $line): bool
    {
        $line = trim($line);
        if ($line === '') {
            return false;
        }

        if (preg_match('/^\[(?:image|IMAGE)\]$/iu', $line) === 1) {
            return true;
        }

        return preg_match('/\[CHÈN\s+HÌNH\s+ẢNH[^\]]*\]/iu', $line) === 1
            || preg_match('/^\[CHÈN\s+HÌNH\s+ẢNH\]$/iu', $line) === 1;
    }

    /**
     * Tách nội dung giới thiệu Mã 2–4 theo mẫu Excel: tiêu đề (II./III./IV.) + đoạn mở đầu (không gồm bảng mẫu).
     *
     * @return array{tieu_de: ?string, noi_dung: string}
     */
    public static function parseGioiThieuForDisplay(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['tieu_de' => null, 'noi_dung' => ''];
        }

        $tieuDe = null;
        $body = [];
        $paragraphs = preg_split('/\n\s*\n/u', $text) ?: [];

        foreach ($paragraphs as $p) {
            $line = trim($p);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^PHẦN\s*6\s*:/ui', $line)) {
                continue;
            }
            if (self::isPhan6ImagePlaceholder($line)) {
                continue;
            }
            if (self::isGioiThieuTemplateRow($line)) {
                continue;
            }
            if ($tieuDe === null && preg_match('/^(II|III|IV)\.\s+/u', $line)) {
                $tieuDe = $line;

                continue;
            }
            $body[] = $line;
        }

        return [
            'tieu_de' => $tieuDe,
            'noi_dung' => trim(implode("\n\n", $body)),
        ];
    }

    public static function phan6ImageRelativePath(string $truLoai): ?string
    {
        return self::PHAN6_TRU_LOAI_IMAGES[$truLoai] ?? null;
    }

    /**
     * @return array<string, string> marker path → URL public
     */
    public static function imageMapForPhan6(): array
    {
        $map = [];
        foreach (self::PHAN6_TRU_LOAI_IMAGES as $relative) {
            $map[$relative] = self::publicUrlForMarkerPath($relative);
        }

        $dir = public_path('images/pdfs/phan-6');
        if (is_dir($dir)) {
            foreach (glob($dir . '/y-nghia-*.png') ?: [] as $file) {
                $rel = 'images/pdfs/phan-6/' . basename($file);
                $map[$rel] = self::publicUrlForMarkerPath($rel);
            }
        }

        return $map;
    }

    /** Dòng bảng mẫu coding trong Excel (không đưa vào giới thiệu). */
    public static function isGioiThieuTemplateRow(string $text): bool
    {
        $t = trim($text);
        if ($t === '') {
            return false;
        }

        if (preg_match('/^(Thiên Can|Địa Chi)(\s+(Năm|Tháng|Ngày|Giờ))+$/u', $t)) {
            return true;
        }

        if (preg_match('/^Nếu có\s+(Hợp|Khắc|Xung|Hình|Hại|Phá)/u', $t)) {
            return true;
        }

        return preg_match('/Thập Thần\s+ở\s+(Thiên Can|Địa Chi)\s+Trụ/u', $t) === 1;
    }

    /**
     * Gộp các dòng văn bản: bỏ [image] → path ảnh; bỏ dòng bảng mẫu Excel.
     *
     * @param  array<int, string>  $lines
     * @return array{noi_dung: string, image: ?string}
     */
    public static function parsePhan6TextLines(
        array $lines,
        ?string $imageRelativePath = null,
        ?string $docxPath = null
    ): array {
        $hasImagePlaceholder = false;
        $out = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            if (self::isPhan6ImagePlaceholder($line)) {
                if ($imageRelativePath !== null) {
                    $hasImagePlaceholder = true;
                }

                continue;
            }
            if (self::isGioiThieuTemplateRow($line)) {
                continue;
            }
            if (($marker = self::placeholderImageMarker($line)) !== null) {
                $out[] = $marker;

                continue;
            }
            $out[] = $line;
        }

        $storedImage = null;
        if ($hasImagePlaceholder && $imageRelativePath !== null) {
            self::ensurePhan6ImageFile($imageRelativePath, $docxPath);
            $storedImage = $imageRelativePath;
        }

        return [
            'noi_dung' => trim(implode("\n\n", $out)),
            'image' => $storedImage,
        ];
    }

    public static function placeholderImagePath(string $line): ?string
    {
        if (! preg_match('/^\[(example_image_(ngu_hanh|ban_menh))\]$/iu', trim($line), $m)) {
            return null;
        }

        $key = mb_strtolower($m[1], 'UTF-8');

        return self::PLACEHOLDER_IMAGES[$key] ?? null;
    }

    public static function placeholderImageMarker(string $line): ?string
    {
        $path = self::placeholderImagePath($line);

        return $path !== null ? self::imageMarker($path) : null;
    }

    public static function imageMarker(string $relativePath): string
    {
        return '[[image:'.$relativePath.']]';
    }

    /** Loại marker [[image:...]] khỏi nội dung (web/API Phần 6). */
    public static function stripImageMarkers(string $text): string
    {
        $stripped = preg_replace('/\[\[image:[^\]]+\]\]/u', '', $text);

        return trim($stripped ?? $text);
    }

    public static function resolveImagePath(string $markerPath): string
    {
        $relative = str_replace('\\', '/', trim($markerPath));
        $candidates = [base_path($relative), $relative];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return str_replace('\\', '/', realpath($path) ?: $path);
            }
        }

        return '';
    }

    /** URL public tương ứng (để hiển thị web). */
    public static function publicUrlForResourcePath(string $resourcePath): string
    {
        $map = [
            self::BO_CUC_IMAGE_NGU_HANH => '/images/pdfs/quyen-1/example-ngu-hanh.png',
            self::BO_CUC_IMAGE_BAN_MENH => '/images/pdfs/quyen-1/example-ban-menh.png',
        ];

        return $map[$resourcePath] ?? '/images/pdfs/quyen-1/'.basename($resourcePath);
    }

    /** URL public cho marker [[image:...]] (resources/ hoặc images/). */
    public static function publicUrlForMarkerPath(string $markerPath): string
    {
        $relative = str_replace('\\', '/', trim($markerPath));
        if (str_starts_with($relative, 'images/')) {
            return '/'.$relative;
        }

        if (str_starts_with($relative, Phan5AssetService::RESOURCE_PREFIX.'/')) {
            return Phan5AssetService::publicUrl($relative);
        }

        return self::publicUrlForResourcePath($relative);
    }

    /**
     * Đảm bảo file ảnh Phần 6 tồn tại: ưu tiên media trong DOCX, không có thì copy ảnh mẫu image.png.
     */
    public static function ensurePhan6ImageFile(string $relativePath, ?string $docxPath = null): bool
    {
        $relative = str_replace('\\', '/', trim($relativePath));
        $dest = public_path($relative);
        $destDir = dirname($dest);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (is_file($dest) && filesize($dest) > 0) {
            return true;
        }

        if ($docxPath !== null && self::extractFirstImageFromDocx($docxPath, $dest)) {
            return true;
        }

        $sample = base_path(self::PHAN6_SAMPLE_SOURCE);
        if (! is_file($sample)) {
            return false;
        }

        return copy($sample, $dest);
    }

    public static function extractFirstImageFromDocx(string $docxPath, string $destPath): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return false;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || ! preg_match('#^word/media/.+\.(png|jpe?g|gif|webp)$#i', $name)) {
                continue;
            }
            $contents = $zip->getFromIndex($i);
            if ($contents === false) {
                continue;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            $dir = dirname($destPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $target = $dir.DIRECTORY_SEPARATOR.pathinfo($destPath, PATHINFO_FILENAME).'.'.$ext;
            file_put_contents($target, $contents);
            $zip->close();

            return is_file($target);
        }

        $zip->close();

        return false;
    }
}
