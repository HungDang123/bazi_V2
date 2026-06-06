<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phan8DuBaoKhiaCanh extends Model
{
    use HasFactory;

    protected $table = 'phan8_du_bao_khia_canh';

    protected $fillable = [
        'khia_canh',
        'gioi_tinh',
        'dieu_kien',
        'noi_dung',
        'thu_tu',
        'sheet_name',
    ];
}

