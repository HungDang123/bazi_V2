<?php

namespace App\Services;

/**
 * Splits Phần 7 Mục II content blocks into A4 pages using page-content-bg.png.
 *
 * Rules:
 *  - A `thap_than_title` block always starts a fresh page.
 *  - Image blocks are capped at MAX_IMAGE_MM to prevent a single image filling the whole page.
 *  - Regular para text wraps across lines estimated by character count.
 */
class Phan7PdfPaginator
{
    private const CONTENT_HEIGHT_MM = 252.0;

    private const CONTENT_WIDTH_MM = 154.0;

    private const CHARS_PER_LINE = 70;

    private const LINE_MM = 5.2;

    private const TITLE_BLOCK_MM = 22.0;

    private const SECTION_LABEL_MM = 7.0;

    private const BLOCK_GAP_MM = 2.5;

    private const IMAGE_GAP_MM = 4.0;

    private const MAX_IMAGE_MM = 95.0;

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array{bgPath: string, blocks: array<int, array<string, mixed>>}>
     */
    public static function paginate(array $blocks, string $bgPath): array
    {
        if ($blocks === []) {
            return [];
        }

        $pages     = [];
        $remaining = $blocks;

        while ($remaining !== []) {
            [$chunk, $remaining] = self::takeForPage($remaining);

            if ($chunk === []) {
                break;
            }

            $pages[] = [
                'bgPath' => $bgPath,
                'blocks' => $chunk,
            ];
        }

        return $pages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private static function takeForPage(array $blocks): array
    {
        $chunk = [];
        $used  = 0.0;
        $idx   = 0;

        while ($idx < count($blocks)) {
            $block = $blocks[$idx];
            $type  = $block['type'] ?? 'para';

            // thap_than_title always starts a new page
            if ($type === 'thap_than_title' && $chunk !== []) {
                break;
            }

            if ($type === 'image') {
                $available = self::CONTENT_HEIGHT_MM - $used - self::IMAGE_GAP_MM;
                $block     = self::clampImage($block, $available);
                $need      = self::imageHeightMm($block) + self::IMAGE_GAP_MM;

                if ($chunk !== [] && ($used + $need) > self::CONTENT_HEIGHT_MM) {
                    break;
                }
            } else {
                $need = self::blockHeightMm($block);

                if ($chunk !== [] && ($used + $need) > self::CONTENT_HEIGHT_MM) {
                    // Try splitting a para block if it just barely overflows
                    if ($type === 'para') {
                        [$head, $tail] = self::splitPara($block, self::CONTENT_HEIGHT_MM - $used);
                        if ($head !== null) {
                            $chunk[] = $head;
                        }
                        $idx++;
                        if ($tail !== null) {
                            $rest = array_slice($blocks, $idx);
                            array_unshift($rest, $tail);

                            return [$chunk, $rest];
                        }
                    }
                    break;
                }
            }

            $chunk[] = $block;
            $used   += $type === 'image'
                ? (self::imageHeightMm($block) + self::IMAGE_GAP_MM)
                : self::blockHeightMm($block);
            $idx++;
        }

        return [$chunk, array_slice($blocks, $idx)];
    }

    private static function blockHeightMm(array $block): float
    {
        return match ($block['type'] ?? 'para') {
            'thap_than_title' => self::TITLE_BLOCK_MM + self::BLOCK_GAP_MM,
            'section_label'   => self::SECTION_LABEL_MM + self::BLOCK_GAP_MM,
            default           => self::paraHeightMm((string) ($block['text'] ?? '')) + self::BLOCK_GAP_MM,
        };
    }

    private static function paraHeightMm(string $text): float
    {
        $lines = max(1, (int) ceil(mb_strlen($text) / self::CHARS_PER_LINE));

        return $lines * self::LINE_MM;
    }

    private static function imageHeightMm(array $block): float
    {
        if (isset($block['maxHeightMm'])) {
            return (float) $block['maxHeightMm'];
        }

        $path = (string) ($block['path'] ?? '');
        if ($path === '' || ! is_file($path)) {
            return 40.0;
        }

        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) <= 0) {
            return 40.0;
        }

        $natural = ((float) $info[1] / (float) $info[0]) * self::CONTENT_WIDTH_MM;

        return min($natural, self::MAX_IMAGE_MM);
    }

    private static function clampImage(array $block, float $maxAvailable): array
    {
        $cap = min(self::MAX_IMAGE_MM, max(20.0, $maxAvailable));
        $h   = self::imageHeightMm($block);

        if ($h > $cap) {
            $block['maxHeightMm'] = round($cap, 1);
        }

        return $block;
    }

    /**
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    private static function splitPara(array $block, float $availableMm): array
    {
        $text     = (string) ($block['text'] ?? '');
        $maxChars = max(40, (int) floor(($availableMm / self::LINE_MM) * self::CHARS_PER_LINE));

        if (mb_strlen($text) <= $maxChars) {
            return [$block, null];
        }

        $head = mb_substr($text, 0, $maxChars);
        $tail = trim(mb_substr($text, $maxChars));

        return [
            array_merge($block, ['text' => $head]),
            $tail !== '' ? array_merge($block, ['text' => $tail]) : null,
        ];
    }
}
