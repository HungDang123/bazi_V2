<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use setasign\Fpdi\Fpdi;

/**
 * Các method Output(), Image(), AddPage() đến từ FPDF (parent của Fpdi).
 * Intelephense không index được classmap của fpdf/fpdf nên báo lỗi giả —
 * code chạy đúng ở runtime.
 *
 * @phpstan-ignore-next-line
 */
class PdfMergeService
{
    private const QPDF_BATCH_SIZE = 25;

    private static ?string $lastMergeDriver = null;

    /** ~300 DPI cho cạnh dài A4 (297mm) — tránh ảnh mờ khi fill full page. */
    private const MAX_RASTER_PX = 3508;

    private const JPEG_QUALITY = 92;

    /** PNG/JPEG đủ nét thì giữ file gốc, không re-encode JPEG. */
    private const SKIP_REENCODE_PNG_MAX_BYTES = 2_000_000;

    private const SKIP_REENCODE_JPEG_MAX_BYTES = 800_000;

    public static function lastMergeDriver(): ?string
    {
        return self::$lastMergeDriver;
    }

    /**
     * Merge nhiều PDF lại thành 1 file.
     * Driver: config pdf.merge_driver = auto|qpdf|pdftk|fpdi (fallback FPDI khi binary thất bại).
     */
    public static function mergeMultiple(array $pdfPaths, string $outputPath): bool
    {
        self::$lastMergeDriver = null;
        $driver                = strtolower((string) config('pdf.merge_driver', 'auto'));

        $tryOrder = match ($driver) {
            'qpdf'  => ['qpdf', 'fpdi'],
            'pdftk' => ['pdftk', 'fpdi'],
            'fpdi'  => ['fpdi'],
            default => ['qpdf', 'pdftk', 'fpdi'],
        };

        foreach ($tryOrder as $name) {
            $ok = match ($name) {
                'qpdf'  => self::mergeWithQpdf($pdfPaths, $outputPath),
                'pdftk' => self::mergeWithPdftk($pdfPaths, $outputPath),
                'fpdi'  => self::mergeWithFpdi($pdfPaths, $outputPath),
                default => false,
            };

            if ($ok) {
                self::$lastMergeDriver = $name;
                Log::debug('PdfMergeService: merge driver', ['driver' => $name, 'files' => count($pdfPaths)]);

                return true;
            }
        }

        return false;
    }

    public static function resolveBinary(string $name, ?string $configuredPath = null): ?string
    {
        if ($configuredPath !== null && $configuredPath !== '') {
            return is_file($configuredPath) ? $configuredPath : null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $result = Process::run(['where', $name]);
        } else {
            $result = Process::run(['which', $name]);
        }

        if (! $result->successful()) {
            return null;
        }

        $line = trim(explode("\n", $result->output())[0] ?? '');

        return $line !== '' && is_file($line) ? $line : null;
    }

    public static function binaryVersion(string $binary): ?string
    {
        $base = basename($binary);
        $flag = str_contains($base, 'pdftk') ? null : '--version';
        $args = $flag !== null ? [$binary, $flag] : [$binary];
        $result = Process::run($args);

        if (! $result->successful() && $result->output() === '' && $result->errorOutput() === '') {
            return null;
        }

        $text = trim($result->output() !== '' ? $result->output() : $result->errorOutput());

        return $text !== '' ? strtok($text, "\r\n") : null;
    }

    /** Driver merge sẽ dùng trên server hiện tại (auto-detect). */
    public static function preferredMergeDriver(): string
    {
        $driver = strtolower((string) config('pdf.merge_driver', 'auto'));

        if ($driver === 'fpdi') {
            return 'fpdi';
        }

        if ($driver === 'qpdf' || ($driver === 'auto' && self::resolveBinary('qpdf', config('pdf.qpdf_binary')) !== null)) {
            return 'qpdf';
        }

        if ($driver === 'pdftk' || ($driver === 'auto' && self::resolveBinary('pdftk', config('pdf.pdftk_binary')) !== null)) {
            return 'pdftk';
        }

        return 'fpdi';
    }

    private static function existingPdfPaths(array $pdfPaths): array
    {
        return array_values(array_filter($pdfPaths, static function ($path) {
            if (! is_string($path) || $path === '') {
                return false;
            }
            if (! file_exists($path)) {
                Log::warning('PdfMergeService: file không tồn tại, bỏ qua', ['path' => $path]);

                return false;
            }

            return true;
        }));
    }

