<?php

namespace App\Models;

use App\Services\DocxTextService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DongChayGioiThieu extends Model
{
    use HasFactory;

    protected $table = 'dong_chay_gioi_thieu';

    protected $fillable = [
        'tru_loai',
        'noi_dung',
        'image',
    ];

    /** Map tru_loai -> key dùng trong API (Trụ Năm - Trụ Tháng, ...) */
    public static array $TRU_LOAI_TO_DISPLAY = [
        'tru_nam_tru_thang' => 'Trụ Năm - Trụ Tháng',
        'tru_thang_tru_ngay' => 'Trụ Tháng - Trụ Ngày',
        'tru_ngay_tru_gio' => 'Trụ Ngày - Trụ Giờ',
    ];

    /**
     * Lấy theo tru_loai.
     */
    public static function getByTruLoai(string $truLoai): ?self
    {
        return static::where('tru_loai', $truLoai)->first();
    }

    /**
     * Lấy dưới dạng nhóm cho API.
     * Trả về: [ 'Trụ Năm - Trụ Tháng' => ['noi_dung' => '...'], ... ]
     */
    public static function getAllGrouped(): array
    {
        $all = static::orderBy('tru_loai')->get();
        $grouped = [];
        foreach ($all as $row) {
            $truLoai = (string) ($row->tru_loai ?? '');
            if ($truLoai === '') {
                continue;
            }
            $key = self::$TRU_LOAI_TO_DISPLAY[$truLoai] ?? $truLoai;
            $imagePath = trim((string) ($row->image ?? ''));
            $grouped[$key] = [
                'noi_dung' => (string) ($row->noi_dung ?? ''),
                'image' => $imagePath !== '' ? $imagePath : null,
                'image_url' => $imagePath !== ''
                    ? DocxTextService::publicUrlForMarkerPath($imagePath)
                    : null,
            ];
        }

        return $grouped;
    }
}
