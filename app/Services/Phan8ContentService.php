<?php

namespace App\Services;

class Phan8ContentService
{
    /**
     * @param  array<string, mixed>|null  $daiVan
     * @param  array<string, mixed>|null  $nienVan
     * @param  array<string, mixed>|null  $nhungNam
     * @param  array<string, mixed>|null  $duBao
     * @return array<int, array<string, mixed>>
     */
    public static function buildAllPageSpecs(
        ?array $daiVan,
        ?array $nienVan,
        string $phanBan = '8a',
        ?array $nhungNam = null,
        ?array $duBao = null
    ): array {
        if ($phanBan === '8b') {
            $specs = [];

            if (is_array($nienVan) && $nienVan !== []) {
                $specs = array_merge(
                    $specs,
                    self::buildNienVanSpecs($nienVan, 'tiep_theo', 'Năm Tiếp Theo', 'Niên Vận Tiếp Theo')
                );
            }

            if (is_array($duBao) && $duBao !== []) {
                $specs = array_merge($specs, self::buildDuBaoKhiaCanhSpecs($duBao));
            }

            return $specs;
        }

        $specs = [];
        if (is_array($daiVan) && $daiVan !== []) {
            $specs = array_merge($specs, self::buildDaiVanSpecs($daiVan));
        }

        if (is_array($nhungNam) && $nhungNam !== []) {
            $specs = array_merge($specs, self::buildNhungNamChuYSpecs($nhungNam));
        }

        return $specs;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    protected static function buildDuBaoKhiaCanhSpecs(array $data): array
    {
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $blocks = [['type' => 'chapter_title', 'text' => 'III. DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG']];
        $hasContent = false;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $match = $item['match'] ?? null;
            if (! is_array($match)) {
                continue;
            }

            $noiDung = trim((string) ($match['noi_dung'] ?? ''));
            if ($noiDung === '') {
                continue;
            }

            $hasContent = true;
            $title = trim((string) ($item['khia_canh'] ?? ''));
            if ($title !== '') {
                $blocks[] = ['type' => 'sub_title', 'text' => $title];
            }

            $dieuKien = trim((string) ($match['dieu_kien'] ?? ''));
            if ($dieuKien !== '') {
                $blocks[] = ['type' => 'para', 'text' => 'Điều kiện khớp: '.$dieuKien];
            }

            foreach (self::paragraphBlocks($noiDung) as $block) {
                $blocks[] = $block;
            }
        }

        if (! $hasContent) {
            return [];
        }

        return [['type' => 'content', 'blocks' => $blocks]];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    protected static function buildNhungNamChuYSpecs(array $data): array
    {
        $daiVanBlocks = is_array($data['dai_van_blocks'] ?? null) ? $data['dai_van_blocks'] : [];
        if ($daiVanBlocks === []) {
            return [];
        }

        $specs = [];
        $introBlocks = [['type' => 'chapter_title', 'text' => 'IV. NHỮNG NĂM CẦN CHÚ Ý']];

        $ghiChuKhacXung = trim((string) ($data['ghi_chu_khac_xung'] ?? ''));
        if ($ghiChuKhacXung !== '') {
            $introBlocks[] = ['type' => 'sub_title', 'text' => 'Khắc + Xung'];
            foreach (self::paragraphBlocks($ghiChuKhacXung) as $block) {
                $introBlocks[] = $block;
            }
        }

        $ghiChuTrung = trim((string) ($data['ghi_chu_trung'] ?? ''));
        if ($ghiChuTrung !== '') {
            $introBlocks[] = ['type' => 'sub_title', 'text' => 'Trùng Can Chi'];
            foreach (self::paragraphBlocks($ghiChuTrung) as $block) {
                $introBlocks[] = $block;
            }
        }

        $specs[] = ['type' => 'content', 'blocks' => $introBlocks];

        foreach ($daiVanBlocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $years = is_array($block['years'] ?? null) ? $block['years'] : [];
            if ($years === []) {
                continue;
            }

            usort($years, static function (array $a, array $b): int {
                return ((int) ($a['nam'] ?? 0)) <=> ((int) ($b['nam'] ?? 0));
            });

            $specs[] = [
                'type' => 'iv_table',
                'data' => [
                    'age' => $block['age'] ?? '',
                    'years' => $years,
                ],
            ];
        }

        return $specs;
    }

    /**
     * @param  array<string, mixed>  $daiVan
     * @return array<int, array<string, mixed>>
     */
    protected static function buildDaiVanSpecs(array $daiVan): array
    {
        $specs = [];
        $introBlocks = [];

        $introBlocks[] = ['type' => 'chapter_title', 'text' => 'I. ĐẠI VẬN'];

        $cdv = $daiVan['current_dai_van'] ?? null;
        if (is_array($cdv)) {
            $introBlocks[] = [
                'type' => 'para',
                'text' => 'Đại Vận hiện tại: Tuổi '.($cdv['age'] ?? '—')
                    .' – Thiên Can: '.($cdv['thien_can'] ?? '—')
                    .' ('.($cdv['thap_than_thien_can'] ?? '—').')'
                    .' | Địa Chi: '.($cdv['dia_chi'] ?? '—')
                    .' ('.($cdv['thap_than_dia_chi'] ?? '—').')',
            ];
        }

        $yNghia = trim((string) ($daiVan['y_nghia']['noi_dung'] ?? ''));
        if ($yNghia !== '') {
            $introBlocks[] = ['type' => 'sub_title', 'text' => 'Ý nghĩa Đại Vận'];
            foreach (self::paragraphBlocks($yNghia) as $b) {
                $introBlocks[] = $b;
            }
        }

        if (count($introBlocks) > 1) {
            $specs[] = ['type' => 'content', 'blocks' => $introBlocks];
        }

        $truMap = [
            'nam' => ['Sự tương tác giữa Đại Vận và Trụ Năm', 'Thiên Can – Đại Vận – Trụ Năm', 'Địa Chi – Đại Vận – Trụ Năm'],
            'thang' => ['Sự tương tác giữa Đại Vận và Trụ Tháng', 'Thiên Can – Đại Vận – Trụ Tháng', 'Địa Chi – Đại Vận – Trụ Tháng'],
            'ngay' => ['Sự tương tác giữa Đại Vận và Trụ Ngày', 'Thiên Can – Đại Vận – Trụ Ngày', 'Địa Chi – Đại Vận – Trụ Ngày'],
            'gio' => ['Sự tương tác giữa Đại Vận và Trụ Giờ', 'Thiên Can – Đại Vận – Trụ Giờ', 'Địa Chi – Đại Vận – Trụ Giờ'],
        ];

        foreach ($truMap as $key => [$sectionTitle, $labelTc, $labelDc]) {
            $tru = $daiVan[$key] ?? null;
            if (! is_array($tru)) {
                continue;
            }

            $truSpecs = self::buildTruSpecs($tru, $sectionTitle, $labelTc, $labelDc);
            $specs = array_merge($specs, $truSpecs);
        }

        return $specs;
    }

    /**
     * @param  array<string, mixed>  $tru
     * @return array<int, array<string, mixed>>
     */
    protected static function buildTruSpecs(array $tru, string $sectionTitle, string $labelTc, string $labelDc): array
    {
        $specs = [];
        $introBlocks = [];
        $hasCoding = false;

        $gt = trim((string) ($tru['gioi_thieu']['noi_dung'] ?? ''));
        if ($gt !== '') {
            $introBlocks[] = ['type' => 'chapter_title', 'text' => $sectionTitle];
            foreach (self::paragraphBlocks($gt) as $b) {
                $introBlocks[] = $b;
            }
        }

        $fallbackBlocks = [];

        foreach (self::normalizeBlocks($tru['thien_can'] ?? null) as $block) {
            $codingSpecs = self::specsFromCodingBlock($block, $labelTc, false);
            if ($codingSpecs !== []) {
                $hasCoding = true;
                $specs = array_merge($specs, $codingSpecs);
            } else {
                $fallbackBlocks = array_merge($fallbackBlocks, self::fallbackBlocksFromCoding($block, $labelTc, false));
            }
        }

        foreach (self::normalizeBlocks($tru['dia_chi'] ?? null) as $block) {
            $codingSpecs = self::specsFromCodingBlock($block, $labelDc, true);
            if ($codingSpecs !== []) {
                $hasCoding = true;
                $specs = array_merge($specs, $codingSpecs);
            } else {
                $fallbackBlocks = array_merge($fallbackBlocks, self::fallbackBlocksFromCoding($block, $labelDc, true));
            }
        }

        if ($fallbackBlocks !== []) {
            $specs[] = ['type' => 'content', 'blocks' => $fallbackBlocks];
        }

        if ($introBlocks !== [] && $specs !== []) {
            foreach ($specs as $idx => $spec) {
                if (($spec['type'] ?? '') === 'coding') {
                    $specs[$idx]['data']['preambleBlocks'] = array_merge(
                        $introBlocks,
                        is_array($specs[$idx]['data']['preambleBlocks'] ?? null)
                            ? $specs[$idx]['data']['preambleBlocks']
                            : []
                    );
                    $introBlocks = [];
                    break;
                }
            }
        }

        if ($introBlocks !== []) {
            $specs = array_merge([['type' => 'content', 'blocks' => $introBlocks]], $specs);
        }

        if (! $hasCoding && $introBlocks === [] && $fallbackBlocks === []) {
            return [];
        }

        return $specs;
    }

    /**
     * @param  array<string, mixed>  $nienVan
     * @return array<int, array<string, mixed>>
     */
    protected static function buildNienVanSpecs(
        array $nienVan,
        string $itemKey = 'hien_tai',
        string $subtitle = 'Năm Hiện Tại',
        string $labelPrefix = 'Niên Vận Hiện Tại'
    ): array {
        $specs = [];
        $item = $nienVan[$itemKey] ?? null;
        if (! is_array($item)) {
            return $specs;
        }

        $year = (int) ($item['nam_number'] ?? date('Y'));
        $nvCodingBg = Phan8AssetService::nienVanCodingBgPath();

        // LBTV-577 là trang bìa mục II. Niên Vận (hình trang trí toàn trang,
        // đã có sẵn số trang) → chỉ đặt tiêu đề căn giữa, không nhồi nội dung.
        $specs[] = [
            'type' => 'nien_van_cover',
            'title' => 'NIÊN VẬN '.$year,
            'subtitle' => $subtitle,
        ];

        // Phần ý nghĩa + tóm tắt đưa sang trang nội dung thường (giống Đại Vận)
        // để nội dung chảy đầy trang, tránh trang trống.
        $introBlocks = [];
        $introBlocks[] = ['type' => 'chapter_title', 'text' => 'II. NIÊN VẬN '.$year];

        $yNghia = trim((string) ($nienVan['y_nghia']['noi_dung'] ?? ''));
        if ($yNghia !== '') {
            foreach (self::paragraphBlocks($yNghia) as $b) {
                $introBlocks[] = $b;
            }
        }

        $introBlocks[] = [
            'type' => 'para',
            'text' => $labelPrefix.': Năm '.$year
                .' – Thiên Can: '.($item['thien_can'] ?? '—')
                .' ('.($item['thap_than_thien_can'] ?? '—').')'
                .' | Địa Chi: '.($item['dia_chi'] ?? '—')
                .' ('.($item['thap_than_dia_chi'] ?? '—').')',
        ];

        $mainIntroBlocks = $introBlocks;

        $truMap = [
            'nam' => ['Trụ Năm', 'Thiên Can – Niên Vận – Trụ Năm', 'Địa Chi – Niên Vận – Trụ Năm'],
            'thang' => ['Trụ Tháng', 'Thiên Can – Niên Vận – Trụ Tháng', 'Địa Chi – Niên Vận – Trụ Tháng'],
            'ngay' => ['Trụ Ngày', 'Thiên Can – Niên Vận – Trụ Ngày', 'Địa Chi – Niên Vận – Trụ Ngày'],
            'gio' => ['Trụ Giờ', 'Thiên Can – Niên Vận – Trụ Giờ', 'Địa Chi – Niên Vận – Trụ Giờ'],
        ];

        $itemIndex = 1;
        foreach ($truMap as $key => [$truLabel, $labelTc, $labelDc]) {
            $tru = $item[$key] ?? null;
            if (! is_array($tru) || ! Phan8TruSectionService::hasDisplayContent($tru)) {
                continue;
            }

            $title = Phan8TruSectionService::interactionTitle($itemIndex, $year, $truLabel);
            $gt = trim((string) ($tru['gioi_thieu']['noi_dung'] ?? ''));
            $body = Phan8TruSectionService::introBodyWithoutTitle($gt);
            $hasCoding = Phan8TruSectionService::hasCodingContent($tru);

            // Có đoạn giới thiệu → trang text LBTV-577 (tiêu đề + nội dung).
            // Không có giới thiệu → gắn tiêu đề vào trang coding kế tiếp,
            // tránh trang text gần trống chỉ có mỗi dòng tiêu đề.
            $pendingHeading = null;
            if ($body !== '') {
                $nvBlocks = [['type' => 'sub_title', 'text' => $title]];
                foreach (self::paragraphBlocks($body) as $b) {
                    $nvBlocks[] = $b;
                }
            } elseif ($hasCoding) {
                $pendingHeading = $title;
                $nvBlocks = [];
            } else {
                $nvBlocks = [];
            }

            if ($body !== '' || $hasCoding) {
                $itemIndex++;
            }

            $truCodingSpecs = [];
            foreach (self::normalizeBlocks($tru['thien_can'] ?? null) as $block) {
                $truCodingSpecs = array_merge($truCodingSpecs, self::specsFromCodingBlock($block, $labelTc, false, $nvCodingBg));
            }
            foreach (self::normalizeBlocks($tru['dia_chi'] ?? null) as $block) {
                $truCodingSpecs = array_merge($truCodingSpecs, self::specsFromCodingBlock($block, $labelDc, true, $nvCodingBg));
            }

            if ($truCodingSpecs !== []) {
                if ($nvBlocks !== []) {
                    $existing = is_array($truCodingSpecs[0]['data']['preambleBlocks'] ?? null)
                        ? $truCodingSpecs[0]['data']['preambleBlocks']
                        : [];
                    $truCodingSpecs[0]['data']['preambleBlocks'] = array_merge($existing, $nvBlocks);
                } elseif ($pendingHeading !== null) {
                    $truCodingSpecs[0]['data']['truHeading'] = $pendingHeading;
                }
            } elseif ($nvBlocks !== []) {
                $specs[] = ['type' => 'content', 'blocks' => $nvBlocks];
            }

            $specs = array_merge($specs, $truCodingSpecs);
        }

        if ($mainIntroBlocks !== []) {
            $attached = false;
            foreach ($specs as $idx => $spec) {
                if (($spec['type'] ?? '') === 'coding') {
                    $existing = is_array($specs[$idx]['data']['preambleBlocks'] ?? null)
                        ? $specs[$idx]['data']['preambleBlocks']
                        : [];
                    $specs[$idx]['data']['preambleBlocks'] = array_merge($mainIntroBlocks, $existing);
                    $attached = true;
                    break;
                }
            }
            if (! $attached) {
                $specs = array_merge([['type' => 'content', 'blocks' => $mainIntroBlocks]], $specs);
            }
        }

        return $specs;
    }

    /**
     * @param  mixed  $blocks
     * @return array<int, array<string, mixed>>
     */
    protected static function normalizeBlocks(mixed $blocks): array
    {
        if (! is_array($blocks)) {
            return [];
        }
        if (isset($blocks['moi_quan_he'])) {
            return [$blocks];
        }

        return array_values(array_filter($blocks, static fn ($b): bool => is_array($b) && ! empty($b['moi_quan_he'] ?? '')));
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    protected static function specsFromCodingBlock(
        array $block,
        string $label,
        bool $isDiaChi,
        ?string $codingBgPath = null
    ): array {
        $phan8a = is_array($block['phan8a'] ?? null) ? $block['phan8a'] : [];
        if ($phan8a === []) {
            return [];
        }

        $specs = [];
        foreach ($phan8a as $p8) {
            if (! is_array($p8)) {
                continue;
            }
            $page = self::buildCodingPageData($block, $p8, $label, $isDiaChi, $codingBgPath);
            if ($page !== null) {
                $specs[] = ['type' => 'coding', 'data' => $page];
            }
        }

        return $specs;
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  array<string, mixed>  $p8
     * @return array<string, mixed>|null
     */
    protected static function buildCodingPageData(
        array $block,
        array $p8,
        string $label,
        bool $isDiaChi,
        ?string $codingBgPath = null
    ): ?array {
        $sections = [];
        foreach ($p8['sections'] ?? [] as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $content = trim((string) ($sec['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            $sections[] = [
                'label' => self::displaySectionLabel((string) ($sec['label'] ?? '')),
                'content' => $content,
            ];
        }

        if ($sections === []) {
            return null;
        }

        [$title, $subtitle] = self::splitTitle((string) ($p8['tieu_de'] ?? ''));

        $meta = [];
        if ($isDiaChi) {
            $meta[] = 'Địa Chi: '.($block['dia_chi_1'] ?? '').' – '.($block['dia_chi_2'] ?? '');
        } else {
            $meta[] = 'Thiên Can: '.($block['thien_can_1'] ?? '').' – '.($block['thien_can_2'] ?? '');
        }
        $meta[] = 'Thập Thần: '.($block['thap_than'] ?? '');
        $meta[] = 'Mối quan hệ: '.($p8['moi_quan_he'] ?? $block['moi_quan_he'] ?? '');

        $titleText = $title !== '' ? $title : mb_strtoupper((string) ($block['thap_than'] ?? ''), 'UTF-8');

        // Render tiêu đề bằng font UTM-Davida thành PNG (giống tiêu đề HÀNH ở Phần 3),
        // vì DomPDF không nạp được Davida nên chữ live bị fallback sang serif.
        $titleImg = NguHanhTitleRenderer::goldTitleToFilePath($titleText, 16, 162.0);
        $contImg  = NguHanhTitleRenderer::goldTitleToFilePath($titleText.' (tiếp)', 12, 162.0);

        return [
            'bgPath' => $codingBgPath ?? Phan8AssetService::codingBgPath(),
            'blockLabel' => $label,
            'title' => $titleText,
            'titleImagePath' => $titleImg['path'],
            'titleImageHeightMm' => $titleImg['heightMm'],
            'contTitleImagePath' => $contImg['path'],
            'contTitleImageHeightMm' => $contImg['heightMm'],
            'subtitle' => $subtitle,
            'meta' => implode(' – ', $meta),
            'sections' => $sections,
        ];
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array<string, mixed>>
     */
    protected static function fallbackBlocksFromCoding(array $block, string $label, bool $isDiaChi): array
    {
        if (empty(trim((string) ($block['moi_quan_he'] ?? '')))) {
            return [];
        }

        $blocks = [];
        $blocks[] = ['type' => 'sub_title', 'text' => $label];

        $meta = [];
        if ($isDiaChi) {
            $meta[] = 'Địa Chi: '.($block['dia_chi_1'] ?? '').' – '.($block['dia_chi_2'] ?? '');
        } else {
            $meta[] = 'Thiên Can: '.($block['thien_can_1'] ?? '').' – '.($block['thien_can_2'] ?? '');
        }
        $meta[] = 'Thập Thần: '.($block['thap_than'] ?? '');
        $meta[] = 'Mối quan hệ: '.($block['moi_quan_he'] ?? '');
        $blocks[] = ['type' => 'para', 'text' => implode(' – ', $meta)];

        $noiDung = is_array($block['noi_dung'] ?? null) ? $block['noi_dung'] : [];
        foreach ($noiDung as $item) {
            if (! is_array($item)) {
                continue;
            }
            $nd = trim((string) ($item['noi_dung'] ?? ''));
            if ($nd === '') {
                continue;
            }
            $filtered = Phan6ContentService::filterNoiDungByMoiQuanHe($nd, (string) ($block['moi_quan_he'] ?? ''));
            if ($filtered === '') {
                continue;
            }
            $huong = trim((string) ($item['huong'] ?? ''));
            if ($huong !== '') {
                $tone = mb_stripos($huong, 'tích') !== false ? 'positive' : 'negative';
                $blocks[] = ['type' => 'huong_label', 'text' => $huong, 'tone' => $tone];
            }
            $blocks[] = ['type' => 'para', 'text' => $filtered];
        }

        return $blocks;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected static function splitTitle(string $tieuDe): array
    {
        $tieuDe = trim($tieuDe);
        if ($tieuDe === '') {
            return ['', ''];
        }
        $parts = preg_split('/\r\n|\r|\n/u', $tieuDe) ?: [];
        $title = trim($parts[0] ?? '');
        $subtitle = trim(implode("\n", array_slice($parts, 1)));

        return [$title, $subtitle];
    }

    public static function displaySectionLabel(string $raw): string
    {
        $lower = mb_strtolower($raw, 'UTF-8');
        if (str_contains($lower, 'cơ hội') || str_starts_with($lower, 'a.')) {
            return 'Cơ hội và sự kiện';
        }
        if (str_contains($lower, 'rủi ro') || str_contains($lower, 'thách') || str_starts_with($lower, 'b.')) {
            return 'Thách thức và thử thách';
        }
        if (str_contains($lower, 'chiến lược') || str_starts_with($lower, 'c.')) {
            return 'Giải pháp và chiến lược trong giai đoạn này';
        }

        return $raw;
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

        if ($blocks === [] && trim($text) !== '') {
            $blocks[] = ['type' => 'para', 'text' => trim($text)];
        }

        return $blocks;
    }
}
