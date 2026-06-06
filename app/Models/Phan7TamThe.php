<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan7TamThe extends Model
{
    use HasFactory;

    protected $table = 'phan7_tam_the';

    protected $fillable = [
        'loai',
        'thap_than',
        'ten_truong_hop',
        'noi_dung',
        'thu_tu',
    ];

    protected $casts = [
        'thu_tu' => 'integer',
    ];

    /**
     * Lấy toàn bộ nội dung sheet 1 (I. XÁC ĐỊNH TÂM THẾ) theo thứ tự.
     */
    public static function getAllOrdered(): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderBy('thu_tu')->orderBy('id')->get();
    }
}
