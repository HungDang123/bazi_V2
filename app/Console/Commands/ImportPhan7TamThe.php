<?php

namespace App\Console\Commands;

use App\Models\Phan7TamThe;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan7TamThe extends Command
{
    protected $signature = 'import:phan7-tam-the
        {file? : Đường dẫn file PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 7 - Sheet 1 (I. XÁC ĐỊNH TÂM THẾ) từ file Excel vào bảng phan7_tam_the';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx'
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
            $sheet = $spreadsheet->getSheet(0);

            $highestRow = $sheet->getHighestRow();
            if ($highestRow < 1) {
                $this->warn('Sheet 1 không có dữ liệu.');
                return 0;
            }

            $count = 0;
            for ($row = 1; $row <= min(3, $highestRow); $row++) {
                $loai = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
                $thapThan = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());
                $tenTruongHop = trim((string) $sheet->getCell('D' . $row)->getCalculatedValue());
                $noiDung = $sheet->getCell('E' . $row)->getCalculatedValue();
                $noiDung = is_string($noiDung) ? trim($noiDung) : (string) $noiDung;

                if ($loai === '' && $noiDung === '') {
                    continue;
                }
                if ($loai === '') {
                    $loai = 'Tổng Quan';
                }

                Phan7TamThe::updateOrCreate(
                    [
                        'thu_tu' => $row,
                    ],
                    [
                        'loai' => $loai,
                        'thap_than' => $thapThan !== '' ? $thapThan : null,
                        'ten_truong_hop' => $tenTruongHop !== '' ? $tenTruongHop : null,
                        'noi_dung' => $noiDung,
                    ]
                );
                $count++;
            }

            $this->info("Import thành công {$count} mục từ Sheet 1 (I. XÁC ĐỊNH TÂM THẾ) vào phan7_tam_the.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }
}
