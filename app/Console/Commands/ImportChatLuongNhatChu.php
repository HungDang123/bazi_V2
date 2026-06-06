<?php

namespace App\Console\Commands;

use App\Models\ChatLuongNhatChu;
use App\Support\ImportPath;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportChatLuongNhatChu extends Command
{
    protected $signature = 'import:chat-luong-nhat-chu {file? : Đường dẫn file Excel} {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import chất lượng nhật chủ từ file Excel chat_luong_nhat_chu.xlsx';

    protected array $muaMap = [
        'mùa xuân' => 'Mùa Xuân',
        'mua xuân' => 'Mùa Xuân',
        'xuân' => 'Mùa Xuân',
        'mùa hè' => 'Mùa Hè',
        'mua he' => 'Mùa Hè',
        'hè' => 'Mùa Hè',
        'mùa thu' => 'Mùa Thu',
        'mua thu' => 'Mùa Thu',
        'thu' => 'Mùa Thu',
        'mùa đông' => 'Mùa Đông',
        'mua dong' => 'Mùa Đông',
        'đông' => 'Mùa Đông',
        'dong' => 'Mùa Đông',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');

        $filePath = ImportPath::resolve(
            $this->argument('file'),
            'chat_luong_nhat_chu.xlsx'
        );

        if (! file_exists($filePath)) {
            $this->error("File không tồn tại: {$filePath}");
            $this->info('Chạy: php artisan import:chat-luong-nhat-chu imports/chat_luong_nhat_chu.xlsx');
            return 1;
        }

        $this->info("Đang đọc file: {$filePath}");

        if ($this->option('fresh')) {
            ChatLuongNhatChu::truncate();
        }

        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $totalSheets = $spreadsheet->getSheetCount();
            $totalImported = 0;

            for ($i = 0; $i < $totalSheets; $i++) {
                $sheet = $spreadsheet->getSheet($i);
                $sheetName = trim((string) $sheet->getTitle());
                $thienCan = $this->normalizeThienCan($sheetName);

                $count = $this->importSheet($sheet, $thienCan);
                $totalImported += $count;
                $this->info("Sheet '{$sheetName}' ({$thienCan}): {$count} mục đã import.");
            }

            $this->info("Hoàn thành! Tổng {$totalImported} mục đã import.");

            return 0;
        } catch (\Throwable $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    protected function importSheet($sheet, string $thienCan): int
    {
        $highestRow = $sheet->getHighestRow();
        $currentMuaSinh = null;
        $currentTrangThai = null;
        $items = [];
        $sortBase = 0;
        $rowCount = 0;
        $flushSection = function () use ($thienCan, &$currentMuaSinh, &$currentTrangThai, &$items, &$sortBase, &$rowCount) {
            if ($currentMuaSinh === null || empty($items)) {
                return;
            }
            foreach ($items as $idx => $item) {
                ChatLuongNhatChu::create([
                    'thien_can' => $thienCan,
                    'mua_sinh' => $currentMuaSinh,
                    'trang_thai' => $currentTrangThai,
                    'title' => $item['title'] ?: null,
                    'content' => $item['content'] ?: null,
                    'sort_order' => $sortBase + $idx,
                ]);
                $rowCount++;
            }
            $sortBase += count($items);
        };

        for ($row = 1; $row <= $highestRow + 1; $row++) {
            $colA = $this->normalizeText((string) ($sheet->getCell("A{$row}")->getValue() ?? ''));
            $title = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
            $content = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));

            $muaSinh = $this->parseMuaSinh($colA);
            $trangThai = $this->parseTrangThai($title);

            if ($muaSinh !== null) {
                $flushSection();
                $currentMuaSinh = $muaSinh;
                $currentTrangThai = $trangThai ?? $currentTrangThai;
                $items = [];
                if ($title || $content) {
                    if ($title) {
                        $items[] = ['title' => $title, 'content' => $content];
                    } else {
                        $items[] = ['title' => null, 'content' => $content];
                    }
                }
            } elseif ($currentMuaSinh !== null && ($title || $content)) {
                if ($title) {
                    if ($trangThai !== null) {
                        $currentTrangThai = $trangThai;
                    }
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

        return $rowCount;
    }

    protected function normalizeText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return $text;
    }

    protected function normalizeThienCan(string $name): string
    {
        $map = [
            'GIÁP' => 'Giáp', 'ẤT' => 'Ất', 'BÍNH' => 'Bính', 'ĐINH' => 'Đinh',
            'MẬU' => 'Mậu', 'KỶ' => 'Kỷ', 'KỲ' => 'Kỷ', 'CANH' => 'Canh', 'TÂN' => 'Tân',
            'NHÂM' => 'Nhâm', 'QUÝ' => 'Quý',
        ];
        $key = mb_strtoupper($name);
        return $map[$key] ?? mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    protected function parseMuaSinh(string $text): ?string
    {
        if (empty($text)) {
            return null;
        }
        $t = mb_strtolower($text);
        foreach ($this->muaMap as $pattern => $mua) {
            if (mb_strpos($t, $pattern) !== false) {
                return $mua;
            }
        }
        return null;
    }

    protected function parseTrangThai(string $text): ?string
    {
        if (empty($text)) {
            return null;
        }
        $t = mb_strtolower($text);
        if (mb_strpos($t, 'thân vượng') !== false || mb_strpos($t, 'than vuong') !== false) {
            return 'Thân Vượng';
        }
        if (mb_strpos($t, 'thân nhược') !== false || mb_strpos($t, 'than nhuoc') !== false) {
            return 'Thân Nhược';
        }
        return null;
    }
}
