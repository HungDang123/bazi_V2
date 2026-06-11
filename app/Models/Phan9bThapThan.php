<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan9bThapThan extends Model
{
    protected $table = 'phan9b_thap_than';

    protected $fillable = [
        'loai',
        'bo',
        'thap_than',
        'tieu_de',
        'noi_dung',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /** @var array<string, string> */
    public const BO_LABELS = [
        'huynh_de' => 'Bộ Huynh Đệ',
        'tu_ton' => 'Bộ Tử Tôn',
        'the_tai' => 'Bộ Thê Tài',
        'quan_quy' => 'Bộ Quan Quỷ',
        'phu_mau' => 'Bộ Phụ Mẫu',
    ];

    /** @var array<string, string> uppercase label => slug */
    public const LABEL_TO_SLUG = [
        'TỶ KIÊN' => 'ty_kien',
        'KIẾP TÀI' => 'kiep_tai',
        'THỰC THẦN' => 'thuc_than',
        'THƯƠNG QUAN' => 'thuong_quan',
        'THIÊN TÀI' => 'thien_tai',
        'CHÍNH TÀI' => 'chinh_tai',
        'THẤT SÁT' => 'that_sat',
        'CHÍNH QUAN' => 'chinh_quan',
        'THIÊN ẤN' => 'thien_an',
        'CHÍNH ẤN' => 'chinh_an',
    ];

    public static function labelToSlug(string $label): ?string
    {
        $key = mb_strtoupper(trim($label), 'UTF-8');

        return self::LABEL_TO_SLUG[$key] ?? null;
    }

    public static function getThapThanRows(string $slug): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->where('loai', 'thap_than')
            ->where('thap_than', $slug)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
