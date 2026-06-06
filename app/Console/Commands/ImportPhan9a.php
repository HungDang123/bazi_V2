<?php

namespace App\Console\Commands;

use App\Models\Phan9aNgoaiLuc;
use App\Models\Phan9aNoiLuc;
use App\Services\DocxTextService;
use App\Services\Phan9aService;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPhan9a extends Command
{
    protected $signature = 'import:phan9a
        {--fresh : Xóa dữ liệu PHẦN 9A trước khi import}';

    protected $description = 'Import PHẦN 9A: Excel I. Nội lực + DOCX intro & II. Ngoại lực';

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        if ($this->option('fresh')) {
            Phan9aNoiLuc::query()->delete();
            Phan9aNgoaiLuc::query()->delete();
            $this->info('Đã xóa dữ liệu PHẦN 9A cũ.');
        }

        $xlsx = base_path('PHẦN 9A.xlsx');
        if (! file_exists($xlsx)) {
            $this->error("Không tìm thấy: {$xlsx}");

            return 1;
        }

        $count = 0;
        $count += $this->importNoiLucFromExcel($xlsx);
        $count += $this->importIntroFromDocx(base_path('PHẦN 9A - I. NỘI LỰC TỰ THÂN.docx'));
        $count += $this->importNgoaiLucFromDocx(base_path('PHẦN 9A - II. NGOẠI LỰC - VKB tự chủ.docx'));

        $this->info("Hoàn thành PHẦN 9A! Tổng {$count} bản ghi.");

        return 0;
    }

    protected function importNoiLucFromExcel(string $filePath): int
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($filePath)->getSheet(0);

        $currentSlug = null;
        $sort = 0;
        $count = 0;
        $highestRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $colA = mb_strtoupper(trim((string) $sheet->getCell('A' . $row)->getValue()), 'UTF-8');
            $colB = trim((string) $sheet->getCell('B' . $row)->getValue());

            if ($colB === '') {
                continue;
            }

            if (Phan9aService::isSkippableImportLine($colB)) {
                continue;
            }

            if ($row === 1 && preg_match('/^I\./u', $colA)) {
                continue;
            }

            if (preg_match('/^(MỘC|THỦY|HỎA|THỔ|KIM)$/u', $colA)) {
                $currentSlug = Phan9aNoiLuc::labelToSlug($colA);
                $sort = 0;
                $this->storeHanhRow($currentSlug, $colB, null, $sort++);
                $count++;

                continue;
            }

            if ($currentSlug === null) {
                continue;
            }

            $tieuDe = null;
            $noiDung = $colB;
            if (preg_match('/^Về\s+/u', $colB)) {
                $tieuDe = $colB;
                $noiDung = '';
            } elseif (str_starts_with($colB, '- ')) {
                $tieuDe = null;
            }

            $this->storeHanhRow($currentSlug, $noiDung, $tieuDe, $sort++);
            $count++;
        }

        $this->info("Excel Nội lực (5 hành): {$count} dòng.");

        return $count;
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

    protected function importIntroFromDocx(string $filePath): int
    {
        if (! file_exists($filePath)) {
            $this->warn('Không tìm thấy DOCX I: ' . basename($filePath));

            return 0;
        }

        $lines = DocxTextService::extractParagraphs($filePath) ?? [];
        $introLines = [];
        $started = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^I\.\s*NỘI LỰC/i', $line)) {
                $started = true;

                continue;
            }

            if (! $started) {
                continue;
            }

            if (Phan9aService::isSkippableImportLine($line)) {
                break;
            }

            if (preg_match('/^(MỘC|THỦY|HỎA|THỔ|KIM)$/iu', $line)) {
                break;
            }

            $introLines[] = $line;
        }

        Phan9aNoiLuc::where('loai', 'intro')->delete();

        $count = 0;
        foreach ($introLines as $i => $paragraph) {
            Phan9aNoiLuc::create([
                'loai' => 'intro',
                'ngu_hanh' => null,
                'tieu_de' => null,
                'noi_dung' => $paragraph,
                'sort_order' => $i,
            ]);
            $count++;
        }

        $this->info("DOCX intro I: {$count} đoạn.");

        return $count;
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
