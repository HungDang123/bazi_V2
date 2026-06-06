<?php

namespace App\Services;

/**
 * Phân trang nội dung Phần 6 (nền page-content-bg.png).
 */
class Phan6PdfPaginator
{
    private const CONTENT_HEIGHT_MM = 252.0;

    private const CONTENT_WIDTH_MM = 154.0;

    private const CHARS_PER_LINE = 72;

    private const LINE_MM = 5.2;

    private const CHAPTER_TITLE_MM = 9.0;

    private const SUB_TITLE_MM = 7.0;

    private const BLOCK_GAP_MM = 2.0;

    private const IMAGE_GAP_MM = 5.0;

    private const TABLE_MM = 48.0;

    private const CODING_BOX_EXTRA_MM = 3.0;

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array{bgPath: string, chapterTitle: string, blocks: array<int, array<string, mixed>>}>
     */
    public static function paginate(array $blocks, string $bgPath): array
    {
        if ($blocks === []) {
            return [];
        }

        $pages = [];
        $pageIndex = 0;
        $remaining = $blocks;
        $chapterOnPage = '';

        while ($remaining !== []) {
            $budget = self::CONTENT_HEIGHT_MM;
            $firstBlock = $remaining[0] ?? null;
            $showChapter = $pageIndex === 0
                || (is_array($firstBlock) && ($firstBlock['type'] ?? '') === 'chapter_title');

            if ($showChapter && is_array($firstBlock) && ($firstBlock['type'] ?? '') === 'chapter_title') {
                $chapterOnPage = trim((string) ($firstBlock['text'] ?? ''));
                $budget -= self::CHAPTER_TITLE_MM;
            } else {
                $chapterOnPage = '';
            }

            [$chunk, $remaining] = self::takeBlocksForHeight($remaining, $budget, $showChapter && $chapterOnPage !== '');

            if ($chunk === []) {
                break;
            }

            if ($showChapter && $chapterOnPage !== '' && ($chunk[0]['type'] ?? '') === 'chapter_title') {
                array_shift($chunk);
            }

            $pages[] = [
                'bgPath' => $bgPath,
                'chapterTitle' => $chapterOnPage,
                'blocks' => $chunk,
            ];

            $pageIndex++;
        }

        return $pages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private static function takeBlocksForHeight(array $blocks, float $maxMm, bool $skipFirstChapter): array
    {
        $chunk = [];
        $used = 0.0;
        $idx = 0;

        if ($skipFirstChapter && isset($blocks[0]) && ($blocks[0]['type'] ?? '') === 'chapter_title') {
            $chunk[] = $blocks[0];
            $used += self::CHAPTER_TITLE_MM + self::BLOCK_GAP_MM;
            $idx = 1;
        }

        while ($idx < count($blocks)) {
            $block = $blocks[$idx];
            $need = self::blockHeightMm($block);

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                break;
            }

            if ($chunk === [] && ($block['type'] ?? '') === 'para' && $need > $maxMm) {
                [$head, $tail] = self::splitParaBlock($block, $maxMm);
                if ($head !== null) {
                    $chunk[] = $head;
                }
                $idx++;
                if ($tail !== null) {
                    $rest = array_slice($blocks, $idx);
                    array_unshift($rest, $tail);

                    return [$chunk, $rest];
                }

                break;
            }

            if (($block['type'] ?? '') === 'image') {
                $block = self::normalizeImageBlock($block, max(20.0, $maxMm - $used - self::IMAGE_GAP_MM));
                $need = self::blockHeightMm($block);
                if ($chunk !== [] && ($used + $need) > $maxMm) {
                    break;
                }
            }

            $chunk[] = $block;
            $used += $need;
            $idx++;
        }

        return [$chunk, array_slice($blocks, $idx)];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function blockHeightMm(array $block): float
    {
        return match ($block['type'] ?? 'para') {
            'chapter_title', 'sub_title', 'sub_ab', 'huong_label' => self::SUB_TITLE_MM + self::BLOCK_GAP_MM,
            'table' => self::TABLE_MM + self::BLOCK_GAP_MM,
            'image' => self::imageHeightMm((string) ($block['path'] ?? '')) + self::IMAGE_GAP_MM,
            'coding_box' => self::paraHeightMm((string) ($block['text'] ?? '')) + self::CODING_BOX_EXTRA_MM + self::BLOCK_GAP_MM,
            default => self::paraHeightMm((string) ($block['text'] ?? '')) + self::BLOCK_GAP_MM,
        };
    }

    private static function paraHeightMm(string $text): float
    {
        $lines = max(1, (int) ceil(mb_strlen($text) / self::CHARS_PER_LINE));

        return $lines * self::LINE_MM;
    }

    private static function imageHeightMm(string $path): float
    {
        if ($path === '' || ! is_file($path)) {
            return 40.0;
        }

        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) <= 0) {
            return 40.0;
        }

        $natural = ((float) $info[1] / (float) $info[0]) * self::CONTENT_WIDTH_MM;

        return min($natural, self::CONTENT_HEIGHT_MM - 8.0);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    private static function splitParaBlock(array $block, float $maxMm): array
    {
        $text = (string) ($block['text'] ?? '');
        $maxChars = max(80, (int) floor(($maxMm / self::LINE_MM) * self::CHARS_PER_LINE));
        if (mb_strlen($text) <= $maxChars) {
            return [$block, null];
        }

        $headText = mb_substr($text, 0, $maxChars);
        $tailText = trim(mb_substr($text, $maxChars));

        return [
            array_merge($block, ['text' => $headText]),
            $tailText !== '' ? array_merge($block, ['text' => $tailText]) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private static function normalizeImageBlock(array $block, float $maxHeightMm): array
    {
        $path = (string) ($block['path'] ?? '');
        $h = self::imageHeightMm($path);
        if ($h > $maxHeightMm) {
            $block['maxHeightMm'] = round($maxHeightMm, 1);
        }

        return $block;
    }
}
