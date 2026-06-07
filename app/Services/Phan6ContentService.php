<?php

namespace App\Services;

class Phan6ContentService
{
    /**
     * Bỏ ảnh khỏi payload Phần 6 (web/API + PDF nội dung; giữ bìa Phần 6).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function stripImagesFromApiData(array $data): array
    {
        unset($data['image_map']);

        if (isset($data['y_nghia_tu_tru']) && is_array($data['y_nghia_tu_tru'])) {
            $data['y_nghia_tu_tru'] = array_map(function ($item): array {
                if (! is_array($item)) {
                    return [];
                }
                unset($item['image']);
                if (isset($item['content'])) {
                    $item['content'] = DocxTextService::stripImageMarkers((string) $item['content']);
                }

                return $item;
            }, $data['y_nghia_tu_tru']);
        }

        if (isset($data['transition_phan8']) && is_array($data['transition_phan8'])) {
            unset($data['transition_phan8']['image']);
            if (isset($data['transition_phan8']['content'])) {
                $data['transition_phan8']['content'] = DocxTextService::stripImageMarkers(
                    (string) $data['transition_phan8']['content']
                );
            }
        }

        if (isset($data['dong_chay']) && is_array($data['dong_chay'])) {
            foreach (['nam_thang', 'thang_ngay', 'ngay_gio'] as $sectionKey) {
                if (! isset($data['dong_chay'][$sectionKey]['gioi_thieu'])
                    || ! is_array($data['dong_chay'][$sectionKey]['gioi_thieu'])) {
                    continue;
                }
                unset($data['dong_chay'][$sectionKey]['gioi_thieu']['image']);
                $noiDung = $data['dong_chay'][$sectionKey]['gioi_thieu']['noi_dung'] ?? null;
                if (is_string($noiDung)) {
                    $data['dong_chay'][$sectionKey]['gioi_thieu']['noi_dung'] =
                        DocxTextService::stripImageMarkers($noiDung);
                }
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    public static function buildAllBlocks(array $data): array
    {
        $imageMap = is_array($data['image_map'] ?? null) ? $data['image_map'] : [];
        $blocks = [];

        $blocks[] = ['type' => 'chapter_title', 'text' => 'I. Ý NGHĨA TỨ TRỤ'];

        foreach ($data['y_nghia_tu_tru'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            $blocks = array_merge($blocks, self::blocksFromYnghiaItem($item, $imageMap));
        }

        $laSo = $data['la_so_bat_tu'] ?? null;
        if (is_array($laSo) && ! empty($laSo['rows'])) {
            $blocks[] = ['type' => 'table', 'table' => $laSo];
        }

        $dongChay = is_array($data['dong_chay'] ?? null) ? $data['dong_chay'] : [];

        if (! empty($dongChay['nam_thang'])) {
            $blocks = array_merge(
                $blocks,
                self::blocksFromDongChaySection(
                    $dongChay['nam_thang'],
                    'II. SỰ TƯƠNG TÁC GIỮA TRỤ NĂM VÀ TRỤ THÁNG',
                    'Thiên Can – Trụ Năm – Trụ Tháng',
                    'Địa Chi – Trụ Năm – Trụ Tháng',
                    $imageMap
                )
            );
        }

        if (! empty($dongChay['thang_ngay'])) {
            $blocks = array_merge(
                $blocks,
                self::blocksFromDongChaySection(
                    $dongChay['thang_ngay'],
                    'III. SỰ TƯƠNG TÁC GIỮA TRỤ THÁNG VÀ TRỤ NGÀY',
                    'Thiên Can – Trụ Tháng – Trụ Ngày',
                    'Địa Chi – Trụ Tháng – Trụ Ngày',
                    $imageMap
                )
            );
        }

        if (! empty($dongChay['ngay_gio'])) {
            $blocks = array_merge(
                $blocks,
                self::blocksFromDongChaySection(
                    $dongChay['ngay_gio'],
                    'IV. SỰ TƯƠNG TÁC GIỮA TRỤ NGÀY VÀ TRỤ GIỜ',
                    'Thiên Can – Trụ Ngày – Trụ Giờ',
                    'Địa Chi – Trụ Ngày – Trụ Giờ',
                    $imageMap
                )
            );
        }

        $transition = $data['transition_phan8'] ?? null;
        if (is_array($transition)) {
            $blocks = array_merge($blocks, self::blocksFromTransition($transition));
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromYnghiaItem(array $item, array $imageMap): array
    {
        $blocks = [];
        $title = trim((string) ($item['title'] ?? ''));
        if ($title !== '') {
            $blocks[] = ['type' => 'sub_title', 'text' => $title];
        }

        $imagePath = Phan6AssetService::resolveImagePath($item['image'] ?? null);
        if ($imagePath !== null) {
            $blocks[] = ['type' => 'image', 'path' => $imagePath];
        }

        $content = trim((string) ($item['content'] ?? ''));
        if ($content !== '') {
            $blocks = array_merge($blocks, self::blocksFromYnghiaText($content, $imageMap));
        }

        return $blocks;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromYnghiaText(string $content, array $imageMap): array
    {
        $hasSub = preg_match('/(^|\n)\s*[abc]\.\s/i', $content) === 1
            || preg_match('/(^|\n)\s*-\s+/m', $content) === 1;

        if (! $hasSub) {
            return self::blocksFromTextWithImages($content, $imageMap);
        }

        $blocks = [];
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^[abc]\.\s/i', $line) === 1) {
                $blocks[] = ['type' => 'sub_ab', 'text' => $line];
            } elseif (preg_match('/^-\s+/', $line) === 1) {
                $blocks[] = ['type' => 'para', 'text' => '• '.ltrim(substr($line, 1))];
            } else {
                $blocks = array_merge($blocks, self::blocksFromTextWithImages($line, $imageMap));
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromTextWithImages(string $text, array $imageMap): array
    {
        $blocks = [];
        $markerRe = '/\[\[image:([^\]]+)\]\]/';
        $last = 0;

        if (preg_match_all($markerRe, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $i => $match) {
                $chunk = trim(substr($text, $last, $match[1] - $last));
                if ($chunk !== '') {
                    foreach (self::paragraphBlocks($chunk) as $b) {
                        $blocks[] = $b;
                    }
                }
                $key = $matches[1][$i][0];
                $url = $imageMap[$key] ?? '/'.$key;
                $path = Phan6AssetService::resolveImagePath($url);
                if ($path !== null) {
                    $blocks[] = ['type' => 'image', 'path' => $path];
                }
                $last = $match[1] + strlen($match[0]);
            }
            $tail = trim(substr($text, $last));
            if ($tail !== '') {
                foreach (self::paragraphBlocks($tail) as $b) {
                    $blocks[] = $b;
                }
            }

            return $blocks;
        }

        return self::paragraphBlocks($text);
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    protected static function paragraphBlocks(string $text): array
    {
        $blocks = [];
        foreach (preg_split('/\n\s*\n/u', trim($text)) ?: [] as $para) {
            $para = trim($para);
            if ($para !== '') {
                $blocks[] = ['type' => 'para', 'text' => $para];
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $section
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromDongChaySection(
        array $section,
        string $fallbackTitle,
        string $labelTc,
        string $labelDc,
        array $imageMap
    ): array {
        $blocks = [];
        $gt = is_array($section['gioi_thieu'] ?? null) ? $section['gioi_thieu'] : null;

        if ($gt !== null) {
            $title = trim((string) ($gt['tieu_de'] ?? ''));
            if ($title === '') {
                $title = $fallbackTitle;
            }
            $blocks[] = ['type' => 'chapter_title', 'text' => $title];

            $noiDung = trim((string) ($gt['noi_dung'] ?? ''));
            if ($noiDung !== '') {
                $blocks = array_merge($blocks, self::blocksFromTextWithImages($noiDung, $imageMap));
            }

            $img = Phan6AssetService::resolveImagePath($gt['image'] ?? null);
            if ($img !== null) {
                $blocks[] = ['type' => 'image', 'path' => $img];
            }
        } else {
            $blocks[] = ['type' => 'chapter_title', 'text' => $fallbackTitle];
        }

        $tcBlocks = is_array($section['thien_can'] ?? null) ? $section['thien_can'] : [];
        foreach ($tcBlocks as $block) {
            if (is_array($block)) {
                $blocks = array_merge($blocks, self::blocksFromCodingBlock($block, $labelTc, false));
            }
        }

        $dcBlocks = is_array($section['dia_chi'] ?? null) ? $section['dia_chi'] : [];
        foreach ($dcBlocks as $block) {
            if (is_array($block)) {
                $blocks = array_merge($blocks, self::blocksFromCodingBlock($block, $labelDc, true));
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $blockData
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromCodingBlock(array $blockData, string $title, bool $isDiaChi): array
    {
        if (empty(trim((string) ($blockData['moi_quan_he'] ?? '')))) {
            return [];
        }

        $blocks = [];
        $blocks[] = ['type' => 'sub_title', 'text' => $title];

        $meta = [];
        if ($isDiaChi) {
            $meta[] = 'Địa Chi: '.($blockData['dia_chi_1'] ?? '').' – '.($blockData['dia_chi_2'] ?? '');
        } else {
            $meta[] = 'Thiên Can: '.($blockData['thien_can_1'] ?? '').' – '.($blockData['thien_can_2'] ?? '');
        }
        $meta[] = 'Thập Thần: '.($blockData['thap_than'] ?? '');
        $meta[] = 'Mối quan hệ: '.($blockData['moi_quan_he'] ?? '');
        if (! empty($blockData['ngu_hanh_sinh_ra'])) {
            $meta[] = '(Ngũ hành sinh ra: '.$blockData['ngu_hanh_sinh_ra'].')';
        }
        $blocks[] = ['type' => 'para', 'text' => implode(' – ', $meta)];

        $gt = is_array($blockData['gioi_thieu'] ?? null) ? $blockData['gioi_thieu'] : [];
        $mqhToKey = [
            'Hợp' => 'hop',
            'Khắc' => 'khac',
            'Xung' => 'xung',
            'Hình' => 'hinh',
            'Hại' => 'hai',
            'Phá' => 'pha',
        ];
        $mqhKey = $mqhToKey[$blockData['moi_quan_he'] ?? ''] ?? ($blockData['moi_quan_he'] ?? '');
        $ndMqh = is_array($gt['noi_dung_theo_moi_quan_he'] ?? null)
            ? ($gt['noi_dung_theo_moi_quan_he'][$mqhKey] ?? null)
            : null;
        if (is_string($ndMqh) && $ndMqh !== '') {
            $filtered = self::filterNoiDungByMoiQuanHe($ndMqh, (string) ($blockData['moi_quan_he'] ?? ''));
            if ($filtered !== '') {
                $blocks[] = ['type' => 'coding_box', 'text' => $filtered];
            }
        }

        foreach ($blockData['phan8a'] ?? [] as $p8) {
            if (is_array($p8)) {
                $blocks = array_merge($blocks, self::blocksFromPhan8a($p8));
            }
        }

        $noiDung = is_array($blockData['noi_dung'] ?? null) ? $blockData['noi_dung'] : [];
        $grouped = [];
        $order = [];
        foreach ($noiDung as $item) {
            if (! is_array($item)) {
                continue;
            }
            $tt = trim((string) ($item['thap_than'] ?? 'Chung'));
            if (! isset($grouped[$tt])) {
                $grouped[$tt] = [];
                $order[] = $tt;
            }
            $grouped[$tt][] = $item;
        }

        foreach ($order as $ttName) {
            $blocks[] = ['type' => 'sub_title', 'text' => $ttName];
            foreach ($grouped[$ttName] as $item) {
                $huong = trim((string) ($item['huong'] ?? ''));
                $nd = trim((string) ($item['noi_dung'] ?? ''));
                if ($nd === '') {
                    continue;
                }
                $filtered = self::filterNoiDungByMoiQuanHe($nd, (string) ($blockData['moi_quan_he'] ?? ''));
                if ($filtered === '') {
                    continue;
                }
                $tone = mb_stripos($huong, 'tích') !== false ? 'positive' : 'negative';
                if ($huong !== '') {
                    $blocks[] = ['type' => 'huong_label', 'text' => $huong, 'tone' => $tone];
                }
                $blocks[] = ['type' => 'para', 'text' => $filtered];
            }
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $p8
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromPhan8a(array $p8): array
    {
        $blocks = [];
        $title = trim((string) ($p8['tieu_de'] ?? ''));
        if ($title === '' && ! empty($p8['moi_quan_he'])) {
            $title = 'Mối quan hệ: '.$p8['moi_quan_he'];
        }
        if ($title !== '') {
            $blocks[] = ['type' => 'sub_title', 'text' => $title];
        }

        foreach ($p8['sections'] ?? [] as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $label = trim((string) ($sec['label'] ?? ''));
            $content = trim((string) ($sec['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            if ($label !== '') {
                $blocks[] = ['type' => 'sub_title', 'text' => $label];
            }
            $blocks[] = ['type' => 'para', 'text' => $content];
        }

        return $blocks;
    }

    /**
     * @param  array<string, mixed>  $transition
     * @return array<int, array<string, mixed>>
     */
    protected static function blocksFromTransition(array $transition): array
    {
        $blocks = [];
        $title = trim((string) ($transition['title'] ?? ''));
        if ($title !== '') {
            $blocks[] = ['type' => 'chapter_title', 'text' => $title];
        }
        $content = trim((string) ($transition['content'] ?? ''));
        if ($content !== '') {
            $blocks[] = ['type' => 'para', 'text' => $content];
        }
        $img = Phan6AssetService::resolveImagePath($transition['image'] ?? null);
        if ($img !== null) {
            $blocks[] = ['type' => 'image', 'path' => $img];
        }

        return $blocks;
    }

    public static function filterNoiDungByMoiQuanHe(string $text, string $moiQuanHeStr): string
    {
        $mqhArr = array_values(array_filter(array_map('trim', explode(',', $moiQuanHeStr))));
        if ($mqhArr === []) {
            return trim($text);
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $out = [];
        $keyRe = '/^\[(Hợp|Khắc|Xung|Hình|Hại|Phá)\]\s*/u';
        $inValid = true;

        foreach ($lines as $line) {
            if (preg_match($keyRe, $line, $m)) {
                $inValid = in_array($m[1], $mqhArr, true);
                if ($inValid) {
                    $out[] = trim(preg_replace($keyRe, '', $line) ?? $line);
                }
            } elseif ($inValid) {
                $out[] = $line;
            }
        }

        return trim(implode("\n", $out));
    }
}
