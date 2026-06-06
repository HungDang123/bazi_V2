<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan7BaiHoc extends Model
{
    use HasFactory;

    protected $table = 'phan7_bai_hoc';

    protected $fillable = [
        'phan',
        'loai',
        'thap_than',
        'gioi_tinh',
        'ten_truong_hop',
        'noi_dung',
        'thu_tu',
    ];

    protected $casts = [
        'thu_tu' => 'integer',
    ];

    /** Tên các phần (sheets) theo thứ tự */
    public const PHAN_ORDER = [
        'II. XÁC ĐỊNH SỰ NGHIỆP',
        'III. TÀI CHÍNH',
        'IV. TÌNH DUYÊN',
        'V. SỨC KHOẺ',
        'VI. PHÁT TRIỂN BẢN THÂN',
        'VII. KẾT NỐI XÃ HỘI',
    ];

    /**
     * Lấy toàn bộ theo phần, nhóm theo loại và thập thần.
     */
    public static function getByPhan(string $phan): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('phan', $phan)->orderBy('thu_tu')->orderBy('id')->get();
    }

    /**
     * Lấy tất cả theo thứ tự phần.
     */
    public static function getAllGroupedByPhan(): array
    {
        $all = static::orderBy('phan')->orderBy('thu_tu')->orderBy('id')->get();

        $grouped = [];
        foreach ($all as $row) {
            $grouped[$row->phan][] = $row->toArray();
        }
        return $grouped;
    }
}
