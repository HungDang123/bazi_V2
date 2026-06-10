<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Queue worker cho PDF lá số — tránh PHP max_execution_time=300 mặc định làm worker chết khi chờ job.
 */
class PdfQueueWork extends Command
{
    protected $signature = 'pdf:queue-work
                            {--once : Chỉ xử lý một job rồi thoát}
                            {--queue= : Queues (mặc định pdf-q1,pdf-q2)}';

    protected $description = 'Chạy queue worker tạo PDF (job timeout 600s, worker không giới hạn 300s PHP)';

    public function handle(): int
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '512M');

        $queues = $this->option('queue') ?: implode(',', [
            config('pdf.queue_q1', 'pdf-q1'),
            config('pdf.queue_q2', 'pdf-q2'),
        ]);

        $params = [
            '--queue' => $queues,
            '--timeout' => 600,
            '--memory' => 512,
            '--tries' => 2,
            '--sleep' => 1,
        ];

        if ($this->option('once')) {
            $params['--once'] = true;
        }

        $this->info("PDF queue worker — queues: {$queues}, job timeout 600s. Nhấn Ctrl+C để dừng.");

        return $this->call('queue:work', $params);
    }
}
