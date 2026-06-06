<?php

namespace App\Console\Commands;

use App\Models\Phan6LaSoBatTu;
use App\Models\YNghiaTuTru;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportPhan6LaSoBatTu extends Command
{
    protected $signature = 'import:phan6-la-so-bat-tu
        {file? : Đường dẫn PHẦN 6.xlsx}
        {--fresh : Xóa bảng phan6_la_so_bat_tu và slug lá_số_bát_tự trong y_nghia_tu_tru}';

    protected $description = 'Import bảng Lá số Bát Tự (sheet I. Bản chất) → phan6_la_so_bat_tu';

    public function handle(): int
    {
        $path = $this->argument('file') ?? base_path('PHẦN 6.xlsx');
        if (! file_exists($path)) {
            $this->error("Không tìm thấy: {$path}");

            return 1;
        }

        if ($this->option('fresh')) {
            Phan6LaSoBatTu::truncate();
            YNghiaTuTru::where('slug', 'like', '%lá_số_bát_tự%')
                ->orWhere('slug', 'like', '%la_so_bat_tu%')
                ->delete();
        }

        $sheet = IOFactory::load($path)->getSheetByName('I. Bản chất');
        if ($sheet === null) {
            $this->error('Không tìm thấy sheet «I. Bản chất».');

            return 1;
        }

        $count = $this->importFromSheet($sheet);
        $this->info("Đã import {$count} ô → phan6_la_so_bat_tu.");

        return 0;
    }

    protected function importFromSheet(Worksheet $sheet): int
    {
        $count = 0;
        $headerRow = null;
        $truByCol = [];

        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $a = $this->cell($sheet, 'A', $row);
            if ($a !== 'Lá số Bát Tự') {
                continue;
            }

            $headerRow = $row;
            foreach (Phan6LaSoBatTu::TRU_COLUMNS as $colDef) {
                $label = $this->cell($sheet, $colDef['col'], $row);
                if ($label === '') {
                    continue;
                }
                $truByCol[$colDef['col']] = $colDef['key'];
            }

            for ($r = $row + 1; $r <= min($row + 5, $sheet->getHighestRow()); $r++) {
                $loaiLabel = $this->cell($sheet, 'A', $r);
                $loaiKey = $this->resolveLoaiKey($loaiLabel);
                if ($loaiKey === null) {
                    if ($loaiLabel !== '' && preg_match('/^(I\.|\d+\.|PHẦN)/u', $loaiLabel)) {
                        break;
                    }
                    continue;
                }

                foreach (Phan6LaSoBatTu::TRU_COLUMNS as $colDef) {
                    if (! isset($truByCol[$colDef['col']])) {
                        continue;
                    }
                    $noiDung = $this->cell($sheet, $colDef['col'], $r);
                    if ($noiDung === '') {
                        continue;
                    }
                    $meta = Phan6LaSoBatTu::LOAI_ROWS[$loaiKey];
                    Phan6LaSoBatTu::updateOrCreate(
                        [
                            'loai' => $loaiKey,
                            'tru' => $colDef['key'],
                        ],
                        [
                            'noi_dung' => $noiDung,
                            'sort_row' => $meta['sort'],
                            'sort_col' => $colDef['sort'],
                        ]
                    );
                    $count++;
                }
            }

            break;
        }

        if ($headerRow === null) {
            $this->warn('Không tìm thấy dòng «Lá số Bát Tự» trong sheet.');
        }

        return $count;
    }

    protected function resolveLoaiKey(string $label): ?string
    {
        $label = trim($label);
        if ($label === 'Thiên Can') {
            return 'thien_can';
        }
        if ($label === 'Địa Chi') {
            return 'dia_chi';
        }

        return null;
    }

    protected function cell(Worksheet $sheet, string $col, int $row): string
    {
        return trim((string) ($sheet->getCell("{$col}{$row}")->getValue() ?? ''));
    }
}
