<?php

namespace App\Console\Commands;

use App\Models\CodingLogicRelationship;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCodingLogic extends Command
{
    protected $signature = 'import:coding-logic
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import mối quan hệ Thiên Can / Địa Chi từ file PHẦN 6 - CODING LOGIC.xlsx';

    protected array $sheetConfig = [
        'THIÊN CAN HỢP' => ['loai' => 'thien_can', 'moi_quan_he' => 'Hợp', 'has_ngu_hanh' => true],
        'THIÊN CAN KHẮC' => ['loai' => 'thien_can', 'moi_quan_he' => 'Khắc', 'has_ngu_hanh' => false],
        'ĐỊA CHI HỢP' => ['loai' => 'dia_chi', 'moi_quan_he' => 'Hợp', 'has_ngu_hanh' => true],
        'ĐỊA CHI XUNG' => ['loai' => 'dia_chi', 'moi_quan_he' => 'Xung', 'has_ngu_hanh' => false],
        'ĐỊA CHI HÌNH' => ['loai' => 'dia_chi', 'moi_quan_he' => 'Hình', 'has_ngu_hanh' => false],
        'ĐỊA CHI HẠI' => ['loai' => 'dia_chi', 'moi_quan_he' => 'Hại', 'has_ngu_hanh' => false],
        'ĐỊA CHI PHÁ' => ['loai' => 'dia_chi', 'moi_quan_he' => 'Phá', 'has_ngu_hanh' => false],
    ];

    public function handle(): int
    {
        $filePath = ImportPath::resolveFirst($this->argument('file'), [
            'PHẦN 6.xlsx',
            'PHẦN 6 - CODING LOGIC.xlsx',
        ]);

        if ($filePath === null) {
            $this->error('File không tồn tại: '.ImportPath::file('PHẦN 6 - CODING LOGIC.xlsx'));
            $this->info('Đặt file trong imports/ hoặc: php artisan import:coding-logic <đường_dẫn_file>');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                CodingLogicRelationship::truncate();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $count = 0;
            $sortBase = 0;

            $totalSheets = $spreadsheet->getSheetCount();
            for ($idx = 0; $idx < $totalSheets; $idx++) {
                $sheet = $spreadsheet->getSheet($idx);
                $sheetName = trim((string) $sheet->getTitle());
                if (! isset($this->sheetConfig[$sheetName])) {
                    continue;
                }
                $config = $this->sheetConfig[$sheetName];
                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {
                    $item1 = trim((string) $sheet->getCell('A' . $row)->getValue());
                    $item2 = trim((string) $sheet->getCell('B' . $row)->getValue());
                    if ($item1 === '' || $item2 === '') {
                        continue;
                    }
                    $nguHanh = $config['has_ngu_hanh']
                        ? trim((string) $sheet->getCell('D' . $row)->getValue())
                        : null;
                    if ($config['has_ngu_hanh'] && $nguHanh === '') {
                        $nguHanh = null;
                    }
                    CodingLogicRelationship::updateOrCreate(
                        [
                            'loai' => $config['loai'],
                            'item1' => $item1,
                            'item2' => $item2,
                            'moi_quan_he' => $config['moi_quan_he'],
                        ],
                        [
                            'ngu_hanh_sinh_ra' => $nguHanh,
                            'sort_order' => $sortBase + $row,
                        ]
                    );
                    $count++;
                }
                $sortBase += 10000;
                $this->info("Sheet '{$sheetName}': đã import.");
            }

            $this->info("Hoàn thành! Tổng {$count} mục đã import.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }
}
