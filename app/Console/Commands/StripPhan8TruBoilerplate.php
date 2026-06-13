<?php

namespace App\Console\Commands;

use App\Models\DongChayGioiThieu;
use App\Services\Phan8TruSectionService;
use Illuminate\Console\Command;

class StripPhan8TruBoilerplate extends Command
{
    protected $signature = 'phan8:strip-tru-boilerplate';

    protected $description = 'Xóa dòng mẫu Excel (Thiên Can Đại Vận, Nếu có Hợp/Khắc…) khỏi giới thiệu Trụ PHẦN 8 trong DB';

    /** @var array<int, string> */
    private const TRU_KEYS = [
        'dai_van_tru_nam',
        'dai_van_tru_thang',
        'dai_van_tru_ngay',
        'dai_van_tru_gio',
        'nien_van_hien_tai_tru_nam',
        'nien_van_hien_tai_tru_thang',
        'nien_van_hien_tai_tru_ngay',
        'nien_van_hien_tai_tru_gio',
        'nien_van_tiep_theo_tru_nam',
        'nien_van_tiep_theo_tru_thang',
        'nien_van_tiep_theo_tru_ngay',
        'nien_van_tiep_theo_tru_gio',
        'nien_van_8b_tiep_theo_tru_nam',
        'nien_van_8b_tiep_theo_tru_thang',
        'nien_van_8b_tiep_theo_tru_ngay',
        'nien_van_8b_tiep_theo_tru_gio',
    ];

    public function handle(): int
    {
        $updated = 0;

        foreach (self::TRU_KEYS as $key) {
            $row = DongChayGioiThieu::query()->where('tru_loai', $key)->first();
            if ($row === null) {
                continue;
            }

            $before = (string) ($row->noi_dung ?? '');
            $after  = Phan8TruSectionService::stripTemplateBoilerplate($before);
            if ($after === $before) {
                continue;
            }

            $row->noi_dung = $after;
            $row->save();
            $updated++;
            $this->line("  [OK] {$key}");
        }

        $this->info("Đã cập nhật {$updated} mục giới thiệu Trụ.");

        return self::SUCCESS;
    }
}
