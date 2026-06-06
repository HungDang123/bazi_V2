<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLuongNhatChu extends Model
{
    use HasFactory;

    protected $table = 'chat_luong_nhat_chu';

    protected $fillable = [
        'thien_can',
        'mua_sinh',
        'trang_thai',
        'title',
        'content',
        'sort_order',
    ];

    /**
     * Lấy theo Thiên Can và Mùa sinh
     */
    public static function findByThienCanMuaSinh(string $thienCan, string $muaSinh)
    {
        return static::where('thien_can', $thienCan)
            ->where('mua_sinh', $muaSinh)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Lấy tất cả theo Thiên Can
     */
    public static function getByThienCan(string $thienCan)
    {
        return static::where('thien_can', $thienCan)
            ->orderBy('mua_sinh')
            ->orderBy('sort_order')
            ->get();
    }
}
