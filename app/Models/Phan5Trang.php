<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan5Trang extends Model
{
    use HasFactory;

    protected $table = 'phan5_trang';

    protected $fillable = [
        'slug',
        'title',
        'image',
        'sort_order',
    ];

    public static function getAllOrdered()
    {
        return static::orderBy('sort_order')->get();
    }

    /**
     * @return array<string, string> slug → image path
     */
    public static function imageMapBySlug(): array
    {
        $map = [];
        foreach (static::getAllOrdered() as $row) {
            $image = trim((string) $row->image);
            if ($image !== '') {
                $map[(string) $row->slug] = $image;
            }
        }

        return $map;
    }
}
