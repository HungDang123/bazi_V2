<?php

namespace App\Jobs;

use App\Models\LaSoPdfExport;
use App\Services\LaSoPdfGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateLaSoPdfJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 2;

    /**
     * @param  array<string, mixed>  $params
     */
    public function __construct(
        public string $exportId,
        public int $quyen,
        public array $params
    ) {
        $queue = $quyen === 1
            ? config('pdf.queue_q1', 'pdf-q1')
            : config('pdf.queue_q2', 'pdf-q2');

        $this->onQueue($queue);
    }

    public function handle(LaSoPdfGeneratorService $generator): void
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');

        $export = LaSoPdfExport::query()->findOrFail($this->exportId);
        $statusCol = $this->quyen === 1 ? 'q1_status' : 'q2_status';
        $pathCol   = $this->quyen === 1 ? 'q1_path' : 'q2_path';
        $errorCol  = $this->quyen === 1 ? 'q1_error' : 'q2_error';
        $readyCol  = $this->quyen === 1 ? 'q1_ready_at' : 'q2_ready_at';

        $export->update([
            $statusCol => LaSoPdfExport::STATUS_PROCESSING,
            $errorCol  => null,
        ]);

        $outputPath = $export->quyenPath($this->quyen);

        try {
            if ($this->quyen === 1) {
                $generator->generateQuyen1($this->params, $outputPath);
            } else {
                $generator->generateQuyen2($this->params, $outputPath);
            }

            if (! is_file($outputPath)) {
                throw new \RuntimeException('File PDF không được tạo');
            }

            $export->update([
                $statusCol => LaSoPdfExport::STATUS_READY,
                $pathCol   => $outputPath,
                $readyCol  => now(),
                $errorCol  => null,
            ]);

            if ($export->queued_at !== null) {
                Log::info('PdfExport wall-clock', [
                    'export_id'          => $this->exportId,
                    'quyen'              => $this->quyen,
                    'queued_to_ready_ms' => (int) $export->queued_at->diffInMilliseconds(now()),
                ]);
            }
        } catch (Throwable $e) {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }

            $export->update([
                $statusCol => LaSoPdfExport::STATUS_FAILED,
                $errorCol  => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $export = LaSoPdfExport::query()->find($this->exportId);
        if ($export === null) {
            return;
        }

        $statusCol = $this->quyen === 1 ? 'q1_status' : 'q2_status';
        $errorCol  = $this->quyen === 1 ? 'q1_error' : 'q2_error';

        if ($export->{$statusCol} !== LaSoPdfExport::STATUS_READY) {
            $export->update([
                $statusCol => LaSoPdfExport::STATUS_FAILED,
                $errorCol  => $exception?->getMessage() ?? 'Job thất bại',
            ]);
        }
    }
}
