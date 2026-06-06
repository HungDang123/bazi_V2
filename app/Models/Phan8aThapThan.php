<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan8aThapThan extends Model
{
    protected $table = 'phan8a_thap_than';

    protected $fillable = [
        'thap_than',
        'vi_tri',
        'vi_tri_tuong_tac',
        'loai_tuong_tac',
        'tieu_de',
        'su_kien_co_hoi',
        'quan_tri_rui_ro',
        'chien_luoc',
        'sort_order',
    ];

    public static function findFor(
        string $thapThan,
        string $viTri,
        string $viTriTuongTac,
        string $loaiTuongTac
    ): ?self {
        $thapThan = trim($thapThan);
        if ($thapThan === '') {
            return null;
        }

        return static::query()
            ->where('thap_than', $thapThan)
            ->where('vi_tri', $viTri)
            ->where('vi_tri_tuong_tac', $viTriTuongTac)
            ->where('loai_tuong_tac', $loaiTuongTac)
            ->first();
    }

    /**
     * @return array{moi_quan_he: string, tieu_de: string|null, sections: array<int, array{label: string, content: string}>}
     */
    public function toApiArray(): array
    {
        $sections = [];
        foreach ([
            'a. Sự kiện cơ hội' => $this->su_kien_co_hoi,
            'b. Quản trị rủi ro' => $this->quan_tri_rui_ro,
            'c. Chiến lược giai đoạn này' => $this->chien_luoc,
        ] as $label => $content) {
            $text = trim((string) $content);
            if ($text !== '') {
                $sections[] = ['label' => $label, 'content' => $text];
            }
        }

        return [
            'moi_quan_he' => (string) $this->loai_tuong_tac,
            'tieu_de' => $this->tieu_de,
            'sections' => $sections,
        ];
    }
}
