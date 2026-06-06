<?php

namespace App\Console\Commands;

use App\Models\SucKhoeHyKyThan;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ImportPhan5SucKhoe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:phan5-suc-khoe 
                            {file? : Đường dẫn tới file PHAN5_V_SUC_KHOE.xlsx} 
                            {--fresh : Xóa dữ liệu cũ trước khi import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dữ liệu PHẦN 5 - V. SỨC KHOẺ từ file PHAN5_V_SUC_KHOE.xlsx vào bảng suc_khoe_hy_ky_than';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '600');

        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHAN5_V_SUC_KHOE.xlsx'
        );

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            return 1;
        }

        if ($this->option('fresh')) {
            $this->info('Xóa dữ liệu cũ trong bảng suc_khoe_hy_ky_than...');
            SucKhoeHyKyThan::truncate();
        }

        $this->info("Đang đọc file Excel: {$filePath}");

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestRow();

            $records = [];
            $currentThanTrangThai = null;
            $sortOrderByTrangThai = [];

            for ($row = 1; $row <= $highestRow; $row++) {
                $colB = trim((string) $sheet->getCell("B{$row}")->getCalculatedValue());
                $colC = trim((string) $sheet->getCell("C{$row}")->getCalculatedValue());
                $colD = trim((string) $sheet->getCell("D{$row}")->getCalculatedValue());

                if ($colB !== '') {
                    $currentThanTrangThai = $colB;
                }

                if ($currentThanTrangThai === null) {
                    continue;
                }

                if ($colC === '' || $colD === '') {
                    continue;
                }

                $slug = Str::slug(mb_strtolower($currentThanTrangThai, 'UTF-8'), '-');
                if (! isset($sortOrderByTrangThai[$slug])) {
                    $sortOrderByTrangThai[$slug] = 0;
                }
                $sortOrderByTrangThai[$slug]++;

                $records[] = [
                    'than_trang_thai' => $currentThanTrangThai,
                    'than_trang_thai_slug' => $slug,
                    'nhom' => $colC,
                    'content' => $colD,
                    'sort_order' => $sortOrderByTrangThai[$slug],
                ];
            }

            if (empty($records)) {
                $this->warn('Không tìm thấy bản ghi hợp lệ nào trong file Excel.');
                return 0;
            }

            $this->info('Bắt đầu import vào bảng suc_khoe_hy_ky_than...');

            $bar = $this->output->createProgressBar(count($records));
            $bar->start();

            foreach ($records as $item) {
                SucKhoeHyKyThan::create($item);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Import thành công " . count($records) . " dòng từ PHAN5_V_SUC_KHOE.xlsx vào phan5_suc_khoe.");

            return 0;
        } catch (\Exception $e) {
            $this->error('Lỗi khi import: ' . $e->getMessage());
            return 1;
        }
    }
}

