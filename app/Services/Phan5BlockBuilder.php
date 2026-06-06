<?php

namespace App\Services;

class Phan5BlockBuilder
{
    /**
     * @param  array<int, array{sub_title: string, content: array<int, array<string, mixed>>}>  $subSections
     * @return array<int, array<string, mixed>>
     */
    public static function fromTongQuan(array $subSections): array
    {
        $blocks = [];
        foreach ($subSections as $sub) {
            $title = trim((string) ($sub['sub_title'] ?? ''));
            if ($title !== '') {
                $blocks[] = ['type' => 'sub_title', 'text' => $title];
            }
            foreach ($sub['content'] ?? [] as $block) {
                if (! is_array($block)) {
                    continue;
                }
                if (($block['type'] ?? 'para') === 'image') {
                    $blocks[] = [
                        'type' => 'image',
                        'path' => $block['path'] ?? '',
                        'widthMm' => 154.0,
                    ];
                } else {
                    $text = trim((string) ($block['text'] ?? ''));
                    if ($text !== '') {
                        $blocks[] = ['type' => 'para', 'text' => $text];
                    }
                }
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function fromSuNghiepOverview(array $data): array
    {
        $blocks = [];
        $blocks[] = ['type' => 'muc_label', 'text' => '1. Tổng quan:'];
        $tq = trim((string) ($data['tongQuan'] ?? ''));
        if ($tq !== '') {
            $blocks[] = ['type' => 'para', 'text' => $tq];
        }
        if (! empty($data['batTu'])) {
            $blocks[] = [
                'type' => 'table',
                'batTu' => $data['batTu'],
                'highlightPillars' => $data['highlightPillars'] ?? [],
            ];
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function fromKhiaCanhOverview(array $data): array
    {
        $blocks = [];
        $title = trim((string) ($data['sectionTitle'] ?? ''));
        if ($title !== '') {
            $blocks[] = ['type' => 'section_title', 'text' => $title];
        }
        $tq = trim((string) ($data['tongQuan'] ?? ''));
        if ($tq !== '') {
            $blocks[] = ['type' => 'muc_label', 'text' => '1. Tổng quan:'];
            $blocks[] = ['type' => 'para', 'text' => $tq];
        }
        if (! empty($data['batTu'])) {
            $blocks[] = [
                'type' => 'table',
                'batTu' => $data['batTu'],
                'highlightPillars' => $data['highlightPillars'] ?? [],
            ];
        }
        if (! empty($data['imageViTriPath'])) {
            $blocks[] = [
                'type' => 'image',
                'path' => $data['imageViTriPath'],
                'widthMm' => 162.0,
            ];
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function fromThapThanItem(array $data): array
    {
        $blocks = [];
        $itemTitle = ($data['itemNumber'] ?? '').'. '
            .mb_strtoupper((string) ($data['itemLabel'] ?? ''), 'UTF-8').': '
            .mb_strtoupper((string) ($data['thapThanUpper'] ?? ''), 'UTF-8');
        $blocks[] = ['type' => 'item_title', 'text' => trim($itemTitle)];

        if (! empty($data['minhHoaPath'])) {
            $width = ($data['layoutVariant'] ?? '') === 'lbtv119' ? 162.0 : 166.0;
            $blocks[] = ['type' => 'image', 'path' => $data['minhHoaPath'], 'widthMm' => $width];
        }

        $keywords = $data['keywords'] ?? [];
        if (is_array($keywords) && $keywords !== []) {
            $blocks[] = [
                'type' => 'keywords',
                'label' => 'Ba từ khóa cốt lõi',
                'keywords' => $keywords,
                'keywordFramePath' => $data['keywordFramePath'] ?? '',
            ];
        }

        $giaiNghia = trim((string) ($data['giaiNghia'] ?? ''));
        if ($giaiNghia !== '') {
            $blocks[] = ['type' => 'muc_label', 'text' => 'Giải nghĩa năng lượng'];
            foreach (self::linesAsParas($giaiNghia) as $p) {
                $blocks[] = $p;
            }
        }

        foreach ($data['bodySections'] ?? [] as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $label = trim((string) ($sec['label'] ?? ''));
            $content = trim((string) ($sec['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            if (mb_stripos($label, 'từ khóa') !== false) {
                $kw = Phan5PdfService::parseKeywords($content);
                if ($kw !== []) {
                    $blocks[] = [
                        'type' => 'keywords',
                        'label' => $label !== '' ? $label : 'Ba từ khóa cốt lõi',
                        'keywords' => $kw,
                        'keywordFramePath' => $data['keywordFramePath'] ?? '',
                    ];
                }

                continue;
            }
            if ($label !== '') {
                $blocks[] = ['type' => 'muc_label', 'text' => $label];
            }
            foreach (self::linesAsParas($content) as $p) {
                $blocks[] = $p;
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function fromTraits(array $data): array
    {
        $blocks = [];
        $tich = trim((string) ($data['tichCuc'] ?? ''));
        $tieu = trim((string) ($data['tieuCuc'] ?? ''));
        if ($tich !== '' || $tieu !== '') {
            $blocks[] = ['type' => 'traits', 'tichCuc' => $tich, 'tieuCuc' => $tieu];
        }

        $chienLuoc = trim((string) ($data['chienLuoc'] ?? ''));
        if ($chienLuoc !== '') {
            $blocks[] = ['type' => 'chien_luoc_title', 'text' => 'Chiến lược phát triển'];
            foreach (self::linesAsParas($chienLuoc) as $p) {
                $blocks[] = $p;
            }
        }

        return $blocks;
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    protected static function linesAsParas(string $text): array
    {
        $blocks = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $blocks[] = ['type' => 'para', 'text' => $line];
            }
        }

        return $blocks;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function withContinuationTitles(array $blocks, string $continuationSuffix = ' (tiếp)'): array
    {
        $out = [];
        $itemTitle = '';
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'item_title') {
                $itemTitle = (string) ($block['text'] ?? '');
            }
            $out[] = $block;
        }

        if ($itemTitle === '') {
            return $blocks;
        }

        return array_map(static function (array $block) use ($itemTitle, $continuationSuffix): array {
            if (($block['type'] ?? '') === 'item_title' && ($block['text'] ?? '') !== $itemTitle) {
                return $block;
            }
            if (($block['type'] ?? '') === 'item_title' && str_contains((string) ($block['text'] ?? ''), $continuationSuffix)) {
                return $block;
            }

            return $block;
        }, $blocks);
    }
}
