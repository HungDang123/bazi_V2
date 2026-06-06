<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfDownloadService
{
    public const FILENAME_QUYEN_1 = 'pdf_cuon_1.pdf';

    public const FILENAME_QUYEN_2 = 'pdf_cuon_2.pdf';

    /**
     * Trả PDF dưới dạng stream, nén gzip nếu client hỗ trợ.
     */
    public static function serveStored(string $filePath, string $filename): StreamedResponse
    {
        return self::download($filePath, $filename, false);
    }

    public static function download(string $filePath, string $filename, bool $deleteAfter = true): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $acceptsGzip = str_contains(strtolower(request()->header('Accept-Encoding', '')), 'gzip')
            && function_exists('deflate_init');

        if (!$acceptsGzip) {
            return self::plainDownload($filePath, $filename, $deleteAfter, $headers);
        }

        return response()->streamDownload(
            function () use ($filePath, $deleteAfter) {
                self::streamGzipFile($filePath, $deleteAfter);
            },
            $filename,
            array_merge($headers, [
                'Content-Encoding' => 'gzip',
                'Vary'             => 'Accept-Encoding',
            ])
        );
    }

    private static function plainDownload(
        string $filePath,
        string $filename,
        bool $deleteAfter,
        array $headers
    ): StreamedResponse {
        return response()->streamDownload(function () use ($filePath, $deleteAfter) {
            readfile($filePath);
            if ($deleteAfter) {
                @unlink($filePath);
            }
        }, $filename, $headers);
    }

    private static function streamGzipFile(string $filePath, bool $deleteAfter): void
    {
        $file = fopen($filePath, 'rb');
        if ($file === false) {
            return;
        }

        $deflate = deflate_init(ZLIB_ENCODING_GZIP, ['level' => 6]);
        if ($deflate === false) {
            fclose($file);
            readfile($filePath);
            if ($deleteAfter) {
                @unlink($filePath);
            }
            return;
        }

        $processed = false;
        $chunk     = fread($file, 65536);

        while ($chunk !== false && $chunk !== '') {
            $processed = true;
            $next      = fread($file, 65536);
            $isLast    = ($next === false || $next === '');

            $out = deflate_add($deflate, $chunk, $isLast ? ZLIB_FINISH : ZLIB_NO_FLUSH);
            if ($out !== false && $out !== '') {
                echo $out;
            }

            $chunk = $next;
        }

        if (!$processed) {
            $out = deflate_add($deflate, '', ZLIB_FINISH);
            if ($out !== false && $out !== '') {
                echo $out;
            }
        }

        fclose($file);

        if ($deleteAfter) {
            @unlink($filePath);
        }
    }
}
