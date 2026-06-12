<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HyKyThan extends Model
{
    use HasFactory;

    protected $table = 'hy_ky_than';

    protected $fillable = [
        'thien_can_ngay',
        'dia_chi_thang',
        'than_nhuoc_than_vuong',
        'hy_than_ngu_hanh',
        'hy_than_can',
        'ky_than_ngu_hanh',
        'ky_than_can',
    ];

    protected $casts = [
        'than_nhuoc_than_vuong' => 'string',
    ];

    /**
     * Chuẩn hóa Địa Chi trước khi tra cứu (Tí/TÍ → TÝ — khớp bảng import).
     */
    public static function normalizeDiaChiForLookup(string $diaChi): string
    {
        $upper = mb_strtoupper(trim($diaChi), 'UTF-8');

        if ($upper === 'TÍ' || $upper === 'TI') {
            return 'TÝ';
        }

        return $upper;
    }

    /**
     * Tìm kiếm theo Thiên Bàn Ngày và Địa Chi Tháng
     * Database lưu chữ hoa, nên cần convert input sang chữ hoa để match
     * Dùng COLLATE utf8_bin để so sánh chính xác theo dấu tiếng Việt
     */
    public static function findByThienCanDiaChi($thienCanNgay, $diaChiThang)
    {
        $thienCanNgay = mb_strtoupper(trim($thienCanNgay), 'UTF-8');
        $diaChiThang = self::normalizeDiaChiForLookup((string) $diaChiThang);

        return static::select(['id', 'than_nhuoc_than_vuong', 'hy_than_ngu_hanh', 'hy_than_can', 'ky_than_ngu_hanh', 'ky_than_can'])
                    ->whereRaw('thien_can_ngay COLLATE utf8mb4_bin = ?', [$thienCanNgay])
                    ->whereRaw('dia_chi_thang COLLATE utf8mb4_bin = ?', [$diaChiThang])
                    ->first();
    }
}