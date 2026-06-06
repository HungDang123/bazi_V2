<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hanh extends Model
{
    use HasFactory;

    protected $table = 'hanh';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'image',
    ];

    /** Đường dẫn tuyệt đối tới file ảnh (fallback theo slug nếu chưa có trong DB). */
    public function resolvedImagePath(?string $fallbackDir = null): string
    {
        if ($this->image) {
            $relative = str_replace('\\', '/', $this->image);
            $candidates = [
                base_path($relative),
                $relative,
            ];

            foreach ($candidates as $path) {
                if (is_file($path)) {
                    return str_replace('\\', '/', realpath($path) ?: $path);
                }
            }
        }

        if ($fallbackDir !== null && $fallbackDir !== '') {
            return rtrim(str_replace('\\', '/', $fallbackDir), '/') . '/' . $this->slug . '.png';
        }

        return '';
    }

    public function noiDung()
    {
        return $this->hasMany(HanhNoiDung::class)->orderBy('sort_order');
    }
}
