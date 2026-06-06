<?php

namespace App\Console\Commands;

use App\Models\TongQuanKhiaCanh;
use Illuminate\Console\Command;

class ImportTongQuanKhiaCanh extends Command
{
    protected $signature = 'import:tong-quan-khia-canh
        {file? : Đường dẫn file DOCX}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import Tổng quan các khía cạnh từ file PHẦN 5 - I. TỔNG QUAN CÁC KHÍA CẠNH.docx';

    /** Map slug khía cạnh (match prefix) */
    protected array $sectionPrefixes = [
        'Sự nghiệp' => 'su_nghiep',
        'Tài chính' => 'tai_chinh',
        'Tình duyên' => 'tinh_duyen',
        'Sức khỏe' => 'suc_khoe',
        'Phát triển bản thân' => 'phat_trien_ban_than',
        'Kết nối xã hội' => 'ket_noi_xa_hoi',
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file')
            ?? base_path('PHẦN 5 - I. TỔNG QUAN CÁC KHÍA CẠNH.docx');

        if (! file_exists($filePath)) {
            $filePath = base_path('database/PHẦN 5 - I. TỔNG QUAN CÁC KHÍA CẠNH.docx');
        }

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file DOCX tại thư mục gốc hoặc: php artisan import:tong-quan-khia-canh <đường_dẫn_file>');
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
                TongQuanKhiaCanh::truncate();
            }

            $items = $this->parseSections($text);
            $count = 0;
            foreach ($items as $sortOrder => $item) {
                TongQuanKhiaCanh::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'title' => $item['title'],
                        'content' => $item['content'],
                        'sort_order' => $sortOrder,
                    ]
                );
                $count++;
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

    protected function parseSections(string $text): array
    {
        $lines = array_map('trim', explode("\n", $text));
        $items = [];
        $introParts = [];
        $currentSlug = null;
        $currentTitle = '';
        $currentContent = [];

        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            if (preg_match('/^PHẦN 5:/u', $line) || $line === '') {
                $i++;
                continue;
            }

            if (preg_match('/^I\.\s+Tổng quan về các khía cạnh/u', $line)) {
                $i++;
                while ($i < count($lines)) {
                    $next = $lines[$i];
                    if ($this->matchSectionPrefix($next) !== null) {
                        break;
                    }
                    if ($next !== '' && $next !== '[CHÈN HÌNH ẢNH]') {
                        $introParts[] = $next;
                    }
                    $i++;
                }
                $items[] = [
                    'slug' => 'intro',
                    'title' => 'I. Tổng quan về các khía cạnh',
                    'content' => implode("\n\n", $introParts),
                ];
                continue;
            }

            $matchedSlug = $this->matchSectionPrefix($line);
            if ($matchedSlug !== null) {
                if ($currentSlug !== null && ! empty($currentContent)) {
                    $content = implode("\n", $currentContent);
                    $content = preg_replace('/\n*\[CHÈN HÌNH ẢNH\]\s*/u', '', $content);
                    $content = trim($content);
                    $items[] = [
                        'slug' => $currentSlug,
                        'title' => $currentTitle,
                        'content' => $content,
                    ];
                }
                $currentSlug = $matchedSlug;
                $currentTitle = $line;
                $currentContent = [];
                $i++;
                while ($i < count($lines)) {
                    $next = $lines[$i];
                    if ($this->matchSectionPrefix($next) !== null) {
                        break;
                    }
                    if ($next !== '[CHÈN HÌNH ẢNH]' && $next !== '') {
                        $currentContent[] = $next;
                    }
                    $i++;
                }
                continue;
            }
            $i++;
        }

        if ($currentSlug !== null && ! empty($currentContent)) {
            $content = implode("\n", $currentContent);
            $content = preg_replace('/\n*\[CHÈN HÌNH ẢNH\]\s*/u', '', $content);
            $content = trim($content);
            $items[] = [
                'slug' => $currentSlug,
                'title' => $currentTitle,
                'content' => $content,
            ];
        }

        return array_values($items);
    }

    /** Match line start with section prefix, return slug or null */
    protected function matchSectionPrefix(string $line): ?string
    {
        foreach ($this->sectionPrefixes as $prefix => $slug) {
            if (mb_strpos($line, $prefix) === 0) {
                return $slug;
            }
        }
        return null;
    }
}
