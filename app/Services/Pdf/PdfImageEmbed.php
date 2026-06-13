<?php

namespace App\Services\Pdf;

use App\Services\NguHanhTitleRenderer;

/**
 * Nhúng ảnh raster cho DomPDF — dùng thống nhất trong blade.
 *
 * Đường dẫn truyền qua $data view đã được PdfRenderService::normalizeViewData() xử lý;
 * chỉ gọi helper này khi path được tạo trong @php của blade (pill, keyword, v.v.).
 */
class PdfImageEmbed
{
    public static function src(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:image/')) {
            return $path;
        }

        $embedded = NguHanhTitleRenderer::embedPath($path);

        return $embedded !== '' ? $embedded : $path;
    }
}
