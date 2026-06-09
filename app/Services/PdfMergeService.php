<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
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
    /** ~300 DPI cho cạnh dài A4 (297mm) — tránh ảnh mờ khi fill full page. */
    private const MAX_RASTER_PX = 3508;

    private const JPEG_QUALITY = 92;

    /** PNG/JPEG đủ nét thì giữ file gốc, không re-encode JPEG. */
    private const SKIP_REENCODE_PNG_MAX_BYTES = 2_000_000;

    private const SKIP_REENCODE_JPEG_MAX_BYTES = 800_000;

    /**
     * Merge nhiều PDF lại thành 1 file.
     */
    public static function mergeMultiple(array $pdfPaths, string $outputPath): bool
    {
        try {
            /** @var \setasign\Fpdi\Fpdi $pdf */
            $pdf = new Fpdi();

            foreach ($pdfPaths as $pdfPath) {
                if (!file_exists($pdfPath)) {
                    Log::warning('PdfMergeService: file không tồn tại, bỏ qua', ['path' => $pdfPath]);
                    continue;
                }

                $pageCount = $pdf->setSourceFile($pdfPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tplId = $pdf->importPage($pageNo);
                    $size  = $pdf->getTemplateSize($tplId);  // @phpstan-ignore-line

                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);  // @phpstan-ignore-line
                    $pdf->useTemplate($tplId);
                }
            }

            $outputDir = dirname($outputPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $pdf->Output('F', $outputPath);  // @phpstan-ignore-line
            return true;
        } catch (\Exception $e) {
            Log::error('PdfMergeService::mergeMultiple thất bại', [
                'error' => $e->getMessage(),
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
