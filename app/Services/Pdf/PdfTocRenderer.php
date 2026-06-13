<?php

namespace App\Services\Pdf;

use App\Services\PdfFooterService;
use App\Services\PdfRenderService;
use Illuminate\Http\Request;

class PdfTocRenderer
{
    private const PAGE_HEIGHT_MM = 297.0;

    /** Trang 1: dưới cuộn «MỤC LỤC». */
    private const FIRST_PAGE_TOP_MM = 50.0;

    /** Trang tiếp (LBTV-587) + nhãn «tiếp theo». */
    private const CONTINUED_PAGE_TOP_MM = 18.0;

    private const CONTINUED_LABEL_MM = 8.0;

    /** Vùng footer overlay (badge + tên). */
    private const FOOTER_RESERVE_MM = 39.0;

    /** Tránh đè lên thuyền rồng góc dưới (586/587). */
    private const DRAGON_RESERVE_MM = 52.0;

    private const PART_MARGIN_MM = 4.0;

    private const ITEM_MARGIN_MM = 2.2;

    private const LINE_MM = 4.0;

    public static function backgroundPath(int $pageIndex = 0): string
    {
        return PdfTocAssetService::backgroundPath($pageIndex);
    }

    /**
     * @param  array<int, array{rows: array<int, array<string, mixed>>}>  $pages
     * @return array<int, array{rows: array<int, array<string, mixed>>, templatePath: string}>
     */
    public static function attachBackgrounds(array $pages): array
    {
        $result = [];
        foreach ($pages as $idx => $page) {
            $result[] = array_merge($page, [
                'templatePath' => PdfTocAssetService::backgroundPath((int) $idx),
            ]);
        }

        return $result;
    }

    /**
     * @param  callable(int): array<int, array{title: string, page: int|null, items: array}>  $outlineFactory
     */
    public static function render(callable $outlineFactory, string $outputPath): string
    {
        $actualTocPages = 1;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $sections = $outlineFactory($actualTocPages);
            $pages = self::attachBackgrounds(self::paginateSections($sections));

            PdfRenderService::saveView('pdfs.shared.la-so-muc-luc', [
                'pages' => $pages,
            ], $outputPath);

            $counted = is_file($outputPath) ? PdfFooterService::countPdfPages($outputPath) : 0;
            if ($counted <= 0) {
                break;
            }

            if ($counted === $actualTocPages) {
                break;
            }

            $actualTocPages = $counted;
        }

        return $outputPath;
    }

    public static function renderQuyen1(
        PdfTocTracker $tracker,
        Request $req,
        array $context,
        string $outputPath
    ): string {
        return self::render(
            static fn (int $tocPages): array => PdfTocOutline::forQuyen1($tracker, $req, $context, $tocPages),
            $outputPath
        );
    }

    public static function renderQuyen2(
        PdfTocTracker $tracker,
        Request $req,
        array $context,
        string $outputPath
    ): string {
        return self::render(
            static fn (int $tocPages): array => PdfTocOutline::forQuyen2($tracker, $req, $context, $tocPages),
            $outputPath
        );
    }

    /**
     * @param  array<int, array{title: string, page: int|null, items: array<int, array{label: string, page: int}>}>  $sections
     * @return array<int, array{rows: array<int, array<string, mixed>>}>
     */
    public static function paginateSections(array $sections): array
    {
        $flat = [];

        foreach ($sections as $section) {
            $partPage = $section['page'] ?? null;
            if ($partPage === null && ($section['items'] ?? []) === []) {
                continue;
            }

            $flat[] = [
                'type'  => 'part',
                'title' => (string) ($section['title'] ?? ''),
                'page'  => $partPage,
            ];

            foreach ($section['items'] ?? [] as $item) {
                if (($item['page'] ?? null) === null) {
                    continue;
                }
                $flat[] = [
                    'type'  => 'item',
                    'title' => (string) ($item['label'] ?? ''),
                    'page'  => (int) $item['page'],
                ];
            }
        }

        if ($flat === []) {
            return [['rows' => []]];
        }

        $pages = [];
        $chunk = [];
        $usedMm = 0.0;
        $pageIndex = 0;

        $i = 0;
        $total = count($flat);
        while ($i < $total) {
            $row = $flat[$i];
            $rowMm = self::estimateRowHeightMm($row);
            $budget = self::pageBudgetMm($pageIndex);

            if (
                ($row['type'] ?? '') === 'part'
                && isset($flat[$i + 1])
                && $chunk !== []
            ) {
                $nextMm = self::estimateRowHeightMm($flat[$i + 1]);
                if ($usedMm + $rowMm + $nextMm > $budget) {
                    [$chunk, $orphan] = self::splitTrailingPartHeader($chunk);
                    if ($chunk !== []) {
                        $pages[] = ['rows' => $chunk];
                    }
                    $chunk = $orphan !== null ? [$orphan] : [];
                    $usedMm = $orphan !== null ? self::estimateRowHeightMm($orphan) : 0.0;
                    $pageIndex++;
                    continue;
                }
            }

            if ($chunk !== [] && ($usedMm + $rowMm) > $budget) {
                [$chunk, $orphan] = self::splitTrailingPartHeader($chunk);
                $pages[] = ['rows' => $chunk];
                $chunk = $orphan !== null ? [$orphan] : [];
                $usedMm = $orphan !== null ? self::estimateRowHeightMm($orphan) : 0.0;
                $pageIndex++;
                continue;
            }

            if ($chunk === [] && $rowMm > $budget) {
                $pages[] = ['rows' => [$row]];
                $pageIndex++;
                $i++;

                continue;
            }

            $chunk[] = $row;
            $usedMm += $rowMm;
            $i++;
        }

        if ($chunk !== []) {
            $pages[] = ['rows' => $chunk];
        }

        return $pages;
    }

    private static function pageBudgetMm(int $pageIndex): float
    {
        $top = $pageIndex === 0 ? self::FIRST_PAGE_TOP_MM : self::CONTINUED_PAGE_TOP_MM;
        if ($pageIndex > 0) {
            $top += self::CONTINUED_LABEL_MM;
        }

        return self::PAGE_HEIGHT_MM - $top - self::FOOTER_RESERVE_MM - self::DRAGON_RESERVE_MM;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<string, mixed>|null}
     */
    private static function splitTrailingPartHeader(array $rows): array
    {
        if ($rows === []) {
            return [$rows, null];
        }

        $lastKey = array_key_last($rows);
        $last = $rows[$lastKey];
        if (($last['type'] ?? '') !== 'part') {
            return [$rows, null];
        }

        unset($rows[$lastKey]);
        $rows = array_values($rows);

        return [$rows, $last];
    }

    /**
     * @param  array{type?: string, title?: string}  $row
     */
    private static function estimateRowHeightMm(array $row): float
    {
        $title = trim((string) ($row['title'] ?? ''));
        $isPart = ($row['type'] ?? '') === 'part';
        $margin = $isPart ? self::PART_MARGIN_MM : self::ITEM_MARGIN_MM;
        $charsPerLine = $isPart ? 52 : 32;
        $len = mb_strlen($title);
        $lines = max(1, (int) ceil($len / $charsPerLine));

        return $margin + ($lines * self::LINE_MM);
    }
}
