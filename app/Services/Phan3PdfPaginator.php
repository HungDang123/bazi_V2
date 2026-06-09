<?php

namespace App\Services;

/**
 * Chia nội dung Phần 3 (blade bố cục ngũ hành) thành nhiều trang PDF.
 * Trang đầu dùng $firstBgPath, các trang sau dùng $contBgPath (cùng khung nền).
 */
class Phan3PdfPaginator
{
    private const CONTENT_HEIGHT_MM = 220.0;

    private const CONTENT_WIDTH_MM = 154.0;

    private const CHARS_PER_LINE = 78;

    private const LINE_MM = 5.5;

    private const CHAPTER_TITLE_MM = 10.5;

    private const SUB_TITLE_MM = 8.5;

    private const BLOCK_GAP_MM = 2.5;

    private const IMAGE_GAP_MM = 5.0;

    /**
     * @param  array{chapterTitle?: string, subSections?: array<int, array{sub_title?: string, content?: array<int, array<string, mixed>>}>}  $section
     * @return array<int, array{bgPath: string, chapterTitle: string, blocks: array<int, array<string, mixed>>}>
     */
    public static function paginateBocucSection(
        array $section,
        string $firstBgPath,
        string $contBgPath
    ): array {
        return self::paginateFlatBlocks(
            trim((string) ($section['chapterTitle'] ?? '')),
            self::flattenSubSections($section['subSections'] ?? []),
            $firstBgPath,
            $contBgPath
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array{bgPath: string, chapterTitle: string, blocks: array<int, array<string, mixed>>}>
     */
    public static function paginateFlatBlocks(
        string $chapterTitle,
        array $blocks,
        string $firstBgPath,
        string $contBgPath
    ): array {
        if ($blocks === []) {
            return [];
        }

        $chapterTitle = trim($chapterTitle);
        $pages        = [];
        $pageIndex    = 0;
        $remaining    = $blocks;

        while ($remaining !== []) {
            $budget = self::CONTENT_HEIGHT_MM;
            if ($pageIndex === 0 && $chapterTitle !== '') {
                $budget -= self::CHAPTER_TITLE_MM;
            }

            [$chunk, $remaining] = self::takeBlocksForHeight($remaining, $budget);

            if ($chunk === []) {
                break;
            }

            $pages[] = [
                'bgPath'       => $pageIndex === 0 ? $firstBgPath : $contBgPath,
                'chapterTitle' => $pageIndex === 0 ? $chapterTitle : '',
                'blocks'       => $chunk,
            ];

            $pageIndex++;
        }

        return $pages;
    }

    /**
     * @param  array<int, array{sub_title?: string, content?: array<int, array<string, mixed>>}>  $subSections
     * @return array<int, array<string, mixed>>
     */
    private static function flattenSubSections(array $subSections): array
    {
        $flat = [];

        foreach ($subSections as $sub) {
            $title = trim((string) ($sub['sub_title'] ?? ''));
            if ($title !== '') {
                $flat[] = ['type' => 'sub_title', 'text' => $title];
            }

            foreach ($sub['content'] ?? [] as $block) {
                $flat[] = $block;
            }
        }

        return $flat;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private static function takeBlocksForHeight(array $blocks, float $maxMm): array
    {
        $chunk = [];
        $used  = 0.0;

        while ($blocks !== []) {
            $block = $blocks[0];
            $need  = self::blockHeightMm($block);

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                break;
            }

            if ($chunk === [] && $block['type'] === 'para' && $need > $maxMm) {
                [$head, $tail] = self::splitParaBlock($block, $maxMm);
                if ($head !== null) {
                    $chunk[] = $head;
                }
                array_shift($blocks);
                if ($tail !== null) {
                    array_unshift($blocks, $tail);
                }

                break;
            }

            if ($chunk === [] && ($block['type'] ?? '') === 'image' && $need > $maxMm) {
                $chunk[] = self::normalizeImageBlock($block, $maxMm - self::IMAGE_GAP_MM);
                array_shift($blocks);
                break;
            }

            if (($block['type'] ?? '') === 'image') {
                $block = self::normalizeImageBlock(
                    $block,
                    max(20.0, $maxMm - $used - self::IMAGE_GAP_MM)
                );
                $need  = self::blockHeightMm($block);
                if ($chunk !== [] && ($used + $need) > $maxMm) {
                    break;
                }
            }

            array_shift($blocks);
            $chunk[] = $block;
            $used   += $need;
        }

        return [$chunk, $blocks];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private static function blockHeightMm(array $block): float
    {
        return match ($block['type'] ?? 'para') {
            'sub_title' => self::SUB_TITLE_MM + self::BLOCK_GAP_MM,
            'image'     => self::imageHeightMm((string) ($block['path'] ?? '')) + self::IMAGE_GAP_MM,
            default     => self::paraHeightMm((string) ($block['text'] ?? '')) + self::BLOCK_GAP_MM,
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
        $maxImg  = self::CONTENT_HEIGHT_MM - 8.0;

        return min($natural, $maxImg);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    private static function normalizeImageBlock(array $block, float $maxImgMm): array
    {
        $path = (string) ($block['path'] ?? '');
        $h    = self::imageHeightMm($path);

        return array_merge($block, [
            'maxHeightMm' => min($h, max(20.0, $maxImgMm)),
        ]);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    private static function splitParaBlock(array $block, float $maxMm): array
    {
        $text     = (string) ($block['text'] ?? '');
        $maxLines = max(1, (int) floor($maxMm / self::LINE_MM));
        $maxChars = max(80, $maxLines * self::CHARS_PER_LINE);

        if (mb_strlen($text) <= $maxChars) {
            return [$block, null];
        }

        $head = mb_substr($text, 0, $maxChars);
        $tail = mb_substr($text, $maxChars);

        if (preg_match('/^(.{0,'.$maxChars.'}[\.!\?\…]\s)/us', $text, $m)) {
            $head = trim($m[1]);
            $tail = trim(mb_substr($text, mb_strlen($m[1])));
        } elseif (($sp = mb_strrpos($head, ' ')) !== false) {
            $tail = trim(mb_substr($text, $sp));
            $head = trim(mb_substr($text, 0, $sp));
        }

        $headBlock = $head !== '' ? ['type' => 'para', 'text' => $head] : null;
        $tailBlock = $tail !== '' ? ['type' => 'para', 'text' => $tail] : null;

        return [$headBlock, $tailBlock];
    }
}
