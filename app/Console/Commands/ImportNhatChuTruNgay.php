<?php

namespace App\Console\Commands;

use App\Models\NhatChuTruNgay;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportNhatChuTruNgay extends Command
{
    protected $signature = 'import:nhat-chu-tru-ngay 
        {dir? : Thư mục chứa 10 file NHẬT CHỦ *.xlsx}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Nhật Chủ Trụ Ngày từ 10 file Excel NHẬT CHỦ ẤT.xlsx, NHẬT CHỦ BÍNH.xlsx, ...';

    /** 10 Thiên Can tương ứng 10 file */
    protected array $thienCanFiles = [
        'ẤT', 'BÍNH', 'CANH', 'ĐINH', 'GIÁP', 'KỶ', 'MẬU', 'NHÂM', 'QUÝ', 'TÂN',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '600');

        $dir = $this->argument('dir') ?: ImportPath::dir();
        if (! is_dir($dir)) {
            $this->error("Thư mục không tồn tại: {$dir}");

            return 1;
        }

        if ($this->option('fresh')) {
            NhatChuTruNgay::truncate();
            $this->info('Đã xóa dữ liệu cũ.');
        }

        $totalImported = 0;

        foreach ($this->thienCanFiles as $thienCan) {
            $file = rtrim($dir, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . "NHẬT CHỦ {$thienCan}.xlsx";
            if (! file_exists($file)) {
                $this->warn("Bỏ qua: {$file} (không tìm thấy).");
                continue;
            }

            try {
                $count = $this->importFile($file, $thienCan);
                $totalImported += $count;
                $this->info("File NHẬT CHỦ {$thienCan}.xlsx: {$count} mục đã import.");
            } catch (\Throwable $e) {
                $this->error("Lỗi file {$file}: " . $e->getMessage());
            }
        }

        $this->info("Hoàn thành! Tổng {$totalImported} mục đã import.");

        return 0;
    }

    protected function importFile(string $filePath, string $thienCan): int
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $count = 0;

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = trim((string) $sheet->getTitle());
            $parts = preg_split('/\s+/u', $sheetName, 2);
            $sheetThienCan = NhatChuTruNgay::normalizeThienCan($parts[0] ?? $thienCan);
            $sheetDiaChi = NhatChuTruNgay::normalizeDiaChi($parts[1] ?? '');

            if ($sheetDiaChi === '') {
                $this->warn("Bỏ qua sheet '{$sheetName}' (không parse được địa chi).");
                continue;
            }

            $highestRow = $sheet->getHighestRow();
            $items = [];
            $cur = ['title' => null, 'chapter' => null, 'sub_title' => null];

            for ($row = 1; $row <= $highestRow; $row++) {
                $colA = trim((string) ($sheet->getCell("A{$row}")->getValue() ?? ''));
                $colB = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
                $colC = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));
                $colD = trim((string) ($sheet->getCell("D{$row}")->getValue() ?? ''));

                if ($colA !== '') {
                    $cur['title'] = $colA;
                }
                if ($colB !== '') {
                    $cur['chapter'] = $colB;
                }
                if ($colC !== '') {
                    $cur['sub_title'] = $colC;
                }

                if ($colD !== '') {
                    $items[] = [
                        'title' => $cur['title'],
                        'chapter' => $cur['chapter'],
                        'sub_title' => $cur['sub_title'],
                        'content' => $colD,
                    ];
                }
            }

            foreach ($items as $item) {
                NhatChuTruNgay::create([
                    'thien_can' => $sheetThienCan,
                    'dia_chi' => $sheetDiaChi,
                    'title' => $item['title'],
                    'chapter' => $item['chapter'],
                    'sub_title' => $item['sub_title'],
                    'content' => $item['content'],
                    'sort_order' => $count,
                ]);
                $count++;
            }
        }

        return $count;
    }

}