    private static function ensureOutputDir(string $outputPath): void
    {
        $outputDir = dirname($outputPath);
        if (! file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
    }

    private static function mergeWithQpdf(array $pdfPaths, string $outputPath): bool
    {
        $qpdf = self::resolveBinary('qpdf', config('pdf.qpdf_binary'));
        if ($qpdf === null) {
            return false;
        }

        $existing = self::existingPdfPaths($pdfPaths);
        if ($existing === []) {
            return false;
        }

        self::ensureOutputDir($outputPath);

        if (count($existing) <= self::QPDF_BATCH_SIZE) {
            return self::runQpdfMerge($qpdf, $existing, $outputPath);
        }

        $tempDir = storage_path('app/temp/qpdf-merge');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $partials = [];
        foreach (array_chunk($existing, self::QPDF_BATCH_SIZE) as $chunk) {
            $partPath = $tempDir.'/batch-'.uniqid('', true).'.pdf';
            if (! self::runQpdfMerge($qpdf, $chunk, $partPath)) {
                foreach ($partials as $partial) {
                    @unlink($partial);
                }

                return false;
            }
            $partials[] = $partPath;
        }

        $ok = self::runQpdfMerge($qpdf, $partials, $outputPath);
        foreach ($partials as $partial) {
            @unlink($partial);
        }

        return $ok;
    }

    /**
     * @param  list<string>  $files
     */
    private static function runQpdfMerge(string $qpdf, array $files, string $outputPath): bool
    {
        $args   = array_merge([$qpdf, '--empty', '--pages'], $files, ['--', $outputPath]);
        $result = Process::run($args);

        if (! $result->successful() || ! file_exists($outputPath)) {
            Log::warning('PdfMergeService: qpdf merge thất bại', [
                'stderr' => trim($result->errorOutput()),
                'files'  => count($files),
            ]);

            return false;
        }

        return true;
    }

    private static function mergeWithPdftk(array $pdfPaths, string $outputPath): bool
    {
        $pdftk = self::resolveBinary('pdftk', config('pdf.pdftk_binary'));
        if ($pdftk === null) {
            return false;
        }

        $existing = self::existingPdfPaths($pdfPaths);
        if ($existing === []) {
            return false;
        }

        self::ensureOutputDir($outputPath);

        $args   = array_merge([$pdftk], $existing, ['cat', 'output', $outputPath]);
        $result = Process::run($args);

        if (! $result->successful() || ! file_exists($outputPath)) {
            Log::warning('PdfMergeService: pdftk merge thất bại', [
                'stderr' => trim($result->errorOutput()),
                'files'  => count($existing),
            ]);

            return false;
        }

        return true;
    }

    private static function mergeWithFpdi(array $pdfPaths, string $outputPath): bool
    {
        try {
            /** @var \setasign\Fpdi\Fpdi $pdf */
            $pdf = new Fpdi();

            foreach (self::existingPdfPaths($pdfPaths) as $pdfPath) {
                $pageCount = $pdf->setSourceFile($pdfPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tplId = $pdf->importPage($pageNo);
                    $size  = $pdf->getTemplateSize($tplId);  // @phpstan-ignore-line

                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);  // @phpstan-ignore-line
                    $pdf->useTemplate($tplId);
                }
            }

            self::ensureOutputDir($outputPath);
            $pdf->Output('F', $outputPath);  // @phpstan-ignore-line

            return file_exists($outputPath);
        } catch (\Exception $e) {
            Log::error('PdfMergeService::mergeWithFpdi thất bại', [
                'error'     => $e->getMessage(),
                'pdf_paths' => $pdfPaths,
            ]);

            return false;
        }
    }

