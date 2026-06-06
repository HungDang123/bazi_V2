<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan6LaSoBatTu extends Model
{
    protected $table = 'phan6_la_so_bat_tu';

    protected $fillable = [
        'loai',
        'tru',
        'noi_dung',
        'sort_row',
        'sort_col',
    ];

    /** @var array<int, array{key: string, label: string, col: string}> */
    public const TRU_COLUMNS = [
        ['key' => 'gio', 'label' => 'Trụ Giờ', 'col' => 'B', 'sort' => 0],
        ['key' => 'ngay', 'label' => 'Trụ Ngày', 'col' => 'C', 'sort' => 1],
        ['key' => 'thang', 'label' => 'Trụ Tháng', 'col' => 'D', 'sort' => 2],
        ['key' => 'nam', 'label' => 'Trụ Năm', 'col' => 'E', 'sort' => 3],
    ];

    /** @var array<string, array{key: string, label: string, sort: int}> */
    public const LOAI_ROWS = [
        'thien_can' => ['key' => 'thien_can', 'label' => 'Thiên Can', 'sort' => 0],
        'dia_chi' => ['key' => 'dia_chi', 'label' => 'Địa Chi', 'sort' => 1],
    ];

    public static function getForApi(): ?array
    {
        $all = static::orderBy('sort_row')->orderBy('sort_col')->get();
        if ($all->isEmpty()) {
            return null;
        }

        $byLoaiTru = [];
        foreach ($all as $row) {
            $byLoaiTru[$row->loai][$row->tru] = (string) $row->noi_dung;
        }

        $rows = [];
        foreach (self::LOAI_ROWS as $loaiKey => $meta) {
            $cells = [];
            foreach (self::TRU_COLUMNS as $col) {
                $cells[$col['key']] = $byLoaiTru[$loaiKey][$col['key']] ?? '';
            }
            $rows[] = [
                'loai' => $meta['label'],
                'loai_key' => $loaiKey,
                'cells' => $cells,
            ];
        }

        return [
            'title' => 'Lá số Bát Tự',
            'columns' => array_map(static fn (array $c): array => [
                'key' => $c['key'],
                'label' => $c['label'],
            ], self::TRU_COLUMNS),
            'rows' => $rows,
        ];
    }
}
