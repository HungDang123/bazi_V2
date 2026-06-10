<?php

namespace App\Services\Pdf;

use Illuminate\Support\Facades\Log;

/**
 * Đo thời gian xuất PDF (render / merge / footer) — reset mỗi lần build quyển.
 */
class PdfExportMetrics
{
    private static float $startedAt = 0.0;

    private static float $baziMs = 0.0;

    private static float $renderMs = 0.0;

    private static int $renderCount = 0;

    private static ?int $quyen = null;

    public static function begin(int $quyen): void
    {
        self::$quyen       = $quyen;
        self::$startedAt   = microtime(true);
        self::$baziMs      = 0.0;
        self::$renderMs    = 0.0;
        self::$renderCount = 0;
    }

    public static function addBaziMs(float $ms): void
    {
        self::$baziMs += $ms;
    }

    public static function recordRender(callable $callback): void
    {
        $t0 = microtime(true);
        $callback();
        self::$renderMs += (microtime(true) - $t0) * 1000;
        self::$renderCount++;
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public static function logFinish(array $extra = []): void
    {
        if (! config('pdf.log_timing', true)) {
            return;
        }

        $totalMs = (microtime(true) - self::$startedAt) * 1000;

        Log::info('PdfExport timing', array_merge([
            'quyen'        => self::$quyen,
            'render_count' => self::$renderCount,
            'bazi_ms'      => round(self::$baziMs, 1),
            'render_ms'    => round(self::$renderMs, 1),
            'total_ms'     => round($totalMs, 1),
        ], $extra));
    }
}
