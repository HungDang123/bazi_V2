<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan9bGiaiPhapCanBang extends Model
{
    protected $table = 'phan9b_giai_phap_can_bang';

    protected $fillable = [
        'loai',
        'than_trang_thai',
        'muc',
        'tieu_de',
        'noi_dung',
        'bo_hy_than',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public const THAN_VUONG = 'than_vuong';

    public const THAN_NHUOC = 'than_nhuoc';

    public const MUC_TRANG_THAI = 'trang_thai_nang_luong_goc';

    public const MUC_CHIEN_LUOC = 'chien_luoc_hanh_dong';

    /** @var array<string, string> */
    public const THAN_LABELS = [
        self::THAN_VUONG => 'Thân Vượng',
        self::THAN_NHUOC => 'Thân Nhược',
    ];

    /** @var array<string, string> */
    public const BO_HY_THAN_LABELS = [
        'tu_ton' => 'Tử Tôn',
        'the_tai' => 'Thê Tài',
        'quan_quy' => 'Quan Quỷ',
        'phu_mau' => 'Phụ Mẫu',
        'huynh_de' => 'Huynh Đệ',
    ];

    public static function getByThanTrangThai(string $thanTrangThai): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->where('loai', 'noi_dung')
            ->where('than_trang_thai', $thanTrangThai)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
