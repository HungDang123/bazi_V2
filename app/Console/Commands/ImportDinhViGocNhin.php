<?php

namespace App\Console\Commands;

use App\Models\DinhViGocNhin;
use App\Services\DocxTextService;
use Illuminate\Console\Command;

class ImportDinhViGocNhin extends Command
{
    protected $signature = 'import:dinh-vi-goc-nhin
        {file? : Đường dẫn file DOCX}
        {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import PHẦN 3 - I- ĐỊNH VỊ VÀ GÓC NHÌN từ file DOCX vào bảng dinh_vi_goc_nhin';

    public function handle(): int
    {
        $filePath = $this->argument('file')
            ?? base_path('PHẦN 3 - I- ĐỊNH VỊ VÀ GÓC NHÌN.docx');

        if (! file_exists($filePath)) {
            $filePath = base_path('database/PHẦN 3 - I- ĐỊNH VỊ VÀ GÓC NHÌN.docx');
        }

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Đặt file DOCX tại thư mục gốc hoặc: php artisan import:dinh-vi-goc-nhin <đường_dẫn_file>');

            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            $lines = DocxTextService::extractParagraphs($filePath);
            if ($lines === null) {
                $this->error('Không thể đọc nội dung file DOCX.');

                return 1;
            }

            $text = implode("\n", $lines);

            if ($this->option('fresh')) {
                DinhViGocNhin::truncate();
            }

            $item = $this->buildSingleItem($text);
            DinhViGocNhin::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'content' => $item['content'],
                    'sort_order' => $item['sort_order'],
                ]
            );

            $this->info('Hoàn thành! Đã import PHẦN 3 - I- ĐỊNH VỊ VÀ GÓC NHÌN.');

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Gom toàn bộ nội dung thành một mục duy nhất.
     */
    protected function buildSingleItem(string $text): array
    {
        $lines = array_map('trim', explode("\n", $text));
        $contentLines = [];

        foreach ($lines as $line) {
            if (DocxTextService::isSkippableLine($line)) {
                continue;
            }
            if (preg_match('/^PHẦN\\s*3/iu', $line)) {
                continue;
            }
            $contentLines[] = $line;
        }

        $content = implode("\n\n", $contentLines);
        $content = preg_replace('/\n*\\[(CHÈN HÌNH ẢNH|example_image_[^\]]+)\\]\\s*/u', '', $content ?? '');

        return [
            'slug' => 'phan3_dinh_vi_goc_nhin',
            'title' => 'I. Định vị và Góc nhìn',
            'content' => trim((string) $content),
            'sort_order' => 1,
        ];
    }
}

