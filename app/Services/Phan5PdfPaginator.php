<?php

namespace App\Services;

/**
 * Phân trang nội dung PDF Phần 5.
 */
class Phan5PdfPaginator
{
    private const CHARS_PER_LINE = 68;

    private const LINE_MM = 5.2;

    private const GAP_MM = 2.0;

    private const SUB_TITLE_MM = 7.0;

    private const ITEM_TITLE_MM = 10.0;

    private const MUC_LABEL_MM = 7.0;

    private const KEYWORDS_MM = 68.0;

    private const TABLE_BAT_TU_MM = 92.0;

    private const TRAITS_PILL_MM = 12.0;

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  ?array{type: string, text: string}  $continuationHeader
     * @return array<int, array{bgPath: string, layoutVariant: string, blocks: array<int, array<string, mixed>>}>
     */
    public static function paginate(
        array $blocks,
        string $bgPath,
        float $contentHeightMm,
        string $layoutVariant = '',
        ?array $continuationHeader = null
    ): array {
        if ($blocks === []) {
            return [];
        }

        $pages = [];
        $remaining = $blocks;
        $pageIndex = 0;

        while ($remaining !== []) {
            $budget = $contentHeightMm;

            if ($pageIndex > 0 && $continuationHeader !== null) {
                $budget -= self::blockHeightMm($continuationHeader);
            }

            [$chunk, $remaining] = self::takeBlocks($remaining, $budget);

            if ($chunk === []) {
                break;
            }

            if ($pageIndex > 0 && $continuationHeader !== null) {
                $contType = $continuationHeader['type'] ?? '';
                if (($chunk[0]['type'] ?? '') !== $contType) {
                    array_unshift($chunk, $continuationHeader);
                }
            }

            $pages[] = [
                'bgPath' => $bgPath,
                'layoutVariant' => $layoutVariant,
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
    private static function takeBlocks(array $blocks, float $maxMm): array
    {
        $chunk = [];
        $used = 0.0;
        $idx = 0;

        while ($idx < count($blocks)) {
            $block = $blocks[$idx];
            $need = self::blockHeightMm($block);

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                break;
            }

            if ($chunk === [] && $need > $maxMm && ($block['type'] ?? '') === 'para') {
                [$head, $tail] = self::splitPara($block, $maxMm);
                if ($head !== null) {
                    $chunk[] = $head;
                }
                $idx++;
                if ($tail !== null) {
                    return [$chunk, array_merge([$tail], array_slice($blocks, $idx))];
                }

                break;
            }

            if ($chunk === [] && $need > $maxMm && ($block['type'] ?? '') === 'traits') {
                break;
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
            'item_title', 'section_title' => self::ITEM_TITLE_MM + self::GAP_MM,
            'sub_title', 'muc_label', 'chien_luoc_title' => self::MUC_LABEL_MM + self::GAP_MM,
            'keywords' => self::KEYWORDS_MM + self::GAP_MM,
            'table' => self::TABLE_BAT_TU_MM + self::GAP_MM,
            'traits' => self::traitsHeightMm($block),
            'image' => self::imageHeightMm((string) ($block['path'] ?? ''), (float) ($block['widthMm'] ?? 162.0)) + self::GAP_MM,
            default => self::paraHeightMm((string) ($block['text'] ?? '')) + self::GAP_MM,
        };
    }

    private static function traitsHeightMm(array $block): float
    {
        $tich = (string) ($block['tichCuc'] ?? '');
        $tieu = (string) ($block['tieuCuc'] ?? '');
        $tichLines = max(1, self::countLines($tich));
        $tieuLines = max(1, self::countLines($tieu));
        $maxLines = max($tichLines, $tieuLines);

        return self::TRAITS_PILL_MM + ($maxLines * 5.5) + 6 + self::GAP_MM;
    }

    private static function countLines(string $text): int
    {
        if (trim($text) === '') {
            return 0;
        }

        return count(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $l): bool => $l !== ''
        ));
    }

    private static function paraHeightMm(string $text): float
    {
        $lines = max(1, (int) ceil(mb_strlen($text) / self::CHARS_PER_LINE));

        return $lines * self::LINE_MM;
    }

    private static function imageHeightMm(string $path, float $widthMm): float
    {
        if ($path === '' || ! is_file($path)) {
            return 45.0;
        }
        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) <= 0) {
            return 45.0;
        }

        return min(((float) $info[1] / (float) $info[0]) * $widthMm, 120.0);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    private static function splitPara(array $block, float $maxMm): array
    {
        $text = (string) ($block['text'] ?? '');
        $maxChars = max(80, (int) floor(($maxMm / self::LINE_MM) * self::CHARS_PER_LINE));
        if (mb_strlen($text) <= $maxChars) {
            return [$block, null];
        }

        return [
            array_merge($block, ['text' => mb_substr($text, 0, $maxChars)]),
            ['type' => 'para', 'text' => trim(mb_substr($text, $maxChars))],
        ];
    }

    public static function contentHeightForLayout(string $layout): float
    {
        return match ($layout) {
            'tong_quan' => 192.0,
            'lbtv119' => 258.0,
            'su_nghiep' => 248.0,
            'su_nghiep_item' => 248.0,
            'traits_su_nghiep' => 255.0,
            'traits_lbtv119' => 255.0,
            default => 255.0,
        };
    }
}
