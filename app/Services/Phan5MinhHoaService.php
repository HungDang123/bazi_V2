<?php

namespace App\Services;

class Phan5MinhHoaService
{
    public const BASE = 'resources/views/pdfs/phan-5/minh-hoa';

    /** @var array<string, array<string, string>> khia slug → item label → file (relative to minh-hoa/) */
    public const MAP = [
        'su_nghiep' => [
            'Thiên Can Trụ Tháng' => 'su-nghiep/thien-can-tru-thang.png',
            'Thiên Can Trụ Năm' => 'su-nghiep/thien-can-tru-nam.png',
        ],
        'tai_chinh' => [
            'Tàng Can Trụ Tháng' => 'tai-chinh/tang-can-tru-thang.png',
            'Tàng Can Trụ Giờ' => 'tai-chinh/tang-can-tru-gio.png',
        ],
        'tinh_duyen' => [
            'Tàng Can Trụ Ngày' => 'tinh-duyen/tang-can-tru-ngay.png',
        ],
        'phat_trien_ban_than' => [
            'Thiên Can Trụ Giờ' => 'phat-trien/thien-can-tru-gio.png',
            'Tàng Can Trụ Giờ' => 'phat-trien/tang-can-tru-gio.png',
        ],
        'ket_noi_xa_hoi' => [
            'Thiên Can Trụ Năm' => 'ket-noi/thien-can-tru-nam.png',
            'Tàng Can Trụ Năm' => 'ket-noi/tang-can-tru-nam.png',
        ],
    ];

    public static function relativePath(string $khiaSlug, string $itemLabel): ?string
    {
        $file = self::MAP[$khiaSlug][$itemLabel] ?? null;

        return $file !== null ? self::BASE.'/'.$file : null;
    }

    public static function resolvePath(string $khiaSlug, string $itemLabel): ?string
    {
        $relative = self::relativePath($khiaSlug, $itemLabel);
        if ($relative === null) {
            return null;
        }

        $resolved = Phan5AssetService::resolvePath($relative);

        return is_file($resolved) ? $resolved : null;
    }

    public static function publicUrl(string $khiaSlug, string $itemLabel): ?string
    {
        $relative = self::relativePath($khiaSlug, $itemLabel);
        if ($relative === null) {
            return null;
        }

        if (! is_file(Phan5AssetService::resolvePath($relative))) {
            return null;
        }

        return Phan5AssetService::publicUrl($relative);
    }
}
