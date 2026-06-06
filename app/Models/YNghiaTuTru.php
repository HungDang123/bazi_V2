<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YNghiaTuTru extends Model
{
    use HasFactory;

    protected $table = 'y_nghia_tu_tru';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'image',
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
