<?php

namespace App\Services;

class Phan8AssetService
{
    public static function resourceDir(): string
    {
        return resource_path('views/pdfs/phan-8');
    }

    public static function coverImagePath(): string
    {
        return self::resourceDir().'/bia-phan-8.png';
    }

    public static function contentBgPath(): string
    {
        return self::resourceDir().'/page-content-bg.png';
    }

    public static function codingBgPath(): string
    {
        return self::resourceDir().'/dai-van-coding-bg.png';
    }

    /** Nền nội dung II. Niên Vận (LBTV-577). */
    public static function nienVanBgPath(): string
    {
        return self::resourceDir().'/nien-van-bg.png';
    }

    /** Nền trang coding Thập Thần trong Niên Vận (LBTV-415). */
    public static function nienVanCodingBgPath(): string
    {
        return self::resourceDir().'/nien-van-coding-bg.png';
    }
}
