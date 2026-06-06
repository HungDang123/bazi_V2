<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanSatRule extends Model
{
    use HasFactory;

    protected $table = 'than_sat_rules';
    protected $fillable = [
        'ten_than_sat', 
        'loai_than_sat', 
        'phuong_phap_tra_cuu'
    ];
    
    public function details()
    {
        return $this->hasMany(ThanSatRuleDetail::class, 'than_sat_rule_id');
    }
}