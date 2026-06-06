<?php

namespace App\Console\Commands;

use App\Models\Phan7BaiHoc;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan7BaiHoc extends Command
{
    protected $signature = 'import:phan7-bai-hoc
        {file? : Đường dẫn file PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 7 - Sheets 2-7 (II đến VII) từ file Excel vào bảng phan7_bai_hoc';

    private const THAP_THAN_NAMES = ['PHỤ MẪU', 'QUAN QUỶ', 'THÊ TÀI', 'TỬ TÔN', 'HUYNH ĐỆ'];

    public function handle(): int
    {
        $filePath = $this->argument('file')
            ?? base_path('PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx');

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file tại thư mục gốc hoặc: php artisan import:phan7-bai-hoc <đường_dẫn_file>');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                Phan7BaiHoc::truncate();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $totalInserted = 0;

            // Sheets 2-7 = index 1 đến 6
            for ($sheetIdx = 1; $sheetIdx <= 6; $sheetIdx++) {
                $sheet = $spreadsheet->getSheet($sheetIdx);
                $phan = trim((string) $sheet->getTitle());
                $highestRow = $sheet->getHighestRow();

                if ($highestRow < 2) {
                    continue;
                }

                // Row 1: Tổng Quan
                $b1 = trim((string) $sheet->getCell('B1')->getCalculatedValue());
                $e1 = $this->getCellE($sheet, 1);
                if ($b1 !== '' || $e1 !== '') {
                    Phan7BaiHoc::create([
                        'phan' => $phan,
                        'loai' => $b1 !== '' ? $b1 : 'Tổng Quan',
                        'thap_than' => null,
                        'gioi_tinh' => null,
                        'ten_truong_hop' => null,
                        'noi_dung' => $e1,
                        'thu_tu' => 1,
                    ]);
                    $totalInserted++;
                }

                // Row 2: Nguyên tắc cốt lõi
                $b2 = trim((string) $sheet->getCell('B2')->getCalculatedValue());
                $e2 = $this->getCellE($sheet, 2);
                if ($b2 !== '' || $e2 !== '') {
                    Phan7BaiHoc::create([
                        'phan' => $phan,
                        'loai' => $b2 !== '' ? $b2 : 'Nguyên tắc cốt lõi',
                        'thap_than' => null,
                        'gioi_tinh' => null,
                        'ten_truong_hop' => null,
                        'noi_dung' => $e2,
                        'thu_tu' => 2,
                    ]);
                    $totalInserted++;
                }

                // Rows 3+: Các trường hợp (Thập Thần + ten_truong_hop + noi_dung)
                $currentThapThan = null;
                $currentGioiTinh = null;
                $thuTu = 3;

                for ($row = 3; $row <= $highestRow; $row++) {
                    $b = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
                    $c = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());
                    $d = trim((string) $sheet->getCell('D' . $row)->getCalculatedValue());
                    $e = $this->getCellE($sheet, $row);

                    if ($c !== '') {
                        $currentThapThan = $this->parseThapThan($c);
                        $currentGioiTinh = $this->parseGioiTinh($c);
                    }

                    // Chỉ tạo bản ghi khi có nội dung (D hoặc E)
                    if ($d === '' && $e === '') {
                        continue;
                    }

                    $loai = $b !== '' ? $b : 'Các trường hợp';
                    if ($loai !== 'Các trường hợp' && $loai !== 'Cách trường hợp') {
                        // Hàng đầu của block "Các trường hợp"
                        $loai = 'Các trường hợp';
                    }

                    Phan7BaiHoc::create([
                        'phan' => $phan,
                        'loai' => 'Các trường hợp',
                        'thap_than' => $currentThapThan,
                        'gioi_tinh' => $currentGioiTinh,
                        'ten_truong_hop' => $d !== '' ? $d : null,
                        'noi_dung' => $e,
                        'thu_tu' => $thuTu,
                    ]);
                    $totalInserted++;
                    $thuTu++;
                }
            }

            $this->info("Import thành công {$totalInserted} mục từ Sheets 2-7 vào phan7_bai_hoc.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }

    private function getCellE($sheet, int $row): string
    {
        $val = $sheet->getCell('E' . $row)->getCalculatedValue();
        return is_string($val) ? trim($val) : (string) $val;
    }

    private function parseThapThan(string $c): ?string
    {
        foreach (self::THAP_THAN_NAMES as $name) {
            if (mb_strpos($c, $name) !== false) {
                return $name;
            }
        }
        return null;
    }

    private function parseGioiTinh(string $c): ?string
    {
        if (mb_strpos($c, 'GIỚI TÍNH: NAM') !== false) {
            return 'NAM';
        }
        if (mb_strpos($c, 'GIỚI TÍNH: NỮ') !== false) {
            return 'NỮ';
        }
        return null;
    }
}
