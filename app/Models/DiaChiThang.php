<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaChiThang extends Model
{
    use HasFactory;

    protected $table = 'dia_chi_thang';

    protected $fillable = [
        'dia_chi',
        'ngu_hanh',
        'am_duong',
        'menh_full',
        'mua_sinh',
        'ngu_hanh_mua_sinh',
        'sort_order',
    ];

    /**
     * Tìm theo địa chi
     */
    public static function findByDiaChi(string $diaChi): ?self
    {
        return static::where('dia_chi', $diaChi)->first();
    }

    /**
     * Lấy tất cả theo thứ tự
     */
    public static function getAllOrdered()
    {
        return static::orderBy('sort_order')->get();
    }
}
