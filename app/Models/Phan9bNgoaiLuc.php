<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan9bNgoaiLuc extends Model
{
    protected $table = 'phan9b_ngoai_luc';

    protected $fillable = [
        'loai',
        'section_number',
        'noi_dung',
        'sort_order',
    ];

    protected $casts = [
        'section_number' => 'integer',
        'sort_order' => 'integer',
    ];
}
