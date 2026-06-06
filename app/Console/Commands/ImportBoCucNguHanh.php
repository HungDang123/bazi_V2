<?php

namespace App\Console\Commands;

use App\Models\Hanh;
use App\Models\HanhNoiDung;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBoCucNguHanh extends Command
{
    protected $signature = 'import:bo-cuc-ngu-hanh {file? : Đường dẫn file Excel} {--fresh : Xóa dữ liệu cũ của hành trước khi import}';

    protected $description = 'Import bố cục ngũ hành từ file Excel bo_cuc_ngu_hanh.xlsx';

    /** Map mức độ (chứa chuỗi) sang slug */
    protected array $mucDoPatterns = [
        'khuyet_0' => ['bị khuyết 0%', 'bị khuyết (0%)', 'khuyết 0%', 'khuyết (0%)'],
        'duoi_30' => ['dưới 30%'],
        '30_60' => ['từ 30% đến 60%', '30% đến 60%'],
        '60_80' => ['từ 60% đến 80%', '60% đến 80%'],
        'tren_80' => ['trên 80%', 'từ trên 80%'],
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');

        $filePath = $this->argument('file')
            ?? base_path('database/bo_cuc_ngu_hanh.xlsx');

        if (! file_exists($filePath)) {
            $filePath = base_path('bo_cuc_ngu_hanh.xlsx');
        }

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Chạy: php artisan import:bo-cuc-ngu-hanh database/bo_cuc_ngu_hanh.xlsx');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $totalSheets = $spreadsheet->getSheetCount();

            $imported = 0;
            $skipped = 0;

            for ($i = 0; $i < $totalSheets; $i++) {
                $sheet = $spreadsheet->getSheet($i);
                $sheetName = trim((string) $sheet->getTitle());

                $hanh = Hanh::whereRaw('LOWER(name) = ?', [mb_strtolower($sheetName)])->first();
                if (! $hanh) {
                    $this->warn("Bỏ qua sheet '{$sheetName}' (không tìm thấy hành).");
                    $skipped++;
                    continue;
                }

                if ($this->option('fresh')) {
                    HanhNoiDung::where('hanh_id', $hanh->id)->delete();
                }

                $highestRow = $sheet->getHighestRow();
                $rowCount = 0;
                $currentSlug = null;
                $items = [];

                $flushSection = function () use ($hanh, &$currentSlug, &$items, &$rowCount) {
                    if ($currentSlug === null || empty($items)) {
                        return;
                    }
                    $slugOrder = array_search($currentSlug, array_keys($this->mucDoPatterns), true);
                    $baseSort = ($slugOrder !== false ? $slugOrder + 1 : 0) * 1000;
                    foreach ($items as $idx => $item) {
                        HanhNoiDung::create([
                            'hanh_id' => $hanh->id,
                            'slug' => $currentSlug,
                            'title' => $item['title'] ?: null,
                            'content' => $item['content'] ?: null,
                            'sort_order' => $baseSort + $idx,
                        ]);
                        $rowCount++;
                    }
                };

                for ($row = 1; $row <= $highestRow + 1; $row++) {
                    $mucDo = $this->normalizeText((string) ($sheet->getCell("A{$row}")->getValue() ?? ''));
                    $title = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
                    $content = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));

                    $slug = $mucDo ? $this->mucDoToSlug($mucDo) : null;

                    if ($slug !== null) {
                        $flushSection();
                        $currentSlug = $slug;
                        $items = [];
                        if ($title || $content) {
                            if ($title) {
                                $items[] = ['title' => $title, 'content' => $content];
                            } else {
                                $items[] = ['title' => null, 'content' => $content];
                            }
                        }
                    } elseif ($currentSlug !== null && ($title || $content)) {
                        if ($title) {
                            $items[] = ['title' => $title, 'content' => $content];
                        } else {
                            if (! empty($items) && $content !== '') {
                                $lastIdx = count($items) - 1;
                                $prev = $items[$lastIdx]['content'] ?? '';
                                $items[$lastIdx]['content'] = $prev === '' ? $content : $prev . "\n" . $content;
                            } elseif ($content !== '') {
                                $items[] = ['title' => null, 'content' => $content];
                            }
                        }
                    }
                }

                $flushSection();
                $imported += $rowCount;
                $this->info("Sheet '{$sheetName}': {$rowCount} mục đã import.");
            }

            $this->info("Hoàn thành! Tổng {$imported} mục đã import.");
            if ($skipped > 0) {
                $this->warn("Đã bỏ qua {$skipped} hàng.");
            }

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            return 1;
        }
    }

    protected function normalizeText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return $text;
    }

    protected function slugToTitle(string $slug, string $hanhName): string
    {
        $map = [
            'khuyet_0' => 'bị khuyết 0%',
            'duoi_30' => 'dưới 30%',
            '30_60' => 'từ 30% đến 60%',
            '60_80' => 'từ 60% đến 80%',
            'tren_80' => 'trên 80%',
        ];
        $suffix = $map[$slug] ?? $slug;
        return "{$hanhName} {$suffix}";
    }

    protected function mucDoToSlug(string $mucDo): ?string
    {
        $mucDo = mb_strtolower($this->normalizeText($mucDo));
        foreach ($this->mucDoPatterns as $slug => $patterns) {
            foreach ($patterns as $pattern) {
                if (mb_strpos($mucDo, mb_strtolower($pattern)) !== false) {
                    return $slug;
                }
            }
        }
        return null;
    }
}
