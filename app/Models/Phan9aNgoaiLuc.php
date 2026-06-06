<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan9aNgoaiLuc extends Model
{
    protected $table = 'phan9a_ngoai_luc';

    protected $fillable = [
        'tieu_de',
        'noi_dung',
        'sort_order',
    ];
}
