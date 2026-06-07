<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan7TamThe extends Model
{
    use HasFactory;

    protected $table = 'phan7_tam_the';

    protected $fillable = [
        'sheet_index',
        'noi_dung',
        'image',
        'thu_tu',
    ];

    protected $casts = [
        'sheet_index' => 'integer',
        'thu_tu' => 'integer',
    ];

    public static function getAllOrdered(int $sheetIndex = 0): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('sheet_index', $sheetIndex)
            ->orderBy('thu_tu')
            ->orderBy('id')
            ->get();
    }
}