    /**
     * Convert ảnh PNG/JPG sang 1 trang PDF A4, căn giữa.
     */
    public static function convertImageToPdf(string $imagePath, string $outputPath): bool
    {
        try {
            $imagePath = self::resolveRasterForPdf($imagePath);
            if (!file_exists($imagePath)) {
                Log::warning('PdfMergeService: ảnh không tồn tại', ['path' => $imagePath]);
                return false;
            }

            $imageInfo = @getimagesize($imagePath);
            if (!$imageInfo) {
                Log::error('PdfMergeService: không đọc được kích thước ảnh', ['path' => $imagePath]);
                return false;
            }

            $imgW = $imageInfo[0];
            $imgH = $imageInfo[1];

            $a4W = 210;
            $a4H = 297;

            $scaleW = $a4W / ($imgW / 96 * 25.4);
            $scaleH = $a4H / ($imgH / 96 * 25.4);
            $scale  = min($scaleW, $scaleH);

            $widthMm  = ($imgW / 96 * 25.4) * $scale;
            $heightMm = ($imgH / 96 * 25.4) * $scale;

            $orientation = ($widthMm > $heightMm) ? 'L' : 'P';

            /** @var \setasign\Fpdi\Fpdi $pdf */
            $pdf = new Fpdi();
            $pdf->AddPage($orientation, [$a4W, $a4H]);  // @phpstan-ignore-line

            $x = ($a4W - $widthMm) / 2;
            $y = ($a4H - $heightMm) / 2;
            $imageType = self::fpdfImageType($imagePath);
            if ($imageType === null) {
                Log::error('PdfMergeService: định dạng ảnh không hỗ trợ', ['path' => $imagePath]);

                return false;
            }
            $pdf->Image($imagePath, $x, $y, $widthMm, $heightMm, $imageType);  // @phpstan-ignore-line

            $outputDir = dirname($outputPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $pdf->Output('F', $outputPath);  // @phpstan-ignore-line
            return true;
        } catch (\Exception $e) {
            Log::error('PdfMergeService::convertImageToPdf thất bại', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);
            return false;
        }
    }

    /**
     * Convert ảnh sang PDF, fill toàn bộ trang A4 (không giữ margin).
     */
    public static function convertImageToPdfFullPage(string $imagePath, string $outputPath): bool
    {
        try {
            $imagePath = self::resolveRasterForPdf($imagePath);
            if (!file_exists($imagePath)) {
                Log::warning('PdfMergeService: ảnh không tồn tại', ['path' => $imagePath]);
                return false;
            }

            $imageType = self::fpdfImageType($imagePath);
            if ($imageType === null) {
                Log::error('PdfMergeService: định dạng ảnh không hỗ trợ', ['path' => $imagePath]);

                return false;
            }

            /** @var \setasign\Fpdi\Fpdi $pdf */
            $pdf = new Fpdi();
            $pdf->AddPage('P', [210, 297]);  // @phpstan-ignore-line
            $pdf->Image($imagePath, 0, 0, 210, 297, $imageType);  // @phpstan-ignore-line

            $outputDir = dirname($outputPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $pdf->Output('F', $outputPath);  // @phpstan-ignore-line
            return true;
        } catch (\Exception $e) {
            Log::error('PdfMergeService::convertImageToPdfFullPage thất bại', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);
            return false;
        }
    }

    /**
     * FPDF chọn decoder theo đuôi file — cần truyền type đúng khi file .png thực chất là JPEG.
     */
    private static function fpdfImageType(string $imagePath): ?string
    {
        $info = @getimagesize($imagePath);
        if ($info === false) {
            return null;
        }

        return match ($info['mime'] ?? '') {
            'image/jpeg' => 'JPEG',
            'image/png' => 'PNG',
            'image/gif' => 'GIF',
            default => null,
        };
    }

    /**
     * PNG lớn nhúng vào PDF rất nặng — chuyển JPEG cache trước khi render.
     */
    public static function optimizedRasterPath(string $imagePath): string
    {
        return self::resolveRasterForPdf($imagePath);
    }

    private static function resolveRasterForPdf(string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            return $imagePath;
        }

        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return $imagePath;
        }

        $info = @getimagesize($imagePath);
        if ($info === false) {
            return $imagePath;
        }

        $fileSize = filesize($imagePath) ?: 0;
        $mime     = (string) ($info['mime'] ?? '');
        $w        = (int) ($info[0] ?? 0);
        $h        = (int) ($info[1] ?? 0);
        $fitsInMax = $w > 0 && $h > 0 && $w <= self::MAX_RASTER_PX && $h <= self::MAX_RASTER_PX;

        // Ảnh đủ nét + nhỏ → nhúng trực tiếp (PNG/JPEG gốc), không nén mất chất lượng
        if ($fitsInMax) {
            if ($mime === 'image/png' && $fileSize <= self::SKIP_REENCODE_PNG_MAX_BYTES) {
                return $imagePath;
            }
            if ($mime === 'image/jpeg' && $fileSize <= self::SKIP_REENCODE_JPEG_MAX_BYTES) {
                return $imagePath;
            }
        }

        $cacheDir = storage_path('app/pdf-cache/raster');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $mtime = filemtime($imagePath) ?: 0;
        $key   = hash('xxh128', $imagePath.'|'.$mtime.'|'.$fileSize.'|jpeg-q'.self::JPEG_QUALITY.'-max'.self::MAX_RASTER_PX.'-white-v5');
        $cached = $cacheDir.DIRECTORY_SEPARATOR.$key.'.jpg';

        if (file_exists($cached)) {
            return $cached;
        }

        $blob = @file_get_contents($imagePath);
        if ($blob === false) {
            return $imagePath;
        }

        $src = @imagecreatefromstring($blob);
        if ($src === false) {
            return $imagePath;
        }

        $w = imagesx($src);
        $h = imagesy($src);

        if ($w > self::MAX_RASTER_PX || $h > self::MAX_RASTER_PX) {
            $scale = min(self::MAX_RASTER_PX / $w, self::MAX_RASTER_PX / $h);
            $nw    = max(1, (int) round($w * $scale));
            $nh    = max(1, (int) round($h * $scale));
            $dst   = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        if ($mime === 'image/png') {
            $src = self::flattenPngOnWhite($src);
        }

        imagejpeg($src, $cached, self::JPEG_QUALITY);
        imagedestroy($src);

        return file_exists($cached) ? $cached : $imagePath;
    }

    /**
     * JPEG không hỗ trợ alpha — PNG trong suốt (khung từ khóa, tiêu đề HÀNH…) phải phẳng nền trắng.
     */
    private static function flattenPngOnWhite(\GdImage $src): \GdImage
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $flat = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefill($flat, 0, 0, $white);
        imagealphablending($src, true);
        imagesavealpha($src, true);
        imagecopy($flat, $src, 0, 0, 0, 0, $w, $h);
        imagedestroy($src);

        return $flat;
    }
}
