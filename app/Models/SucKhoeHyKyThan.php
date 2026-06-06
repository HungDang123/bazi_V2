<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SucKhoeHyKyThan extends Model
{
    use HasFactory;

    protected $table = 'suc_khoe_hy_ky_than';

    protected $fillable = [
        'than_trang_thai',
        'than_trang_thai_slug',
        'nhom',
        'content',
        'sort_order',
    ];
}

