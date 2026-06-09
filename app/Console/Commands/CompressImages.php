<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompressImages extends Command
{
    protected $signature = 'images:compress
        {apikey : TinyPNG API key từ tinypng.com/developers}
        {--dry-run : Chỉ hiển thị danh sách, không nén thật}
        {--skip-larger : Bỏ qua nếu file nén lớn hơn file gốc}';

    protected $description = 'Nén tất cả ảnh PNG/JPG trong resources/views/pdfs và public/images bằng TinyPNG API';

    /** @var array<int, string> */
    private array $scanDirs = [];

    /** @var array<int, string> */
    private array $skipDirs = ['storage', 'vendor', 'node_modules'];

    public function handle(): int
    {
        $apiKey = (string) $this->argument('apikey');
        $dryRun = (bool) $this->option('dry-run');
        $skipLarger = (bool) $this->option('skip-larger');

        $this->scanDirs = [
            resource_path('views/pdfs'),
            public_path('images'),
        ];

        \Tinify\setKey($apiKey);

        // Validate API key
        try {
            \Tinify\validate();
        } catch (\Tinify\AccountException $e) {
            $this->error('API key không hợp lệ: ' . $e->getMessage());
            return 1;
        }

        $compressions = \Tinify\compressionCount();
        $this->info("API key hợp lệ — đã dùng {$compressions}/500 lần nén tháng này.");

        $images = $this->collectImages();
        $count  = count($images);

        if ($count === 0) {
            $this->info('Không tìm thấy ảnh.');
            return 0;
        }

        $this->info("Tìm thấy {$count} ảnh.");

        if ($dryRun) {
            $this->table(['File', 'Kích thước'], array_map(fn (string $p) => [
                str_replace(base_path() . DIRECTORY_SEPARATOR, '', $p),
                round(filesize($p) / 1024 / 1024, 2) . ' MB',
            ], $images));
            $this->warn("--dry-run: không nén thật.");
            return 0;
        }

        if (500 - $compressions < $count) {
            $this->error("Không đủ quota: cần {$count}, còn " . (500 - $compressions) . " lần.");
            return 1;
        }

        $saved    = 0;
        $errors   = 0;
        $skipped  = 0;
        $bar      = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($images as $path) {
            $rel      = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
            $origSize = filesize($path);

            try {
                $tmpPath = $path . '.tinify.tmp';
                \Tinify\fromFile($path)->toFile($tmpPath);
                $newSize = filesize($tmpPath);

                if ($skipLarger && $newSize >= $origSize) {
                    unlink($tmpPath);
                    $skipped++;
                } else {
                    rename($tmpPath, $path);
                    $savedBytes = $origSize - $newSize;
                    $saved     += $savedBytes;
                    $pct        = round($savedBytes / $origSize * 100);
                    $this->newLine();
                    $this->line("  ✓ {$rel} — tiết kiệm {$pct}% (" . round($savedBytes / 1024) . " KB)");
                }
            } catch (\Tinify\Exception $e) {
                if (isset($tmpPath) && file_exists($tmpPath)) {
                    unlink($tmpPath);
                }
                $errors++;
                $this->newLine();
                $this->warn("  ✗ {$rel}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $savedMB = round($saved / 1024 / 1024, 1);
        $this->info("Hoàn thành! Tiết kiệm tổng: {$savedMB} MB | Lỗi: {$errors} | Bỏ qua: {$skipped}");

        $after = \Tinify\compressionCount();
        $this->info("Đã dùng tổng {$after}/500 lần nén tháng này.");

        return $errors > 0 ? 1 : 0;
    }

    /** @return array<int, string> */
    private function collectImages(): array
    {
        $result = [];

        foreach ($this->scanDirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($it as $file) {
                /** @var \SplFileInfo $file */
                if (! $file->isFile()) {
                    continue;
                }

                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
                    continue;
                }

                $result[] = $file->getRealPath();
            }
        }

        // Loại bỏ trùng lặp (symlink, etc.)
        return array_values(array_unique($result));
    }
}
