<?php

namespace App\Console\Commands;

use App\Models\Phan8aThapThan;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportPhan8aThapThan extends Command
{
    protected $signature = 'import:phan8a-thap-than
        {file? : Đường dẫn file Excel}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 8A - THẬP THẦN (New).xlsx (Đại Vận/Niên Vận × Trụ)';

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

    protected array $viTriMap = [
        'NĂM' => 'Trụ Năm',
        'THÁNG' => 'Trụ Tháng',
        'NGÀY' => 'Trụ Ngày',
        'GIỜ' => 'Trụ Giờ',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $filePath = $this->argument('file')
            ?? base_path('PHẦN 8A - THẬP THẦN (New).xlsx');

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");

            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            if ($this->option('fresh')) {
                Phan8aThapThan::query()->delete();
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $imported = $this->importSpreadsheet($spreadsheet);
            $this->info("Hoàn thành! Tổng {$imported} mục đã import.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
    }

    protected function importSpreadsheet(Spreadsheet $spreadsheet): int
    {
        $imported = 0;

        foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
            $thapThan = $this->normalizeThapThan(trim((string) $sheet->getTitle()));
            if ($thapThan === '') {
                $this->warn("Bỏ qua sheet '{$sheet->getTitle()}'.");

                continue;
            }

            $curViTri = 'Trụ Năm';
            $curViTriTac = null;
            $curLoai = null;
            $curTieuDe = null;
            $curSection = null;
            $sortBase = ($sheetIndex + 1) * 100000;

            for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
                $colA = $this->trimVal($sheet->getCell("A{$row}")->getValue());
                $colB = $this->trimVal($sheet->getCell("B{$row}")->getValue());
                $colC = $this->trimVal($sheet->getCell("C{$row}")->getValue());
                $colD = $this->trimVal($sheet->getCell("D{$row}")->getValue());

                $parsedViTri = $this->parseViTriFromA($colA);
                if ($parsedViTri !== null) {
                    $curViTri = $parsedViTri;
                    $curViTriTac = null;
                    $curLoai = null;
                    $curTieuDe = null;
                    $curSection = null;
                }

                $parsedB = $this->parseLoaiTuongTac($colB);
                if ($parsedB !== null) {
                    $curViTriTac = $parsedB['vi_tri_tuong_tac'];
                    $curLoai = $parsedB['loai_tuong_tac'];
                    $curTieuDe = $colC !== '' ? $colC : null;
                    $curSection = null;
                } elseif ($colC !== '' && $this->looksLikeTieuDe($colC)) {
                    $curTieuDe = $colC;
                }

                $section = $this->parseSectionKey($colC);
                if ($section !== null && $colD !== '') {
                    $this->upsertField(
                        $thapThan,
                        $curViTri,
                        $curViTriTac ?? 'Thiên Can',
                        $curLoai ?? 'Hợp',
                        $curTieuDe,
                        $section,
                        $colD,
                        $sortBase + $row
                    );
                    $imported++;
                }
            }

            $this->info("Sheet '{$sheet->getTitle()}' ({$thapThan}): đã import.");
        }

        return $imported;
    }

    protected function upsertField(
        string $thapThan,
        string $viTri,
        string $viTriTac,
        string $loai,
        ?string $tieuDe,
        string $field,
        string $content,
        int $sortOrder
    ): void {
        $record = Phan8aThapThan::firstOrNew([
            'thap_than' => $thapThan,
            'vi_tri' => $viTri,
            'vi_tri_tuong_tac' => $viTriTac,
            'loai_tuong_tac' => $loai,
        ]);

        if ($tieuDe !== null && trim((string) $record->tieu_de) === '') {
            $record->tieu_de = $tieuDe;
        }

        $record->{$field} = $content;
        $record->sort_order = $sortOrder;
        $record->save();
    }

    protected function parseViTriFromA(string $colA): ?string
    {
        if (! preg_match('/TRỤ\s*\[([^\]]+)\]/u', mb_strtoupper($colA), $m)) {
            return null;
        }

        $key = mb_strtoupper(trim($m[1]));

        return $this->viTriMap[$key] ?? null;
    }

    protected function parseLoaiTuongTac(string $colB): ?array
    {
        $b = mb_strtoupper(trim($colB));
        if ($b === '') {
            return null;
        }

        if (mb_strpos($b, 'THIÊN CAN') !== false) {
            $viTriTac = 'Thiên Can';
            if (mb_strpos($b, 'KHẮC') !== false) {
                return ['vi_tri_tuong_tac' => $viTriTac, 'loai_tuong_tac' => 'Khắc'];
            }
            if (mb_strpos($b, 'HỢP') !== false) {
                return ['vi_tri_tuong_tac' => $viTriTac, 'loai_tuong_tac' => 'Hợp'];
            }
        }

        if (mb_strpos($b, 'ĐỊA CHI') !== false) {
            $viTriTac = 'Địa Chi';
            foreach (['XUNG' => 'Xung', 'HÌNH' => 'Hình', 'HẠI' => 'Hại', 'PHÁ' => 'Phá', 'HỢP' => 'Hợp'] as $kw => $loai) {
                if (mb_strpos($b, $kw) !== false) {
                    return ['vi_tri_tuong_tac' => $viTriTac, 'loai_tuong_tac' => $loai];
                }
            }
        }

        return null;
    }

    protected function parseSectionKey(string $colC): ?string
    {
        $c = mb_strtolower(trim($colC));
        if (mb_strpos($c, 'sự kiện cơ hội') !== false) {
            return 'su_kien_co_hoi';
        }
        if (mb_strpos($c, 'quản trị rủi ro') !== false) {
            return 'quan_tri_rui_ro';
        }
        if (mb_strpos($c, 'chiến lược') !== false) {
            return 'chien_luoc';
        }

        return null;
    }

    protected function looksLikeTieuDe(string $colC): bool
    {
        return mb_strpos(mb_strtolower($colC), 'bài học') !== false
            || mb_strpos($colC, '–') !== false
            || mb_strpos($colC, '-') !== false;
    }

    protected function normalizeThapThan(string $name): string
    {
        $key = mb_strtoupper(trim($name));

        return $this->thapThanMap[$key] ?? $name;
    }

    protected function trimVal($val): string
    {
        return trim((string) ($val ?? ''));
    }
}
