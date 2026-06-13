<?php

namespace App\Console\Commands;

use App\Models\Phan9aNgoaiLuc;
use App\Models\Phan9aNoiLuc;
use App\Support\ImportPath;
use App\Services\DocxTextService;
use App\Services\Phan9aService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan9a extends Command
{
    protected $signature = 'import:phan9a
        {--fresh : Xóa dữ liệu PHẦN 9A trước khi import}';

    protected $description = 'Import PHẦN 9A: Excel I. Nội lực (intro + 5 hành) + DOCX II. Ngoại lực';

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        if ($this->option('fresh')) {
            Phan9aNoiLuc::query()->delete();
            Phan9aNgoaiLuc::query()->delete();
            $this->info('Đã xóa dữ liệu PHẦN 9A cũ.');
        }

        $xlsx = ImportPath::resolve(null, 'PHẦN 9A.xlsx');
        if (! file_exists($xlsx)) {
            $this->error("Không tìm thấy: {$xlsx}");

            return 1;
        }

        $count = 0;
        $count += $this->importNoiLucFromExcel($xlsx);
        $count += $this->importNgoaiLucFromDocx(ImportPath::resolve(null, 'PHẦN 9A - II. NGOẠI LỰC - VKB tự chủ.docx'));

        $this->info("Hoàn thành PHẦN 9A! Tổng {$count} bản ghi.");

        return 0;
    }

    protected function importNoiLucFromExcel(string $filePath): int
    {
        Phan9aNoiLuc::query()
            ->whereIn('loai', ['intro', 'hanh'])
            ->delete();

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($filePath)->getSheet(0);

        $currentSlug = null;
        $hanhSort = 0;
        $introSort = 0;
        $introCount = 0;
        $hanhCount = 0;
        $highestRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = mb_strtoupper(trim((string) $sheet->getCell('A' . $row)->getValue()), 'UTF-8');
            $colBRaw = (string) $sheet->getCell('B' . $row)->getValue();
            $colB = trim($colBRaw);

            if ($colB === '') {
                continue;
            }

            if ($row === 1 && preg_match('/^I\./u', $colA)) {
                continue;
            }

            if (preg_match('/^(MỘC|THỦY|HỎA|THỔ|KIM)$/u', $colA)) {
                $currentSlug = Phan9aNoiLuc::labelToSlug($colA);
                $hanhSort = 0;
                $this->storeHanhRow($currentSlug, $colB, null, $hanhSort++);
                $hanhCount++;

                continue;
            }

            foreach ($this->splitCellLines($colBRaw) as $line) {
                $line = trim($line);
                if ($line === '' || Phan9aService::isSkippableImportLine($line)) {
                    continue;
                }

                if ($currentSlug === null) {
                    Phan9aNoiLuc::create([
                        'loai' => 'intro',
                        'ngu_hanh' => null,
                        'tieu_de' => null,
                        'noi_dung' => $line,
                        'sort_order' => $introSort++,
                    ]);
                    $introCount++;

                    continue;
                }

                $tieuDe = null;
                $noiDung = $line;
                if (preg_match('/^Về\s+/u', $line)) {
                    $tieuDe = $line;
                    $noiDung = '';
                }

                $this->storeHanhRow($currentSlug, $noiDung, $tieuDe, $hanhSort++);
                $hanhCount++;
            }
        }

        $this->info("Excel Nội lực: {$introCount} đoạn intro, {$hanhCount} dòng hành.");

        return $introCount + $hanhCount;
    }

    /**
     * @return array<int, string>
     */
    protected function splitCellLines(string $raw): array
    {
        $parts = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_filter(
            $parts,
            static fn (string $part): bool => trim($part) !== ''
        ));
    }

    protected function storeHanhRow(?string $slug, string $noiDung, ?string $tieuDe, int $sort): void
    {
        if ($slug === null || ($noiDung === '' && $tieuDe === null)) {
            return;
        }

        Phan9aNoiLuc::updateOrCreate(
            [
                'loai' => 'hanh',
                'ngu_hanh' => $slug,
                'tieu_de' => $tieuDe,
                'noi_dung' => $noiDung,
            ],
            ['sort_order' => $sort]
        );
    }

    protected function importNgoaiLucFromDocx(string $filePath): int
    {
        if (! file_exists($filePath)) {
            $this->warn('Không tìm thấy DOCX II: ' . basename($filePath));

            return 0;
        }

        $lines = DocxTextService::extractParagraphs($filePath) ?? [];
        $parts = [];
        $title = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($title === null && preg_match('/^(II\.|III\.)\s*NGOẠI LỰC/iu', $line)) {
                $title = preg_replace('/^III\./iu', 'II.', $line);

                continue;
            }

            $parts[] = $line;
        }

        Phan9aNgoaiLuc::query()->delete();
        Phan9aNgoaiLuc::create([
            'tieu_de' => $title ?? 'II. NGOẠI LỰC - CÔNG CỤ HỖ TRỢ',
            'noi_dung' => implode("\n\n", $parts),
            'sort_order' => 0,
        ]);

        $this->info('DOCX II Ngoại lực: 1 bản ghi (' . count($parts) . ' đoạn).');

        return 1;
    }
}
