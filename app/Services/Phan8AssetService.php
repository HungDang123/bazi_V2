<?php

namespace App\Services;

class Phan8AssetService
{
    public static function resourceDir(): string
    {
        return resource_path('views/pdfs/phan-8');
    }

    /** Bìa Phần 8A — «DỰ BÁO ĐẠI VẬN» (chỉ Quyển 1). */
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

    /** Nền nội dung Niên Vận + Phần 6 (Cuốn 1) — nien-van-bg.png. */
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
