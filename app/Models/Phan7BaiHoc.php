<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan7BaiHoc extends Model
{
    use HasFactory;

    protected $table = 'phan7_bai_hoc';

    protected $fillable = [
        'thap_than',
        'ten_truong_hop',
        'tieu_de',
        'noi_dung',
        'thu_tu',
    ];

    protected $casts = [
        'thu_tu' => 'integer',
    ];

    /** Tên các Thập Thần theo thứ tự sheet */
    public const THAP_THAN_ORDER = [
        'HUYNH ĐỆ',
        'TỬ TÔN',
        'THÊ TÀI',
        'QUAN QUỶ',
        'PHỤ MẪU',
    ];

    public static function getByThapThan(string $thapThan): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('thap_than', $thapThan)
            ->orderBy('thu_tu')
            ->orderBy('id')
            ->get();
    }

    public static function getAllGroupedByThapThan(): array
    {
        $all = static::orderBy('thap_than')->orderBy('thu_tu')->orderBy('id')->get();

        $grouped = [];
        foreach ($all as $row) {
            $grouped[$row->thap_than][] = $row->toArray();
        }
        return $grouped;
    }
}
