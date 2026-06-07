<?php

namespace App\Console\Commands;

use App\Models\Phan7BaiHoc;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan7BaiHoc extends Command
{
    protected $signature = 'import:phan7-bai-hoc
        {file? : Đường dẫn file PHẦN 7 - II. BÀI HỌC CUỘC SỐNG VÀ SỰ CHUYỂN HÓA.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 7 - Mục II (Bài học cuộc sống và sự chuyển hóa) từ file Excel vào bảng phan7_bai_hoc';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 7 - II. BÀI HỌC CUỘC SỐNG VÀ SỰ CHUYỂN HÓA.xlsx'
        );

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file trong imports/ hoặc: php artisan import:phan7-bai-hoc <đường_dẫn_file>');
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

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetTitle = trim((string) $sheet->getTitle());
                $thapThan = $this->parseThapThan($sheetTitle);

                if ($thapThan === null) {
                    $this->warn("Bỏ qua sheet không nhận dạng được: '{$sheetTitle}'");
                    continue;
                }

                $this->info("  Đang xử lý sheet: {$sheetTitle} → {$thapThan}");

                $highestRow = $sheet->getHighestRow();
                $currentTruongHop = null;
                $currentTieuDe = null;
                $thuTu = 1;

                for ($row = 1; $row <= $highestRow; $row++) {
                    $colA = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());
                    $colB = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
                    $colC = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());

                    // Carry-forward: cập nhật ten_truong_hop và tieu_de khi có giá trị mới
                    if ($colA !== '') {
                        $currentTruongHop = $colA;
                        $currentTieuDe = null;
                    }
                    if ($colB !== '') {
                        $currentTieuDe = $colB;
                    }

                    // Chỉ tạo bản ghi khi có nội dung ở cột C
                    if ($colC === '' || $currentTruongHop === null) {
                        continue;
                    }

                    Phan7BaiHoc::create([
                        'thap_than'      => $thapThan,
                        'ten_truong_hop' => $currentTruongHop,
                        'tieu_de'        => $currentTieuDe,
                        'noi_dung'       => $colC,
                        'thu_tu'         => $thuTu,
                    ]);
                    $totalInserted++;
                    $thuTu++;
                }
            }

            $this->info("Import thành công {$totalInserted} dòng vào phan7_bai_hoc.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }

    /**
     * Parse tên Thập Thần từ tiêu đề sheet.
     * VD: "1. HUYNH ĐỆ" → "HUYNH ĐỆ", "2. TỬ TÔN" → "TỬ TÔN"
     */
    private function parseThapThan(string $sheetTitle): ?string
    {
        // Bỏ số thứ tự đầu "1. ", "2. " ...
        $name = preg_replace('/^\d+\.\s*/', '', $sheetTitle);
        $name = trim((string) $name);

        $known = ['HUYNH ĐỆ', 'TỬ TÔN', 'THÊ TÀI', 'QUAN QUỶ', 'PHỤ MẪU'];
        foreach ($known as $k) {
            if (mb_strtoupper($name) === $k || mb_strpos(mb_strtoupper($sheetTitle), $k) !== false) {
                return $k;
            }
        }

        return null;
    }
}
