<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TongQuanKhiaCanh extends Model
{
    use HasFactory;

    protected $table = 'tong_quan_khia_canh';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'sort_order',
    ];

    /**
     * Lấy tất cả mục theo sort_order.
     */
    public static function getAllOrdered()
    {
        return static::orderBy('sort_order')->get();
    }
}
