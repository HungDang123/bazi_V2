<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan9bHieuQuaChuyenHoa extends Model
{
    protected $table = 'phan9b_hieu_qua_chuyen_hoa';

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
