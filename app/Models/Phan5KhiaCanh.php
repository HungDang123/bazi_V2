<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan5KhiaCanh extends Model
{
    use HasFactory;

    protected $table = 'phan5_khia_canh';

    protected $fillable = [
        'slug',
        'section_code',
        'title',
        'tong_quan',
        'image_vi_tri',
        'sort_order',
    ];

    public static function getAllOrdered()
    {
        return static::orderBy('sort_order')->get();
    }
}
