<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HyKyThan;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportHyKyThan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:hy-ky-than {file? : Path to Excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Hỷ Thần - Kỵ Thần data from Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file') ?? storage_path('app/public/hy_ky_than.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Loading Excel file: {$filePath}");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getSheetByName('sheet 1') ?? $spreadsheet->getActiveSheet();
            
            $data = $this->parseExcelData($worksheet);
            
            $this->importToDatabase($data);
            
            $this->info('Import completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error during import: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Parse data from Excel worksheet
     */
    private function parseExcelData($worksheet): array
    {
        $data = [];
        $startRow = 30; // Dòng bắt đầu dữ liệu (dựa trên file bạn cung cấp)
        
        for ($row = $startRow; $row <= $worksheet->getHighestRow(); $row++) {
            $thienBanNgay = trim($worksheet->getCell("A{$row}")->getCalculatedValue());
            $diaChiThang = trim($worksheet->getCell("B{$row}")->getCalculatedValue());
            
            // Nếu không có dữ liệu ở cột A, kết thúc
            if (empty($thienBanNgay)) {
                break;
            }

            $thanNhuocThanVuong = trim($worksheet->getCell("C{$row}")->getCalculatedValue());
            $hyThanNguHanh = trim($worksheet->getCell("D{$row}")->getCalculatedValue());
            $hyThanCan = trim($worksheet->getCell("E{$row}")->getCalculatedValue());
            $kyThanNguHanh = trim($worksheet->getCell("F{$row}")->getCalculatedValue());
            $kyThanCan = trim($worksheet->getCell("G{$row}")->getCalculatedValue());
            $this->info('Thien Ban Ngay: ' . $thienBanNgay);
            $this->info('Dia Chi Thang: ' . $diaChiThang);
            $this->info('Than Nhuoc Than Vuong: ' . $thanNhuocThanVuong);
            $this->info('Hy Than Ngu Hanh: ' . $hyThanNguHanh);
            $this->info('Hy Than Can: ' . $hyThanCan);
            $this->info('Ky Than Ngu Hanh: ' . $kyThanNguHanh);
            $this->info('Ky Than Can: ' . $kyThanCan);
            $data[] = [
                'thien_can_ngay' => $thienBanNgay,
                'dia_chi_thang' => $diaChiThang == 'TÍ' ? 'TÝ' : $diaChiThang,
                'than_nhuoc_than_vuong' => $thanNhuocThanVuong,
                'hy_than_ngu_hanh' => $hyThanNguHanh,
                'hy_than_can' => $hyThanCan,
                'ky_than_ngu_hanh' => $kyThanNguHanh,
                'ky_than_can' => $kyThanCan,
            ];
        }

        return $data;
    }

    /**
     * Import data to database
     */
    private function importToDatabase(array $data): void
    {
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        foreach ($data as $item) {
            // Kiểm tra xem bản ghi đã tồn tại chưa
            $existing = HyKyThan::findByThienCanDiaChi($item['thien_can_ngay'], $item['dia_chi_thang']);

            if ($existing) {
                // Cập nhật bản ghi đã tồn tại
                $existing->update($item);
            } else {
                // Tạo bản ghi mới
                HyKyThan::create($item);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported/Updated " . count($data) . " records.");
    }
}