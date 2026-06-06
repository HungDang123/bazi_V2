<?php

namespace App\Console\Commands;

use App\Models\YNghiaTuTru;
use App\Support\ImportPath;
use App\Services\DocxTextService;
use Illuminate\Console\Command;

class ImportYNghiaTuTru extends Command
{
    protected $signature = 'import:y-nghia-tu-tru
        {file? : Đường dẫn file DOCX}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Ý nghĩa Tứ Trụ từ file PHẦN 6 - I- Ý NGHĨA TỨ TRỤ.docx';

    protected array $sections = [
        'I. Ý nghĩa Tứ Trụ và tiềm năng trong La Bàn Thịnh Vượng' => ['slug' => 'intro', 'title' => 'I. Ý nghĩa Tứ Trụ và tiềm năng trong La Bàn Thịnh Vượng'],
        'Cơ chế vận hành của La Bàn Thịnh Vượng' => ['slug' => 'co_che_van_hanh', 'title' => 'Cơ chế vận hành của La Bàn Thịnh Vượng'],
        'Nhận diện điểm nghẽn để chuyển hóa' => ['slug' => 'nhan_dien_diem_nghen', 'title' => 'Nhận diện điểm nghẽn để chuyển hóa'],
        'Làm chủ bản đồ thay vì chấp nhận định mệnh' => ['slug' => 'lam_chu_ban_do', 'title' => 'Làm chủ bản đồ thay vì chấp nhận định mệnh'],
    ];

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 6 - I- Ý NGHĨA TỨ TRỤ.docx'
        );

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file DOCX trong imports/ hoặc: php artisan import:y-nghia-tu-tru <đường_dẫn_file>');

            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            $text = $this->extractTextFromDocx($filePath);
            if ($text === null) {
                $this->error('Không thể đọc nội dung file DOCX.');

                return 1;
            }

            if ($this->option('fresh')) {
                YNghiaTuTru::truncate();
            }

            $items = $this->parseSections($text, $filePath);
            $count = 0;
            foreach ($items as $sortOrder => $item) {
                YNghiaTuTru::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'title' => $item['title'],
                        'content' => $item['content'],
                        'image' => $item['image'] ?? null,
                        'sort_order' => $sortOrder,
                    ]
                );
                $count++;
                $imgNote = ! empty($item['image']) ? ' + ảnh' : '';
                $this->info("  {$item['slug']}{$imgNote}");
            }

            $this->info("Hoàn thành! Tổng {$count} mục đã import.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());

            return 1;
        }
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

    protected function parseSections(string $text, string $docxPath): array
    {
        $lines = array_map('trim', explode("\n", $text));
        $items = [];
        $currentSlug = null;
        $currentTitle = '';
        $currentContent = [];

        $flush = function () use (&$items, &$currentSlug, &$currentTitle, &$currentContent, $docxPath): void {
            if ($currentSlug === null) {
                return;
            }
            $parsed = DocxTextService::parsePhan6TextLines(
                $currentContent,
                DocxTextService::yNghiaImagePath($currentSlug),
                $docxPath
            );
            if ($parsed['noi_dung'] === '' && $parsed['image'] === null) {
                $currentSlug = null;
                $currentTitle = '';
                $currentContent = [];

                return;
            }
            $items[] = [
                'slug' => $currentSlug,
                'title' => $currentTitle,
                'content' => $parsed['noi_dung'],
                'image' => $parsed['image'],
            ];
            $currentSlug = null;
            $currentTitle = '';
            $currentContent = [];
        };

        foreach ($lines as $line) {
            if ($line === '' || preg_match('/^PHẦN 6:/u', $line)) {
                continue;
            }

            $matched = null;
            foreach ($this->sections as $pattern => $info) {
                if (mb_strpos($line, $pattern) === 0 || $line === $pattern) {
                    $matched = $info;
                    break;
                }
            }

            if ($matched !== null) {
                $flush();
                $currentSlug = $matched['slug'];
                $currentTitle = $matched['title'];
                $currentContent = [];
                continue;
            }

            if ($currentSlug !== null && $line !== '') {
                $currentContent[] = $line;
            }
        }

        $flush();

        return $items;
    }
}
