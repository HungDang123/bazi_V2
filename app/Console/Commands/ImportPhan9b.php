<?php

namespace App\Console\Commands;

use App\Models\Phan9bGiaiPhapCanBang;
use App\Models\Phan9bHieuQuaChuyenHoa;
use App\Models\Phan9bNgoaiLuc;
use App\Models\Phan9bNoiLuc;
use App\Models\Phan9bThapThan;
use App\Support\ImportPath;
use App\Services\DocxTextService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan9b extends Command
{
    protected $signature = 'import:phan9b
        {file? : Đường dẫn file PHẦN 9B.xlsx}
        {--fresh : Xóa toàn bộ dữ liệu PHẦN 9B trước khi import}
        {--sheet= : Chỉ import một sheet (I. | II. 1- Ngũ Hành | II. 2- Thập Thần | IV.)}';

    protected $description = 'Import PHẦN 9B: Excel (I, II.1, II.2) + DOCX III. Ngoại lực';

    /** @var array<int, string> */
    protected array $sheetNames = [
        'I.',
        'II. 1- Ngũ Hành',
        'II. 2- Thập Thần',
        'IV.',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 9B.xlsx'
        );

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file trong imports/ hoặc: php artisan import:phan9b <đường_dẫn_file>');

            return 1;
        }

        if ($this->option('fresh')) {
            Phan9bGiaiPhapCanBang::query()->delete();
            Phan9bNoiLuc::query()->delete();
            Phan9bThapThan::query()->delete();
            Phan9bNgoaiLuc::query()->delete();
            Phan9bHieuQuaChuyenHoa::query()->delete();
            $this->info('Đã xóa dữ liệu PHẦN 9B cũ.');
        }

        $onlySheet = $this->option('sheet');
        if ($onlySheet !== null && $onlySheet !== '') {
            return $this->importSingleSheet($filePath, (string) $onlySheet);
        }

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $total = 0;

            foreach ($this->sheetNames as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if ($sheet === null) {
                    $this->warn("Không tìm thấy sheet: {$sheetName}");

                    continue;
                }

                if ($sheetName === 'I.') {
                    $count = $this->importSheetCanBang($sheet);
                } elseif ($sheetName === 'II. 1- Ngũ Hành') {
                    $count = $this->importSheetNoiLuc($sheet);
                } elseif ($sheetName === 'II. 2- Thập Thần') {
                    $count = $this->importSheetThapThan($sheet);
                } else {
                    $count = $this->importSheetHieuQua($sheet);
                }

                $total += $count;
                $this->info("  {$sheetName}: {$count} bản ghi.");
            }

            $docxPath = ImportPath::resolve(null, 'PHẦN 9B - III. NGOẠI LỰC - VKB tự chủ.docx');
            $total += $this->importNgoaiLucFromDocx($docxPath);

            $this->info("Hoàn thành PHẦN 9B! Tổng {$total} bản ghi.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return 1;
        }
    }

    protected function importSingleSheet(string $filePath, string $sheetName): int
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if ($sheet === null) {
                $this->error("Không tìm thấy sheet: {$sheetName}");

                return 1;
            }

            if ($sheetName === 'I.') {
                $count = $this->importSheetCanBang($sheet);
            } elseif ($sheetName === 'II. 1- Ngũ Hành') {
                $count = $this->importSheetNoiLuc($sheet);
            } elseif ($sheetName === 'II. 2- Thập Thần') {
                $count = $this->importSheetThapThan($sheet);
            } elseif ($sheetName === 'IV.') {
                $count = $this->importSheetHieuQua($sheet);
            } else {
                $this->error("Sheet không hợp lệ: {$sheetName}");

                return 1;
            }

            $this->info("  {$sheetName}: {$count} bản ghi.");
            $this->info("Hoàn thành! {$count} bản ghi.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
    }

    protected function importNgoaiLucFromDocx(string $filePath): int
    {
        if (! is_file($filePath)) {
            $this->warn('Không tìm thấy DOCX III: ' . basename($filePath));

            return 0;
        }

        $lines = DocxTextService::extractParagraphs($filePath) ?? [];
        $sort = 0;
        $count = 0;
        $currentSection = null;
        $hasHeader = false;

        Phan9bNgoaiLuc::query()->delete();

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (! $hasHeader && preg_match('/^(II\.|III\.)\s*NGOẠI LỰC/iu', $line)) {
                Phan9bNgoaiLuc::create([
                    'loai' => 'header',
                    'section_number' => null,
                    'noi_dung' => $line,
                    'sort_order' => $sort++,
                ]);
                $count++;
                $hasHeader = true;

                continue;
            }

            if ($hasHeader && $currentSection === null && str_starts_with($line, '(')) {
                Phan9bNgoaiLuc::create([
                    'loai' => 'subtitle',
                    'section_number' => null,
                    'noi_dung' => $line,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if (preg_match('/^(\d+)\.\s/u', $line, $matches)) {
                $currentSection = (int) $matches[1];
                Phan9bNgoaiLuc::create([
                    'loai' => 'section',
                    'section_number' => $currentSection,
                    'noi_dung' => $line,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($currentSection === null) {
                Phan9bNgoaiLuc::create([
                    'loai' => 'intro',
                    'section_number' => null,
                    'noi_dung' => $line,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            Phan9bNgoaiLuc::create([
                'loai' => 'item',
                'section_number' => $currentSection,
                'noi_dung' => $line,
                'sort_order' => $sort++,
            ]);
            $count++;
        }

        $this->info('  DOCX III. Ngoại lực: ' . $count . ' bản ghi.');

        return $count;
    }

    protected function importSheetCanBang(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): int
    {
        $highestRow = $sheet->getHighestRow();
        $currentThan = null;
        $currentMuc = null;
        $currentTieuDe = null;
        $sort = 0;
        $count = 0;

        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());
            $colB = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
            $colC = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());

            if ($colA === '' && $colB === '' && $colC === '') {
                continue;
            }

            if ($this->isCanBangHeaderRow($colA, $colB, $colC)) {
                Phan9bGiaiPhapCanBang::create([
                    'loai' => 'header',
                    'than_trang_thai' => null,
                    'muc' => null,
                    'tieu_de' => null,
                    'noi_dung' => $colA,
                    'bo_hy_than' => null,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($this->isCanBangSectionRow($colA, $colB, $colC)) {
                Phan9bGiaiPhapCanBang::create([
                    'loai' => 'section',
                    'than_trang_thai' => null,
                    'muc' => null,
                    'tieu_de' => null,
                    'noi_dung' => $colA,
                    'bo_hy_than' => null,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($colA !== '') {
                $parsedThan = $this->parseThanTrangThai($colA);
                if ($parsedThan !== null) {
                    $currentThan = $parsedThan;
                    $currentTieuDe = null;
                    $currentMuc = null;
                }
            }

            if ($colB !== '') {
                $currentTieuDe = $colB;
                $currentMuc = $this->parseMuc($colB);
            }

            if ($colC === '' || $currentThan === null) {
                continue;
            }

            Phan9bGiaiPhapCanBang::create([
                'loai' => 'noi_dung',
                'than_trang_thai' => $currentThan,
                'muc' => $currentMuc,
                'tieu_de' => $currentTieuDe,
                'noi_dung' => $colC,
                'bo_hy_than' => $this->extractBoHyThan($colC),
                'sort_order' => $sort++,
            ]);
            $count++;
        }

        return $count;
    }

    protected function importSheetNoiLuc(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): int
    {
        $highestRow = $sheet->getHighestRow();
        $currentSlug = null;
        $sort = 0;
        $count = 0;
        $passedMuc = false;

        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = mb_strtoupper(trim((string) $sheet->getCell('A' . $row)->getCalculatedValue()), 'UTF-8');
            $colB = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());

            if ($colA === '' && $colB === '') {
                continue;
            }

            if (preg_match('/^II\.\s*NỘI LỰC/iu', $colA) && $colB === '') {
                Phan9bNoiLuc::create([
                    'loai' => 'section',
                    'ngu_hanh' => null,
                    'tieu_de' => null,
                    'noi_dung' => trim((string) $sheet->getCell('A' . $row)->getCalculatedValue()),
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if (preg_match('/^1\.\s*Thay đổi tư duy/iu', $colA) && $colB === '') {
                $passedMuc = true;
                Phan9bNoiLuc::create([
                    'loai' => 'muc',
                    'ngu_hanh' => null,
                    'tieu_de' => null,
                    'noi_dung' => trim((string) $sheet->getCell('A' . $row)->getCalculatedValue()),
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if (! $passedMuc && $colA === '' && $colB !== '') {
                Phan9bNoiLuc::create([
                    'loai' => 'intro',
                    'ngu_hanh' => null,
                    'tieu_de' => null,
                    'noi_dung' => $colB,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if (preg_match('/^(MỘC|THỦY|HỎA|THỔ|KIM)$/u', $colA)) {
                $currentSlug = Phan9bNoiLuc::labelToSlug($colA);
                if ($currentSlug === null || $colB === '') {
                    continue;
                }

                Phan9bNoiLuc::create([
                    'loai' => 'hanh',
                    'ngu_hanh' => $currentSlug,
                    'tieu_de' => null,
                    'noi_dung' => $colB,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($currentSlug === null || $colB === '') {
                continue;
            }

            $tieuDe = null;
            $noiDung = $colB;
            if (preg_match('/^Về\s+/u', $colB)) {
                $tieuDe = $colB;
                $noiDung = '';
            }

            Phan9bNoiLuc::create([
                'loai' => 'hanh',
                'ngu_hanh' => $currentSlug,
                'tieu_de' => $tieuDe,
                'noi_dung' => $noiDung,
                'sort_order' => $sort++,
            ]);
            $count++;
        }

        return $count;
    }

    protected function importSheetThapThan(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): int
    {
        $highestRow = $sheet->getHighestRow();
        $currentBo = null;
        $currentSlug = null;
        $sort = 0;
        $count = 0;
        $passedMuc = false;
        $introImported = false;

        for ($row = 1; $row <= $highestRow; $row++) {
            $colARaw = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());
            $colA = mb_strtoupper($colARaw, 'UTF-8');
            $colB = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
            $colC = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());

            if ($colA === '' && $colB === '' && $colC === '') {
                continue;
            }

            if (preg_match('/^II\.\s*NỘI LỰC/iu', $colARaw) && $colB === '' && $colC === '') {
                continue;
            }

            if (preg_match('/^2\.\s*Thay đổi cách làm việc/iu', $colARaw)) {
                $passedMuc = true;
                Phan9bThapThan::create([
                    'loai' => 'muc',
                    'bo' => null,
                    'thap_than' => null,
                    'tieu_de' => null,
                    'noi_dung' => $colARaw,
                    'sort_order' => $sort++,
                ]);
                $count++;

                if ($colB !== '') {
                    Phan9bThapThan::create([
                        'loai' => 'muc_note',
                        'bo' => null,
                        'thap_than' => null,
                        'tieu_de' => null,
                        'noi_dung' => $colB,
                        'sort_order' => $sort++,
                    ]);
                    $count++;
                }

                continue;
            }

            if ($passedMuc && ! $introImported && $currentSlug === null
                && $colA === '' && $colB !== '' && $colC === ''
                && Phan9bThapThan::labelToSlug($colB) === null) {
                Phan9bThapThan::create([
                    'loai' => 'intro',
                    'bo' => null,
                    'thap_than' => null,
                    'tieu_de' => null,
                    'noi_dung' => $colB,
                    'sort_order' => $sort++,
                ]);
                $count++;
                $introImported = true;

                continue;
            }

            if ($colA !== '' && preg_match('/^BỘ\s+/u', $colARaw)) {
                $currentBo = $this->parseBo($colARaw);
            }

            if ($colB !== '') {
                $slug = Phan9bThapThan::labelToSlug($colB);
                if ($slug !== null) {
                    $currentSlug = $slug;
                    if ($colC !== '') {
                        Phan9bThapThan::create([
                            'loai' => 'thap_than',
                            'bo' => $currentBo,
                            'thap_than' => $currentSlug,
                            'tieu_de' => $colC,
                            'noi_dung' => '',
                            'sort_order' => $sort++,
                        ]);
                        $count++;
                    }

                    continue;
                }
            }

            if ($currentSlug === null || $colC === '') {
                continue;
            }

            $tieuDe = null;
            $noiDung = $colC;
            if (preg_match('/^Về\s+/u', $colC)) {
                $tieuDe = $colC;
                $noiDung = '';
            }

            Phan9bThapThan::create([
                'loai' => 'thap_than',
                'bo' => $currentBo,
                'thap_than' => $currentSlug,
                'tieu_de' => $tieuDe,
                'noi_dung' => $noiDung,
                'sort_order' => $sort++,
            ]);
            $count++;
        }

        return $count;
    }

    protected function importSheetHieuQua(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): int
    {
        $highestRow = $sheet->getHighestRow();
        $sort = 0;
        $count = 0;
        $currentSection = null;

        Phan9bHieuQuaChuyenHoa::query()->delete();

        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());
            $colB = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());

            if ($colA === '' && $colB === '') {
                continue;
            }

            if ($this->isHieuQuaTableRow($colA)) {
                continue;
            }

            if (preg_match('/^IV\.\s*HIỆU QUẢ/iu', $colA) && $colB === '') {
                Phan9bHieuQuaChuyenHoa::create([
                    'loai' => 'header',
                    'section_number' => null,
                    'noi_dung' => $colA,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if (preg_match('/^Điều gì sẽ xảy ra/iu', $colA) && $colB === '') {
                Phan9bHieuQuaChuyenHoa::create([
                    'loai' => 'subtitle',
                    'section_number' => null,
                    'noi_dung' => $colA,
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($this->isHieuQuaChartPlaceholder($colA)) {
                Phan9bHieuQuaChuyenHoa::create([
                    'loai' => 'chart',
                    'section_number' => $currentSection,
                    'noi_dung' => '[image_chart]',
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($this->isHieuQuaFirstChartPlaceholder($colA)) {
                continue;
            }

            if (preg_match('/^(\d+)\.\s/u', $colA, $matches)) {
                $currentSection = (int) $matches[1];
                Phan9bHieuQuaChuyenHoa::create([
                    'loai' => 'section',
                    'section_number' => $currentSection,
                    'noi_dung' => $colA,
                    'sort_order' => $sort++,
                ]);
                $count++;

                if ($colB !== '') {
                    Phan9bHieuQuaChuyenHoa::create([
                        'loai' => 'intro',
                        'section_number' => $currentSection,
                        'noi_dung' => $colB,
                        'sort_order' => $sort++,
                    ]);
                    $count++;
                } else {
                    // section without intro in same row
                }

                continue;
            }

            if ($colA === '' && $colB !== '' && $currentSection !== null) {
                Phan9bHieuQuaChuyenHoa::create([
                    'loai' => 'item',
                    'section_number' => $currentSection,
                    'noi_dung' => $this->normalizeHieuQuaText($colB),
                    'sort_order' => $sort++,
                ]);
                $count++;

                continue;
            }

            if ($colA !== '' && $colB !== '' && $currentSection !== null) {
                Phan9bHieuQuaChuyenHoa::create([
                    'loai' => 'paragraph',
                    'section_number' => $currentSection,
                    'noi_dung' => $this->normalizeHieuQuaText($colB),
                    'sort_order' => $sort++,
                ]);
                $count++;
            }
        }

        return $count;
    }

    protected function isHieuQuaTableRow(string $colA): bool
    {
        $upper = mb_strtoupper($colA, 'UTF-8');

        if (in_array($upper, ['NGŨ HÀNH', 'HÀNH ĐỘNG', 'TOTAL'], true)) {
            return true;
        }

        if (in_array($upper, ['VƯỢNG', 'TƯỚNG', 'HƯU', 'TÙ', 'TỬ'], true)) {
            return true;
        }

        if (preg_match('/^(SIM SỐ|TRANG SỨC|TRANG PHỤC)/u', $upper)) {
            return true;
        }

        return false;
    }

    protected function isHieuQuaChartPlaceholder(string $colA): bool
    {
        return (bool) preg_match('/\[MÔ PHỎNG BIỂU ĐỒ TRẠNG THÁI CHO NGŨ HÀNH CẦN CẢI THIỆN\]/u', $colA);
    }

    protected function isHieuQuaFirstChartPlaceholder(string $colA): bool
    {
        return (bool) preg_match('/\[CHÈN HÌNH ẢNH MÔ PHỎNG BIỂU ĐỒ/u', $colA);
    }

    protected function normalizeHieuQuaText(string $text): string
    {
        $text = str_replace(
            '[MÔ PHỎNG BIỂU ĐỒ TRẠNG THÁI CHO NGŨ HÀNH CẦN CẢI THIỆN]',
            '[image_chart]',
            $text
        );

        $placeholders = [
            '[VỊ TRÍ QUAN QUỶ]' => '[quan_quy]',
            '[VỊ TRÍ THÊ TÀI]' => '[the_tai]',
            '[VỊ TRÍ PHỤ MẪU]' => '[phu_mau]',
            '[VỊ TRÍ TỬ TÔN]' => '[tu_ton]',
            '[VỊ TRÍ HUYNH ĐỆ]' => '[huynh_de]',
            '[NAM: THÊ TÀI / NỮ: QUAN QUỶ]' => '[tinh_duyen]',
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    protected function parseBo(string $colA): ?string
    {
        $upper = mb_strtoupper($colA, 'UTF-8');

        if (preg_match('/HUYNH\s*ĐỆ/u', $upper)) {
            return 'huynh_de';
        }
        if (preg_match('/TỬ\s*TÔN/u', $upper)) {
            return 'tu_ton';
        }
        if (preg_match('/THÊ\s*TÀI/u', $upper)) {
            return 'the_tai';
        }
        if (preg_match('/QUAN\s*QUỶ/u', $upper)) {
            return 'quan_quy';
        }
        if (preg_match('/PHỤ\s*MÂU/u', $upper)) {
            return 'phu_mau';
        }

        return null;
    }

    protected function isCanBangHeaderRow(string $colA, string $colB, string $colC): bool
    {
        return $colB === '' && $colC === '' && preg_match('/^PHẦN\s*9\b/iu', $colA);
    }

    protected function isCanBangSectionRow(string $colA, string $colB, string $colC): bool
    {
        return $colB === '' && $colC === '' && preg_match('/^I\.\s*GIẢI PHÁP CÂN BẰNG/iu', $colA);
    }

    protected function parseThanTrangThai(string $colA): ?string
    {
        $upper = mb_strtoupper($colA, 'UTF-8');

        if (preg_match('/THÂN\s+VƯỢNG/u', $upper)) {
            return Phan9bGiaiPhapCanBang::THAN_VUONG;
        }

        if (preg_match('/THÂN\s+NHƯỢC/u', $upper)) {
            return Phan9bGiaiPhapCanBang::THAN_NHUOC;
        }

        return null;
    }

    protected function parseMuc(string $tieuDe): ?string
    {
        if (preg_match('/1\.\s*Trạng thái năng lượng gốc/iu', $tieuDe)) {
            return Phan9bGiaiPhapCanBang::MUC_TRANG_THAI;
        }

        if (preg_match('/2\.\s*Chiến lược hành động/iu', $tieuDe)) {
            return Phan9bGiaiPhapCanBang::MUC_CHIEN_LUOC;
        }

        return null;
    }

    protected function extractBoHyThan(string $noiDung): ?string
    {
        if (preg_match('/\[(tu_ton|the_tai|quan_quy|phu_mau|huynh_de)\]/', $noiDung, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
