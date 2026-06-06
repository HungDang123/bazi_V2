<?php

namespace App\Console\Commands;

use App\Models\GiaiPhapThapThan;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportGiaiPhapThapThan extends Command
{
    protected $signature = 'import:giai-phap-thap-than
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Giải pháp gia tăng năng lượng Thập Thần từ file PHẦN 5 - II, III, IV, V, VI, VII- GIẢI PHÁP CHO TỪNG THẬP THẦN.xlsx';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 5 - II, III, IV, V, VI, VII- GIẢI PHÁP CHO TỪNG THẬP THẦN.xlsx'
        );

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file trong imports/ hoặc: php artisan import:giai-phap-thap-than <đường_dẫn_file>');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                GiaiPhapThapThan::truncate();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $count = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $thapThan = trim((string) $sheet->getCell('B' . $row)->getValue());
                $content = trim((string) $sheet->getCell('C' . $row)->getValue());

                if ($thapThan === '' || $content === '') {
                    continue;
                }

                GiaiPhapThapThan::updateOrCreate(
                    ['thap_than' => $thapThan],
                    [
                        'content' => $content,
                        'sort_order' => $row - 1,
                    ]
                );
                $count++;
            }

            $this->info("Hoàn thành! Tổng {$count} mục đã import.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }
}
