<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiaiPhapThapThan extends Model
{
    use HasFactory;

    protected $table = 'giai_phap_thap_than';

    protected $fillable = [
        'thap_than',
        'content',
        'sort_order',
    ];

    /**
     * Lấy theo Thập Thần.
     */
    public static function findByThapThan(string $thapThan): ?self
    {
        return static::where('thap_than', trim($thapThan))->first();
    }

    /**
     * Lấy tất cả theo sort_order.
     */
    public static function getAllOrdered()
    {
        return static::orderBy('sort_order')->get();
    }
}
