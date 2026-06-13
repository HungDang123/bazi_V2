<?php

namespace App\Services\Pdf;

use App\Models\DinhViGocNhin;
use App\Models\Phan5KhiaCanh;
use Illuminate\Http\Request;

/**
 * Xây dựng cấu trúc mục lục từ bookmark tracker.
 */
class PdfTocOutline
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array{title: string, page: int|null, items: array<int, array{label: string, page: int|null}>}>
     */
    public static function forQuyen1(PdfTocTracker $tracker, Request $req, array $context, int $actualTocPages = 1): array
    {
        $phan3Records = $context['phan3_records'] ?? null;
        $hasClnc = (bool) ($context['has_clnc'] ?? false);
        $phan5Slugs = is_array($context['phan5_slugs'] ?? null) ? $context['phan5_slugs'] : [];

        $recordI = $phan3Records instanceof \Illuminate\Support\Collection
            ? $phan3Records->get('phan3_dinh_vi_goc_nhin')
            : null;
        $recordII = $phan3Records instanceof \Illuminate\Support\Collection
            ? $phan3Records->get('phan3_bocuc_ngu_hanh_ii')
            : null;

        $label3I = self::romanSectionLabel($recordI?->title, 'I. ĐỊNH VỊ VÀ GÓC NHÌN');
        $label3II = self::romanSectionLabel($recordII?->title, 'II. BỐ CỤC NGŨ HÀNH BẢN MỆNH');

        $sections = [];

        $sections = array_merge(
            self::part1And2Sections($tracker, $actualTocPages),
            $sections
        );

        $sections[] = self::partBlock(
            'PHẦN 3: TỔNG QUAN NGŨ HÀNH BẢN MỆNH',
            $tracker->bookmarkDisplayPage('phan3', $actualTocPages),
            array_filter([
                self::item($label3I, $tracker->bookmarkDisplayPage('phan3.i', $actualTocPages)),
                self::item($label3II, $tracker->bookmarkDisplayPage('phan3.ii', $actualTocPages)),
                $hasClnc
                    ? self::item('III. CHẤT LƯỢNG NHẬT CHỦ', $tracker->bookmarkDisplayPage('phan3.iii', $actualTocPages))
                    : null,
            ])
        );

        $sections[] = self::partBlock(
            'PHẦN 5: THẬP THẦN VÀ CÁC KHÍA CẠNH TRONG CUỘC SỐNG',
            $tracker->bookmarkDisplayPage('phan5', $actualTocPages),
            self::phan5Items($tracker, $phan5Slugs, $actualTocPages)
        );

        $sections[] = self::partBlock(
            'PHẦN 6: LUẬN GIẢI DÒNG NĂNG LƯỢNG TRONG LÁ SỐ',
            $tracker->bookmarkDisplayPage('phan6', $actualTocPages),
            array_filter([
                self::item('I. Ý NGHĨA TỨ TRỤ', $tracker->bookmarkDisplayPage('phan6.i', $actualTocPages)),
                self::item('II. SỰ TƯƠNG TÁC GIỮA TRỤ NĂM VÀ TRỤ THÁNG', $tracker->bookmarkDisplayPage('phan6.ii', $actualTocPages)),
                self::item('III. SỰ TƯƠNG TÁC GIỮA TRỤ THÁNG VÀ TRỤ NGÀY', $tracker->bookmarkDisplayPage('phan6.iii', $actualTocPages)),
                self::item('IV. SỰ TƯƠNG TÁC GIỮA TRỤ NGÀY VÀ TRỤ GIỜ', $tracker->bookmarkDisplayPage('phan6.iv', $actualTocPages)),
            ])
        );

        $sections[] = self::partBlock(
            'PHẦN 8: DỰ BÁO HẠN VẬN – ĐẠI VẬN',
            $tracker->bookmarkDisplayPage('phan8', $actualTocPages),
            array_filter([
                self::item('I. ĐẠI VẬN', $tracker->bookmarkDisplayPage('phan8.i', $actualTocPages)),
                self::item('IV. NHỮNG NĂM CẦN CHÚ Ý', $tracker->bookmarkDisplayPage('phan8.iv', $actualTocPages)),
            ])
        );

        $sections[] = self::partBlock(
            'PHẦN 9: GIẢI PHÁP TỐI ƯU ĐỂ KIẾN TẠO VẬN MỆNH',
            $tracker->bookmarkDisplayPage('phan9', $actualTocPages),
            array_filter([
                self::item('I. NỘI LỰC TỰ THÂN', $tracker->bookmarkDisplayPage('phan9.i', $actualTocPages)),
                self::item('II. NGOẠI LỰC', $tracker->bookmarkDisplayPage('phan9.ii', $actualTocPages)),
            ])
        );

        return array_values(array_filter($sections, static fn (array $s): bool => ($s['page'] ?? null) !== null || $s['items'] !== []));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array{title: string, page: int|null, items: array<int, array{label: string, page: int|null}>}>
     */
    public static function forQuyen2(
        PdfTocTracker $tracker,
        Request $req,
        array $context,
        int $actualTocPages = 1
    ): array {
        $nhatChuChapters = is_array($context['nhat_chu_chapters'] ?? null)
            ? $context['nhat_chu_chapters']
            : [];
        $hasPhan9b = (bool) ($context['has_phan9b'] ?? false);
        $nienVanYear = (int) ($context['nien_van_year'] ?? 0);

        $phan4Items = [];
        $chapterKeys = ['phan4.i', 'phan4.ii', 'phan4.iii', 'phan4.iv'];
        foreach ($nhatChuChapters as $idx => $chapter) {
            $key = $chapterKeys[$idx] ?? null;
            if ($key === null) {
                break;
            }
            $label = trim((string) ($chapter['chapter'] ?? ''));
            if ($label === '') {
                continue;
            }
            $page = $tracker->bookmarkDisplayPage($key, $actualTocPages);
            if ($page !== null) {
                $phan4Items[] = self::item($label, $page);
            }
        }

        if ($phan4Items === []) {
            foreach ($chapterKeys as $idx => $key) {
                $defaults = ['I.', 'II.', 'III.', 'IV.'];
                $page = $tracker->bookmarkDisplayPage($key, $actualTocPages);
                if ($page !== null) {
                    $phan4Items[] = self::item($defaults[$idx] ?? 'I.', $page);
                }
            }
        }

        $sections = [];

        $sections = array_merge(
            self::part1And2Sections($tracker, $actualTocPages),
            $sections
        );

        if ($phan4Items !== []) {
            $sections[] = self::partBlock(
                'PHẦN 4: NHẬT CHỦ TRỤ NGÀY',
                $tracker->bookmarkDisplayPage('phan4', $actualTocPages),
                $phan4Items
            );
        }

        $phan7Items = array_filter([
            self::item('I. TAM THẾ', $tracker->bookmarkDisplayPage('phan7.i', $actualTocPages)),
            self::item('II. BÀI HỌC CUỘC SỐNG', $tracker->bookmarkDisplayPage('phan7.ii', $actualTocPages)),
        ]);

        if ($phan7Items !== []) {
            $sections[] = self::partBlock(
                'PHẦN 7: BÀI HỌC CUỘC SỐNG',
                $tracker->bookmarkDisplayPage('phan7', $actualTocPages),
                $phan7Items
            );
        }

        $label8ii = $nienVanYear > 0
            ? 'II. NIÊN VẬN '.$nienVanYear
            : 'II. NIÊN VẬN';

        $phan8Items = array_filter([
            self::item($label8ii, $tracker->bookmarkDisplayPage('phan8.ii', $actualTocPages)),
            self::item('III. DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG', $tracker->bookmarkDisplayPage('phan8.iii', $actualTocPages)),
        ]);

        if ($phan8Items !== []) {
            $sections[] = self::partBlock(
                'PHẦN 8: DỰ BÁO HẠN VẬN',
                $tracker->bookmarkDisplayPage('phan8', $actualTocPages),
                $phan8Items
            );
        }

        if ($hasPhan9b) {
            $sections[] = self::partBlock(
                'PHẦN 9B: GIẢI PHÁP CÂN BẰNG',
                $tracker->bookmarkDisplayPage('phan9b', $actualTocPages),
                array_filter([
                    self::item('I. GIẢI PHÁP CÂN BẰNG', $tracker->bookmarkDisplayPage('phan9b.i', $actualTocPages)),
                    self::item('II. NỘI LỰC TỰ THÂN', $tracker->bookmarkDisplayPage('phan9b.ii', $actualTocPages)),
                    self::item('III. NGOẠI LỰC', $tracker->bookmarkDisplayPage('phan9b.iii', $actualTocPages)),
                    self::item('IV. HIỆU QUẢ CHUYỂN HÓA', $tracker->bookmarkDisplayPage('phan9b.iv', $actualTocPages)),
                ])
            );
        }

        return array_values(array_filter($sections, static fn (array $s): bool => ($s['page'] ?? null) !== null || $s['items'] !== []));
    }

    /**
     * @param  array<int, string>  $activeSlugs
     * @return array<int, array{label: string, page: int|null}>
     */
    private static function phan5Items(PdfTocTracker $tracker, array $activeSlugs, int $actualTocPages): array
    {
        $items = [];
        $pageI = $tracker->bookmarkDisplayPage('phan5.i', $actualTocPages);
        if ($pageI !== null) {
            $items[] = self::item('I. TỔNG QUAN CÁC KHÍA CẠNH', $pageI);
        }

        $slugKeyMap = [
            'su_nghiep' => 'phan5.ii',
            'tai_chinh' => 'phan5.iii',
            'tinh_duyen' => 'phan5.iv',
            'phat_trien_ban_than' => 'phan5.vi',
            'ket_noi_xa_hoi' => 'phan5.vii',
        ];

        foreach (Phan5KhiaCanh::getAllOrdered() as $layout) {
            $slug = (string) $layout->slug;
            if ($slug === 'su_nghiep') {
                $key = 'phan5.ii';
            } else {
                $key = $slugKeyMap[$slug] ?? 'phan5.'.$slug;
            }

            if ($slug !== 'su_nghiep' && ! in_array($slug, $activeSlugs, true)) {
                continue;
            }

            if ($slug === 'su_nghiep' && ! $tracker->hasBookmark('phan5.ii')) {
                continue;
            }

            $code = trim((string) $layout->section_code);
            $title = trim((string) $layout->title);
            $label = self::khiaCanhItemLabel($code, $title);

            $page = $tracker->bookmarkDisplayPage($key, $actualTocPages);
            if ($page !== null && $label !== '') {
                $items[] = self::item($label, $page);
            }
        }

        return $items;
    }

    /**
     * @return array<int, array{title: string, page: int|null, items: array<int, array{label: string, page: int|null}>}>
     */
    private static function part1And2Sections(PdfTocTracker $tracker, int $actualTocPages): array
    {
        $blocks = [];

        $part1 = self::partBlock(
            'PHẦN 1: HIỂU VỀ CÁC KHÁI NIỆM',
            $tracker->bookmarkDisplayPage('phan1', $actualTocPages),
            array_filter([
                self::item('I. BÁT TỰ – TỨ TRỤ', $tracker->bookmarkDisplayPage('phan1.i', $actualTocPages)),
                self::item('II. THUYẾT TAM TÀI & NGŨ THUẬT', $tracker->bookmarkDisplayPage('phan1.ii', $actualTocPages)),
                self::item('III. ÂM DƯƠNG – NGŨ HÀNH', $tracker->bookmarkDisplayPage('phan1.iii', $actualTocPages)),
                self::item('IV. CÁC KHÁI NIỆM KHÁC', $tracker->bookmarkDisplayPage('phan1.iv', $actualTocPages)),
            ])
        );
        if (($part1['page'] ?? null) !== null || $part1['items'] !== []) {
            $blocks[] = $part1;
        }

        $part2 = self::partBlock(
            'PHẦN 2: LÁ SỐ BÁT TỰ CỦA BẠN',
            $tracker->bookmarkDisplayPage('phan2', $actualTocPages),
            array_filter([
                self::item('CHẤT LƯỢNG NGŨ HÀNH', $tracker->bookmarkDisplayPage('phan2.i', $actualTocPages)),
                self::item('CHẤT LƯỢNG THẬP THẦN', $tracker->bookmarkDisplayPage('phan2.ii', $actualTocPages)),
                self::item('CÁC KHÍA CẠNH CUỘC SỐNG', $tracker->bookmarkDisplayPage('phan2.iii', $actualTocPages)),
            ])
        );
        if (($part2['page'] ?? null) !== null || $part2['items'] !== []) {
            $blocks[] = $part2;
        }

        return $blocks;
    }

    private static function khiaCanhItemLabel(string $code, string $title): string
    {
        $title = trim($title);
        $code = trim($code);

        if ($title !== '' && preg_match('/^([IVXLCĐ]+)\./u', mb_strtoupper($title))) {
            return mb_strtoupper($title);
        }

        if ($code !== '' && $title !== '') {
            $prefix = rtrim($code, '.').'.';

            return mb_strtoupper($prefix.' '.$title);
        }

        return mb_strtoupper($title !== '' ? $title : $code);
    }

    /**
     * @param  array<int, array{label: string, page: int|null}>|null  $items
     * @return array{title: string, page: int|null, items: array<int, array{label: string, page: int|null}>}
     */
    private static function partBlock(string $title, ?int $page, ?array $items): array
    {
        $items = array_values(array_filter(
            $items ?? [],
            static fn (array $i): bool => ($i['page'] ?? null) !== null && ($i['label'] ?? '') !== ''
        ));

        return [
            'title' => mb_strtoupper(trim($title)),
            'page'  => $page,
            'items' => $items,
        ];
    }

    /**
     * @return array{label: string, page: int|null}|null
     */
    private static function item(string $label, ?int $page): ?array
    {
        if ($page === null || trim($label) === '') {
            return null;
        }

        return [
            'label' => mb_strtoupper(trim($label)),
            'page'  => $page,
        ];
    }

    private static function romanSectionLabel(?string $dbTitle, string $fallback): string
    {
        $title = trim((string) $dbTitle);
        if ($title === '') {
            return mb_strtoupper($fallback);
        }

        if (preg_match('/^([IVXLCĐ]+)\./u', $title, $m)) {
            return mb_strtoupper($title);
        }

        return mb_strtoupper($fallback);
    }

    /**
     * @return \Illuminate\Support\Collection<string, DinhViGocNhin>
     */
    public static function loadPhan3Records(): \Illuminate\Support\Collection
    {
        return DinhViGocNhin::query()
            ->whereIn('slug', ['phan3_dinh_vi_goc_nhin', 'phan3_bocuc_ngu_hanh_ii'])
            ->get()
            ->keyBy('slug');
    }
}
