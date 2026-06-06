<?php

namespace App\Console\Commands;

use App\Models\DinhViGocNhin;
use App\Support\ImportPath;
use App\Services\DocxTextService;
use Illuminate\Console\Command;

class ImportPhan3BoCucNguHanhII extends Command
{
    protected $signature = 'import:phan3-bocuc-nguhanh-ii
        {file? : Đường dẫn file DOCX}
        {--fresh : Xóa mục cũ (theo slug) trước khi import}';

    protected $description = 'Import PHẦN 3 - II. Bố cục Ngũ Hành từ file DOCX vào bảng dinh_vi_goc_nhin';

    private const SLUG = 'phan3_bocuc_ngu_hanh_ii';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 3 - II. BỐ CỤC NGŨ HÀNH BẢN MỆNH - MỤC 1.docx'
        );

        if (! is_file($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Chạy: php artisan import:phan3-bocuc-nguhanh-ii imports/<file.docx>');

            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            $lines = DocxTextService::extractParagraphs($filePath);
            if ($lines === null || $lines === []) {
                $this->error('Không thể đọc nội dung file DOCX.');

                return 1;
            }

            $item = $this->buildItem($lines);

            if ($this->option('fresh')) {
                DinhViGocNhin::where('slug', self::SLUG)->delete();
            }

            DinhViGocNhin::updateOrCreate(
                ['slug' => self::SLUG],
                [
                    'title'      => $item['title'],
                    'content'    => $item['content'],
                    'sort_order' => 2,
                ]
            );

            $this->info('Hoàn thành! Đã import: '.self::SLUG);

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{title: string, content: string}
     */
    protected function buildItem(array $lines): array
    {
        $title         = 'II. Bố cục Ngũ Hành Bản Mệnh';
        $contentLines  = [];

        foreach ($lines as $line) {
            if (($marker = DocxTextService::placeholderImageMarker($line)) !== null) {
                $contentLines[] = $marker;

                continue;
            }

            if (DocxTextService::isSkippableLine($line)) {
                continue;
            }

            if (preg_match('/^II\.\s/iu', $line)) {
                $title = $line;

                continue;
            }

            $contentLines[] = $line;
        }

        return [
            'title'   => $title,
            'content' => $this->buildNumberedContent($contentLines),
        ];
    }

    /**
     * @param  array<int, string>  $lines
     */
    protected function buildNumberedContent(array $lines): string
    {
        if ($lines === []) {
            return '';
        }

        $sections = [];
        $current  = [];

        foreach ($lines as $line) {
            if (preg_match('/^\d+\.\s/u', $line)) {
                if ($current !== []) {
                    $sections[] = implode("\n\n", $current);
                    $current    = [];
                }
            }

            $current[] = $line;
        }

        if ($current !== []) {
            $sections[] = implode("\n\n", $current);
        }

        return trim(implode("\n\n", $sections));
    }
}
