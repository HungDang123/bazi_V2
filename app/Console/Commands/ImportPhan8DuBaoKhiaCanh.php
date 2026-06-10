<?php

namespace App\Console\Commands;

use App\Models\Phan8DuBaoKhiaCanh;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportPhan8DuBaoKhiaCanh extends Command
{
    protected $signature = 'import:phan8-du-bao-khia-canh
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu cũ của phan_ban trước khi import}
        {--phan-ban=8a : 8a (PHẦN 8 III) hoặc 8b (PHẦN 8B II)}';

    protected $description = 'Import dự báo khía cạnh PHẦN 8 vào bảng phan8_du_bao_khia_canh';

    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $phanBan = $this->option('phan-ban') === '8b' ? '8b' : '8a';

        $defaultFile = $phanBan === '8b'
            ? 'PHẦN 8B - II. DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG.xlsx'
            : 'PHẦN 8 - III- DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG.xlsx';

        $filePath = ImportPath::resolve($this->argument('file'), $defaultFile);

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Dùng: php artisan import:phan8-du-bao-khia-canh imports/<file.xlsx> --phan-ban=8b');

            return 1;
        }

        try {
            if ($this->option('fresh')) {
                Phan8DuBaoKhiaCanh::where('phan_ban', $phanBan)->delete();
                $this->info("Đã xóa dữ liệu phan_ban={$phanBan} trong phan8_du_bao_khia_canh.");
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $totalInserted = $phanBan === '8b'
                ? $this->import8bFormat($spreadsheet, $phanBan)
                : $this->import8aFormat($spreadsheet, $phanBan);

            $this->info("Hoàn thành (phan_ban={$phanBan})! Đã import {$totalInserted} bản ghi.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
    }

    protected function import8aFormat(Spreadsheet $spreadsheet, string $phanBan): int
    {
        $totalInserted = 0;

        for ($sheetIdx = 1; $sheetIdx <= 6; $sheetIdx++) {
            if ($sheetIdx >= $spreadsheet->getSheetCount()) {
                break;
            }

            $sheet = $spreadsheet->getSheet($sheetIdx);
            $sheetName = trim((string) $sheet->getTitle());
            $highestRow = $sheet->getHighestRow();

            $khiaCanh = trim((string) $sheet->getCell('A1')->getCalculatedValue());
            if ($khiaCanh === '') {
                $khiaCanh = $sheetName;
            }

            $currentGender = null;
            $currentCase = null;
            $thuTu = 1;

            $flushCurrent = function () use (&$currentCase, &$totalInserted, &$thuTu, $khiaCanh, $sheetName, $phanBan): void {
                if ($currentCase === null) {
                    return;
                }
                $noiDung = trim(implode("\n\n", array_filter($currentCase['parts'], static fn ($x) => trim((string) $x) !== '')));
                if ($noiDung === '' || trim((string) $currentCase['dieu_kien']) === '') {
                    $currentCase = null;

                    return;
                }
                Phan8DuBaoKhiaCanh::create([
                    'phan_ban'   => $phanBan,
                    'khia_canh'  => $khiaCanh,
                    'gioi_tinh'  => $currentCase['gioi_tinh'],
                    'dieu_kien'  => $currentCase['dieu_kien'],
                    'noi_dung'   => $noiDung,
                    'thu_tu'     => $thuTu,
                    'sheet_name' => $sheetName,
                ]);
                $thuTu++;
                $totalInserted++;
                $currentCase = null;
            };

            for ($row = 1; $row <= $highestRow; $row++) {
                $b = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
                $c = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());
                $d = trim((string) $sheet->getCell('D' . $row)->getCalculatedValue());

                if ($b === 'NAM' || $b === 'NỮ') {
                    $currentGender = $b;
                }

                $dieuKien = '';
                if ($c !== '') {
                    $dieuKien = $c;
                } elseif ($b !== '' && $b !== 'NAM' && $b !== 'NỮ') {
                    $dieuKien = $b;
                }

                if ($dieuKien !== '' && $this->looksLikeDieuKien($dieuKien)) {
                    $flushCurrent();
                    $currentCase = [
                        'gioi_tinh' => $currentGender,
                        'dieu_kien' => $dieuKien,
                        'parts' => [],
                    ];
                }

                if ($d !== '' && $currentCase !== null) {
                    $currentCase['parts'][] = $this->stripBullets($d);
                }
            }

            $flushCurrent();
            $this->info("Sheet {$sheetIdx} ({$sheetName}): import xong.");
        }

        return $totalInserted;
    }

    protected function import8bFormat(Spreadsheet $spreadsheet, string $phanBan): int
    {
        $totalInserted = 0;

        for ($sheetIdx = 1; $sheetIdx <= 6; $sheetIdx++) {
            if ($sheetIdx >= $spreadsheet->getSheetCount()) {
                break;
            }

            $sheet = $spreadsheet->getSheet($sheetIdx);
            $sheetName = trim((string) $sheet->getTitle());
            $highestRow = $sheet->getHighestRow();
            $khiaCanh = $this->normalizeKhiaCanhFromSheet($sheetName);

            $currentGender = null;
            $currentCase = null;
            $thuTu = 1;

            $flushCurrent = function () use (&$currentCase, &$totalInserted, &$thuTu, $khiaCanh, $sheetName, $phanBan): void {
                if ($currentCase === null) {
                    return;
                }
                $noiDung = trim(implode("\n\n", array_filter($currentCase['parts'], static fn ($x) => trim((string) $x) !== '')));
                if ($noiDung === '' || trim((string) $currentCase['dieu_kien']) === '') {
                    $currentCase = null;

                    return;
                }
                Phan8DuBaoKhiaCanh::create([
                    'phan_ban'   => $phanBan,
                    'khia_canh'  => $khiaCanh,
                    'gioi_tinh'  => $currentCase['gioi_tinh'],
                    'dieu_kien'  => $currentCase['dieu_kien'],
                    'noi_dung'   => $noiDung,
                    'thu_tu'     => $thuTu,
                    'sheet_name' => $sheetName,
                ]);
                $thuTu++;
                $totalInserted++;
                $currentCase = null;
            };

            for ($row = 1; $row <= $highestRow; $row++) {
                $a = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());
                $b = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
                $c = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());

                if ($a === 'NAM' || $a === 'NỮ') {
                    $currentGender = $a;
                }

                $dieuKien = '';
                $contentCol = '';

                if ($this->looksLikeDieuKien($a)) {
                    $dieuKien = $a;
                    $contentCol = $b;
                } elseif ($this->looksLikeDieuKien($b)) {
                    $dieuKien = $b;
                    $contentCol = $c !== '' ? $c : $b;
                }

                if ($dieuKien !== '') {
                    $flushCurrent();
                    $currentCase = [
                        'gioi_tinh' => $currentGender,
                        'dieu_kien' => $dieuKien,
                        'parts' => [],
                    ];
                    if ($contentCol !== '' && ! $this->looksLikeDieuKien($contentCol)) {
                        $currentCase['parts'][] = $this->stripBullets($contentCol);
                    }
                    continue;
                }

                $extra = $b !== '' ? $b : $c;
                if ($extra !== '' && $currentCase !== null && ! $this->looksLikeDieuKien($extra)) {
                    $currentCase['parts'][] = $this->stripBullets($extra);
                }
            }

            $flushCurrent();
            $this->info("Sheet {$sheetIdx} ({$sheetName}): import xong.");
        }

        return $totalInserted;
    }

    protected function stripBullets(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        $lines = array_map(static function (string $line): string {
            return preg_replace('/^[\x{2022}\x{2013}\x{2014}\-]\s*/u', '', $line) ?? $line;
        }, $lines);

        return implode("\n", array_map('trim', $lines));
    }

    protected function looksLikeDieuKien(string $text): bool
    {
        $u = mb_strtoupper($text);

        if (str_contains($u, 'CHÊNH LỆCH')) {
            return true;
        }

        if (preg_match('/(?:QUAN QUỶ|HUYNH ĐỆ|THÊ TÀI|PHỤ MẪU|TỬ TÔN)\s+NIÊN MỆNH.*(?:>|<|=)/u', $u)) {
            return true;
        }

        if (preg_match('/TRÊN\s+\d+(?:[.,]\d+)?\s*%/u', $u)) {
            return true;
        }

        return false;
    }

    protected function normalizeKhiaCanhFromSheet(string $sheetName): string
    {
        $n = mb_strtolower(trim($sheetName));

        if (str_contains($n, 'sự nghiệp') || str_contains($n, 'su nghiep')) {
            return 'Sự nghiệp';
        }
        if (str_contains($n, 'tài chính') || str_contains($n, 'tai chinh')) {
            return 'Tài chính';
        }
        if (str_contains($n, 'tình duyên') || str_contains($n, 'tinh duyen')) {
            return 'Tình duyên';
        }
        if (str_contains($n, 'sức kh') || str_contains($n, 'suc kh')) {
            return 'Sức khỏe';
        }
        if (str_contains($n, 'phát triển') || str_contains($n, 'phat trien')) {
            return 'Phát triển bản thân';
        }
        if (str_contains($n, 'kết nối') || str_contains($n, 'ket noi')) {
            return 'Kết nối xã hội';
        }

        return $sheetName;
    }
}
