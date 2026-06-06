<?php

namespace App\Console\Commands;

use App\Models\DongChayGioiThieu;
use App\Support\ImportPath;
use App\Services\DocxTextService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDongChayGioiThieu extends Command
{
    protected $signature = 'import:dong-chay-gioi-thieu
        {--fresh : Xóa dữ liệu cũ trước khi import}
        {--only= : Chỉ import tru_loai (phẩy): nam_thang, thang_ngay, ngay_gio}';

    protected $description = 'Import giới thiệu DOCX PHẦN 6 - II, III, IV → dong_chay_gioi_thieu (Mã 2–4)';

    /** @var array<string, list<string>> */
    protected function getFileConfig(): array
    {
        return [
            'tru_nam_tru_thang' => [
                'PHẦN 6 - II- TRỤ NĂM - TRỤ THÁNG.docx',
            ],
            'tru_thang_tru_ngay' => [
                'PHẦN 6 - III. SỰ TƯƠNG TÁC GIỮA TRỤ THÁNG VÀ TRỤ NGÀY.docx',
            ],
            'tru_ngay_tru_gio' => [
                'PHẦN 6 - IV. SỰ TƯƠNG TÁC GIỮA TRỤ NGÀY VÀ TRỤ GIỜ.docx',
            ],
        ];
    }

    protected function resolveOnlyFilter(): ?array
    {
        $only = trim((string) $this->option('only'));
        if ($only === '') {
            return null;
        }

        $map = [
            'nam_thang' => 'tru_nam_tru_thang',
            'thang_ngay' => 'tru_thang_tru_ngay',
            'ngay_gio' => 'tru_ngay_tru_gio',
        ];

        $keys = array_map('trim', explode(',', $only));
        $resolved = [];
        foreach ($keys as $key) {
            if (isset($map[$key])) {
                $resolved[] = $map[$key];
            }
        }

        return $resolved === [] ? null : $resolved;
    }

    protected function resolveDocxPath(array $filenames): ?string
    {
        foreach ($filenames as $filename) {
            $filePath = ImportPath::resolve(null, $filename);
            if (is_file($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    public function handle(): int
    {
        if ($this->option('fresh')) {
            DongChayGioiThieu::truncate();
        }

        $count = 0;
        $only = $this->resolveOnlyFilter();

        foreach ($this->getFileConfig() as $truLoai => $candidates) {
            if ($only !== null && ! in_array($truLoai, $only, true)) {
                continue;
            }

            $parsed = $this->buildMergedNoiDung($candidates, $truLoai);
            if ($parsed !== null && $parsed['image'] === null) {
                $parsed['image'] = $this->detectImageFromPhan6Xlsx($truLoai);
            }
            if ($parsed === null || ($parsed['noi_dung'] === '' && $parsed['image'] === null)) {
                $this->warn('Bỏ qua (không đọc được / rỗng): ' . basename($candidates[0]));
                continue;
            }

            DongChayGioiThieu::updateOrCreate(
                ['tru_loai' => $truLoai],
                [
                    'noi_dung' => $parsed['noi_dung'],
                    'image' => $parsed['image'],
                ]
            );
            $count++;
            $len = mb_strlen($parsed['noi_dung']);
            $imgNote = $parsed['image'] ? ' + ảnh: ' . $parsed['image'] : '';
            $this->info("Đã import: {$truLoai} ({$len} ký tự){$imgNote}");
        }

        $this->info("Hoàn thành! Tổng {$count} mục đã lưu.");

        return 0;
    }

    /**
     * @return array{noi_dung: string, image: ?string}|null
     */
    protected function buildMergedNoiDung(array $candidates, string $truLoai): ?array
    {
        $modernPath = isset($candidates[0]) ? $this->resolveDocxPath([$candidates[0]]) : null;
        $legacyPath = isset($candidates[1]) ? $this->resolveDocxPath([$candidates[1]]) : null;
        $sourceForImage = $modernPath ?? $legacyPath;

        $modern = ['noi_dung' => '', 'image' => null];
        if ($modernPath !== null) {
            $raw = $this->extractTextFromDocx($modernPath);
            if ($raw === null) {
                return null;
            }
            $modern = $this->parseDocxContent($raw, $truLoai, $modernPath);
        }

        $legacy = ['noi_dung' => '', 'image' => null];
        if ($legacyPath !== null && $legacyPath !== $modernPath) {
            $raw = $this->extractTextFromDocx($legacyPath);
            if ($raw !== null) {
                $legacy = $this->parseDocxContent($raw, $truLoai, $legacyPath);
            }
        }

        if ($modern['noi_dung'] === '' && $legacy['noi_dung'] === ''
            && $modern['image'] === null && $legacy['image'] === null) {
            return null;
        }

        $noiDung = $modern['noi_dung'];
        if ($legacy['noi_dung'] !== '') {
            $noiDung = $noiDung === ''
                ? $legacy['noi_dung']
                : trim($noiDung) . "\n\n" . trim($this->stripLeadingSectionTitle($legacy['noi_dung']));
        }

        $image = $modern['image'] ?? $legacy['image'];

        return [
            'noi_dung' => trim($noiDung),
            'image' => $image,
        ];
    }

    /** Bỏ dòng tiêu đề II/III/IV cũ ở đầu file legacy để tránh trùng với file mới. */
    protected function stripLeadingSectionTitle(string $text): string
    {
        $paragraphs = preg_split('/\n\s*\n/u', $text) ?: [];
        if ($paragraphs === []) {
            return $text;
        }
        $first = trim((string) $paragraphs[0]);
        if (preg_match('/^(II|III|IV)\.\s/u', $first)) {
            array_shift($paragraphs);
        }

        return trim(implode("\n\n", $paragraphs));
    }

    protected function extractTextFromDocx(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false) {
            return null;
        }
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $paras = $xpath->query('//w:p');
        $lines = [];
        foreach ($paras as $p) {
            $ts = $xpath->query('.//w:t', $p);
            $text = '';
            foreach ($ts as $t) {
                $text .= $t->textContent;
            }
            $lines[] = trim($text);
        }

        return implode("\n", $lines);
    }

    /**
     * [image] → cột image (không chèn marker vào text); văn bản khác giữ nguyên.
     *
     * @return array{noi_dung: string, image: ?string}
     */
    protected function parseDocxContent(string $text, string $truLoai, ?string $docxPath = null): array
    {
        $lines = array_map('trim', explode("\n", $text));

        return DocxTextService::parsePhan6TextLines(
            $lines,
            DocxTextService::phan6ImageRelativePath($truLoai),
            $docxPath
        );
    }

    /** Đọc PHẦN 6.xlsx: nếu sheet có dòng [image] thì gán ảnh (DOCX II thường không có placeholder). */
    protected function detectImageFromPhan6Xlsx(string $truLoai): ?string
    {
        $path = ImportPath::resolve(null, 'PHẦN 6.xlsx');
        if (! file_exists($path)) {
            return null;
        }

        $sheetTitleByTru = [
            'tru_nam_tru_thang' => 'II. Trụ Năm - Trụ Tháng',
            'tru_thang_tru_ngay' => 'III. Trụ Tháng - Trụ Ngày',
            'tru_ngay_tru_gio' => 'IV. Trụ Ngày - Trụ Giờ',
        ];
        $sheetTitle = $sheetTitleByTru[$truLoai] ?? null;
        $imageRel = DocxTextService::phan6ImageRelativePath($truLoai);
        if ($sheetTitle === null || $imageRel === null) {
            return null;
        }

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                if (trim((string) $sheet->getTitle()) !== $sheetTitle) {
                    continue;
                }
                for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
                    foreach (['A', 'B', 'C'] as $col) {
                        $v = trim((string) $sheet->getCell($col . $row)->getValue());
                        if (DocxTextService::isPhan6ImagePlaceholder($v)
                            || preg_match('/\[CHÈN\s+HÌNH\s+ẢNH/u', $v)) {
                            DocxTextService::ensurePhan6ImageFile($imageRel);

                            return $imageRel;
                        }
                    }
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
