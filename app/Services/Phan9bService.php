<?php

namespace App\Services;

use App\Models\HyKyThan;
use App\Models\Phan9bGiaiPhapCanBang;
use App\Models\Phan9bHieuQuaChuyenHoa;
use App\Models\Phan9bNgoaiLuc;
use App\Models\Phan9bNoiLuc;
use App\Models\Phan9bThapThan;

class Phan9bService
{
    /** @var array<int, string> Thứ tự hiển thị biểu đồ beam chart */
    public const CHART_HANH_ORDER = ['moc', 'hoa', 'tho', 'kim', 'thuy'];

    /** @var array<int, array{0: string, 1: array<int, string>, 2: int}> */
    public const TRANG_THAI_MATRIX = [
        ['Vượng', ['Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'], 60],
        ['Tướng', ['Hỏa', 'Thổ', 'Kim', 'Thủy', 'Mộc'], 0],
        ['Hưu', ['Thủy', 'Mộc', 'Hỏa', 'Thổ', 'Kim'], -5],
        ['Tù', ['Kim', 'Thủy', 'Mộc', 'Hỏa', 'Thổ'], -10],
        ['Tử', ['Thổ', 'Kim', 'Thủy', 'Mộc', 'Hỏa'], -15],
    ];

    /** @var array<string, int> slug => cột index trong TRANG_THAI_MATRIX */
    public const TRANG_THAI_COLUMN_INDEX = [
        'moc' => 0,
        'hoa' => 1,
        'tho' => 2,
        'kim' => 3,
        'thuy' => 4,
    ];

    /** @var array<int, string> */
    public const THAP_THAN_ORDER = [
        'Tỷ Kiên',
        'Kiếp Tài',
        'Thực Thần',
        'Thương Quan',
        'Thiên Tài',
        'Chính Tài',
        'Thất Sát',
        'Chính Quan',
        'Thiên Ấn',
        'Chính Ấn',
    ];

    /** @var array<string, string> */
    public const THAP_THAN_SLUG = [
        'Tỷ Kiên' => 'ty_kien',
        'Kiếp Tài' => 'kiep_tai',
        'Thực Thần' => 'thuc_than',
        'Thương Quan' => 'thuong_quan',
        'Thiên Tài' => 'thien_tai',
        'Chính Tài' => 'chinh_tai',
        'Thất Sát' => 'that_sat',
        'Chính Quan' => 'chinh_quan',
        'Thiên Ấn' => 'thien_an',
        'Chính Ấn' => 'chinh_an',
    ];

    /** @var array<string, string> bo slug => Lục Thần trên biểu đồ NGŨ HÀNH BẢN MỆNH */
    public const BO_LUC_THAN_LABEL = [
        'quan_quy' => 'Quan Quỷ',
        'the_tai' => 'Thê Tài',
        'phu_mau' => 'Phụ Mẫu',
        'tu_ton' => 'Tử Tôn',
        'huynh_de' => 'Huynh Đệ',
    ];

    /** @var array<string, string> */
    public const THAP_THAN_TO_BO = [
        'Tỷ Kiên' => 'huynh_de',
        'Kiếp Tài' => 'huynh_de',
        'Thực Thần' => 'tu_ton',
        'Thương Quan' => 'tu_ton',
        'Thiên Tài' => 'the_tai',
        'Chính Tài' => 'the_tai',
        'Thất Sát' => 'quan_quy',
        'Chính Quan' => 'quan_quy',
        'Thiên Ấn' => 'phu_mau',
        'Chính Ấn' => 'phu_mau',
    ];

    /** @var array<string, string> */
    protected static array $stemElements = [
        'Giáp' => 'Mộc', 'Ất' => 'Mộc',
        'Bính' => 'Hỏa', 'Đinh' => 'Hỏa',
        'Mậu' => 'Thổ', 'Kỷ' => 'Thổ',
        'Canh' => 'Kim', 'Tân' => 'Kim',
        'Nhâm' => 'Thủy', 'Quý' => 'Thủy',
    ];

