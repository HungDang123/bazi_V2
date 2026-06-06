<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViNhanNhatChu extends Model
{
    use HasFactory;

    protected $table = 'vi_nhan_nhat_chu';

    protected $fillable = [
        'thien_can',
        'ten_nguoi',
        'sort_order',
    ];

    /**
     * Lấy danh sách vĩ nhân theo Thiên Can.
     */
    public static function findByThienCan(string $thienCan)
    {
        $thienCan = NhatChuTruNgay::normalizeThienCan($thienCan);

        return static::where('thien_can', $thienCan)
            ->orderBy('sort_order')
            ->get();
    }
}
