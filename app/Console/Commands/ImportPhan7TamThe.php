<?php

namespace App\Console\Commands;

use App\Models\Phan7TamThe;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan7TamThe extends Command
{
    protected $signature = 'import:phan7-tam-the
        {file? : Đường dẫn file PHẦN 7 - I.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 7 - Mục I (Sự vận hành của 5 trung tâm năng lượng) từ file Excel vào bảng phan7_tam_the';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 7 - I.xlsx'
        );

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file trong imports/ hoặc: php artisan import:phan7-tam-the <đường_dẫn_file>');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                Phan7TamThe::truncate();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $count = 0;
            $thuTu = 1;

            // Sheet 1 (index 0) → đầu Mục I; Sheet 2 (index 1) → cuối Phần 7 (sau Mục II)
            foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
                $highestRow = $sheet->getHighestRow();
                for ($row = 1; $row <= $highestRow; $row++) {
                    $noiDung = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());

                    if ($noiDung === '') {
                        continue;
                    }

                    // Phát hiện placeholder ảnh → chuyển thành [image]
                    $imagePath = null;
                    if (mb_strpos($noiDung, '[CHÈN') !== false || mb_strpos($noiDung, '[IMAGE]') !== false || mb_strpos($noiDung, '[image]') !== false) {
                        $noiDung = '[image]';
                        $imagePath = 'images/phan-7/ngu-hanh-ban-menh.png';
                    }

                    Phan7TamThe::updateOrCreate(
                        ['thu_tu' => $thuTu],
                        [
                            'sheet_index' => $sheetIndex,
                            'noi_dung' => $noiDung,
                            'image' => $imagePath,
                        ]
                    );
                    $count++;
                    $thuTu++;
                }
            }

            $this->info("Import thành công {$count} dòng từ PHẦN 7 - I.xlsx vào phan7_tam_the.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}