    /** @var array<string, bool> true = Dương */
    protected static array $stemYinYang = [
        'Giáp' => true, 'Ất' => false,
        'Bính' => true, 'Đinh' => false,
        'Mậu' => true, 'Kỷ' => false,
        'Canh' => true, 'Tân' => false,
        'Nhâm' => true, 'Quý' => false,
    ];

    public static function resolveThanTrangThai(?HyKyThan $hyKyThan): ?string
    {
        if ($hyKyThan === null) {
            return null;
        }

        $label = trim((string) $hyKyThan->than_nhuoc_than_vuong);

        if (preg_match('/VƯỢNG/u', mb_strtoupper($label, 'UTF-8'))) {
            return Phan9bGiaiPhapCanBang::THAN_VUONG;
        }

        if (preg_match('/NHƯỢC/u', mb_strtoupper($label, 'UTF-8'))) {
            return Phan9bGiaiPhapCanBang::THAN_NHUOC;
        }

        return null;
    }

    /**
     * @return array<string, string> bo slug => ngũ hành
     */
    public static function resolveBoHyThanNguHanh(string $dayStem, ?HyKyThan $hyKyThan): array
    {
        if ($hyKyThan === null) {
            return [];
        }

        $cans = self::splitList((string) $hyKyThan->hy_than_can);
        $hanhs = self::splitList((string) $hyKyThan->hy_than_ngu_hanh);
        $map = [];

        foreach ($cans as $i => $can) {
            $thapThan = self::relation($dayStem, $can);
            $bo = self::THAP_THAN_TO_BO[$thapThan] ?? null;
            if ($bo === null) {
                continue;
            }

            $hanh = isset($hanhs[$i]) && $hanhs[$i] !== ''
                ? $hanhs[$i]
                : (self::$stemElements[$can] ?? '');

            if ($hanh !== '') {
                $map[$bo] = $hanh;
            }
        }

        return $map;
    }

    public static function replaceBoPlaceholders(string $text, array $boNguHanh): string
    {
        foreach ($boNguHanh as $bo => $hanh) {
            $text = str_replace('[' . $bo . ']', $hanh, $text);
        }

        return preg_replace('/\[(tu_ton|the_tai|quan_quy|phu_mau|huynh_de)\]/', '', $text) ?? $text;
    }

    /**
     * 2 Thập Thần có % bản mệnh (natal) cao nhất trong CHẤT LƯỢNG THẬP THẦN.
     *
     * @param  array<int, array{name?: string, natal?: mixed}>|null  $chatLuongThapThan
     * @return array<int, array{name: string, natal: int, slug: string}>
     */
    public static function resolveTop2ThapThanBanMenh(?array $chatLuongThapThan, ?int $minNatal = null): array
    {
        if (! is_array($chatLuongThapThan) || count($chatLuongThapThan) === 0) {
            return [];
        }

        $items = [];
        foreach ($chatLuongThapThan as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $natal = (int) ($row['natal'] ?? 0);
            if ($minNatal !== null && $natal < $minNatal) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'natal' => $natal,
                'slug' => self::THAP_THAN_SLUG[$name] ?? mb_strtolower(str_replace(' ', '_', $name), 'UTF-8'),
            ];
        }

        usort($items, static function (array $a, array $b): int {
            if ($a['natal'] !== $b['natal']) {
                return $b['natal'] <=> $a['natal'];
            }

            $order = array_flip(self::THAP_THAN_ORDER);

            return ($order[$a['name']] ?? 99) <=> ($order[$b['name']] ?? 99);
        });

