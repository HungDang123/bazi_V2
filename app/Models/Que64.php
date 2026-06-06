<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Que64 extends Model
{
    use HasFactory;

    protected $table = 'que_64';

    protected $fillable = [
        'name',
        'tong_quan',
        'su_nghiep',
        'tai_chinh',
        'tinh_duyen',
        'suc_khoe',
        'phat_trien_ban_than',
        'ket_noi_xa_hoi',
    ];

    protected $casts = [
        'tong_quan' => 'array',
        'su_nghiep' => 'array',
        'tai_chinh' => 'array',
        'tinh_duyen' => 'array',
        'suc_khoe' => 'array',
        'phat_trien_ban_than' => 'array',
        'ket_noi_xa_hoi' => 'array',
    ];
}
