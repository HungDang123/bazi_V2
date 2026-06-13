<?php

namespace App\Services;

use App\Http\Controllers\PdfExportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LaSoPdfGeneratorService
{
    public function __construct(
        private readonly PdfExportController $pdfExportController
    ) {}

    public function generateQuyen1(array $params, string $outputPath): string
    {
        $this->ensureParentDir($outputPath);
        $this->pdfExportController->buildQuyen1Pdf($this->makeRequest($params), $outputPath);

        return $outputPath;
    }

    public function generateQuyen2(array $params, string $outputPath): string
    {
        $this->ensureParentDir($outputPath);
        $this->pdfExportController->buildQuyen2Pdf($this->makeRequest($params), $outputPath);

        return $outputPath;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function normalizeParams(array $params): array
    {
        $normalized = $params;

        if (empty($normalized['gender'])) {
            $normalized['gender'] = ($normalized['g'] ?? 'male') === 'female' ? 'Nữ' : 'Nam';
        }

        if (empty($normalized['birth_date'])) {
            $normalized['birth_date'] = $this->formatBirthDate($normalized);
        }

        if (empty($normalized['bat_tu']) && ! empty($normalized['y']) && ! empty($normalized['m']) && ! empty($normalized['d'])) {
            try {
                $bazi = BaZiServiceV2::calc(
                    (string) ($normalized['full_name'] ?? ''),
                    (int) $normalized['y'],
                    (int) $normalized['m'],
                    (int) $normalized['d'],
                    isset($normalized['h']) && $normalized['h'] !== '' ? (int) $normalized['h'] : null,
                    isset($normalized['minute']) && $normalized['minute'] !== '' ? (int) $normalized['minute'] : null,
                    (string) ($normalized['g'] ?? 'male')
                );
                $normalized['bat_tu'] = $this->formatBatTuString($bazi['bat_tu'] ?? []);
            } catch (\Throwable) {
                $normalized['bat_tu'] = '';
            }
        }

        $nguHanhDong = $normalized['ngu_hanh_dong'] ?? null;
        if (is_array($nguHanhDong)) {
            foreach (['kim', 'moc', 'thuy', 'hoa', 'tho'] as $key) {
                if (! array_key_exists($key, $normalized) && isset($nguHanhDong[$key])) {
                    $normalized[$key] = (int) $nguHanhDong[$key];
                }
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function paramsHash(array $params): string
    {
        $normalized = $this->normalizeParams($params);
        ksort($normalized);

        return hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }

    private function makeRequest(array $params): Request
    {
        return Request::create('/', 'GET', $this->normalizeParams($params));
    }

    private function ensureParentDir(string $outputPath): void
    {
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            File::ensureDirectoryExists($dir, 0755, true);
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function formatBirthDate(array $params): string
    {
        $d = (int) ($params['d'] ?? 0);
        $m = (int) ($params['m'] ?? 0);
        $y = (int) ($params['y'] ?? 0);
        $h = $params['h'] ?? null;
        $minute = $params['minute'] ?? '00';

        if ($h === null || $h === '') {
            return sprintf('ngày %02d thg %02d, %d', $d, $m, $y);
        }

        return sprintf(
            '%02d:%02d – ngày %02d thg %02d, %d',
            (int) $h,
            (int) $minute,
            $d,
            $m,
            $y
        );
    }

    /**
     * @param  array<string, mixed>  $batTu
     */
    private function formatBatTuString(array $batTu): string
    {
        $parts = [];
        $order = [
            'year'  => 'Năm',
            'month' => 'Tháng',
            'day'   => 'Ngày',
            'hour'  => 'Giờ',
        ];

        foreach ($order as $key => $label) {
            $pillar = $batTu[$key] ?? null;
            if (! is_array($pillar)) {
                continue;
            }

            $can = trim((string) ($pillar['can']['thien_can'] ?? ''));
            $chi = trim((string) ($pillar['chi']['dia_chi'] ?? ''));
            if ($can === '' && $chi === '') {
                continue;
            }

            $parts[] = trim($label.' '.$can.' '.$chi);
        }

        return implode(', ', $parts);
    }
}