        return array_slice($items, 0, 2);
    }

    /**
     * @param  array<int, array{name?: string}>  $top2
     */
    public static function formatThapThanCaoNhat(array $top2): string
    {
        $names = [];
        foreach ($top2 as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        if (count($names) === 0) {
            return '';
        }

        if (count($names) === 1) {
            return $names[0];
        }

        return $names[0] . ' và ' . $names[1];
    }

    public static function replaceThapThanCaoNhatPlaceholder(string $text, array $top2): string
    {
        $formatted = self::formatThapThanCaoNhat($top2);
        if ($formatted === '') {
            return preg_replace('/\[thap_than_cao_nhat\]/u', '', $text) ?? $text;
        }

        return str_replace('[thap_than_cao_nhat]', $formatted, $text);
    }

    /**
     * Ngũ hành có % thấp nhất trong biểu đồ NGŨ HÀNH BẢN MỆNH (cột Bản Mệnh).
     *
     * @param  array<string, int|float>|null  $nguHanhDong
     * @return array{slug: string, ten: string, phan_tram: int}|null
     */
    public static function resolveYeuNhatBanMenh(?array $nguHanhDong, bool $requireAboveZero = false): ?array
    {
        $normalized = Phan9aService::normalizeNguHanhDong(is_array($nguHanhDong) ? $nguHanhDong : []);
        if ($normalized === []) {
            return null;
        }

        if ($requireAboveZero) {
            $normalized = array_filter($normalized, static fn (int $v): bool => $v > 0);
            if ($normalized === []) {
                return null;
            }
        }

        return Phan9aService::resolveYeuNhatNguHanh($normalized);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function buildDisplay(string $thanTrangThai, array $boNguHanh): ?array
    {
        $rows = Phan9bGiaiPhapCanBang::query()
            ->where('loai', 'noi_dung')
            ->where('than_trang_thai', $thanTrangThai)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $header = Phan9bGiaiPhapCanBang::query()
            ->where('loai', 'section')
            ->orderBy('sort_order')
            ->value('noi_dung');

        $sections = [];
        $currentMuc = null;
        $currentIndex = -1;

        foreach ($rows as $row) {
            $muc = $row->muc ?? '';
            if ($muc !== $currentMuc) {
                $sections[] = [
                    'muc' => $muc,
                    'tieu_de' => $row->tieu_de,
                    'doan' => [],
                ];
                $currentIndex++;
                $currentMuc = $muc;
            } elseif ($row->tieu_de !== null && $row->tieu_de !== '' && ($sections[$currentIndex]['tieu_de'] ?? '') === '') {
                $sections[$currentIndex]['tieu_de'] = $row->tieu_de;
            }

            $noiDung = self::replaceBoPlaceholders(trim((string) $row->noi_dung), $boNguHanh);
            if ($noiDung === '') {
                continue;
            }

            $sections[$currentIndex]['doan'][] = [
                'noi_dung' => $noiDung,
                'bo_hy_than' => $row->bo_hy_than,
                'is_hanh_dong' => $row->bo_hy_than !== null,
            ];
        }

        return [
            'tieu_de' => $header ?: 'I. GIẢI PHÁP CÂN BẰNG',
            'sections' => $sections,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildNoiLucDisplay(): array
    {
        $section = Phan9bNoiLuc::query()
            ->where('loai', 'section')
            ->orderBy('sort_order')
            ->value('noi_dung');

        $intro = Phan9bNoiLuc::query()
            ->where('loai', 'intro')
            ->orderBy('sort_order')
            ->pluck('noi_dung')
            ->map(fn ($text) => trim((string) $text))
            ->filter(fn (string $text) => $text !== '')
            ->values()
            ->all();

        $muc = Phan9bNoiLuc::query()
            ->where('loai', 'muc')
            ->orderBy('sort_order')
            ->value('noi_dung');

        $hanhBlocks = [];
        foreach (Phan9bNoiLuc::HANH_ORDER as $slug) {
            $rows = Phan9bNoiLuc::getHanhRows($slug);
            if ($rows->isEmpty()) {
                continue;
            }

            $hanhBlocks[] = self::buildHanhBlock($slug, $rows);
        }

        return [
            'tieu_de' => $section ?: 'II. NỘI LỰC TỰ THÂN',
            'intro' => $intro,
            'muc' => $muc,
            'hanh' => $hanhBlocks,
        ];
    }

    /**
     * Sheet II.2 — Thập Thần: toàn bộ nội dung, thay [thap_than_cao_nhat].
     *
     * @param  array<int, array{name?: string, natal?: int, slug?: string}>  $top2
     * @return array<string, mixed>|null
     */
    public static function buildThapThanDisplay(array $top2): ?array
    {
        $muc = Phan9bThapThan::query()
            ->where('loai', 'muc')
            ->orderBy('sort_order')
            ->value('noi_dung');

        if ($muc === null) {
            return null;
        }

        $mucNote = Phan9bThapThan::query()
            ->where('loai', 'muc_note')
            ->orderBy('sort_order')
            ->value('noi_dung');

        $intro = Phan9bThapThan::query()
            ->where('loai', 'intro')
            ->orderBy('sort_order')
            ->pluck('noi_dung')
            ->map(fn ($text) => trim((string) $text))
            ->filter(fn (string $text) => $text !== '')
            ->values()
            ->all();

        if ($mucNote !== null && trim((string) $mucNote) !== '') {
            $note = self::replaceThapThanCaoNhatPlaceholder(trim((string) $mucNote), $top2);
            if ($note !== '') {
                $muc = trim($muc) . ' — ' . $note;
            }
        }

        $topSlugs = array_values(array_filter(array_map(
            static fn (array $item): string => trim((string) ($item['slug'] ?? '')),
            $top2
        )));

        $blocks = [];
        foreach (self::THAP_THAN_ORDER as $label) {
            $slug = self::THAP_THAN_SLUG[$label] ?? null;
            if ($slug === null) {
                continue;
            }

            $rows = Phan9bThapThan::getThapThanRows($slug);
            if ($rows->isEmpty()) {
                continue;
            }

            $blocks[] = self::buildThapThanBlock($label, $slug, $rows, in_array($slug, $topSlugs, true));
        }

        if ($blocks === []) {
            return null;
        }

        return [
            'muc' => $muc,
            'intro' => $intro,
            'thap_than' => $blocks,
        ];
    }

    /**
     * @param  iterable<Phan9bThapThan>  $rows
     * @return array<string, mixed>
     */
    public static function buildThapThanBlock(string $label, string $slug, iterable $rows, bool $isTop = false): array
    {
        $bo = null;
        $tagline = null;
        $intro = null;
        $sections = [];
        $current = null;

        foreach ($rows as $row) {
            if ($row->bo !== null && $row->bo !== '') {
                $bo = $row->bo;
            }

            $td = trim((string) ($row->tieu_de ?? ''));
            $nd = trim((string) ($row->noi_dung ?? ''));

            if ($tagline === null && $td !== '' && $nd === '' && ! preg_match('/^Về\s+/u', $td)) {
                $tagline = $td;

                continue;
            }

            if ($intro === null && $td === '' && $nd !== '' && ! preg_match('/^Về\s+/u', $nd)) {
                $intro = $nd;

                continue;
            }

            if ($td !== '' && preg_match('/^Về\s+/u', $td)) {
                $current = ['tieu_de' => $td, 'doan' => []];
                $sections[] = $current;

                continue;
            }

            if ($nd === '') {
                continue;
            }

            if ($current === null) {
                $current = ['tieu_de' => $td !== '' ? $td : null, 'doan' => []];
                $sections[] = $current;
            }

            $sections[count($sections) - 1]['doan'][] = self::formatBulletParagraphs($nd);
        }

        return [
            'thap_than' => [
                'slug' => $slug,
                'ten' => $label,
            ],
            'bo' => $bo !== null ? (Phan9bThapThan::BO_LABELS[$bo] ?? $bo) : null,
            'tagline' => $tagline,
            'intro' => $intro,
            'is_top' => $isTop,
            'sections' => $sections,
        ];
    }

    /**
     * @param  iterable<Phan9bNoiLuc>  $rows
     * @return array<string, mixed>
     */
    public static function buildHanhBlock(string $slug, iterable $rows): array
    {
        $tieuDeChinh = null;
        $sections = [];
        $current = null;

        foreach ($rows as $row) {
            $td = trim((string) ($row->tieu_de ?? ''));
            $nd = trim((string) ($row->noi_dung ?? ''));

            if ($td !== '' && preg_match('/^Về\s+/u', $td)) {
                $current = ['tieu_de' => $td, 'doan' => []];
                $sections[] = $current;

                continue;
            }

            if ($tieuDeChinh === null && $td === '' && $nd !== '') {
                $tieuDeChinh = $nd;

                continue;
            }

            if ($nd === '') {
                continue;
            }

            if ($current === null) {
                $current = ['tieu_de' => $td !== '' ? $td : null, 'doan' => []];
                $sections[] = $current;
            }

            $sections[count($sections) - 1]['doan'][] = self::formatBulletParagraphs($nd);
        }

        return [
            'ngu_hanh' => [
                'slug' => $slug,
                'ten' => Phan9bNoiLuc::SLUG_TO_LABEL[$slug] ?? $slug,
            ],
            'tieu_de_chinh' => $tieuDeChinh,
            'sections' => $sections,
        ];
    }

    protected static function formatBulletParagraphs(string $text): string
    {
        $text = preg_replace('/\r\n|\r|\n/u', "\n", $text) ?? $text;
        $text = preg_replace('/\n\s*•\s*/u', "\n- ", $text) ?? $text;
        $text = preg_replace('/^•\s*/u', '- ', $text) ?? $text;

        if (! str_contains($text, "\n- ") && ! str_starts_with($text, '- ')) {
            return $text;
        }

        $lines = preg_split('/\r\n|\r|\n/u', $text) ?: [];
        $parts = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts[] = preg_replace('/^-\s*/u', '', $line) ?? $line;
        }

        return implode("\n", $parts);
    }

    /**
     * @param  array<int, string>  $parts
     * @return array<int, string>
     */
    protected static function splitList(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/u', $raw) ?: [])));
    }

    protected static function relation(string $dayStem, string $otherStem): string
    {
        $dayElement = self::$stemElements[$dayStem] ?? null;
        $otherElement = self::$stemElements[$otherStem] ?? null;
        if (! $dayElement || ! $otherElement) {
            return 'Unknown';
        }

        $dayYin = self::$stemYinYang[$dayStem] ?? true;
        $otherYin = self::$stemYinYang[$otherStem] ?? true;

        $genCycle = [
            'Mộc' => 'Hỏa', 'Hỏa' => 'Thổ', 'Thổ' => 'Kim', 'Kim' => 'Thủy', 'Thủy' => 'Mộc',
        ];
        $ctrlCycle = [
            'Mộc' => 'Thổ', 'Thổ' => 'Thủy', 'Thủy' => 'Hỏa', 'Hỏa' => 'Kim', 'Kim' => 'Mộc',
        ];

        if ($dayElement === $otherElement) {
            return $dayYin === $otherYin ? 'Tỷ Kiên' : 'Kiếp Tài';
        }
        if (($genCycle[$dayElement] ?? null) === $otherElement) {
            return $dayYin === $otherYin ? 'Thực Thần' : 'Thương Quan';
        }
        if (($genCycle[$otherElement] ?? null) === $dayElement) {
            return $dayYin === $otherYin ? 'Thiên Ấn' : 'Chính Ấn';
        }
        if (($ctrlCycle[$dayElement] ?? null) === $otherElement) {
            return $dayYin === $otherYin ? 'Thiên Tài' : 'Chính Tài';
        }
        if (($ctrlCycle[$otherElement] ?? null) === $dayElement) {
            return $dayYin === $otherYin ? 'Thất Sát' : 'Chính Quan';
        }

        return 'Unknown';
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function buildNgoaiLucDisplay(): ?array
    {
        $rows = Phan9bNgoaiLuc::query()->orderBy('sort_order')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return null;
        }

        $tieuDe = null;
        $subtitle = null;
        $intro = [];
        $sections = [];
        $sectionIndex = [];

        foreach ($rows as $row) {
            $text = trim((string) $row->noi_dung);
            if ($text === '') {
                continue;
            }

            switch ($row->loai) {
                case 'header':
                    $tieuDe = $text;
                    break;
                case 'subtitle':
                    $subtitle = $text;
                    break;
                case 'intro':
                    $intro[] = $text;
                    break;
                case 'section':
                    $num = (int) ($row->section_number ?? 0);
                    $sections[] = [
                        'number' => $num,
                        'tieu_de' => $text,
                        'items' => [],
                    ];
                    $sectionIndex[$num] = count($sections) - 1;
                    break;
                case 'item':
                    $num = (int) ($row->section_number ?? 0);
                    if (! isset($sectionIndex[$num])) {
                        break;
                    }
                    $sections[$sectionIndex[$num]]['items'][] = $text;
                    break;
            }
        }

        if ($tieuDe === null && $sections === []) {
            return null;
        }

        return [
            'tieu_de' => $tieuDe ?? 'III. NGOẠI LỰC - CÔNG CỤ HỖ TRỢ',
            'subtitle' => $subtitle,
            'intro' => $intro,
            'sections' => $sections,
        ];
    }

    /**
     * Tính biểu đồ chuyển hóa: lấy cột hành yếu nhất, cộng/trừ điểm % theo từng dòng trạng thái.
     *
     * @param  array<string, int|float>|null  $nguHanhBanMenh
     * @return array<string, mixed>|null
     */
    public static function computeNguHanhChuyenHoaChart(?array $nguHanhBanMenh): ?array
    {
        $normalized = Phan9aService::normalizeNguHanhDong(is_array($nguHanhBanMenh) ? $nguHanhBanMenh : []);
        if ($normalized === []) {
            return null;
        }

        $weakest = self::resolveYeuNhatBanMenh($normalized, true);
        if ($weakest === null) {
            return null;
        }

        $colIndex = self::TRANG_THAI_COLUMN_INDEX[$weakest['slug']] ?? null;
        if ($colIndex === null) {
            return null;
        }

        $deltas = array_fill_keys(self::CHART_HANH_ORDER, 0);

        foreach (self::TRANG_THAI_MATRIX as $row) {
            $hanhLabel = $row[1][$colIndex] ?? '';
            $diem = (int) $row[2];
            $slug = Phan9bNoiLuc::labelToSlug($hanhLabel);
            if ($slug === null) {
                continue;
            }
            $deltas[$slug] += $diem;
        }

        $rows = [];
        foreach (self::CHART_HANH_ORDER as $slug) {
            $before = (int) ($normalized[$slug] ?? 0);
            $after = max(0, min(100, $before + $deltas[$slug]));
            $direction = 'stable';
            if ($after > $before) {
                $direction = 'tang';
            } elseif ($after < $before) {
                $direction = 'giam';
            }

            $rows[] = [
                'slug' => $slug,
                'ten' => Phan9bNoiLuc::SLUG_TO_LABEL[$slug] ?? $slug,
                'before' => $before,
                'after' => $after,
                'direction' => $direction,
            ];
        }

        return [
            'weakest' => $weakest,
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, string>|null  $lucThan
     * @return array<string, mixed>|null
     */
    public static function buildHieuQuaDisplay(?array $lucThan, string $gender = 'male'): ?array
    {
        $rows = Phan9bHieuQuaChuyenHoa::query()->orderBy('sort_order')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return null;
        }

        $tieuDe = null;
        $subtitle = null;
        $sections = [];
        $sectionIndex = [];

        foreach ($rows as $row) {
            $text = trim((string) $row->noi_dung);
            if ($text === '') {
                continue;
            }

            $text = self::replaceHieuQuaBanMenhPlaceholders($text, $lucThan, $gender);
            $num = (int) ($row->section_number ?? 0);

            switch ($row->loai) {
                case 'header':
                    $tieuDe = $text;
                    break;
                case 'subtitle':
                    $subtitle = $text;
                    break;
                case 'section':
                    $sections[] = [
                        'number' => $num,
                        'tieu_de' => $text,
                        'intro' => null,
                        'items' => [],
                    ];
                    $sectionIndex[$num] = count($sections) - 1;
                    break;
                case 'intro':
                    if (isset($sectionIndex[$num])) {
                        $sections[$sectionIndex[$num]]['intro'] = $text;
                    }
                    break;
                case 'chart':
                    if (isset($sectionIndex[$num])) {
                        $sections[$sectionIndex[$num]]['items'][] = [
                            'type' => 'chart',
                            'noi_dung' => '[image_chart]',
                        ];
                    }
                    break;
                case 'item':
                case 'paragraph':
                    if (! isset($sectionIndex[$num])) {
                        break;
                    }
                    if ($text === '[image_chart]' || str_contains($text, '[image_chart]')) {
                        $sections[$sectionIndex[$num]]['items'][] = [
                            'type' => 'chart',
                            'noi_dung' => '[image_chart]',
                        ];
                        if ($text !== '[image_chart]') {
                            $sections[$sectionIndex[$num]]['items'][] = [
                                'type' => 'text',
                                'noi_dung' => str_replace('[image_chart]', '', $text),
                            ];
                        }
                    } else {
                        $sections[$sectionIndex[$num]]['items'][] = [
                            'type' => 'text',
                            'noi_dung' => $text,
                        ];
                    }
                    break;
            }
        }

        if ($tieuDe === null && $sections === []) {
            return null;
        }

        return [
            'tieu_de' => $tieuDe ?? 'IV. HIỆU QUẢ CHUYỂN HÓA VÀ LỘ TRÌNH PHÁT TRIỂN THỰC TẾ',
            'subtitle' => $subtitle,
            'sections' => $sections,
        ];
    }

    /**
     * Thay [quan_quy], [the_tai], … bằng tên hành trên biểu đồ NGŨ HÀNH BẢN MỆNH.
     *
     * @param  array<string, string>|null  $lucThan
     */
    public static function replaceHieuQuaBanMenhPlaceholders(string $text, ?array $lucThan, string $gender): string
    {
        foreach (self::BO_LUC_THAN_LABEL as $bo => $lucThanLabel) {
            $hanh = self::resolveBanMenhHanhChoBo($lucThan, $lucThanLabel);
            $text = str_replace('[' . $bo . ']', $hanh ?? '—', $text);
        }

        $tinhDuyenLabel = $gender === 'female'
            ? self::BO_LUC_THAN_LABEL['quan_quy']
            : self::BO_LUC_THAN_LABEL['the_tai'];
        $tinhDuyen = self::resolveBanMenhHanhChoBo($lucThan, $tinhDuyenLabel) ?? '—';
        $text = str_replace('[tinh_duyen]', $tinhDuyen, $text);

        $legacy = [
            '[VỊ TRÍ QUAN QUỶ]' => self::resolveBanMenhHanhChoBo($lucThan, 'Quan Quỷ') ?? '—',
            '[VỊ TRÍ THÊ TÀI]' => self::resolveBanMenhHanhChoBo($lucThan, 'Thê Tài') ?? '—',
            '[VỊ TRÍ PHỤ MẪU]' => self::resolveBanMenhHanhChoBo($lucThan, 'Phụ Mẫu') ?? '—',
            '[VỊ TRÍ TỬ TÔN]' => self::resolveBanMenhHanhChoBo($lucThan, 'Tử Tôn') ?? '—',
            '[VỊ TRÍ HUYNH ĐỆ]' => self::resolveBanMenhHanhChoBo($lucThan, 'Huynh Đệ') ?? '—',
            '[NAM: THÊ TÀI / NỮ: QUAN QUỶ]' => $tinhDuyen,
        ];
        $text = str_replace(array_keys($legacy), array_values($legacy), $text);

        return preg_replace('/\[(tu_ton|the_tai|quan_quy|phu_mau|huynh_de|tinh_duyen)\]/', '—', $text) ?? $text;
    }

    /**
     * Lục Thần trên biểu đồ → slug hành → tên hành (Mộc, Hỏa, …).
     *
     * @param  array<string, string>|null  $lucThan
     */
    public static function resolveBanMenhHanhChoBo(?array $lucThan, string $lucThanLabel): ?string
    {
        if (! is_array($lucThan)) {
            return null;
        }

        foreach ($lucThan as $slug => $name) {
            if (trim((string) $name) === $lucThanLabel) {
                return Phan9bNoiLuc::SLUG_TO_LABEL[$slug] ?? null;
            }
        }

        return null;
    }
}
