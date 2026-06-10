<?php

namespace App\Services;

use App\Services\Pdf\Phan3NguHanhBanMenhPaginator;
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

    /** Budget = zone × ratio — cùng ngưỡng Phần 3 (98% vùng 85% A4). */
    private const BUDGET_RATIO = 0.98;

    /**
     * Phân trang NHIỀU block coding liên tiếp chảy liền nhau — mỗi block gồm
     * (preamble +) header + sections; chỉ sang trang mới khi trang đầy.
     *
     * Mỗi page trả về: ['bgPath', 'items' => [...], 'contentZoneTopMm', 'contentZoneHeightMm'].
     * Mỗi item: kind = preamble | header | cont | section.
     *
     * @param  array<int, array<string, mixed>>  $datas
     * @return array<int, array<string, mixed>>
     */
    public static function paginateMany(array $datas): array
    {
        $zoneHeight = Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_HEIGHT_MM;
        $zoneBudget = round($zoneHeight * self::BUDGET_RATIO, 1);

        $pages = [];
        $items = [];
        $used = 0.0;
        $pageBg = '';

        $flush = static function () use (&$pages, &$items, &$used, &$pageBg, $zoneHeight): void {
            if ($items === []) {
                return;
            }
            $pages[] = [
                'bgPath' => $pageBg !== '' ? $pageBg : Phan8AssetService::codingBgPath(),
                'items' => $items,
                'contentZoneTopMm' => Phan3NguHanhBanMenhPaginator::CONTENT_ZONE_TOP_MM,
                'contentZoneHeightMm' => $zoneHeight,
            ];
            $items = [];
            $used = 0.0;
            $pageBg = '';
        };

        foreach ($datas as $data) {
            if (! is_array($data)) {
                continue;
            }
            $data = PdfTextSanitizer::trimCodingData($data);
            $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
            if ($sections === []) {
                continue;
            }

            $dataBg = (string) ($data['bgPath'] ?? '');

            // Preamble (Niên Vận có thể gắn intro vào coding) — Đại Vận intro đã ở trang content
            foreach ((array) ($data['preambleBlocks'] ?? []) as $block) {
                if (! is_array($block)) {
                    continue;
                }
                $h = self::preambleHeightMm([$block]);
                if ($items !== [] && ($used + $h) > $zoneBudget) {
                    $flush();
                }
                if ($items === []) {
                    $pageBg = $dataBg;
                }
                $items[] = ['kind' => 'preamble', 'block' => $block];
                $used += $h;
            }

            $headerH = self::headerHeightMm($data);
            $sectionsTotalH = array_sum(array_map(
                static fn (array $s): float => self::sectionHeightMm($s),
                $sections
            ));
            $blockTotalH = $headerH + $sectionsTotalH;

            // Cả block (tiêu đề + 3 section) vừa 1 trang → gom một lần, hạn chế chuyển trang
            if ($items !== [] && ($used + $blockTotalH) > $zoneBudget) {
                $flush();
            }
            if ($items === []) {
                $pageBg = $dataBg;
            }
            if ($blockTotalH <= $zoneBudget - $used) {
                $items[] = [
                    'kind' => 'header',
                    'data' => self::headerData($data),
                ];
                $used += $headerH;
                foreach ($sections as $sec) {
                    $items[] = ['kind' => 'section', 'section' => $sec];
                    $used += self::sectionHeightMm($sec);
                }

                continue;
            }

            // Header phải còn chỗ cho ít nhất 1 section nguyên khối
            if ($items !== [] && ($used + $headerH + 25.0) > $zoneBudget) {
                $flush();
            }
            if ($items === []) {
                $pageBg = $dataBg;
            }
            $items[] = [
                'kind' => 'header',
                'data' => self::headerData($data),
            ];
            $used += $headerH;

            $remaining = $sections;
            $needCont = false;
            while ($remaining !== []) {
                if ($needCont) {
                    $contImgH = (float) ($data['contTitleImageHeightMm'] ?? 0.0);
                    if ($items === []) {
                        $pageBg = $dataBg;
                    }
                    $items[] = [
                        'kind' => 'cont',
                        'data' => [
                            'contTitleImagePath' => $data['contTitleImagePath'] ?? '',
                            'contTitleImageHeightMm' => $contImgH,
                            'continuationTitle' => trim((string) ($data['title'] ?? '')).' (tiếp)',
                        ],
                    ];
                    $used += $contImgH > 0 ? $contImgH + 5.0 : self::CONT_TITLE_MM;
                    $needCont = false;
                }

                [$chunk, $rest] = self::takeSections($remaining, max(25.0, $zoneBudget - $used));

                if ($chunk === []) {
                    $flush();
                    $needCont = true;

                    continue;
                }

                foreach ($chunk as $sec) {
                    if ($items === []) {
                        $pageBg = $dataBg;
                    }
                    $items[] = ['kind' => 'section', 'section' => $sec];
                    $used += self::sectionHeightMm($sec);
                }
                $remaining = $rest;

                if ($remaining !== []) {
                    $flush();
                    $needCont = true;
                }
            }
        }

        $flush();

        return $pages;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function headerData(array $data): array
    {
        return [
            'truHeading' => $data['truHeading'] ?? '',
            'title' => $data['title'] ?? '',
            'titleImagePath' => $data['titleImagePath'] ?? '',
            'titleImageHeightMm' => $data['titleImageHeightMm'] ?? 0.0,
            'subtitle' => $data['subtitle'] ?? '',
        ];
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

            // Không tách section — cả khối (vd. Cơ hội và sự kiện) chuyển sang trang sau
            if ($chunk === [] && $need > $maxMm) {
                $chunk[] = $sec;
                $used += $need;
                $idx++;

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
