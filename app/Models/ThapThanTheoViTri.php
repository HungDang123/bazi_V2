<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThapThanTheoViTri extends Model
{
    use HasFactory;

    protected $table = 'thap_than_theo_vi_tri';

    protected $fillable = [
        'thap_than',
        'vi_tri',
        'loai_can',
        'khia_canh',
        'huong',
        'content',
        'sort_order',
    ];

    /**
     * Lấy theo Thập Thần, sắp xếp sort_order.
     */
    public static function getByThapThan(string $thapThan)
    {
        return static::where('thap_than', $thapThan)->orderBy('sort_order')->get();
    }
}
