<?php

namespace App\Console\Commands;

use App\Models\Phan8DuBaoKhiaCanh;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan8DuBaoKhiaCanh extends Command
{
    protected $signature = 'import:phan8-du-bao-khia-canh
        {file? : Đường dẫn file PHẦN 8 - III- DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 8 - III (Sheets 2-7) vào bảng phan8_du_bao_khia_canh';

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 8 - III- DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG.xlsx'
        );

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Dùng: php artisan import:phan8-du-bao-khia-canh imports/<file.xlsx>');
            return 1;
        }

        try {
            if ($this->option('fresh')) {
                Phan8DuBaoKhiaCanh::truncate();
                $this->info('Đã xóa dữ liệu cũ trong phan8_du_bao_khia_canh.');
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $totalInserted = 0;

            // Sheets 2-7 => index 1..6
            for ($sheetIdx = 1; $sheetIdx <= 6; $sheetIdx++) {
                if ($sheetIdx >= $spreadsheet->getSheetCount()) {
                    break;
                }

                $sheet = $spreadsheet->getSheet($sheetIdx);
                $sheetName = trim((string) $sheet->getTitle());
                $highestRow = $sheet->getHighestRow();

                $khiaCanh = trim((string) $sheet->getCell('A1')->getCalculatedValue());
                if ($khiaCanh === '') {
                    $khiaCanh = $sheetName;
                }

                $currentGender = null;
                $currentCase = null; // ['gioi_tinh','dieu_kien','parts'=>[]]
                $thuTu = 1;

                $flushCurrent = function () use (&$currentCase, &$totalInserted, &$thuTu, $khiaCanh, $sheetName): void {
                    if ($currentCase === null) {
                        return;
                    }
                    $noiDung = trim(implode("\n\n", array_filter($currentCase['parts'], static fn ($x) => trim((string) $x) !== '')));
                    if ($noiDung === '' || trim((string) $currentCase['dieu_kien']) === '') {
                        $currentCase = null;
                        return;
                    }
                    Phan8DuBaoKhiaCanh::create([
                        'khia_canh'  => $khiaCanh,
                        'gioi_tinh'  => $currentCase['gioi_tinh'],
                        'dieu_kien'  => $currentCase['dieu_kien'],
                        'noi_dung'   => $noiDung,
                        'thu_tu'     => $thuTu,
                        'sheet_name' => $sheetName,
                    ]);
                    $thuTu++;
                    $totalInserted++;
                    $currentCase = null;
                };

                for ($row = 1; $row <= $highestRow; $row++) {
                    $b = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
                    $c = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());
                    $d = trim((string) $sheet->getCell('D' . $row)->getCalculatedValue());

                    if ($b === 'NAM' || $b === 'NỮ') {
                        $currentGender = $b;
                    }

                    $dieuKien = '';
                    if ($c !== '') {
                        $dieuKien = $c;
                    } elseif ($b !== '' && $b !== 'NAM' && $b !== 'NỮ') {
                        $dieuKien = $b;
                    }

                    if ($dieuKien !== '') {
                        $flushCurrent();
                        $currentCase = [
                            'gioi_tinh' => $currentGender,
                            'dieu_kien' => $dieuKien,
                            'parts' => [],
                        ];
                    }

                    if ($d !== '' && $currentCase !== null) {
                        $currentCase['parts'][] = $d;
                    }
                }

                $flushCurrent();
                $this->info("Sheet {$sheetIdx} ({$sheetName}): import xong.");
            }

            $this->info("Hoàn thành! Đã import {$totalInserted} bản ghi từ sheets 2-7.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }
}

