<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HanhNoiDung extends Model
{
    use HasFactory;

    protected $table = 'hanh_noi_dung';

    protected $fillable = [
        'hanh_id',
        'slug',
        'title',
        'content',
        'sort_order',
    ];

    public function hanh()
    {
        return $this->belongsTo(Hanh::class);
    }
}
