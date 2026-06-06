<?php

namespace App\Http\Controllers;

use App\Models\Que64;
use Illuminate\Http\Request;

class Que64Controller extends Controller
{
    public function show($id)
    {
        $que = Que64::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $que->id,
                'name' => $que->name,
                'chinese_name' => $que->chinese_name ?? null,
                'tong_quan' => $que->tong_quan,
                'su_nghiep' => $que->su_nghiep,
                'tai_chinh' => $que->tai_chinh,
                'tinh_duyen' => $que->tinh_duyen,
                'suc_khoe' => $que->suc_khoe,
                'phat_trien_ban_than' => $que->phat_trien_ban_than,
                'ket_noi_xa_hoi' => $que->ket_noi_xa_hoi,
            ]
        ]);
    }
}
