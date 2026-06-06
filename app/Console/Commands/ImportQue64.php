<?php

namespace App\Console\Commands;

use App\Models\Que64;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportQue64 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:que64 {--fresh : Xóa dữ liệu cũ trước khi import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dữ liệu 64 quẻ từ file Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Tăng memory limit và execution time cho file Excel lớn
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '600');
        
        $filePath = storage_path('app/CHI TIẾT 64 QUẺ.xlsx');

        if (!file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            return 1;
        }

        if ($this->option('fresh')) {
            $this->info('Xóa dữ liệu cũ...');
            Que64::truncate();
        }

        $this->info('Đang đọc file Excel...');
        
        try {
            // Load only data, no formatting
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $totalSheets = $spreadsheet->getSheetCount();
            
            $this->info("Tổng số sheet: {$totalSheets}");
            
            $bar = $this->output->createProgressBar($totalSheets);
            $bar->start();

            $imported = 0;

            for ($i = 0; $i < $totalSheets; $i++) {
                $sheet = $spreadsheet->getSheet($i);
                $sheetName = $sheet->getTitle();
                
                // Bỏ số thứ tự ở đầu tên (ví dụ: "1. THUẦN CÀN" -> "THUẦN CÀN")
                $cleanName = preg_replace('/^\d+\.\s*/', '', $sheetName);
                
                // Parse dữ liệu từ sheet
                $data = $this->parseSheetData($sheet);
                
                if ($data) {
                    Que64::create(
                        array_merge(['name' => $cleanName], $data)
                    );
                    $imported++;
                }
                
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Import thành công {$imported}/{$totalSheets} quẻ!");

            return 0;

        } catch (\Exception $e) {
            $this->error('Lỗi khi đọc file: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Parse dữ liệu từ sheet
     */
    protected function parseSheetData($sheet)
    {
        $data = [
            'tong_quan' => null,
            'su_nghiep' => ['tich_cuc' => '', 'tieu_cuc' => ''],
            'tai_chinh' => ['tich_cuc' => '', 'tieu_cuc' => ''],
            'tinh_duyen' => ['tich_cuc' => '', 'tieu_cuc' => ''],
            'suc_khoe' => ['tich_cuc' => '', 'tieu_cuc' => ''],
            'phat_trien_ban_than' => ['tich_cuc' => '', 'tieu_cuc' => ''],
            'ket_noi_xa_hoi' => ['tich_cuc' => '', 'tieu_cuc' => ''],
        ];

        $currentSection = null;
        $currentKey = null;

        // Đọc từng dòng trong sheet
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellA = trim($sheet->getCell("A{$row}")->getValue() ?? '');
            $cellB = trim($sheet->getCell("B{$row}")->getValue() ?? '');
            $cellC = trim($sheet->getCell("C{$row}")->getValue() ?? '');
            
            // Xác định section từ cột A
            $isSectionHeader = false;
            if (stripos($cellA, 'TỔNG QUAN') !== false) {
                $currentSection = 'tong_quan';
                $currentKey = null;
                $isSectionHeader = true;
            } elseif (stripos($cellA, 'SỰ NGHIỆP') !== false) {
                $currentSection = 'su_nghiep';
                $currentKey = null;
                $isSectionHeader = true;
            } elseif (stripos($cellA, 'TÀI CHÍNH') !== false) {
                $currentSection = 'tai_chinh';
                $currentKey = null;
                $isSectionHeader = true;
            } elseif (stripos($cellA, 'TÌNH DUYÊN') !== false) {
                $currentSection = 'tinh_duyen';
                $currentKey = null;
                $isSectionHeader = true;
            } elseif (stripos($cellA, 'SỨC KH') !== false) { // Match both SỨC KHOẺ and SỨC KHỎE
                $currentSection = 'suc_khoe';
                $currentKey = null;
                $isSectionHeader = true;
            } elseif (stripos($cellA, 'PHÁT TRIỂN BẢN THÂN') !== false) {
                $currentSection = 'phat_trien_ban_than';
                $currentKey = null;
                $isSectionHeader = true;
            } elseif (stripos($cellA, 'KẾT NỐI XÃ HỘI') !== false) {
                $currentSection = 'ket_noi_xa_hoi';
                $currentKey = null;
                $isSectionHeader = true;
            }

            // Xác định tích cực / tiêu cực từ cột B
            if (stripos($cellB, 'Tích cực') !== false) {
                $currentKey = 'tich_cuc';
            } elseif (stripos($cellB, 'Tiêu cực') !== false) {
                $currentKey = 'tieu_cuc';
            }

            // Nếu là section header mới và có cột B, C thì xử lý luôn (không continue)
            // Nếu là section header nhưng không có content thì continue
            if ($isSectionHeader && empty($cellC)) {
                continue;
            }

            // Lấy nội dung từ cột C
            if ($currentSection && !empty($cellC)) {
                if ($currentSection === 'tong_quan') {
                    if ($data['tong_quan'] === null) {
                        $data['tong_quan'] = $cellC;
                    } else {
                        $data['tong_quan'] .= "\n" . $cellC;
                    }
                } elseif ($currentKey && isset($data[$currentSection][$currentKey])) {
                    if (!empty($data[$currentSection][$currentKey])) {
                        $data[$currentSection][$currentKey] .= "\n" . $cellC;
                    } else {
                        $data[$currentSection][$currentKey] = $cellC;
                    }
                }
            }
        }

        // Trim tất cả giá trị
        if ($data['tong_quan']) {
            $data['tong_quan'] = trim($data['tong_quan']);
        }
        
        foreach (['su_nghiep', 'tai_chinh', 'tinh_duyen', 'suc_khoe', 'phat_trien_ban_than', 'ket_noi_xa_hoi'] as $section) {
            $data[$section]['tich_cuc'] = trim($data[$section]['tich_cuc']);
            $data[$section]['tieu_cuc'] = trim($data[$section]['tieu_cuc']);
        }

        return $data;
    }
}
