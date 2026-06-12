<?php

namespace App\Services;

use App\Models\DongChayGioiThieu;
use App\Models\HyKyThan;
use App\Models\Phan9bGiaiPhapCanBang;
use App\Services\Pdf\PdfContentPaginator;
use App\Services\Pdf\PdfPaginationConfig;
use App\Services\Pdf\PdfPaginationProfiles;
use App\Services\Pdf\PdfTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PDF Phần 9B — Giải pháp cân bằng (Cuốn 2).
 */
class Phan9bPdfService
{
    public static function coverImagePath(): string
    {
        return resource_path('views/pdfs/phan-9/bia-phan-9b.png');
    }

    public static function introScrollPath(): string
    {
        return Phan9PdfService::introScrollPath();
    }

    public static function contentBgPath(): string
    {
        return Phan9bAssetService::contentBgPath();
    }

    /**
     * @param  array<string, int|float>  $nguHanhDong
     * @param  array<int, array<string, mixed>>|null  $chatLuongThapThan
     * @param  array<string, mixed>  $batTuData  Bát Tự đã tính ở PdfExportController (tránh calc lại)
     * @param  array<string, string>|null  $lucThan
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    public static function buildPdfPages(
        Request $req,
        array $nguHanhDong,
        ?array $chatLuongThapThan = null,
        array $batTuData = [],
        ?array $lucThan = null
    ): array {
        $context = self::resolveContext($req, $nguHanhDong, $chatLuongThapThan, $batTuData, $lucThan);
        if ($context === null) {
            Log::warning('Phan9bPdfService: resolveContext trả null — không render nội dung Phần 9B');

            return [];
        }

        $specs = [];
        $bgPath = self::contentBgPath();

        $sectionIBlocks = [];
        if ($context['display'] !== null) {
            $display = $context['display'];
            if (! empty($context['than_label'])) {
                $display['than_label'] = $context['than_label'];
            }
            self::appendSectionI($sectionIBlocks, $display);
        }
        if ($context['transition'] !== null && $context['transition'] !== '') {
            $sectionIBlocks[] = ['type' => 'sub_title', 'text' => $context['transition']];
        }
        self::appendSpecsFromBlocks($specs, $sectionIBlocks, $bgPath, self::introScrollPath());

        $nl = $context['noi_luc'];
        if (is_array($nl)) {
            $introPages = Phan9bNoiLucPdfBuilder::buildIntroPages($nl);
            if ($introPages !== []) {
                $specs[] = [
                    'view' => 'pdfs.phan-8.la-so-phan-8-content',
                    'data' => ['pages' => $introPages],
                ];
            }

            $hanhPages = Phan9bNoiLucPdfBuilder::buildHanhPages($nl['hanh'] ?? []);
            if ($hanhPages !== []) {
                $specs[] = [
                    'view' => 'pdfs.phan-9.la-so-phan-9b-noi-luc',
                    'data' => ['pages' => $hanhPages],
                ];
            }
        }

        $tailBlocks = [];
        Phan9bNoiLucPdfBuilder::appendThapThanBlocks(
            $tailBlocks,
            $context['thap_than'],
            $context['thap_than_label']
        );
        if ($context['ngoai_luc'] !== null) {
            self::appendSectionIII($tailBlocks, $context['ngoai_luc']);
        }
        if ($context['hieu_qua'] !== null) {
            self::appendSectionIV($tailBlocks, $context['hieu_qua'], $context['chart']);
        }
        self::appendSpecsFromBlocks($specs, $tailBlocks, $bgPath);

        return $specs;
    }

    /**
     * @param  array<int, array{view: string, data: array<string, mixed>}>  $specs
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private static function appendSpecsFromBlocks(
        array &$specs,
        array $blocks,
        string $contentBgPath,
        ?string $introBgPath = null
    ): void {
        if ($blocks === []) {
            return;
        }

        $profile = $introBgPath !== null
            ? PdfPaginationProfiles::phan9WithIntro($contentBgPath, $introBgPath)
            : PdfPaginationProfiles::phan9($contentBgPath);

        $pages = PdfContentPaginator::paginate($blocks, $profile);
        if ($pages === []) {
            return;
        }

        $pages = self::compactFlatPages($pages, $profile);

        $specs[] = [
            'view' => 'pdfs.phan-8.la-so-phan-8-content',
            'data' => ['pages' => $pages],
        ];
    }

    /**
     * @param  array<string, int|float>  $nguHanhDong
     * @param  array<int, array<string, mixed>>|null  $chatLuongThapThan
     * @param  array<string, mixed>  $batTuData
     * @param  array<string, string>|null  $lucThan
     * @return array<string, mixed>|null
     */
    private static function resolveContext(
        Request $req,
        array $nguHanhDong,
        ?array $chatLuongThapThan,
        array $batTuData = [],
        ?array $lucThan = null
    ): ?array {
        try {
            $g = (string) $req->input('g', 'male');

            if ($batTuData === []) {
                $y = (int) $req->input('y');
                $m = (int) $req->input('m');
                $d = (int) $req->input('d');
                $h = $req->filled('h') ? (int) $req->input('h') : null;
                $minute = $req->filled('minute') ? (int) $req->input('minute') : null;

                $result = BaZiServiceV2::calc(
                    (string) $req->input('full_name', ''),
                    $y,
                    $m,
                    $d,
                    $h,
                    $minute,
                    $g,
                    needStrength: false
                );

                $batTuData = is_array($result['bat_tu'] ?? null) ? $result['bat_tu'] : [];
                if ($chatLuongThapThan === null && is_array($result['chat_luong_thap_than'] ?? null)) {
                    $chatLuongThapThan = $result['chat_luong_thap_than'];
                }
                if ($lucThan === null && is_array($result['luc_than'] ?? null)) {
                    $lucThan = $result['luc_than'];
                }
            }

            $dayStem = trim((string) ($batTuData['day']['can']['thien_can'] ?? ''));
            $monthBranch = trim((string) ($batTuData['month']['chi']['dia_chi'] ?? ''));

            if ($dayStem === '' || $monthBranch === '') {
                Log::warning('Phan9bPdfService: thiếu Can ngày / Chi tháng', [
                    'day_stem' => $dayStem,
                    'month_branch' => $monthBranch,
                ]);

                return null;
            }

            $nguHanhBanMenh = self::normalizeNguHanhFromRequest($req, $nguHanhDong);
            $thapThanCaoNhat = Phan9bService::resolveTop2ThapThanBanMenh($chatLuongThapThan);

            $hyKyThan = Phan9bService::findHyKyThan($dayStem, $monthBranch);
            $thanTrangThai = Phan9bService::resolveThanTrangThaiForChart($dayStem, $monthBranch, $batTuData);

            $boNguHanh = Phan9bService::resolveBoHyThanNguHanh($dayStem, $hyKyThan);

            $transition = DongChayGioiThieu::query()
                ->where('tru_loai', 'transition_phan9b')
                ->value('noi_dung');

            return [
                'display' => $thanTrangThai !== null
                    ? Phan9bService::buildDisplay($thanTrangThai, $boNguHanh)
                    : null,
                'than_label' => $thanTrangThai !== null
                    ? (Phan9bGiaiPhapCanBang::THAN_LABELS[$thanTrangThai] ?? $thanTrangThai)
                    : null,
                'transition' => $transition !== null ? trim((string) $transition) : null,
                'noi_luc' => Phan9bService::buildNoiLucDisplay(),
                'thap_than' => Phan9bService::buildThapThanDisplay($thapThanCaoNhat),
                'thap_than_label' => Phan9bService::formatThapThanCaoNhat($thapThanCaoNhat),
                'ngoai_luc' => Phan9bService::buildNgoaiLucDisplay(),
                'hieu_qua' => Phan9bService::buildHieuQuaDisplay($lucThan, $g),
                'chart' => Phan9bService::computeNguHanhChuyenHoaChart($nguHanhBanMenh),
            ];
        } catch (\Throwable $e) {
            Log::error('Phan9bPdfService: resolveContext lỗi — '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, int|float>  $fallback
     * @return array<string, int>
     */
    private static function normalizeNguHanhFromRequest(Request $req, array $fallback): array
    {
        $keys = ['kim', 'moc', 'thuy', 'hoa', 'tho'];
        $fromRequest = [];
        foreach ($keys as $key) {
            if ($req->filled($key)) {
                $fromRequest[$key] = (int) $req->input($key);
            }
        }

        if (count($fromRequest) === 5) {
            return Phan9aService::normalizeNguHanhDong($fromRequest);
        }

        return Phan9aService::normalizeNguHanhDong($fallback);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $display
     */
    private static function appendSectionI(array &$blocks, array $display): void
    {
        $blocks[] = [
            'type' => 'chapter_title',
            'text' => $display['tieu_de'] ?? 'I. GIẢI PHÁP CÂN BẰNG',
        ];

        if (! empty($display['than_label'])) {
            $blocks[] = [
                'type' => 'sub_title',
                'text' => 'Trạng thái Nhật Chủ: '.$display['than_label'],
            ];
        }

        foreach ($display['sections'] ?? [] as $sec) {
            if (! empty($sec['tieu_de'])) {
                $blocks[] = ['type' => 'sub_title', 'text' => (string) $sec['tieu_de']];
            }
            foreach ($sec['doan'] ?? [] as $item) {
                $text = trim((string) ($item['noi_dung'] ?? ''));
                if ($text === '') {
                    continue;
                }
                if (! empty($item['is_hanh_dong'])) {
                    $blocks[] = ['type' => 'sub_title', 'text' => $text];
                } else {
                    self::appendParagraphs($blocks, $text);
                }
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $nluc
     */
    private static function appendSectionIII(array &$blocks, array $nluc): void
    {
        $blocks[] = ['type' => 'chapter_title', 'text' => $nluc['tieu_de'] ?? 'III. NGOẠI LỰC'];
        if (! empty($nluc['subtitle'])) {
            $blocks[] = ['type' => 'sub_title', 'text' => (string) $nluc['subtitle']];
        }
        foreach ($nluc['intro'] ?? [] as $para) {
            self::appendParagraphs($blocks, (string) $para);
        }
        foreach ($nluc['sections'] ?? [] as $sec) {
            if (! empty($sec['tieu_de'])) {
                $blocks[] = ['type' => 'sub_title', 'text' => (string) $sec['tieu_de']];
            }
            foreach ($sec['items'] ?? [] as $item) {
                self::appendParagraphs($blocks, (string) $item);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $hq
     * @param  array<string, mixed>|null  $chart
     */
    private static function appendSectionIV(array &$blocks, array $hq, ?array $chart): void
    {
        $blocks[] = [
            'type' => 'chapter_title',
            'text' => $hq['tieu_de'] ?? 'IV. HIỆU QUẢ CHUYỂN HÓA',
        ];
        if (! empty($hq['subtitle'])) {
            $blocks[] = ['type' => 'sub_title', 'text' => (string) $hq['subtitle']];
        }

        foreach ($hq['sections'] ?? [] as $sec) {
            if (! empty($sec['tieu_de'])) {
                $blocks[] = ['type' => 'sub_title', 'text' => (string) $sec['tieu_de']];
            }
            if (! empty($sec['intro'])) {
                self::appendParagraphs($blocks, (string) $sec['intro']);
            }
            foreach ($sec['items'] ?? [] as $item) {
                if (($item['type'] ?? '') === 'chart') {
                    self::appendChartSummary($blocks, $chart);

                    continue;
                }
                self::appendParagraphs($blocks, (string) ($item['noi_dung'] ?? ''));
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>|null  $chart
     */
    private static function appendChartSummary(array &$blocks, ?array $chart): void
    {
        if ($chart === null || empty($chart['rows'])) {
            return;
        }

        $blocks[] = ['type' => 'sub_title', 'text' => 'Mô phỏng biểu đồ Ngũ Hành cần cải thiện'];
        if (! empty($chart['weakest']['ten'])) {
            $blocks[] = [
                'type' => 'para',
                'text' => 'Hành yếu nhất: '.$chart['weakest']['ten']
                    .' ('.($chart['weakest']['phan_tram'] ?? 0).'%)',
            ];
        }
        foreach ($chart['rows'] as $row) {
            $delta = (int) ($row['after'] ?? 0) - (int) ($row['before'] ?? 0);
            $sign = $delta > 0 ? '+' : ($delta < 0 ? '' : '');
            $dir = match ($row['direction'] ?? 'stable') {
                'tang' => 'Tăng',
                'giam' => 'Giảm',
                default => 'Không đổi',
            };
            $blocks[] = [
                'type' => 'para',
                'text' => sprintf(
                    '%s: %d%% → %d%% (%s%s%d%%)',
                    $row['ten'] ?? '',
                    (int) ($row['before'] ?? 0),
                    (int) ($row['after'] ?? 0),
                    $dir,
                    $sign,
                    $delta
                ),
            ];
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private static function appendParagraphs(array &$blocks, string $text): void
    {
        PdfTextSanitizer::appendParagraphBlocks($blocks, $text);
    }

    /**
     * Gộp trang phẳng liền kề nếu còn budget — giảm phân trang thừa.
     *
     * @param  array<int, array<string, mixed>>  $pages
     * @return array<int, array<string, mixed>>
     */
    public static function compactFlatPages(array $pages, \App\Services\Pdf\PdfPaginationConfig $config): array
    {
        if (count($pages) < 2) {
            return $pages;
        }

        $budget = $config->contentHeightMm;
        $merged = [$pages[0]];

        for ($i = 1; $i < count($pages); $i++) {
            $prevIdx = count($merged) - 1;
            $prev    = $merged[$prevIdx];
            $cur     = $pages[$i];

            if (($prev['bgPath'] ?? '') !== ($cur['bgPath'] ?? '')) {
                $merged[] = $cur;
                continue;
            }

            $combined = array_merge($prev['blocks'] ?? [], $cur['blocks'] ?? []);
            $used     = 0.0;
            foreach ($combined as $block) {
                if (is_array($block)) {
                    $used += PdfContentPaginator::blockHeightMm($block, $config);
                }
            }

            $pageBudget = $budget;
            if ($config->budgetAdjustResolver !== null && ($prev['chapterTitle'] ?? '') !== '') {
                $pageBudget = ($config->budgetAdjustResolver)(0, $combined, $budget);
            }

            if ($used <= $pageBudget + 4.0) {
                $merged[$prevIdx]['blocks'] = $combined;
                continue;
            }

            $merged[] = $cur;
        }

        foreach ($merged as $idx => &$page) {
            if ($config->bgResolver !== null) {
                $page['bgPath'] = ($config->bgResolver)($idx);
            }
            if ($config->pageMetaResolver !== null) {
                $page = array_merge($page, ($config->pageMetaResolver)($idx, $page));
            }
        }
        unset($page);

        return $merged;
    }
}
