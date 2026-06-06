<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodingLogicRelationship extends Model
{
    use HasFactory;

    protected $table = 'coding_logic_relationships';

    protected $fillable = [
        'loai',
        'item1',
        'item2',
        'moi_quan_he',
        'ngu_hanh_sinh_ra',
        'sort_order',
    ];

    /**
     * @param  bool  $allowReverse  Nếu false: chỉ tra item1=can1, item2=can2. Với Trụ Ngày - Trụ Giờ phải dùng false.
     */
    public static function findByThienCan(string $can1, string $can2, ?string $moiQuanHe = null, bool $allowReverse = true)
    {
        $q = static::where('loai', 'thien_can')
            ->where(function ($query) use ($can1, $can2, $allowReverse) {
                $query->where('item1', $can1)->where('item2', $can2);
                if ($allowReverse) {
                    $query->orWhere(function ($q) use ($can1, $can2) {
                        $q->where('item1', $can2)->where('item2', $can1);
                    });
                }
            });
        if ($moiQuanHe !== null) {
            $q->where('moi_quan_he', $moiQuanHe);
        }
        return $q->get();
    }

    /**
     * @param  bool  $allowReverse  Nếu false: chỉ tra item1=chi1, item2=chi2. Với Trụ Ngày - Trụ Giờ phải dùng false.
     */
    public static function findByDiaChi(string $chi1, string $chi2, ?string $moiQuanHe = null, bool $allowReverse = true)
    {
        $q = static::where('loai', 'dia_chi')
            ->where(function ($query) use ($chi1, $chi2, $allowReverse) {
                $query->where('item1', $chi1)->where('item2', $chi2);
                if ($allowReverse) {
                    $query->orWhere(function ($q) use ($chi1, $chi2) {
                        $q->where('item1', $chi2)->where('item2', $chi1);
                    });
                }
            });
        if ($moiQuanHe !== null) {
            $q->where('moi_quan_he', $moiQuanHe);
        }
        return $q->get();
    }

    public static function getAllOrdered()
    {
        return static::orderBy('loai')->orderBy('moi_quan_he')->orderBy('sort_order')->get();
    }
}
