<?php

namespace App\Console\Commands;

use App\Services\PdfMergeService;
use Illuminate\Console\Command;

class PdfDoctor extends Command
{
    protected $signature = 'pdf:doctor';

    protected $description = 'Kiểm tra môi trường xuất PDF (GD, queue, merge binary, worker)';

    public function handle(): int
    {
        $failures = 0;

        $this->info('=== PDF export health check ===');
        $this->newLine();

        $failures += $this->checkPhpExtensions();
        $failures += $this->checkPhpRuntime();
        $failures += $this->checkMergeBinaries();
        $failures += $this->checkQueue();
        $this->checkWorkersHint();

        $this->newLine();
        if ($failures > 0) {
            $this->error("Có {$failures} mục cần xử lý trước khi deploy production.");

            return self::FAILURE;
        }

        $this->info('Môi trường PDF OK.');

        return self::SUCCESS;
    }

    private function checkPhpExtensions(): int
    {
        $this->line('<fg=cyan>PHP extensions</>');

        $failures = 0;

        if (extension_loaded('gd')) {
            $gd = gd_info();
            $this->line('  [OK] GD');
            $freetype = ($gd['FreeType Support'] ?? false) ? 'yes' : 'no';
            $this->line("       FreeType: {$freetype}");
            if ($freetype !== 'yes') {
                $this->warn('       Footer/ảnh cần GD + FreeType — cài php-gd với FreeType.');
                $failures++;
            }
        } else {
            $this->error('  [FAIL] GD — bắt buộc cho footer và ảnh PDF');
            $failures++;
        }

        if (extension_loaded('imagick')) {
            $this->line('  [OK] Imagick (tùy chọn — footer SVG đẹp hơn)');
        } else {
            $this->line('  [--] Imagick không có — footer dùng GD fallback');
        }

        $this->newLine();

        return $failures;
    }

    private function checkPhpRuntime(): int
    {
        $this->line('<fg=cyan>PHP runtime</>');

        $failures = 0;
        $memory   = ini_get('memory_limit') ?: 'unknown';
        $this->line("  memory_limit: {$memory}");
        $memoryBytes = $this->parseMemoryLimit($memory);
        if ($memoryBytes !== null && $memoryBytes < 512 * 1024 * 1024) {
            $this->warn('  Khuyến nghị memory_limit ≥ 512M cho job PDF');
            $failures++;
        }

        if (function_exists('opcache_get_status')) {
            $opcache = opcache_get_status(false);
            $enabled = is_array($opcache) && ($opcache['opcache_enabled'] ?? false);
            $this->line($enabled ? '  [OK] OPcache bật' : '  [WARN] OPcache tắt — bật trên production');
            if (! $enabled) {
                $failures++;
            }
        } else {
            $this->line('  [--] OPcache không khả dụng (CLI)');
        }

        $this->newLine();

        return $failures;
    }

    private function checkMergeBinaries(): int
    {
        $this->line('<fg=cyan>PDF merge</>');

        $failures  = 0;
        $configured = config('pdf.merge_driver', 'auto');
        $this->line("  PDF_MERGE_DRIVER: {$configured}");
        $this->line('  preferred driver: '.PdfMergeService::preferredMergeDriver());

        $qpdf = PdfMergeService::resolveBinary('qpdf', config('pdf.qpdf_binary'));
        if ($qpdf !== null) {
            $ver = PdfMergeService::binaryVersion($qpdf);
            $this->line("  [OK] qpdf: {$qpdf}".($ver ? " ({$ver})" : ''));
        } else {
            $this->line('  [--] qpdf không có trong PATH');
            $this->line('       Cài: apt install qpdf | choco install qpdf | brew install qpdf');
            if (in_array($configured, ['qpdf', 'auto'], true)) {
                $this->line('       (auto sẽ fallback FPDI — chậm hơn trên PDF dài)');
            }
        }

        $pdftk = PdfMergeService::resolveBinary('pdftk', config('pdf.pdftk_binary'));
        if ($pdftk !== null) {
            $ver = PdfMergeService::binaryVersion($pdftk);
            $this->line("  [OK] pdftk: {$pdftk}".($ver ? " ({$ver})" : ''));
        } else {
            $this->line('  [--] pdftk không có (tùy chọn, qpdf ưu tiên hơn)');
        }

        $this->newLine();

        return $failures;
    }

    private function checkQueue(): int
    {
        $this->line('<fg=cyan>Queue</>');

        $failures   = 0;
        $connection = config('queue.default');
        $this->line("  QUEUE_CONNECTION: {$connection}");

        if ($connection === 'sync') {
            $this->error('  [FAIL] sync — dùng database hoặc redis trên live');
            $failures++;
        } elseif ($connection === 'redis') {
            $this->line('  [OK] Redis queue (nhanh hơn database khi nhiều worker)');
        } else {
            $this->line('  [OK] database queue (ổn với 1–2 worker; cân nhắc redis khi scale)');
        }

        $this->line('  pdf-q1 queue: '.config('pdf.queue_q1', 'pdf-q1'));
        $this->line('  pdf-q2 queue: '.config('pdf.queue_q2', 'pdf-q2'));
        $this->newLine();

        return $failures;
    }

    private function checkWorkersHint(): void
    {
        $this->line('<fg=cyan>Workers (Supervisor)</>');
        $this->line('  Chạy ≥2 process để Q1 + Q2 song song:');
        $this->line('    deploy/supervisor/pdf-workers.conf');
        $this->line('  Dev/local:');
        $this->line('    php artisan pdf:queue-work   # lắng nghe pdf-q1,pdf-q2');
        $this->line('  Hoặc 2 terminal:');
        $this->line('    php artisan queue:work --queue=pdf-q1 --timeout=600 --memory=512');
        $this->line('    php artisan queue:work --queue=pdf-q2 --timeout=600 --memory=512');
        $this->newLine();
        $this->line('  Đo baseline: xem log "PdfExport timing" và "PdfExport wall-clock" sau mỗi export.');
    }

    private function parseMemoryLimit(string $limit): ?int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        if (preg_match('/^(\d+)([KMG])?$/i', trim($limit), $m) !== 1) {
            return null;
        }

        $value = (int) $m[1];
        $unit  = strtoupper($m[2] ?? '');

        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => $value,
        };
    }
}
