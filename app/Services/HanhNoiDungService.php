<?php

namespace App\Services;

use App\Models\Hanh;
use App\Models\HanhNoiDung;

class HanhNoiDungService
{
    /** Thứ tự hiển thị: Kim → Mộc → Thủy → Hỏa → Thổ */
    public const ELEMENT_ORDER = ['kim', 'moc', 'thuy', 'hoa', 'tho'];

    /** ~32 dòng text sau title + ảnh trên trang đầu mỗi hành */
    private const FIRST_PAGE_MAX_LINES = 32;

    /** ~48 dòng text trên trang overflow */
    private const CONT_PAGE_MAX_LINES = 48;

    private const CHARS_PER_LINE = 92;

    public static function phanTramToSlug(int $percent): string
    {
        if ($percent == 0) {
            return 'khuyet_0';
        }
        if ($percent < 30) {
            return 'duoi_30';
        }
        if ($percent < 60) {
            return '30_60';
        }
        if ($percent < 80) {
            return '60_80';
        }

        return 'tren_80';
    }

    /**
     * @param  array<string, int>  $phanTramNienVan
     * @return array<int, array{hanh_name: string, hanh_slug: string, percent: int, slug: string, image: string|null, items: array<int, array{title: string|null, content: string|null}>}>
     */
    public static function buildHanhNoiDungNienVan(array $phanTramNienVan): array
    {
        $hanhs = Hanh::whereIn('slug', self::ELEMENT_ORDER)->get()->keyBy('slug');
        $hanhIds = $hanhs->pluck('id')->all();

        $noiDungByKey = [];
        if (!empty($hanhIds)) {
            foreach (HanhNoiDung::whereIn('hanh_id', $hanhIds)->orderBy('sort_order')->get() as $r) {
                $noiDungByKey[$r->hanh_id . '|' . $r->slug][] = [
                    'title'   => $r->title,
                    'content' => $r->content,
                ];
            }
        }

        $out = [];

        foreach (self::ELEMENT_ORDER as $elementSlug) {
            $percent = (int) ($phanTramNienVan[$elementSlug] ?? 0);
            $slug    = self::phanTramToSlug($percent);

            $hanh     = $hanhs->get($elementSlug);
            $hanhName = $hanh ? $hanh->name : $elementSlug;
            $items    = $hanh ? ($noiDungByKey[$hanh->id . '|' . $slug] ?? []) : [];

            $out[] = [
                'hanh_name' => $hanhName,
                'hanh_slug' => $elementSlug,
                'percent'   => $percent,
                'slug'      => $slug,
                'image'     => $hanh?->image,
                'items'     => $items,
            ];
        }

        return $out;
    }

    /**
     * Chia nội dung 5 hành thành các trang PDF (ảnh đầu, overflow sang page-22-bg).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function buildPdfPages(
        array $phanTramNienVan,
        string $imageDir,
        string $firstBgPath,
        string $contBgPath
    ): array {
        $hanhs    = Hanh::whereIn('slug', self::ELEMENT_ORDER)->get()->keyBy('slug');
        $elements = self::buildHanhNoiDungNienVan($phanTramNienVan);
        $pages    = [];

        foreach ($elements as $element) {
            $blocks = self::flattenElementBlocks($element);
            if (empty($blocks)) {
                continue;
            }

            $hanhModel = $hanhs->get($element['hanh_slug'] ?? '');
            $imagePath = $hanhModel instanceof Hanh
                ? $hanhModel->resolvedImagePath($imageDir)
                : $imageDir . '/' . ($element['hanh_slug'] ?? '') . '.png';
            $pageIndex = 0;

            while (!empty($blocks)) {
                $maxLines = $pageIndex === 0 ? self::FIRST_PAGE_MAX_LINES : self::CONT_PAGE_MAX_LINES;
                [$chunk, $blocks] = self::takeBlocksByLines($blocks, $maxLines);

                $pages[] = [
                    'bgPath'     => $pageIndex === 0 ? $firstBgPath : $contBgPath,
                    'showTitle'  => $pageIndex === 0,
                    'showImage'  => $pageIndex === 0,
                    'hanhName'   => mb_strtoupper($element['hanh_name'], 'UTF-8'),
                    'percent'    => $element['percent'],
                    'imagePath'  => $imagePath,
                    'titleImagePath' => $pageIndex === 0
                        ? NguHanhTitleRenderer::toFilePath($element['hanh_name'], $element['percent'])
                        : '',
                    'blocks'     => $chunk,
                ];

                $pageIndex++;
            }
        }

        return $pages;
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    private static function flattenElementBlocks(array $element): array
    {
        $blocks = [];

        foreach ($element['items'] as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title !== '') {
                $blocks[] = ['type' => 'item_title', 'text' => $title];
            }

            foreach (self::splitParagraphs((string) ($item['content'] ?? '')) as $para) {
                $blocks[] = ['type' => 'para', 'text' => $para];
            }
        }

        return $blocks;
    }

    /**
     * @param  array<int, array{type: string, text: string}>  $blocks
     * @return array{0: array<int, array{type: string, text: string}>, 1: array<int, array{type: string, text: string}>}
     */
    private static function takeBlocksByLines(array $blocks, float $maxLines): array
    {
        $chunk = [];
        $used  = 0.0;

        while (!empty($blocks)) {
            $block = $blocks[0];
            $need  = self::blockLineCount($block);

            if (!empty($chunk) && ($used + $need) > $maxLines) {
                break;
            }

            if (empty($chunk) && $need > $maxLines) {
                [$head, $tail] = self::splitBlockByLines($block, (int) floor($maxLines));
                if ($head !== null) {
                    $chunk[] = $head;
                }
                array_shift($blocks);
                if ($tail !== null) {
                    array_unshift($blocks, $tail);
                }
                break;
            }

            $chunk[] = array_shift($blocks);
            $used   += $need;
        }

        return [$chunk, $blocks];
    }

    private static function blockLineCount(array $block): float
    {
        if ($block['type'] === 'item_title') {
            return 1.3;
        }

        $lines = max(1, (int) ceil(mb_strlen($block['text']) / self::CHARS_PER_LINE));

        return $lines * 1.05;
    }

    /**
     * @return array{0: ?array{type: string, text: string}, 1: ?array{type: string, text: string}}
     */
    private static function splitBlockByLines(array $block, int $maxLines): array
    {
        $maxChars = max(100, $maxLines * self::CHARS_PER_LINE);
        $text     = $block['text'];

        if (mb_strlen($text) <= $maxChars) {
            return [$block, null];
        }

        $head = mb_substr($text, 0, $maxChars);
        $tail = mb_substr($text, $maxChars);

        if ($block['type'] === 'para') {
            if (preg_match('/^(.{0,' . $maxChars . '}[\.!\?\…]\s)/us', $text, $m)) {
                $head = trim($m[1]);
                $tail = trim(mb_substr($text, mb_strlen($m[1])));
            } elseif (($sp = mb_strrpos($head, ' ')) !== false) {
                $tail = trim(mb_substr($text, $sp));
                $head = trim(mb_substr($text, 0, $sp));
            }
        }

        $headBlock = $head !== '' ? ['type' => $block['type'], 'text' => $head] : null;
        $tailBlock = $tail !== '' ? ['type' => $block['type'], 'text' => $tail] : null;

        return [$headBlock, $tailBlock];
    }

    private static function splitParagraphs(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $text)));
        if (empty($paragraphs)) {
            $paragraphs = array_filter(array_map('trim', explode("\n", $text)));
        }

        return array_values($paragraphs);
    }
}
