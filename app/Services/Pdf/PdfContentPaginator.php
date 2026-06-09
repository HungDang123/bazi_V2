<?php

namespace App\Services\Pdf;

/**
 * Engine phân trang nội dung PDF dùng chung.
 */
class PdfContentPaginator
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $blocks, PdfPaginationConfig $config): array
    {
        if ($blocks === []) {
            return [];
        }

        $pages     = [];
        $remaining = $blocks;
        $pageIndex = 0;

        while ($remaining !== []) {
            $budget = $config->contentHeightMm;

            if ($config->budgetAdjustResolver !== null) {
                $budget = ($config->budgetAdjustResolver)($pageIndex, $remaining, $budget);
            }

            [$chunk, $remaining, $usedMm] = self::takeBlocksForHeight($remaining, $budget, $config, $pageIndex);

            if ($chunk === []) {
                break;
            }

            $chapterTitle = '';
            if (isset($chunk[0]) && ($chunk[0]['type'] ?? '') === 'chapter_title') {
                $chapterTitle = trim((string) ($chunk[0]['text'] ?? ''));
            }

            if ($config->chunkAdjustResolver !== null) {
                $chunk = ($config->chunkAdjustResolver)($pageIndex, $chunk, $remaining);
            }

            $page = [
                'bgPath'              => self::resolveBg($config, $pageIndex),
                'blocks'              => $chunk,
                'chapterTitle'        => $chapterTitle,
                'contentUsedMm'       => round($usedMm, 2),
                'paddingTopMm'        => 0.0,
                'contentZoneTopMm'    => $config->contentZoneTopMm,
                'contentZoneHeightMm' => $config->contentZoneHeightMm,
                'contentLeftMm'       => $config->contentLeftMm,
                'contentWidthMm'      => $config->contentWidthMm,
            ];

            if ($config->pageMetaResolver !== null) {
                $page = array_merge($page, ($config->pageMetaResolver)($pageIndex, $page));
            }

            $pages[] = $page;
            $pageIndex++;
        }

        return $pages;
    }

    private static function resolveBg(PdfPaginationConfig $config, int $pageIndex): string
    {
        if ($config->bgResolver !== null) {
            return (string) ($config->bgResolver)($pageIndex);
        }

        return '';
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>, 2: float}
     */
    private static function takeBlocksForHeight(
        array $blocks,
        float $maxMm,
        PdfPaginationConfig $config,
        int $pageIndex
    ): array {
        $chunk = [];
        $used  = 0.0;
        $idx   = 0;

        while ($idx < count($blocks)) {
            $block = $blocks[$idx];
            $type  = (string) ($block['type'] ?? 'para');

            foreach ($config->forceNewPageBefore as $forceType) {
                if ($type === $forceType && $chunk !== []) {
                    return [$chunk, array_slice($blocks, $idx), $used];
                }
            }

            if ($config->clampImages && $type === 'image') {
                $available = $maxMm - $used - $config->imageGapMm;

                // Không đủ chỗ tối thiểu cho ảnh → đẩy sang trang mới
                if ($chunk !== [] && $available < $config->minImagePageMm) {
                    return [$chunk, array_slice($blocks, $idx), $used];
                }

                $block = self::clampImageBlock($block, $available, $config);
                $block = self::withImageRenderHeight($block, $config);
                $need  = self::blockHeightMm($block, $config);

                if ($chunk !== [] && ($used + $need) > $maxMm) {
                    break;
                }
            } else {
                $need = self::blockHeightMm($block, $config);
            }

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                if ($config->splitOversizedPara && $type === 'para') {
                    [$head, $tail] = self::splitParaBlock($block, $maxMm - $used, $config);
                    if ($head !== null) {
                        $chunk[] = $head;
                        $used   += self::blockHeightMm($head, $config);
                    }
                    $idx++;
                    if ($tail !== null) {
                        $rest = array_slice($blocks, $idx);
                        array_unshift($rest, $tail);

                        return [$chunk, $rest, $used];
                    }

                    break;
                }

                break;
            }

            if ($chunk === [] && $need > $maxMm && $type === 'para' && $config->splitOversizedPara) {
                [$head, $tail] = self::splitParaBlock($block, $maxMm, $config);
                if ($head !== null) {
                    $chunk[] = $head;
                    $used   += self::blockHeightMm($head, $config);
                }
                $idx++;
                if ($tail !== null) {
                    $rest = array_slice($blocks, $idx);
                    array_unshift($rest, $tail);

                    return [$chunk, $rest, $used];
                }

                break;
            }

            if ($chunk === [] && $need > $maxMm && $type === 'traits' && $config->skipOversizedTraits) {
                break;
            }

            if ($chunk === [] && $need > $maxMm && $type === 'image' && $config->clampImages) {
                $block = self::clampImageBlock($block, $maxMm - $config->imageGapMm, $config);
                $block = self::withImageRenderHeight($block, $config);
                $need  = self::blockHeightMm($block, $config);
                $chunk[] = $block;
                $used += $need;
                $idx++;

                break;
            }

            if ($type === 'image') {
                $block = self::withImageRenderHeight($block, $config);
            }

            $chunk[] = $block;
            $used   += $need;
            $idx++;
        }

        return [$chunk, array_slice($blocks, $idx), $used];
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public static function blockHeightMm(array $block, PdfPaginationConfig $config): float
    {
        if ($config->blockHeightResolver !== null) {
            $custom = ($config->blockHeightResolver)($block);
            if ($custom > 0) {
                return $custom;
            }
        }

        $type = (string) ($block['type'] ?? 'para');

        if (isset($config->fixedBlockHeights[$type])) {
            return $config->fixedBlockHeights[$type] + $config->blockGapMm;
        }

        if ($type === 'image') {
            return self::imageHeightMm($block, $config) + $config->imageGapMm;
        }

        return self::paraHeightMm((string) ($block['text'] ?? ''), $config) + $config->blockGapMm;
    }

    public static function paraHeightMm(string $text, PdfPaginationConfig $config): float
    {
        $effectiveChars = max(1, (int) floor($config->charsPerLine * $config->lineWidthThreshold));
        $lines = max(1, (int) ceil(mb_strlen($text) / $effectiveChars));

        return $lines * $config->lineMm;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public static function imageHeightMm(array $block, PdfPaginationConfig $config): float
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

        $widthMm = (float) ($block['widthMm'] ?? $config->contentWidthMm);
        $natural = ((float) $info[1] / (float) $info[0]) * $widthMm;
        $cap     = $config->maxImageMm ?? ($config->contentHeightMm - 8.0);

        return min($natural, $cap);
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public static function clampImageBlock(array $block, float $maxAvailable, PdfPaginationConfig $config): array
    {
        $cap = $config->maxImageMm !== null
            ? min($config->maxImageMm, max(20.0, $maxAvailable))
            : max(20.0, $maxAvailable);

        $h = self::imageHeightMm($block, $config);
        if ($h > $cap) {
            $block['maxHeightMm'] = round($cap, 1);
        }

        return $block;
    }

    /**
     * Gắn chiều cao render cho blade — paginator và PDF phải khớp nhau.
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public static function withImageRenderHeight(array $block, PdfPaginationConfig $config): array
    {
        if (($block['type'] ?? '') !== 'image') {
            return $block;
        }

        $block['maxHeightMm'] = round(self::imageHeightMm($block, $config), 1);

        return $block;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    public static function splitParaBlock(array $block, float $maxMm, PdfPaginationConfig $config): array
    {
        $text = (string) ($block['text'] ?? '');
        if ($maxMm <= 0) {
            return [null, $block];
        }

        $effectiveChars = max(1, (int) floor($config->charsPerLine * $config->lineWidthThreshold));
        $maxChars = max(40, (int) floor(($maxMm / $config->lineMm) * $effectiveChars));
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
}
