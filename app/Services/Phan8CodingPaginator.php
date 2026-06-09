<?php

namespace App\Services;

use App\Services\Pdf\PdfPaginationConfig;
use App\Services\Pdf\PdfTextSanitizer;
use App\Services\Pdf\PdfTextWrapHelper;

/**
 * Phân trang trang coding Đại Vận (LBTV-195).
 */
class Phan8CodingPaginator
{
    private const CHARS_PER_LINE = 72;

    private const LINE_MM = 5.5;

    /** khoảng cách giữa các bullet trong .section-box */
    private const PARA_GAP_MM = 2.5;

    private const TITLE_CHARS_PER_LINE = 26;

    private const TITLE_LINE_MM = 9.5;

    private const SUBTITLE_LINE_MM = 4.6;

    private const SECTION_TITLE_MM = 10.0;

    private const SECTION_GAP_MM = 3.0;

    private const BOX_PAD_MM = 9.0;

    private const CONT_TITLE_MM = 14.0;

    /** Budget = zone × ratio — buffer chống tràn vùng overflow:hidden */
    private const BUDGET_RATIO = 0.88;

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $data): array
    {
        $data     = PdfTextSanitizer::trimCodingData($data);
        $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
        if ($sections === []) {
            return [];
        }

        $pages = [];
        $remaining = $sections;
        $pageIndex = 0;
        $zoneHeight = PdfPaginationConfig::CONTENT_ZONE_HEIGHT_MM;

        while ($remaining !== []) {
            $budget = round($zoneHeight * self::BUDGET_RATIO, 1);
            $showHeader = $pageIndex === 0;

            if ($showHeader) {
                $budget -= self::headerHeightMm($data);
                $budget -= self::preambleHeightMm($data['preambleBlocks'] ?? []);
            } else {
                $contH = (float) ($data['contTitleImageHeightMm'] ?? 0.0);
                $budget -= $contH > 0 ? $contH + 5.0 : self::CONT_TITLE_MM;
            }

            [$chunk, $remaining] = self::takeSections($remaining, max(25.0, $budget));

            if ($chunk === []) {
                break;
            }

            $pages[] = [
                'bgPath' => $data['bgPath'] ?? Phan8AssetService::codingBgPath(),
                'showHeader' => $showHeader,
                'truHeading' => $showHeader ? ($data['truHeading'] ?? '') : '',
                'blockLabel' => $showHeader ? ($data['blockLabel'] ?? '') : '',
                'title' => $showHeader ? ($data['title'] ?? '') : '',
                'titleImagePath' => $showHeader ? ($data['titleImagePath'] ?? '') : '',
                'titleImageHeightMm' => $showHeader ? ($data['titleImageHeightMm'] ?? 0.0) : 0.0,
                'subtitle' => $showHeader ? ($data['subtitle'] ?? '') : '',
                'meta' => $showHeader ? ($data['meta'] ?? '') : '',
                'preambleBlocks' => $showHeader ? ($data['preambleBlocks'] ?? []) : [],
                'continuationTitle' => $showHeader ? '' : trim((string) ($data['title'] ?? '')).' (tiếp)',
                'contTitleImagePath' => $showHeader ? '' : ($data['contTitleImagePath'] ?? ''),
                'contTitleImageHeightMm' => $showHeader ? 0.0 : ($data['contTitleImageHeightMm'] ?? 0.0),
                'sections' => $chunk,
                'contentZoneTopMm' => PdfPaginationConfig::CONTENT_ZONE_TOP_MM,
                'contentZoneHeightMm' => $zoneHeight,
            ];

            $pageIndex++;
        }

        return $pages;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function headerHeightMm(array $data): float
    {
        $h = 0.0;

        $truHeading = trim((string) ($data['truHeading'] ?? ''));
        if ($truHeading !== '') {
            $h += 9.0;
        }

        $blockLabel = trim((string) ($data['blockLabel'] ?? ''));
        if ($blockLabel !== '') {
            $h += 6.0;
        }

        $titleImgH = (float) ($data['titleImageHeightMm'] ?? 0.0);
        if ($titleImgH > 0) {
            $h += $titleImgH + 4.0;
        } else {
            $title = trim((string) ($data['title'] ?? ''));
            if ($title !== '') {
                $h += max(1, (int) ceil(mb_strlen($title) / self::TITLE_CHARS_PER_LINE)) * self::TITLE_LINE_MM + 4.0;
            }
        }

        $subtitle = trim((string) ($data['subtitle'] ?? ''));
        if ($subtitle !== '') {
            $h += max(1, (int) ceil(mb_strlen($subtitle) / 40)) * self::SUBTITLE_LINE_MM + 5.0;
        }

        $meta = trim((string) ($data['meta'] ?? ''));
        if ($meta !== '') {
            $h += 9.0;
        }

        return $h;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private static function preambleHeightMm(array $blocks): float
    {
        $h = 0.0;
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? 'para');
            if ($type === 'chapter_title') {
                $h += 13.0;
            } elseif (in_array($type, ['sub_title', 'sub_ab'], true)) {
                $h += 9.0;
            } else {
                $text = trim((string) ($block['text'] ?? ''));
                $lines = max(1, self::countContentLines($text));
                $h += $lines * (self::LINE_MM + self::PARA_GAP_MM);
            }
        }

        return $h;
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    private static function takeSections(array $sections, float $maxMm): array
    {
        $chunk = [];
        $used = 0.0;
        $idx = 0;

        while ($idx < count($sections)) {
            $sec = $sections[$idx];
            $need = self::sectionHeightMm($sec);

            if ($chunk !== [] && ($used + $need) > $maxMm) {
                break;
            }

            if ($chunk === [] && $need > $maxMm) {
                [$head, $tail] = self::splitSection($sec, $maxMm);
                if ($head !== null) {
                    $chunk[] = $head;
                    $used += self::sectionHeightMm($head);
                }
                $idx++;
                if ($tail !== null) {
                    $rest = array_slice($sections, $idx);
                    array_unshift($rest, $tail);

                    return [$chunk, $rest];
                }

                break;
            }

            $chunk[] = $sec;
            $used += $need;
            $idx++;
        }

        return [$chunk, array_slice($sections, $idx)];
    }

    /**
     * @param  array<string, mixed>  $section
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    private static function splitSection(array $section, float $maxMm): array
    {
        $label = (string) ($section['label'] ?? '');
        $content = trim((string) ($section['content'] ?? ''));
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $content) ?: []),
            static fn (string $l): bool => $l !== ''
        ));

        if ($lines === []) {
            return [$section, null];
        }

        $overhead = self::SECTION_TITLE_MM + self::BOX_PAD_MM + self::SECTION_GAP_MM;
        $available = $maxMm - $overhead;
        if ($available <= self::LINE_MM) {
            return [null, $section];
        }

        $headLines = [];
        $used = 0.0;
        foreach ($lines as $line) {
            $lineH = self::lineHeightMm($line);
            if ($headLines !== [] && ($used + $lineH) > $available) {
                break;
            }
            if ($headLines === [] && $lineH > $available) {
                $headLines[] = $line;
                break;
            }
            $headLines[] = $line;
            $used += $lineH;
        }

        if ($headLines === []) {
            return [null, $section];
        }

        $tailLines = array_slice($lines, count($headLines));
        $head = array_merge($section, [
            'label' => $label,
            'content' => implode("\n", $headLines),
        ]);
        $tail = $tailLines === [] ? null : array_merge($section, [
            'label' => $label.' (tiếp)',
            'content' => implode("\n", $tailLines),
        ]);

        return [$head, $tail];
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private static function sectionHeightMm(array $section): float
    {
        $content = trim((string) ($section['content'] ?? ''));
        $lineCount = max(1, self::countContentLines($content));

        return self::SECTION_TITLE_MM
            + self::BOX_PAD_MM
            + self::contentLinesHeightMm($content)
            + self::SECTION_GAP_MM;
    }

    private static function contentLinesHeightMm(string $content): float
    {
        $h = 0.0;
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $h += self::lineHeightMm($line);
        }

        return max($h, self::LINE_MM);
    }

    private static function lineHeightMm(string $line): float
    {
        $chunks = PdfTextWrapHelper::wrapAtChars($line, self::CHARS_PER_LINE);
        if ($chunks === []) {
            return self::LINE_MM + self::PARA_GAP_MM;
        }

        return count($chunks) * (self::LINE_MM + self::PARA_GAP_MM);
    }

    private static function countContentLines(string $content): int
    {
        if ($content === '') {
            return 0;
        }

        $total = 0;
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $total += max(1, (int) ceil(mb_strlen($line) / self::CHARS_PER_LINE));
        }

        return $total;
    }
}
