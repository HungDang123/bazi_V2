<?php

namespace App\Console\Commands;

use App\Models\TongQuanKhiaCanh;
use App\Support\ImportPath;
use App\Services\DocxTextService;
use Illuminate\Console\Command;

class ImportPhan5Phan6Transition extends Command
{
    protected $signature = 'import:phan5-phan6-transition
        {file? : Đường dẫn file PHẦN 5 - PHẦN 6.docx}';

    protected $description = 'Import đoạn kết nối Phần 5 → Phần 6 vào cuối tong_quan_khia_canh';

    public function handle(): int
    {
        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'PHẦN 5 - PHẦN 6.docx'
        );

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");

            return 1;
        }

        $lines = DocxTextService::extractParagraphs($filePath);
        if ($lines === null || $lines === []) {
            $this->error('Không đọc được nội dung file DOCX.');

            return 1;
        }

        $content = implode("\n\n", array_values(array_filter(array_map('trim', $lines))));

        $maxSort = (int) TongQuanKhiaCanh::max('sort_order');

        TongQuanKhiaCanh::updateOrCreate(
            ['slug' => 'transition_phan6'],
            [
                'title' => null,
                'content' => $content,
                'sort_order' => $maxSort + 1,
            ]
        );

        $this->info('Đã import đoạn kết nối Phần 5 → Phần 6 (slug: transition_phan6).');

        return 0;
    }
}
