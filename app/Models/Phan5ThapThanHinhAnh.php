<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan5ThapThanHinhAnh extends Model
{
    use HasFactory;

    protected $table = 'phan5_thap_than_hinh_anh';

    protected $fillable = [
        'thap_than',
        'image',
    ];

    /**
     * @return array<string, string> thap_than → image path
     */
    public static function imageMapByThapThan(): array
    {
        $map = [];
        foreach (static::query()->get() as $row) {
            $image = trim((string) $row->image);
            if ($image !== '') {
                $map[(string) $row->thap_than] = $image;
            }
        }

        return $map;
    }
}
