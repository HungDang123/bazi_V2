<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanSatResult extends Model
{
    use HasFactory;

    protected $table = 'than_sat_results';
    protected $fillable = [
        'tu_tru_data',
        'ket_qua',
        'gioi_tinh',
        'am_duong'
    ];
    
    protected $casts = [
        'tu_tru_data' => 'array',
        'ket_qua' => 'array'
    ];
}