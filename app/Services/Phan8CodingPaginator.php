<?php

namespace App\Services;

/**
 * Phân trang trang coding Đại Vận (LBTV-195).
 */
class Phan8CodingPaginator
{
    private const CONTENT_HEIGHT_MM = 222.0;

    private const CHARS_PER_LINE = 72;

    private const LINE_MM = 4.7;

    private const BLOCK_LABEL_MM = 6.0;

    private const TITLE_CHARS_PER_LINE = 26;

    private const TITLE_LINE_MM = 9.5;

    private const SUBTITLE_LINE_MM = 4.6;

    private const META_MM = 9.0;

    private const SECTION_TITLE_MM = 6.5;

    private const SECTION_GAP_MM = 2.5;

    private const BOX_PAD_MM = 6.0;

    private const CONT_TITLE_MM = 13.0;

    private const TRU_HEADING_MM = 8.0;

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function paginate(array $data): array
    {
        $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
        if ($sections === []) {
            return [];
        }

        $pages = [];
        $remaining = $sections;
        $pageIndex = 0;

        while ($remaining !== []) {
            $budget = self::CONTENT_HEIGHT_MM;
            $showHeader = $pageIndex === 0;

            if ($showHeader) {
                $budget -= self::headerHeightMm($data);
            } else {
                $contH = (float) ($data['contTitleImageHeightMm'] ?? 0.0);
                $budget -= $contH > 0 ? $contH + 4.0 : self::CONT_TITLE_MM;
            }

            [$chunk, $remaining] = self::takeSections($remaining, $budget);

            if ($chunk === []) {
                break;
            }

            $page = [
                'bgPath' => $data['bgPath'] ?? Phan8AssetService::codingBgPath(),
                'showHeader' => $showHeader,
                'truHeading' => $showHeader ? ($data['truHeading'] ?? '') : '',
                'blockLabel' => $showHeader ? ($data['blockLabel'] ?? '') : '',
                'title' => $showHeader ? ($data['title'] ?? '') : '',
                'titleImagePath' => $showHeader ? ($data['titleImagePath'] ?? '') : '',
                'titleImageHeightMm' => $showHeader ? ($data['titleImageHeightMm'] ?? 0.0) : 0.0,
                'subtitle' => $showHeader ? ($data['subtitle'] ?? '') : '',
                'meta' => $showHeader ? ($data['meta'] ?? '') : '',
                'continuationTitle' => $showHeader ? '' : trim((string) ($data['title'] ?? '')).' (tiếp)',
                'contTitleImagePath' => $showHeader ? '' : ($data['contTitleImagePath'] ?? ''),
                'contTitleImageHeightMm' => $showHeader ? 0.0 : ($data['contTitleImageHeightMm'] ?? 0.0),
                'sections' => $chunk,
            ];

            $pages[] = $page;
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

        $titleImgH = (float) ($data['titleImageHeightMm'] ?? 0.0);
        if ($titleImgH > 0) {
            $h += $titleImgH + 3;
        } else {
            $title = trim((string) ($data['title'] ?? ''));
            if ($title !== '') {
                $h += max(1, (int) ceil(mb_strlen($title) / self::TITLE_CHARS_PER_LINE)) * self::TITLE_LINE_MM + 3;
            }
        }

        $subtitle = trim((string) ($data['subtitle'] ?? ''));
        if ($subtitle !== '') {
            $h += max(1, (int) ceil(mb_strlen($subtitle) / 40)) * self::SUBTITLE_LINE_MM + 5;
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

            $chunk[] = $sec;
            $used += $need;
            $idx++;
        }

        return [$chunk, array_slice($sections, $idx)];
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private static function sectionHeightMm(array $section): float
    {
        $content = trim((string) ($section['content'] ?? ''));
        $lines = max(1, self::countContentLines($content));

        return self::SECTION_TITLE_MM + self::BOX_PAD_MM + ($lines * self::LINE_MM) + self::SECTION_GAP_MM;
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
