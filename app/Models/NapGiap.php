<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NapGiap extends Model
{
    use SoftDeletes;
    protected $table = 'nap_giap';

    protected $fillable = [
        'nap_giap_nam',
        'nap_giap_thang',
        'thoi_diem_bat_dau_ngay',
        'thoi_diem_bat_dau_gio'
    ];
}
