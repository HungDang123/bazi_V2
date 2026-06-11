<?php

namespace App\Services;

use App\Models\Hanh;
use App\Models\HanhNoiDung;
use App\Services\Pdf\Phan3NguHanhBanMenhPaginator;

class HanhNoiDungService
{
    /** Thứ tự hiển thị: Kim → Mộc → Thủy → Hỏa → Thổ */
    public const ELEMENT_ORDER = ['kim', 'moc', 'thuy', 'hoa', 'tho'];

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
     * Chia nội dung 5 hành thành các trang PDF (paginator riêng PHẦN 3, ~80% chiều cao A4).
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
            if ($blocks === []) {
                continue;
            }

            $hanhModel = $hanhs->get($element['hanh_slug'] ?? '');
            $imagePath = $hanhModel instanceof Hanh
                ? $hanhModel->resolvedImagePath($imageDir)
                : $imageDir . '/' . ($element['hanh_slug'] ?? '') . '.png';

            $elementPages = Phan3NguHanhBanMenhPaginator::paginate(
                $blocks,
                [
                    'hanh_name'       => (string) ($element['hanh_name'] ?? ''),
                    'percent'         => (int) ($element['percent'] ?? 0),
                    'imagePath'       => $imagePath,
                    'titleImagePath'  => NguHanhTitleRenderer::toFilePath(
                        (string) ($element['hanh_name'] ?? ''),
                        (int) ($element['percent'] ?? 0)
                    ),
                ],
                $firstBgPath
            );

            $pages = array_merge($pages, $elementPages);
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

    private static function splitParagraphs(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $text)));
        if ($paragraphs === []) {
            $paragraphs = array_filter(array_map('trim', explode("\n", $text)));
        }

        return array_values($paragraphs);
    }
}
