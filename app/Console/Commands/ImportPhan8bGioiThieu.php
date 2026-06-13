<?php

namespace App\Console\Commands;

use App\Models\DongChayGioiThieu;
use App\Services\Phan8TruSectionService;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan8bGioiThieu extends Command
{
    protected $signature = 'import:phan8b-gioi-thieu
        {file? : Đường dẫn file PHẦN 8B - I, III.xlsx}
        {--fresh : Xóa dữ liệu PHẦN 8B cũ trước khi import}';

    protected $description = 'Import nội dung giới thiệu PHẦN 8B (Niên Vận tiếp theo / Những năm cần chú ý)';

    protected array $keys8b = [
        'nien_van_8b_y_nghia',
        'nien_van_8b_tiep_theo_tru_nam',
        'nien_van_8b_tiep_theo_tru_thang',
        'nien_van_8b_tiep_theo_tru_ngay',
        'nien_van_8b_tiep_theo_tru_gio',
        'nhung_nam_8b_can_chu_y',
        'nhung_nam_8b_ghi_chu_khac_xung',
        'nhung_nam_8b_ghi_chu_trung',
        'transition_phan9b',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 8B - I, III.xlsx'
        );

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");

            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                foreach ($this->keys8b as $key) {
                    DongChayGioiThieu::where('tru_loai', $key)->delete();
                }
                $this->info('Đã xóa dữ liệu PHẦN 8B cũ.');
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $count = 0;

            $nienSheet = $spreadsheet->getSheetByName('I. NIÊN VẬN TIẾP THEO')
                ?? $spreadsheet->getSheet(0);
            $nienParts = $this->splitByHeaders($nienSheet, [
                ['pattern' => '/^I\.\s*NIÊN\s*VẬN/ui', 'key' => 'nien_van_8b_y_nghia'],
                ['pattern' => '/1\.\s*Sự\s*tương\s*tác.*TRỤ\s*NĂM/ui', 'key' => 'nien_van_8b_tiep_theo_tru_nam'],
                ['pattern' => '/2\.\s*Sự\s*tương\s*tác.*TRỤ\s*THÁNG/ui', 'key' => 'nien_van_8b_tiep_theo_tru_thang'],
                ['pattern' => '/3\.\s*Sự\s*tương\s*tác.*TRỤ\s*NGÀY/ui', 'key' => 'nien_van_8b_tiep_theo_tru_ngay'],
                ['pattern' => '/4\.\s*Sự\s*tương\s*tác.*TRỤ\s*GIỜ/ui', 'key' => 'nien_van_8b_tiep_theo_tru_gio'],
            ]);

            foreach ($nienParts as $key => $lines) {
                if (empty($lines)) {
                    continue;
                }
                DongChayGioiThieu::updateOrCreate(
                    ['tru_loai' => $key],
                    ['noi_dung' => Phan8TruSectionService::cleanTruGioiThieu(implode("\n\n", $lines), $key)]
                );
                $count++;
                $this->info("I. NIÊN VẬN TIẾP THEO ({$key}): " . count($lines) . ' đoạn.');
            }

            $chuYSheet = $spreadsheet->getSheetByName('III. NHỮNG NĂM CẦN CHÚ Ý');
            if ($chuYSheet !== null) {
                $lines = $this->readAllLines($chuYSheet);
                if (! empty($lines)) {
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => 'nhung_nam_8b_can_chu_y'],
                        ['noi_dung' => implode("\n\n", $lines)]
                    );
                    $count++;
                }

                $khacXung = $this->readCellLines($chuYSheet, 7, 'C');
                $trung = $this->readCellLines($chuYSheet, 10, 'C');
                if (! empty($khacXung)) {
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => 'nhung_nam_8b_ghi_chu_khac_xung'],
                        ['noi_dung' => implode("\n\n", $khacXung)]
                    );
                    $count++;
                }
                if (! empty($trung)) {
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => 'nhung_nam_8b_ghi_chu_trung'],
                        ['noi_dung' => implode("\n\n", $trung)]
                    );
                    $count++;
                }
            }

            $transition = $spreadsheet->getSheetByName('ĐOẠN NỐI QUA 9B');
            if ($transition !== null) {
                $lines = $this->readAllLines($transition);
                if (! empty($lines)) {
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => 'transition_phan9b'],
                        ['noi_dung' => implode("\n\n", $lines)]
                    );
                    $count++;
                }
            }

            $this->info("Hoàn thành (PHẦN 8B)! Tổng {$count} mục đã import.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * @param  array<int, array{pattern: string, key: string}>  $rules
     * @return array<string, array<int, string>>
     */
    protected function splitByHeaders(
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
