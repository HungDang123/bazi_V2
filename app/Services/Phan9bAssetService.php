<?php

namespace App\Services;

use App\Models\Phan9bNoiLuc;

class Phan9bAssetService
{
    public static function resourceDir(): string
    {
        return resource_path('views/pdfs/phan-9');
    }

    public static function contentBgPath(): string
    {
        return Phan9PdfService::contentBgPath();
    }

    public static function hanhBgPath(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if (! in_array($slug, Phan9bNoiLuc::HANH_ORDER, true)) {
            return self::contentBgPath();
        }

        return self::resourceDir().'/'.$slug.'.png';
    }
}
