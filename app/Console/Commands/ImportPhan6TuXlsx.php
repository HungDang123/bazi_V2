<?php

namespace App\Console\Commands;

use App\Models\DongChayGioiThieu;
use App\Models\YNghiaTuTru;
use App\Services\DocxTextService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportPhan6TuXlsx extends Command
{
    protected $signature = 'import:phan6-xlsx
        {file? : Đường dẫn PHẦN 6.xlsx}
        {--fresh : Xóa dữ liệu y_nghia_tu_tru và dong_chay_gioi_thieu trước khi import}
        {--from-sheet=8 : Chỉ import từ sheet thứ N (mặc định 8)}';

    protected $description = 'Import Phần 6 (Mã 1 + giới thiệu II–IV) từ PHẦN 6.xlsx, sheet 8 trở đi';

    /** Sheet title → tru_loai */
    protected array $sheetToTruLoai = [
        'II. Trụ Năm - Trụ Tháng' => 'tru_nam_tru_thang',
        'III. Trụ Tháng - Trụ Ngày' => 'tru_thang_tru_ngay',
        'IV. Trụ Ngày - Trụ Giờ' => 'tru_ngay_tru_gio',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '600');

        $filePath = $this->resolveFilePath();
        if ($filePath === null) {
            return 1;
        }

        $fromSheet = max(1, (int) $this->option('from-sheet'));
        $startIndex = $fromSheet - 1;

        $this->info("Đang đọc: {$filePath} (sheet {$fromSheet} trở đi)");

        try {
            if ($this->option('fresh')) {
                YNghiaTuTru::truncate();
                DongChayGioiThieu::truncate();
                $this->info('Đã xóa dữ liệu cũ (y_nghia_tu_tru, dong_chay_gioi_thieu).');
            }

            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $totalSheets = $spreadsheet->getSheetCount();
            $this->info("Tổng {$totalSheets} sheet trong file.");

            $importedYn = 0;
            $importedGt = 0;

            $allSheets = $spreadsheet->getAllSheets();

            for ($i = $startIndex; $i < count($allSheets); $i++) {
                $sheet = $allSheets[$i];
                $title = trim((string) $sheet->getTitle());
                $sheetNum = $i + 1;

                if ($title === 'I. Bản chất') {
                    $count = $this->importBanChatSheet($sheet, $filePath);
                    $importedYn += $count;
                    $this->info("Sheet {$sheetNum} «{$title}»: {$count} mục → y_nghia_tu_tru");
                    continue;
                }

                if (isset($this->sheetToTruLoai[$title])) {
                    $truLoai = $this->sheetToTruLoai[$title];
                    $parsed = $this->buildGioiThieuParsed($sheet, $truLoai);
                    DongChayGioiThieu::updateOrCreate(
                        ['tru_loai' => $truLoai],
                        [
                            'noi_dung' => $parsed['noi_dung'],
                            'image' => $parsed['image'],
                        ]
                    );
                    $importedGt++;
                    $imgNote = $parsed['image'] ? ' + ảnh' : '';
                    $this->info("Sheet {$sheetNum} «{$title}»: → dong_chay_gioi_thieu ({$truLoai}){$imgNote}");
                    continue;
                }

                if (preg_match('/ĐOẠN\s+NỐI/ui', $title)) {
                    continue;
                }

                $this->warn("Sheet {$sheetNum} «{$title}»: bỏ qua (không map).");
            }

            $this->importTransitionSheetByName($spreadsheet, $importedYn);

            $this->info("Hoàn thành! y_nghia_tu_tru: {$importedYn} mục, dong_chay_gioi_thieu: {$importedGt} mục.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
    }

    protected function resolveFilePath(): ?string
    {
        $candidates = array_filter([
            $this->argument('file'),
            base_path('PHẦN 6.xlsx'),
            'C:/Users/HUNG/Downloads/PHẦN 6.xlsx',
        ]);

        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        $this->error('Không tìm thấy PHẦN 6.xlsx');

        return null;
    }

    /**
     * Sheet I. Bản chất → các mục y_nghia_tu_tru (title cột A, content cột B).
     *
     * @return int Số mục import
     */
    protected function importBanChatSheet(Worksheet $sheet, string $filePath): int
    {
        $items = [];
        $currentTitle = null;
        $currentSlug = null;
        $currentContent = [];

        $flush = function () use (&$items, &$currentTitle, &$currentSlug, &$currentContent): void {
            if ($currentTitle === null) {
                return;
            }
            $slug = $currentSlug ?? 'section';
            $parsed = DocxTextService::parsePhan6TextLines(
                $currentContent,
                DocxTextService::yNghiaImagePath($slug)
            );
            if ($parsed['noi_dung'] === '' && $parsed['image'] === null) {
                $currentTitle = null;
                $currentSlug = null;
                $currentContent = [];

                return;
            }
            $items[] = [
                'title' => $currentTitle,
                'content' => $parsed['noi_dung'],
                'image' => $parsed['image'],
            ];
            $currentTitle = null;
            $currentSlug = null;
            $currentContent = [];
        };

        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $a = $this->cell($sheet, 'A', $row);
            $b = $this->cell($sheet, 'B', $row);
            $c = $this->cell($sheet, 'C', $row);
            $d = $this->cell($sheet, 'D', $row);
            $e = $this->cell($sheet, 'E', $row);

            if ($a !== '' && preg_match('/^PHẦN\s*6\s*:/ui', $a)) {
                continue;
            }

            if ($a === 'Lá số Bát Tự') {
                $flush();
                continue;
            }

            if ($a !== '' && preg_match('/^(I\.|\d+\.)/u', $a)) {
                $flush();
                $currentTitle = $a;
                $currentSlug = $this->slugFromTitle($a, count($items));
                $this->appendBanChatRowCells($currentContent, $currentTitle, $a, $b, $c, $d, $e);
                continue;
            }

            if ($currentTitle === null) {
                continue;
            }

            $this->appendBanChatRowCells($currentContent, $currentTitle, $a, $b, $c, $d, $e);
        }

        $flush();

        $this->call('import:phan6-la-so-bat-tu', [
            'file' => $filePath,
        ]);

        $sort = 0;
        foreach ($items as $item) {
            $slug = $this->slugFromTitle($item['title'], $sort);
            YNghiaTuTru::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $item['title'],
                    'content' => $item['content'],
                    'image' => $item['image'] ?? null,
                    'sort_order' => $sort,
                ]
            );
            $sort++;
        }

        return count($items);
    }

    protected function importTransitionSheetByName($spreadsheet, int &$importedYn): void
    {
        foreach ($spreadsheet->getAllSheets() as $idx => $sheet) {
            $title = trim((string) $sheet->getTitle());
            if (preg_match('/ĐOẠN\s+NỐI/ui', $title)) {
                $this->importTransitionSheet($sheet, $idx + 1, $importedYn);
            }
        }
    }

    protected function importTransitionSheet(Worksheet $sheet, int $sheetNum, int &$importedYn): void
    {
        $parsed = $this->buildGioiThieuParsed($sheet, null);
        if ($parsed['noi_dung'] === '') {
            for ($row = 1; $row <= max(1, $sheet->getHighestRow()); $row++) {
                foreach (['A', 'B', 'C'] as $col) {
                    $v = trim((string) ($sheet->getCell("{$col}{$row}")->getValue() ?? ''));
                    if ($v !== '' && ! $this->isSkippableLine($v)) {
                        $parsed = DocxTextService::parsePhan6TextLines([$v]);
                        break 2;
                    }
                }
            }
        }
        if ($parsed['noi_dung'] === '' && $parsed['image'] === null) {
            $this->warn("Sheet {$sheetNum}: đoạn nối Phần 8 trống.");

            return;
        }

        YNghiaTuTru::updateOrCreate(
            ['slug' => 'transition_phan8'],
            [
                'title' => null,
                'content' => $parsed['noi_dung'],
                'image' => $parsed['image'],
                'sort_order' => 9999,
            ]
        );
        $importedYn++;
        $this->info("Sheet {$sheetNum} «{$sheet->getTitle()}»: → y_nghia_tu_tru (transition_phan8)");
    }

    /**
     * @return array{noi_dung: string, image: ?string}
     */
    protected function buildGioiThieuParsed(Worksheet $sheet, ?string $truLoai): array
    {
        $lines = [];

        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            $a = $this->cell($sheet, 'A', $row);
            $b = $this->cell($sheet, 'B', $row);

            if ($a !== '' && preg_match('/^PHẦN\s*6\s*:/ui', $a)) {
                continue;
            }

            if ($a !== '' && ! $this->isSkippableLine($a)) {
                $lines[] = $a;
            }
            if ($b !== '' && ! $this->isSkippableLine($b)) {
                $lines[] = $b;
            }
        }

        return DocxTextService::parsePhan6TextLines(
            $lines,
            $truLoai !== null ? DocxTextService::phan6ImageRelativePath($truLoai) : null
        );
    }

    protected function cell(Worksheet $sheet, string $col, int $row): string
    {
        return trim((string) ($sheet->getCell("{$col}{$row}")->getValue() ?? ''));
    }

    /**
     * Sheet I: cột C chứa bullet a./b. (Hợp, Khắc, Xung…); bảng Lá số Bát Tự gom A–E.
     *
     * @param  array<int, string>  $currentContent
     */
    protected function appendBanChatRowCells(
        array &$currentContent,
        string $currentTitle,
        string $a,
        string $b,
        string $c,
        string $d,
        string $e
    ): void {
        if ($a !== '' && preg_match('/^(I\.|\d+\.)/u', $a)) {
            if ($b !== '' && ! $this->isSkippableLine($b)) {
                $currentContent[] = $b;
            }

            return;
        }

        if ($a !== '' && $b !== '') {
            if (! $this->isSkippableLine($a)) {
                $currentContent[] = $a;
            }
            if (! $this->isSkippableLine($b)) {
                $currentContent[] = $b;
            }
        } elseif ($b !== '' && ! $this->isSkippableLine($b)) {
            $currentContent[] = $b;
        } elseif ($a !== '' && ! $this->isSkippableLine($a)) {
            $currentContent[] = $a;
        }

        if ($c !== '' && ! $this->isSkippableLine($c)) {
            $currentContent[] = $c;
        }
    }

    protected function isSkippableLine(string $text): bool
    {
        if ($text === '') {
            return true;
        }

        return DocxTextService::isPhan6ImagePlaceholder($text)
            || preg_match('/^\[CHÈN HÌNH ẢNH\]/iu', $text) === 1;
    }

    protected function slugFromTitle(string $title, int $index): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\x{00C0}-\x{1FFF}\x{2C00}-\x{2DFF}]+/u', '_', $slug);
        $slug = trim((string) $slug, '_');

        return $slug !== '' ? mb_substr($slug, 0, 60) : 'section_' . $index;
    }
}
