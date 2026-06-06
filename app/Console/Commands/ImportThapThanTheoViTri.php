<?php

namespace App\Console\Commands;

use App\Models\ThapThanTheoViTri;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportThapThanTheoViTri extends Command
{
    protected $signature = 'import:thap-than-theo-vi-tri
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Thập Thần theo khía cạnh từ PHẦN 5 - THẬP THẦN (New).xlsx (hoặc file cũ theo từng vị trí)';

    /** Sheet New → khia_canh trong DB (khớp TongQuanKhiaCanhController) */
    protected array $sheetKhiaCanh = [
        'II. SỰ NGHIỆP' => ['Sự Nghiệp'],
        'III. TÀI CHÍNH' => ['Tài Chính'],
        'IV. TÌNH DUYÊN' => ['Tình Cảm'],
        'VI. PHÁT TRIỂN BẢN THÂN' => ['Phát triển bản thân', 'Tính cách'],
        'VII. KẾT NỐI XÃ HỘI' => ['Mối quan hệ xã hội', 'Tính cách: tính cách thể hiện ra ngoài xã hội'],
    ];

    protected array $thapThanMap = [
        'TỶ KIÊN' => 'Tỷ Kiên',
        'KIẾP TÀI' => 'Kiếp Tài',
        'THƯƠNG QUAN' => 'Thương Quan',
        'THỰC THẦN' => 'Thực Thần',
        'CHÍNH TÀI' => 'Chính Tài',
        'THIÊN TÀI' => 'Thiên Tài',
        'CHÍNH QUAN' => 'Chính Quan',
        'THẤT SÁT' => 'Thất Sát',
        'CHÍNH ẤN' => 'Chính Ấn',
        'THIÊN ẤN' => 'Thiên Ấn',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');

        $filePath = ImportPath::resolveFirst($this->argument('file'), [
            'PHẦN 5 - THẬP THẦN (New).xlsx',
            'PHẦN 5 - II, III, IV, V, VI, VII- THẬP THẦN THEO TỪNG VỊ TRÍ.xlsx',
        ]);

        if ($filePath === null) {
            $this->error('File không tồn tại: '.ImportPath::file('PHẦN 5 - THẬP THẦN (New).xlsx'));

            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                ThapThanTheoViTri::truncate();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $imported = $this->isNewFormat($spreadsheet)
                ? $this->importNewFormat($spreadsheet)
                : $this->importLegacyFormat($spreadsheet);

            $this->info("Hoàn thành! Tổng {$imported} mục đã import.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
    }

    protected function isNewFormat(Spreadsheet $spreadsheet): bool
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $title = trim((string) $sheet->getTitle());
            if (isset($this->sheetKhiaCanh[$title])) {
                return true;
            }
        }

        return false;
    }

    protected function importNewFormat(Spreadsheet $spreadsheet): int
    {
        $imported = 0;

        foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
            $sheetName = trim((string) $sheet->getTitle());
            $khiaCanhTargets = $this->sheetKhiaCanh[$sheetName] ?? null;

            if ($khiaCanhTargets === null) {
                $this->warn("Bỏ qua sheet '{$sheetName}' (không map khía cạnh).");

                continue;
            }

            $isTaiChinh = in_array('Tài Chính', $khiaCanhTargets, true);
            $sortBase = ($sheetIndex + 1) * 100000;

            $curViTri = null;
            $curLoaiCan = null;
            $curThapThan = null;

            for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
                $colA = $this->trimVal($sheet->getCell("A{$row}")->getValue());
                $colB = $this->trimVal($sheet->getCell("B{$row}")->getValue());
                $colC = $this->trimVal($sheet->getCell("C{$row}")->getValue());
                $colD = $this->trimVal($sheet->getCell("D{$row}")->getValue());

                $parsed = $this->parseViTriLoaiCan($colA);
                if ($parsed !== null) {
                    $curViTri = $parsed['vi_tri'];
                    $curLoaiCan = $parsed['loai_can'];
                }

                if ($colB !== '') {
                    $curThapThan = $this->normalizeThapThan($colB);
                }

                if ($curThapThan === '' || $curViTri === null || $colD === '') {
                    continue;
                }

                if ($isTaiChinh) {
                    $content = $colC !== '' ? trim($colC . "\n" . $colD) : $colD;
                    ThapThanTheoViTri::create([
                        'thap_than' => $curThapThan,
                        'vi_tri' => $curViTri,
                        'loai_can' => $curLoaiCan,
                        'khia_canh' => 'Tài Chính',
                        'huong' => null,
                        'content' => $content,
                        'sort_order' => $sortBase + $row,
                    ]);
                    $imported++;

                    continue;
                }

                $huong = $this->parseHuong($colC);
                if ($huong === null) {
                    continue;
                }

                foreach ($khiaCanhTargets as $khiaCanh) {
                    ThapThanTheoViTri::updateOrCreate(
                        [
                            'thap_than' => $curThapThan,
                            'vi_tri' => $curViTri,
                            'loai_can' => $curLoaiCan,
                            'khia_canh' => $khiaCanh,
                            'huong' => $huong,
                        ],
                        [
                            'content' => $colD,
                            'sort_order' => $sortBase + $row,
                        ]
                    );
                    $imported++;
                }
            }

            $this->info("Sheet '{$sheetName}': đã import.");
        }

        return $imported;
    }

    /** Import file cũ: 10 sheet theo Thập Thần, cột F */
    protected function importLegacyFormat(Spreadsheet $spreadsheet): int
    {
        $imported = 0;

        for ($i = 0; $i < $spreadsheet->getSheetCount(); $i++) {
            $sheet = $spreadsheet->getSheet($i);
            $sheetName = trim((string) $sheet->getTitle());
            $thapThan = $this->normalizeThapThan($sheetName);

            $highestRow = $sheet->getHighestRow();
            $curThapThan = null;
            $curViTri = null;
            $curLoaiCan = null;
            $curKhiaCanh = null;
            $curHuong = null;
            $globalSort = ($i + 1) * 10000;

            for ($row = 1; $row <= $highestRow; $row++) {
                $colA = $this->trimVal($sheet->getCell('A' . $row)->getValue());
                $colB = $this->trimVal($sheet->getCell('B' . $row)->getValue());
                $colC = $this->trimVal($sheet->getCell('C' . $row)->getValue());
                $colD = $this->trimVal($sheet->getCell('D' . $row)->getValue());
                $colE = $this->trimVal($sheet->getCell('E' . $row)->getValue());
                $colF = $this->trimVal($sheet->getCell('F' . $row)->getValue());

                if ($row === 1) {
                    continue;
                }

                if ($colA !== '' && $colA !== '-') {
                    $curThapThan = $this->normalizeThapThan($colA);
                }
                if ($colB !== '' && $colB !== '-') {
                    $curViTri = $colB;
                }
                if ($colC !== '' && $colC !== '-') {
                    $curLoaiCan = $colC;
                }
                if ($colD !== '' && $colD !== '-') {
                    $curKhiaCanh = $colD;
                }
                if ($colE !== '' && $colE !== '-') {
                    $curHuong = $colE;
                }

                if ($curThapThan === null || $curThapThan === '') {
                    $curThapThan = $thapThan;
                }

                if ($colF === '') {
                    continue;
                }

                ThapThanTheoViTri::create([
                    'thap_than' => $curThapThan,
                    'vi_tri' => $curViTri ?: null,
                    'loai_can' => $curLoaiCan ?: null,
                    'khia_canh' => $curKhiaCanh ?: null,
                    'huong' => $curHuong ?: null,
                    'content' => $colF,
                    'sort_order' => $globalSort + $row,
                ]);
                $imported++;
            }

            $this->info("Sheet '{$sheetName}': đã import.");
        }

        return $imported;
    }

    protected function parseViTriLoaiCan(string $colA): ?array
    {
        $a = mb_strtoupper(trim($colA));
        if ($a === '' || $a === 'VỊ TRÍ') {
            return null;
        }

        $viTri = null;
        if (mb_strpos($a, 'TRỤ NĂM') !== false) {
            $viTri = 'Trụ Năm';
        } elseif (mb_strpos($a, 'TRỤ THÁNG') !== false) {
            $viTri = 'Trụ Tháng';
        } elseif (mb_strpos($a, 'TRỤ NGÀY') !== false) {
            $viTri = 'Trụ Ngày';
        } elseif (mb_strpos($a, 'TRỤ GIỜ') !== false) {
            $viTri = 'Trụ Giờ';
        }

        $loaiCan = null;
        if (mb_strpos($a, 'THIÊN CAN') !== false) {
            $loaiCan = 'Thiên Can';
        } elseif (mb_strpos($a, 'TÀNG CAN') !== false) {
            $loaiCan = 'Tàng Can';
        }

        if ($viTri === null || $loaiCan === null) {
            return null;
        }

        return ['vi_tri' => $viTri, 'loai_can' => $loaiCan];
    }

    protected function parseHuong(string $colC): ?string
    {
        $c = mb_strtolower(trim($colC));
        if (mb_strpos($c, 'từ khóa') !== false) {
            return 'Từ khóa cốt lõi';
        }
        if (mb_strpos($c, 'giải nghĩa') !== false) {
            return 'Giải nghĩa năng lượng';
        }
        if (mb_strpos($c, 'tích cực') !== false || mb_strpos($c, 'mặt tích') !== false) {
            return 'Mặt tích cực';
        }
        if (mb_strpos($c, 'tiêu cực') !== false || mb_strpos($c, 'mặt tiêu') !== false) {
            return 'Mặt tiêu cực';
        }
        if (mb_strpos($c, 'chiến lược') !== false) {
            return 'Chiến lược phát triển';
        }

        return null;
    }

    protected function normalizeThapThan(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $key = mb_strtoupper($name);

        return $this->thapThanMap[$key] ?? $name;
    }

    protected function trimVal($val): string
    {
        return trim((string) ($val ?? ''));
    }
}
