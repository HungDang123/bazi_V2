<?php

namespace App\Console\Commands;

use App\Models\DongChayGioiThieu;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan8GioiThieu extends Command
{
    protected $signature = 'import:phan8-gioi-thieu
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu PHẦN 8 cũ trước khi import}';

    protected $description = 'Import nội dung giới thiệu PHẦN 8 (Đại Vận / Niên Vận) từ PHẦN 8A.xlsx hoặc PHẦN 8 - CODING LOGIC.xlsx';

    /**
     * Sheets đơn giản (toàn bộ nội dung → 1 record): sheet_index → tru_loai
     */
    protected array $simpleSheetMap = [
        1 => 'dai_van_y_nghia',
        2 => 'dai_van_tru_nam',
        3 => 'dai_van_tru_thang',
        4 => 'dai_van_tru_ngay',
        5 => 'dai_van_tru_gio',
        6 => 'nien_van_y_nghia',
        9 => 'nhung_nam_can_chu_y',
    ];

    /**
     * Sheets Niên Vận cần tách theo Trụ: sheet_index → prefix của tru_loai
     * Mỗi sheet sẽ tạo ra:
     *   {prefix}          ← phần giới thiệu chung (trước section Trụ đầu tiên)
     *   {prefix}_tru_nam
     *   {prefix}_tru_thang
     *   {prefix}_tru_ngay
     *   {prefix}_tru_gio
     */
    protected array $nienVanSheetMap = [
        7 => 'nien_van_hien_tai',
        8 => 'nien_van_tiep_theo',
    ];

    /** Keyword trong col A để nhận diện bắt đầu section Trụ */
    protected array $truKeywords = [
        'tru_nam'   => ['TRỤ NĂM'],
        'tru_thang' => ['TRỤ THÁNG'],
        'tru_ngay'  => ['TRỤ NGÀY'],
        'tru_gio'   => ['TRỤ GIỜ'],
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $filePath = $this->argument('file')
            ?? base_path('PHẦN 8A.xlsx');

        if (! file_exists($filePath)) {
            $filePath = base_path('PHẦN 8 - CODING LOGIC.xlsx');
        }

        if (! file_exists($filePath)) {
            $filePath = base_path('database/PHẦN 8 - CODING LOGIC.xlsx');
        }

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file tại thư mục gốc hoặc truyền đường dẫn: php artisan import:phan8-gioi-thieu <đường_dẫn>');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                $allKeys = array_values($this->simpleSheetMap);
                foreach (array_values($this->nienVanSheetMap) as $prefix) {
                    $allKeys[] = $prefix;
                    foreach (array_keys($this->truKeywords) as $truKey) {
                        $allKeys[] = $prefix . '_' . $truKey;
                    }
                }
                $allKeys = array_merge($allKeys, [
                    'nhung_nam_ghi_chu_khac_xung',
                    'nhung_nam_ghi_chu_trung',
                    'transition_phan9a',
                ]);
                foreach ($allKeys as $key) {
                    DongChayGioiThieu::where('tru_loai', $key)->delete();
                }
                $this->info('Đã xóa dữ liệu PHẦN 8 cũ.');
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            if ($this->isPhan8aFormat($spreadsheet)) {
                $count = $this->importPhan8aFormat($spreadsheet);
                $this->info("Hoàn thành (PHẦN 8A)! Tổng {$count} mục đã import.");

                return 0;
            }

            $count = 0;

            // --- Import sheets đơn giản (PHẦN 8 - CODING LOGIC) ---
            foreach ($this->simpleSheetMap as $sheetIdx => $truLoai) {
                if ($sheetIdx >= $spreadsheet->getSheetCount()) {
                    $this->warn("Sheet $sheetIdx không tồn tại, bỏ qua.");
                    continue;
                }
                $lines = $this->readAllLines($spreadsheet->getSheet($sheetIdx));
                if (empty($lines)) {
                    $this->warn("Sheet $sheetIdx ($truLoai): không có nội dung.");
                    continue;
                }
                DongChayGioiThieu::updateOrCreate(
                    ['tru_loai' => $truLoai],
                    ['noi_dung' => implode("\n\n", $lines)]
                );
                $count++;
                $this->info("Sheet $sheetIdx ($truLoai): " . count($lines) . ' đoạn.');
            }

            // --- Import sheets Niên Vận (tách theo Trụ) ---
            foreach ($this->nienVanSheetMap as $sheetIdx => $prefix) {
                if ($sheetIdx >= $spreadsheet->getSheetCount()) {
                    $this->warn("Sheet $sheetIdx không tồn tại, bỏ qua.");
                    continue;
                }
                $sheet = $spreadsheet->getSheet($sheetIdx);
                $title = $sheet->getTitle();
                $sections = $this->splitByTru($sheet);

                // Lưu phần giới thiệu chung
                if (! empty($sections['intro'])) {
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => $prefix],
                        ['noi_dung' => implode("\n\n", $sections['intro'])]
                    );
                    $count++;
                    $this->info("Sheet '$title' ($prefix): intro " . count($sections['intro']) . ' đoạn.');
                }

                // Lưu từng Trụ
                foreach (array_keys($this->truKeywords) as $truKey) {
                    $lines = $sections[$truKey] ?? [];
                    if (empty($lines)) {
                        $this->warn("Sheet '$title': không tìm thấy section $truKey.");
                        continue;
                    }
                    $truLoai = $prefix . '_' . $truKey;
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => $truLoai],
                        ['noi_dung' => implode("\n\n", $lines)]
                    );
                    $count++;
                    $this->info("Sheet '$title' ($truLoai): " . count($lines) . ' đoạn.');
                }
            }

            $this->info("Hoàn thành! Tổng {$count} mục đã import.");
            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Đọc tất cả dòng không rỗng từ sheet (dừng sau 20 dòng trống liên tiếp).
     * Trả về mảng các chuỗi.
     */
    protected function readAllLines(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $lines = [];
        $emptyStreak = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowLines = [];
            for ($col = 'A'; $col <= $highestCol; $col++) {
                $val = trim((string) $sheet->getCell($col . $row)->getValue());
                if ($val !== '') {
                    $rowLines[] = $val;
                }
            }
            if (! empty($rowLines)) {
                array_push($lines, ...$rowLines);
                $emptyStreak = 0;
            } else {
                $emptyStreak++;
                if ($emptyStreak >= 20) {
                    break;
                }
            }
        }

        return $lines;
    }

    /**
     * Tách nội dung sheet Niên Vận thành các section theo Trụ.
     * Trả về ['intro' => [...], 'tru_nam' => [...], 'tru_thang' => [...], ...]
     */
    protected function splitByTru(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        $sections = ['intro' => [], 'tru_nam' => [], 'tru_thang' => [], 'tru_ngay' => [], 'tru_gio' => []];
        $currentSection = 'intro';
        $emptyStreak = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            // Lấy giá trị col A để phát hiện section header
            $colA = mb_strtoupper(trim((string) $sheet->getCell('A' . $row)->getValue()));

            // Kiểm tra xem dòng này có phải section header không
            $detectedTru = null;
            foreach ($this->truKeywords as $truKey => $keywords) {
                foreach ($keywords as $kw) {
                    if (mb_strpos($colA, mb_strtoupper($kw)) !== false) {
                        $detectedTru = $truKey;
                        break 2;
                    }
                }
            }
            if ($detectedTru !== null) {
                $currentSection = $detectedTru;
                $emptyStreak = 0;
                // Không bỏ qua dòng này – đọc nội dung của nó luôn
            }

            // Đọc tất cả các cell trong dòng
            $rowLines = [];
            for ($col = 'A'; $col <= $highestCol; $col++) {
                $val = trim((string) $sheet->getCell($col . $row)->getValue());
                if ($val !== '') {
                    $rowLines[] = $val;
                }
            }

            if (! empty($rowLines)) {
                array_push($sections[$currentSection], ...$rowLines);
                $emptyStreak = 0;
            } else {
                $emptyStreak++;
                if ($emptyStreak >= 20) {
                    break;
                }
            }
        }

        return $sections;
    }

    protected function isPhan8aFormat(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): bool
    {
        $title = trim((string) ($spreadsheet->getSheet(0)?->getTitle() ?? ''));

        return mb_stripos($title, 'ĐẠI VẬN') !== false;
    }

    /**
     * Import PHẦN 8A.xlsx (4 sheet: I Đại Vận, II Niên Vận, III Năm chú ý, Đoạn nối).
     */
    protected function importPhan8aFormat(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet): int
    {
        $count = 0;

        $daiVanSheet = $spreadsheet->getSheetByName('I. ĐẠI VẬN') ?? $spreadsheet->getSheet(0);
        $daiVanParts = $this->splitPhan8aByHeaders($daiVanSheet, [
            ['pattern' => '/1\.\s*Ý\s*NGHĨA\s*ĐẠI\s*VẬN/ui', 'key' => 'dai_van_y_nghia'],
            ['pattern' => '/2\.\s*SỰ\s*TƯƠNG\s*TÁC.*TRỤ\s*NĂM/ui', 'key' => 'dai_van_tru_nam'],
            ['pattern' => '/3\.\s*SỰ\s*TƯƠNG\s*TÁC.*TRỤ\s*THÁNG/ui', 'key' => 'dai_van_tru_thang'],
            ['pattern' => '/4\.\s*SỰ\s*TƯƠNG\s*TÁC.*TRỤ\s*NGÀY/ui', 'key' => 'dai_van_tru_ngay'],
            ['pattern' => '/5\.\s*SỰ\s*TƯƠNG\s*TÁC.*TRỤ\s*GIỜ/ui', 'key' => 'dai_van_tru_gio'],
        ]);
        foreach ($daiVanParts as $key => $lines) {
            if (empty($lines)) {
                continue;
            }
            DongChayGioiThieu::updateOrCreate(['tru_loai' => $key], ['noi_dung' => implode("\n\n", $lines)]);
            $count++;
            $this->info("I. ĐẠI VẬN ({$key}): " . count($lines) . ' đoạn.');
        }

        $nienSheet = $spreadsheet->getSheetByName('II. NĂM HIỆN TẠI');
        if ($nienSheet !== null) {
            $nienParts = $this->splitPhan8aByHeaders($nienSheet, [
                ['pattern' => '/^II\.\s*NIÊN\s*VẬN/ui', 'key' => 'nien_van_y_nghia', 'until' => '/1\.\s*Sự\s*tương\s*tác/ui'],
                ['pattern' => '/1\.\s*Sự\s*tương\s*tác.*TRỤ\s*\[?NĂM\]?/ui', 'key' => 'nien_van_hien_tai_tru_nam'],
                ['pattern' => '/2\.\s*Sự\s*tương\s*tác.*TRỤ\s*\[?THÁNG\]?/ui', 'key' => 'nien_van_hien_tai_tru_thang'],
                ['pattern' => '/3\.\s*Sự\s*tương\s*tác.*TRỤ\s*\[?NGÀY\]?/ui', 'key' => 'nien_van_hien_tai_tru_ngay'],
                ['pattern' => '/4\.\s*Sự\s*tương\s*tác.*TRỤ\s*\[?GIỜ\]?/ui', 'key' => 'nien_van_hien_tai_tru_gio'],
            ]);
            foreach ($nienParts as $key => $lines) {
                if (empty($lines)) {
                    continue;
                }
                DongChayGioiThieu::updateOrCreate(['tru_loai' => $key], ['noi_dung' => implode("\n\n", $lines)]);
                $count++;
                $this->info("II. NĂM HIỆN TẠI ({$key}): " . count($lines) . ' đoạn.');
            }
        }

        $chuYSheet = $spreadsheet->getSheetByName('III. NHỮNG NĂM CẦN CHÚ Ý');
        if ($chuYSheet !== null) {
            $lines = $this->readAllLines($chuYSheet);
            if (! empty($lines)) {
                DongChayGioiThieu::updateOrCreate(
                    ['tru_loai' => 'nhung_nam_can_chu_y'],
                    ['noi_dung' => implode("\n\n", $lines)]
                );
                $count++;
            }
            $khacXung = $this->readCellLines($chuYSheet, 7, 'C');
            $trung = $this->readCellLines($chuYSheet, 10, 'C');
            if (! empty($khacXung)) {
                DongChayGioiThieu::updateOrCreate(
                    ['tru_loai' => 'nhung_nam_ghi_chu_khac_xung'],
                    ['noi_dung' => implode("\n\n", $khacXung)]
                );
                $count++;
            }
            if (! empty($trung)) {
                DongChayGioiThieu::updateOrCreate(
                    ['tru_loai' => 'nhung_nam_ghi_chu_trung'],
                    ['noi_dung' => implode("\n\n", $trung)]
                );
                $count++;
            }
        }

        $transition = $spreadsheet->getSheetByName('ĐOẠN NỐI QUA 9A');
        if ($transition !== null) {
            $lines = $this->readAllLines($transition);
            if (! empty($lines)) {
                DongChayGioiThieu::updateOrCreate(
                    ['tru_loai' => 'transition_phan9a'],
                    ['noi_dung' => implode("\n\n", $lines)]
                );
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<int, array{pattern: string, key: string, until?: string}>  $rules
     * @return array<string, array<int, string>>
     */
    protected function splitPhan8aByHeaders(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        array $rules
    ): array {
        $result = [];
        foreach ($rules as $rule) {
            $result[$rule['key']] = [];
        }

        $currentKey = null;
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $emptyStreak = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = mb_strtoupper(trim((string) $sheet->getCell('A' . $row)->getValue()));
            $matched = null;
            foreach ($rules as $rule) {
                if (preg_match($rule['pattern'], $colA)) {
                    $matched = $rule['key'];
                    break;
                }
            }
            if ($matched !== null) {
                $currentKey = $matched;
                $emptyStreak = 0;
            }

            if ($currentKey === null) {
                continue;
            }

            $rowLines = [];
            for ($col = 'A'; $col <= $highestCol; $col++) {
                $val = trim((string) $sheet->getCell($col . $row)->getValue());
                if ($val !== '') {
                    $rowLines[] = $val;
                }
            }

            if (! empty($rowLines)) {
                array_push($result[$currentKey], ...$rowLines);
                $emptyStreak = 0;
            } else {
                $emptyStreak++;
                if ($emptyStreak >= 20) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    protected function readCellLines(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $row,
        string $col
    ): array {
        $lines = [];
        $highestRow = $sheet->getHighestRow();
        for ($r = $row; $r <= min($row + 3, $highestRow); $r++) {
            $val = trim((string) $sheet->getCell($col . $r)->getValue());
            if ($val !== '') {
                $lines[] = $val;
            }
        }

        return $lines;
    }
}
