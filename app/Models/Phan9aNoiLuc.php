<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan9aNoiLuc extends Model
{
    protected $table = 'phan9a_noi_luc';

    protected $fillable = [
        'loai',
        'ngu_hanh',
        'tieu_de',
        'noi_dung',
        'sort_order',
    ];

    public const HANH_ORDER = ['kim', 'moc', 'thuy', 'hoa', 'tho'];

    /** @var array<string, string> */
    public const SLUG_TO_LABEL = [
        'kim' => 'Kim',
        'moc' => 'Mộc',
        'thuy' => 'Thủy',
        'hoa' => 'Hỏa',
        'tho' => 'Thổ',
    ];

    /** @var array<string, string> */
    public const LABEL_TO_SLUG = [
        'KIM' => 'kim',
        'MỘC' => 'moc',
        'THỦY' => 'thuy',
        'HỎA' => 'hoa',
        'THỔ' => 'tho',
    ];

    public static function labelToSlug(string $label): ?string
    {
        $key = mb_strtoupper(trim($label), 'UTF-8');

        return self::LABEL_TO_SLUG[$key] ?? null;
    }
}
