<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanSatRuleDetail extends Model
{
    use HasFactory;

    protected $table = 'than_sat_rule_details';
    protected $fillable = [
        'than_sat_rule_id',
        'loai_tra_cuu', 
        'gia_tri_tra_cuu',
        'vi_tri_tim_thay',
        'vi_tri_xuat_hien',
        'thu_tu_uu_tien'
    ];
    
    public function rule()
    {
        return $this->belongsTo(ThanSatRule::class, 'than_sat_rule_id');
    }
}