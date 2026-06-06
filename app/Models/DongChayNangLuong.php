<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DongChayNangLuong extends Model
{
    use HasFactory;

    protected $table = 'dong_chay_nang_luong';

    protected $fillable = [
        'thap_than',
        'vi_tri_tuong_tac',
        'loai_tuong_tac',
        'tru_tuong_tac',
        'huong',
        'noi_dung',
        'sort_order',
    ];

    /**
     * Tìm theo Thập Thần và các tiêu chí khác.
     */
    public static function findByThapThan(
        string $thapThan,
        ?string $viTri = null,
        ?string $loai = null,
        ?string $tru = null,
        ?string $huong = null
    ) {
        $q = static::where('thap_than', $thapThan);
        if ($viTri !== null) {
            $q->where('vi_tri_tuong_tac', $viTri);
        }
        if ($loai !== null) {
            $q->where('loai_tuong_tac', $loai);
        }
        if ($tru !== null) {
            $q->where('tru_tuong_tac', $tru);
        }
        if ($huong !== null) {
            $q->where('huong', $huong);
        }
        return $q->orderBy('sort_order')->get();
    }

    /**
     * Lấy tất cả Thập Thần (unique).
     */
    public static function getThapThanList(): array
    {
        return static::distinct()
            ->pluck('thap_than')
            ->sort()
            ->values()
            ->toArray();
    }
}
