<?php

namespace App\Console\Commands;

use App\Models\NhatChuTruNgay;
use App\Models\ViNhanNhatChu;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportViNhanNhatChu extends Command
{
    protected $signature = 'import:vi-nhan-nhat-chu 
        {file? : Đường dẫn file PHẦN 4 - V- VÍ DỤ VỀ VỸ NHÂN.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Ví dụ vĩ nhân từ file Excel PHẦN 4 - V- VÍ DỤ VỀ VỸ NHÂN.xlsx';

    public function handle(): int
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');

        $file = $this->argument('file') ?: base_path('PHẦN 4 - V- VÍ DỤ VỀ VỸ NHÂN.xlsx');
        if (! file_exists($file)) {
            $this->error("File không tồn tại: {$file}");

            return 1;
        }

        if ($this->option('fresh')) {
            ViNhanNhatChu::truncate();
            $this->info('Đã xóa dữ liệu cũ.');
        }

        try {
            $count = $this->importFile($file);
            $this->info("Hoàn thành! Tổng {$count} mục đã import.");
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    protected function importFile(string $filePath): int
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getSheet(0);

        $highestRow = $sheet->getHighestRow();
        $curThienCan = null;
        $count = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $colB = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
            $colC = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));

            if ($colB !== '') {
                $curThienCan = NhatChuTruNgay::normalizeThienCan($colB);
            }

            if ($curThienCan !== null && $colC !== '') {
                ViNhanNhatChu::create([
                    'thien_can' => $curThienCan,
                    'ten_nguoi' => $colC,
                    'sort_order' => $count,
                ]);
                $count++;
            }
        }

        return $count;
    }
}
