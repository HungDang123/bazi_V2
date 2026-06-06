<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhatChuTruNgay extends Model
{
    use HasFactory;

    protected $table = 'nhat_chu_tru_ngay';

    protected $fillable = [
        'thien_can',
        'dia_chi',
        'title',
        'chapter',
        'sub_title',
        'content',
        'sort_order',
    ];

    /**
     * Chuẩn hóa địa chi (Tí -> Tý).
     */
    public static function normalizeDiaChi(string $diaChi): string
    {
        $d = trim($diaChi);
        if (mb_strtoupper($d) === 'TÍ' || $d === 'Tí') {
            return 'Tý';
        }

        return mb_convert_case($d, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Chuẩn hóa thiên can (KỶ -> Kỷ).
     */
    public static function normalizeThienCan(string $thienCan): string
    {
        $t = trim($thienCan);
        if ($t === '') {
            return '';
        }

        return mb_convert_case($t, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Lấy theo Thiên Can và Địa Chi (Trụ ngày).
     */
    public static function findByThienCanDiaChi(string $thienCan, string $diaChi)
    {
        $thienCan = self::normalizeThienCan($thienCan);
        $diaChi = self::normalizeDiaChi($diaChi);

        return static::where('thien_can', $thienCan)
            ->where('dia_chi', $diaChi)
            ->orderBy('sort_order')
            ->get();
    }
}
