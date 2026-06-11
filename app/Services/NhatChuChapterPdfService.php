<?php

namespace App\Services;

use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationConfig;
use App\Services\Pdf\PdfPaginationProfiles;

/**
 * Phân trang nội dung NHẬT CHỦ TRỤ NGÀY (Cuốn 2 — Phần 4, trang 17–21).
 */
class NhatChuChapterPdfService
{
    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @param  array{searchKw?: array<int, string>, roman?: string, prefix?: string, fallbackIndex?: int}  $finder
     * @return array<int, array<string, mixed>>
     */
    public static function buildPages(
        array $chapters,
        array $finder,
        string $firstBgPath,
        ?string $contBgPath = null,
        ?PdfPaginationConfig $config = null
    ): array {
        $chapter = self::resolveChapter($chapters, $finder);
        if ($chapter === null) {
            return [];
        }

        $blocks = self::chapterToBlocks($chapter);
        if ($blocks === []) {
            return [];
        }

        $contBgPath ??= $firstBgPath;
        $chapterTitle = trim((string) ($chapter['chapter'] ?? ''));
        $config ??= PdfPaginationProfiles::quyen2NhatChuChapter($firstBgPath, $contBgPath, $chapterTitle);

        return self::finalizePages(
            PdfContentPaginator::paginate($blocks, $config),
            $config
        );
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @return array<int, array<string, mixed>>
     */
    public static function buildTongQuanPages(array $chapters, string $pdfDir): array
    {
        $blocks = self::tongQuanToBlocks($chapters);
        if ($blocks === []) {
            return [];
        }

        $firstBg = $pdfDir.'/page-17-bg.png';
        $contBg  = $pdfDir.'/page-19-bg.png';
        $config  = PdfPaginationProfiles::quyen2TongQuan($firstBg, $contBg);

        return self::finalizePages(
            PdfContentPaginator::paginate($blocks, $config),
            $config
        );
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @return array<int, array<string, mixed>>
     */
    public static function buildXuHuongPages(array $chapters, string $pdfDir): array
    {
        $bg = $pdfDir.'/page-19-bg.png';

        return self::buildPages($chapters, [
            'searchKw'      => ['xu hướng', 'xu huong', 'tính cách', 'tinh cach'],
            'fallbackIndex' => 1,
        ], $bg);
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @return array<int, array<string, mixed>>
     */
    public static function buildChapterIiiPages(array $chapters, string $pdfDir): array
    {
        $bg = $pdfDir.'/page-20-bg.png';

        return self::buildPages($chapters, [
            'roman'         => 'iii',
            'prefix'        => '3.',
            'fallbackIndex' => 2,
        ], $bg);
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @return array<int, array<string, mixed>>
     */
    public static function buildChapterIvPages(array $chapters, string $pdfDir): array
    {
        $bg = $pdfDir.'/page-21-bg.png';

        return self::buildPages($chapters, [
            'roman'         => 'iv',
            'prefix'        => '4.',
            'fallbackIndex' => 3,
        ], $bg);
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @param  array{searchKw?: array<int, string>, roman?: string, prefix?: string, fallbackIndex?: int}  $finder
     * @return array{chapter: string, sub_sections: array<int, array{sub_title?: string, content?: string}>}|null
     */
    private static function resolveChapter(array $chapters, array $finder): ?array
    {
        $searchKw = $finder['searchKw'] ?? [];
        if ($searchKw !== []) {
            foreach ($chapters as $ch) {
                $chLower = mb_strtolower(trim((string) ($ch['chapter'] ?? '')), 'UTF-8');
                foreach ($searchKw as $kw) {
                    if (str_contains($chLower, mb_strtolower($kw, 'UTF-8'))) {
                        return $ch;
                    }
                }
            }
        }

        $roman = trim((string) ($finder['roman'] ?? ''));
        $prefix = trim((string) ($finder['prefix'] ?? ''));
        if ($roman !== '' || $prefix !== '') {
            foreach ($chapters as $ch) {
                $chLower = mb_strtolower(trim((string) ($ch['chapter'] ?? '')), 'UTF-8');
                if ($roman !== '' && preg_match('/\b'.preg_quote($roman, '/').'\b/', $chLower)) {
                    return $ch;
                }
                if ($prefix !== '' && str_starts_with(ltrim($chLower), $prefix)) {
                    return $ch;
                }
            }
        }

        $fallbackIndex = (int) ($finder['fallbackIndex'] ?? -1);
        if ($fallbackIndex >= 0 && isset($chapters[$fallbackIndex])) {
            return $chapters[$fallbackIndex];
        }

        return null;
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @return array<int, array{type: string, text: string}>
     */
    private static function tongQuanToBlocks(array $chapters): array
    {
        $blocks = [];

        foreach (self::filterTongQuanChapters($chapters) as $chapter) {
            $title = trim((string) ($chapter['chapter'] ?? ''));
            if ($title !== '') {
                $blocks[] = ['type' => 'chapter_title', 'text' => $title];
            }

            foreach ($chapter['sub_sections'] ?? [] as $sub) {
                $subTitle = trim((string) ($sub['sub_title'] ?? ''));
                if ($subTitle !== '') {
                    $blocks[] = ['type' => 'sub_title', 'text' => $subTitle];
                }

                $content = self::joinParagraphs((string) ($sub['content'] ?? ''));
                if ($content !== '') {
                    $blocks[] = ['type' => 'para', 'text' => $content];
                }
            }
        }

        return $blocks;
    }

    /**
     * @param  array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>  $chapters
     * @return array<int, array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}>
     */
    private static function filterTongQuanChapters(array $chapters): array
    {
        $includeSubKw = ['ý nghĩa', 'y nghia'];
        $excludeSubKw = ['phân tích hình ảnh', 'phan tich hinh anh', 'hình ảnh ẩn dụ'];
        $filtered     = [];

        foreach ($chapters as $ch) {
            $subs = [];
            foreach ($ch['sub_sections'] ?? [] as $sub) {
                $stLower = mb_strtolower(trim((string) ($sub['sub_title'] ?? '')), 'UTF-8');

                $excluded = false;
                foreach ($excludeSubKw as $kw) {
                    if (str_contains($stLower, $kw)) {
                        $excluded = true;
                        break;
                    }
                }
                if ($excluded) {
                    continue;
                }

                $isMatch = $stLower === '';
                foreach ($includeSubKw as $kw) {
                    if (str_contains($stLower, $kw)) {
                        $isMatch = true;
                        break;
                    }
                }

                if ($isMatch) {
                    $subs[] = $sub;
                }
            }

            if ($subs !== []) {
                $filtered[] = array_merge($ch, ['sub_sections' => $subs]);
            }
        }

        return $filtered;
    }

    /**
     * @param  array{chapter?: string, sub_sections?: array<int, array{sub_title?: string, content?: string}>}  $chapter
     * @return array<int, array{type: string, text: string}>
     */
    private static function chapterToBlocks(array $chapter): array
    {
        $blocks = [];

        foreach ($chapter['sub_sections'] ?? [] as $sub) {
            $title = trim((string) ($sub['sub_title'] ?? ''));
            if ($title !== '') {
                $blocks[] = ['type' => 'sub_title', 'text' => $title];
            }

            $content = self::joinParagraphs((string) ($sub['content'] ?? ''));
            if ($content !== '') {
                $blocks[] = ['type' => 'para', 'text' => $content];
            }
        }

        return $blocks;
    }

    private static function joinParagraphs(string $rawText): string
    {
        $paragraphs = self::splitParagraphs($rawText);

        return implode("\n\n", $paragraphs);
    }

    /**
     * @return array<int, string>
     */
    private static function splitParagraphs(string $rawText): array
    {
        $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $rawText) ?: []));
        if ($paragraphs === []) {
            $paragraphs = array_filter(array_map('trim', explode("\n", $rawText)));
        }

        return array_values($paragraphs);
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     * @return array<int, array<string, mixed>>
     */
    private static function finalizePages(array $pages, PdfPaginationConfig $config): array
    {
        if ($pages === []) {
            return [];
        }

        $pages = self::compactSparsePages($pages, $config);

        return array_values(array_filter($pages, static function (array $page): bool {
            foreach ($page['blocks'] ?? [] as $block) {
                if (trim((string) ($block['text'] ?? '')) !== '') {
                    return true;
                }
            }

            return trim((string) ($page['chapterTitle'] ?? '')) !== '';
        }));
    }

    /**
     * Gộp trang kế tiếp vào trang hiện tại nếu còn đủ budget (tránh trang trống nửa chừng).
     *
     * @param  array<int, array<string, mixed>>  $pages
     * @return array<int, array<string, mixed>>
     */
    private static function compactSparsePages(array $pages, PdfPaginationConfig $config): array
    {
        if (count($pages) < 2) {
            return $pages;
        }

        $merged = [$pages[0]];

        for ($i = 1; $i < count($pages); $i++) {
            $prevIdx  = count($merged) - 1;
            $prev     = $merged[$prevIdx];
            $current  = $pages[$i];
            $budget   = self::pageBudgetMm($prevIdx, $prev, $config);
            $combined = array_merge($prev['blocks'] ?? [], $current['blocks'] ?? []);
            $used     = self::blocksHeightMm($combined, $config);

            if ($used <= $budget + 1.0) {
                $merged[$prevIdx]['blocks'] = $combined;
                continue;
            }

            $merged[] = $current;
        }

        foreach ($merged as $idx => &$page) {
            if ($config->bgResolver !== null) {
                $page['bgPath'] = ($config->bgResolver)($idx);
            }
            if ($config->pageMetaResolver !== null) {
                $page = array_merge($page, ($config->pageMetaResolver)($idx, $page));
            }
        }
        unset($page);

        return $merged;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private static function blocksHeightMm(array $blocks, PdfPaginationConfig $config): float
    {
        $used = 0.0;
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $used += PdfContentPaginator::blockHeightMm($block, $config);
        }

        return $used;
    }

    /**
     * @param  array<string, mixed>  $page
     */
    private static function pageBudgetMm(int $pageIndex, array $page, PdfPaginationConfig $config): float
    {
        $budget = $config->contentHeightMm;

        if ($config->budgetAdjustResolver !== null) {
            $budget = ($config->budgetAdjustResolver)($pageIndex, $page['blocks'] ?? [], $budget);
        }

        return $budget;
    }
}
