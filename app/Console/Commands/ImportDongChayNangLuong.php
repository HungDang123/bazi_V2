<?php

namespace App\Console\Commands;

use App\Models\DongChayNangLuong;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDongChayNangLuong extends Command
{
    protected $signature = 'import:dong-chay-nang-luong
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Dòng chảy năng lượng từ file PHẦN 6 - II, III, IV- DÒNG CHẢY NĂNG LƯỢNG.xlsx';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 6 - II, III, IV- DÒNG CHẢY NĂNG LƯỢNG.xlsx'
        );

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                DongChayNangLuong::truncate();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $count = 0;
            $sortBase = 0;

            $totalSheets = $spreadsheet->getSheetCount();
            for ($idx = 0; $idx < $totalSheets; $idx++) {
                $sheet = $spreadsheet->getSheet($idx);
                $thapThan = trim((string) $sheet->getTitle());
                $highestRow = $sheet->getHighestRow();

                $viTri = '';
                $loai = '';
                $tru = '';

                for ($row = 2; $row <= $highestRow; $row++) {
                    $a = trim((string) $sheet->getCell('A' . $row)->getValue());
                    $b = trim((string) $sheet->getCell('B' . $row)->getValue());
                    $c = trim((string) $sheet->getCell('C' . $row)->getValue());
                    $d = trim((string) $sheet->getCell('D' . $row)->getValue());
                    $e = trim((string) $sheet->getCell('E' . $row)->getValue());
                    $f = trim((string) $sheet->getCell('F' . $row)->getValue());

                    if ($b !== '') {
                        $viTri = $b;
                    }
                    if ($c !== '') {
                        $loai = $c;
                    }
                    if ($d !== '') {
                        $tru = $d;
                    }

                    if ($f === '') {
                        continue;
                    }

                    if ($e !== '') {
                        $huong = $e;
                    } else {
                        continue;
                    }

                    if ($viTri === '' || $loai === '' || $tru === '') {
                        continue;
                    }

                    DongChayNangLuong::updateOrCreate(
                        [
                            'thap_than' => $thapThan,
                            'vi_tri_tuong_tac' => $viTri,
                            'loai_tuong_tac' => $loai,
                            'tru_tuong_tac' => $tru,
                            'huong' => $huong,
                        ],
                        [
                            'noi_dung' => $f,
                            'sort_order' => $sortBase + $row,
                        ]
                    );
                    $count++;
                }

                $sortBase += 10000;
                $this->info("Sheet '{$thapThan}': đã import.");
            }

            $this->info("Hoàn thành! Tổng {$count} mục đã import.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }
}
