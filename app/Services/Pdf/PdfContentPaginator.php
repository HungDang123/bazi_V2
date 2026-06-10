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

        $blocks = PdfTextSanitizer::trimBlocks($blocks);

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

                // Minh họa + từ khóa luôn cùng trang (Phần 5 Thập Thần)
                $nextBlock = $blocks[$idx + 1] ?? null;
                if (is_array($nextBlock) && ($nextBlock['type'] ?? '') === 'keywords') {
                    $keywordsNeed = self::blockHeightMm($nextBlock, $config);

                    // Chỉ trừ chỗ keywords — ảnh luôn full width, chiều cao tự nhiên
                    $available = min(
                        $available,
                        max(40.0, $maxMm - $used - $keywordsNeed - $config->blockGapMm - $config->imageGapMm)
                    );
                }

                // Không đủ chỗ tối thiểu cho ảnh → đẩy sang trang mới
                if ($chunk !== [] && $available < $config->minImagePageMm) {
                    return [$chunk, array_slice($blocks, $idx), $used];
                }

                $block = self::clampImageBlock($block, $available, $config);
                $block = self::withImageRenderHeight($block, $config);
                $maxWidthMm = (float) ($block['widthMm'] ?? $config->contentWidthMm);
                $renderWidth = (float) ($block['renderWidthMm'] ?? $maxWidthMm);

                // Không thu nhỏ ảnh — nếu không đủ chỗ full width thì sang trang mới
                if ($chunk !== [] && $renderWidth < $maxWidthMm * 0.92) {
                    return [$chunk, array_slice($blocks, $idx), $used];
                }

                $need  = self::blockHeightMm($block, $config);

                if ($chunk !== [] && ($used + $need) > $maxMm) {
                    break;
                }
            } else {
                $need = self::blockHeightMm($block, $config);
            }

            if ($chunk !== [] && self::mustKeepWithNext($type, $blocks, $idx)) {
                $nextNeed = self::keepWithNextHeightMm($type, $blocks, $idx, $config);
                if ($nextNeed > 0 && ($used + $need + $nextNeed) > $maxMm) {
                    break;
                }
            }

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                // Traits không vừa phần còn lại → tách phần đầu vào trang này
                if ($type === 'traits') {
                    [$head, $tail] = self::splitTraitsBlock($block, $maxMm - $used);
                    if ($head !== null) {
                        $chunk[] = $head;
                        $used   += self::blockHeightMm($head, $config);
                        $idx++;
                        $rest = array_slice($blocks, $idx);
                        if ($tail !== null) {
                            array_unshift($rest, $tail);
                        }

                        return [$chunk, $rest, $used];
                    }

                    break;
                }

                // Cắt đoạn văn khi không vừa phần còn lại của trang (không chỉ khi dài hơn cả trang)
                if ($config->splitOversizedPara && $type === 'para') {
                    [$head, $tail] = self::splitParaBlock($block, $maxMm - $used, $config);
                    if ($head !== null && ($used + self::blockHeightMm($head, $config)) <= $maxMm + 0.01) {
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

            if ($chunk === [] && $need > $maxMm && $type === 'traits') {
                // Traits cao hơn cả trang — tách, phần dư qua trang sau (tránh clip chữ)
                [$head, $tail] = self::splitTraitsBlock($block, $maxMm);
                if ($head !== null) {
                    $chunk[] = $head;
                    $used   += self::blockHeightMm($head, $config);
                    $idx++;
                    $rest = array_slice($blocks, $idx);
                    if ($tail !== null) {
                        array_unshift($rest, $tail);
                    }

                    return [$chunk, $rest, $used];
                }

                // Không tách được — vẫn render để không mất block
                $chunk[] = $block;
                $used += $need;
                $idx++;

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

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                break;
            }

            $chunk[] = $block;
            $used   += $need;
            $idx++;
        }

        return [$chunk, array_slice($blocks, $idx), $used];
    }

    /**
     * Tách block traits theo chiều cao khả dụng — trả [head, tail] (null nếu không tách được).
     *
     * @param  array<string, mixed>  $block
     * @return array{0: array<string, mixed>|null, 1: array<string, mixed>|null}
     */
    private static function splitTraitsBlock(array $block, float $availableMm): array
    {
        $split = Phan5TraitLayout::splitByHeight(
            (string) ($block['tichCuc'] ?? ''),
            (string) ($block['tieuCuc'] ?? ''),
            $availableMm
        );

        if ($split === null) {
            return [null, null];
        }

        [$headTich, $headTieu, $tailTich, $tailTieu] = $split;

        $head = array_merge($block, ['tichCuc' => $headTich, 'tieuCuc' => $headTieu]);
        $tail = ($tailTich !== '' || $tailTieu !== '')
            ? array_merge($block, ['tichCuc' => $tailTich, 'tieuCuc' => $tailTieu])
            : null;

        return [$head, $tail];
    }

    private static function mustKeepWithNext(string $type, array $blocks, int $idx): bool
    {
        if (in_array($type, [
            'sub_title',
            'section_title',
            'muc_label',
            'chien_luoc_title',
            'red_title',
            'huong_label',
        ], true)) {
            return true;
        }

        $next = $blocks[$idx + 1] ?? null;
        if (! is_array($next)) {
            return false;
        }

        if ($type === 'item_title' && ($next['type'] ?? '') === 'image') {
            return true;
        }

        return $type === 'image' && ($next['type'] ?? '') === 'keywords';
    }

    private static function keepWithNextHeightMm(
        string $type,
        array $blocks,
        int $idx,
        PdfPaginationConfig $config
    ): float {
        $next = $blocks[$idx + 1] ?? null;
        if (! is_array($next)) {
            return 0.0;
        }

        $need = self::blockHeightMm($next, $config);

        if ($type === 'item_title' && ($next['type'] ?? '') === 'image') {
            // Ảnh clamp được → chỉ cần chỗ tối thiểu, không phải chiều cao tự nhiên
            $need = min($need, $config->minImagePageMm + $config->imageGapMm);

            $third = $blocks[$idx + 2] ?? null;
            if (is_array($third) && ($third['type'] ?? '') === 'keywords') {
                $need += self::blockHeightMm($third, $config);
            }

            return $need;
        }

        // Label-like blocks: chỉ cần ~12mm theo sau (1–2 dòng đầu)
        $labelTypes = ['muc_label', 'sub_title', 'section_title', 'chien_luoc_title', 'red_title', 'huong_label'];
        if (in_array($type, $labelTypes, true)) {
            return min($need, 12.0);
        }

        return $need;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public static function blockHeightMm(array $block, PdfPaginationConfig $config): float
    {
        if ($config->blockHeightResolver !== null) {
            $custom = ($config->blockHeightResolver)($block);
            if ($custom > 0) {
                return $custom + $config->blockGapMm;
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
        // Đo theo độ rộng mm thật (font metrics DomPDF) — không đoán theo ký tự
        return PdfTextWrapHelper::renderedHeightMmByWidth(
            $text,
            $config->contentWidthMm,
            $config->lineMm,
            $config->paraLinePaddingMm
        );
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public static function imageHeightMm(array $block, PdfPaginationConfig $config): float
    {
        if (isset($block['renderHeightMm'])) {
            return (float) $block['renderHeightMm'];
        }

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
            ? min($config->maxImageMm, max(30.0, $maxAvailable))
            : max(30.0, $maxAvailable);

        $path = (string) ($block['path'] ?? '');
        $maxWidthMm = (float) ($block['widthMm'] ?? $config->contentWidthMm);
        $info = @getimagesize($path);
        $naturalHeight = 40.0;
        if ($info !== false && ($info[0] ?? 0) > 0) {
            $naturalHeight = ((float) $info[1] / (float) $info[0]) * $maxWidthMm;
        }

        if ($naturalHeight > $cap + 0.01) {
            $block['maxHeightMm'] = round($cap, 1);
        } else {
            unset($block['maxHeightMm']);
        }

        return $block;
    }

    /**
     * Gắn kích thước render (mm) giữ đúng tỉ lệ gốc — DomPDF không hỗ trợ object-fit.
     *
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public static function withImageRenderHeight(array $block, PdfPaginationConfig $config): array
    {
        if (($block['type'] ?? '') !== 'image') {
            return $block;
        }

        $path = (string) ($block['path'] ?? '');
        $maxWidthMm = (float) ($block['widthMm'] ?? $config->contentWidthMm);

        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) <= 0) {
            $block['renderWidthMm'] = round($maxWidthMm, 1);
            $block['renderHeightMm'] = 40.0;

            return $block;
        }

        $aspect = (float) $info[1] / (float) $info[0];
        $naturalHeight = $maxWidthMm * $aspect;

        // Nếu bị clamp: thu cả width lẫn height cùng tỉ lệ (DomPDF không có object-fit)
        if (isset($block['maxHeightMm']) && $naturalHeight > (float) $block['maxHeightMm'] + 0.01) {
            $renderHeight = (float) $block['maxHeightMm'];
            $renderWidth  = $renderHeight / $aspect;
        } else {
            $renderWidth  = $maxWidthMm;
            $renderHeight = $naturalHeight;
        }

        $block['renderWidthMm'] = round($renderWidth, 1);
        $block['renderHeightMm'] = round($renderHeight, 1);

        return $block;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    public static function splitParaBlock(array $block, float $roomMm, PdfPaginationConfig $config): array
    {
        $text = (string) ($block['text'] ?? '');
        if ($roomMm <= $config->blockGapMm) {
            return [null, $block];
        }

        $contentBudget = $roomMm - $config->blockGapMm;

        if (self::paraHeightMm($text, $config) <= $contentBudget) {
            return [$block, null];
        }

        $parts = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $l): bool => $l !== ''
        ));

        if ($parts === []) {
            return [null, $block];
        }

        $allLines = [];
        foreach ($parts as $part) {
            foreach (PdfTextWrapHelper::wrapByWidthMm($part, $config->contentWidthMm) as $line) {
                $allLines[] = $line;
            }
        }

        if ($allLines === []) {
            return [$block, null];
        }

        $headLines = [];
        foreach ($allLines as $line) {
            $tryHead = array_merge($headLines, [$line]);
            $tryText = trim(implode(' ', $tryHead));
            $tryHeight = self::paraHeightMm($tryText, $config);

            if ($tryHeight > $contentBudget && $headLines !== []) {
                break;
            }

            $headLines = $tryHead;

            if ($tryHeight > $contentBudget) {
                break;
            }
        }

        if ($headLines === []) {
            $headLines = [$allLines[0]];
        }

        $tailLines = array_slice($allLines, count($headLines));

        $headText = trim(implode(' ', $headLines));
        $tailText = trim(implode(' ', $tailLines));

        return [
            $headText !== '' ? array_merge($block, ['text' => $headText]) : null,
            $tailText !== '' ? array_merge($block, ['text' => $tailText]) : null,
        ];
    }
}
