<?php

namespace App\Http\Controllers;

use App\Models\DinhViGocNhin;
use App\Models\NhatChuTruNgay;
use App\Services\BaZiServiceV2;
use App\Services\ChatLuongNhatChuService;
use App\Services\HanhNoiDungService;
use App\Services\NhatChuChapterPdfService;
use App\Services\Phan3PdfPaginator;
use App\Services\Phan5PdfService;
use App\Services\Phan6PdfService;
use App\Services\Phan7MucIIPdfService;
use App\Services\Phan7MucIPdfService;
use App\Services\Phan7PdfService;
use App\Services\Phan8PdfService;
use App\Services\Phan9PdfService;
use App\Services\Phan9bPdfService;
use App\Services\Phan9aService;
use App\Services\PdfDownloadService;
use App\Services\PdfFooterService;
use App\Services\PdfMergeService;
use App\Services\PdfRenderService;
use App\Services\PdfStaticPageCache;
use App\Services\PdfViewCache;
use App\Services\Pdf\PdfExportMetrics;
use App\Services\Pdf\PdfTocRenderer;
use App\Services\Pdf\PdfTocTracker;
use App\Services\PdfBaziCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PdfExportController extends Controller
{
    /**
     * GET /api/la-so/export-pdf-1
     *
     * Xuất PDF quyển 1 gồm:
     *   Trang  1 : page-01.pdf          (Bìa LBTV)
     *   Trang  2 : blade la-so-trang-2  (page-02-bg.png + thông tin cá nhân)
     *   Trang  3 : page-03.pdf          (LBTV-148)
     *   Trang  4 : page-04.pdf          (LBTV-145)
     *   Trang  5 : la-so-muc-luc (LBTV-586 – mục lục động)
     *   Trang  6 : page-06.png          (LBTV-147)
     *   Trang  7 : page-07.png          (LBTV-144)
     *   Trang  8 : page-08.png          (LBTV-149)
     *   Trang  9 : page-09.png          (LBTV-150)
     *   Trang 10 : page-10.png          (LBTV-151)
     *   Trang 11 : page-11.png          (LBTV-152)
     *   Trang 12 : page-12.png          (LBTV-153)
     *   Trang 13 : page-13.png          (LBTV-529 – Phần 2 cover)
     *   Trang 14 : blade la-so-bat-tu   (quyen-2/page-12-bg.png + tứ trụ bát tự)
     *   Trang 15 : blade la-so-dai-van  (quyen-2/page-13-bg.png + đại vận + niên vận)
     *   Trang 16 : blade la-so-chat-luong (quyen-2/page-14-bg.png + radar + thập thần)
     *   Trang 17 : blade la-so-6-khia-canh (quyen-2/page-15-bg.png + biểu đồ 6 khía cạnh)
     *   Trang 18 : page-18.png          (LBTV-101 – Phần 3 cover)
     *   Trang 19 : blade la-so-tong-quan-ngu-hanh (page-19-bg.png + I. + mục 1–2)
     *   Trang 20 : blade la-so-tong-quan-ngu-hanh-tiep (chỉ khi còn mục 3+)
     *   Trang 21+ : blade la-so-bocuc-ngu-hanh (II. Bố cục Ngũ Hành Bản Mệnh)
     *   Trang …+ : blade la-so-ngu-hanh-ban-menh  (Kim→Thổ — sau mục 2.2 Giải mã bức tranh năng lượng)
     *   Trang …  : blade la-so-bocuc-ngu-hanh (III. Chất lượng nhật chủ — theo lá số)
     *   Trang …  : bia-phan-5.png                  (bìa PHẦN 5)
     *   Trang …  : blade la-so-tong-quan-khia-canh (tong-quan-bg.png + I. Tổng quan)
     *   Trang …  : blade la-so-su-nghiep (su-nghiep-bg.png + 1. Tổng quan + bảng Tứ Trụ)
     *   Trang …  : la-so-su-nghiep-thap-than-item (2. Thiên Can Trụ Tháng / 3. Trụ Năm)
     *   Trang …  : la-so-su-nghiep-thap-than-traits (Tích cực / Tiêu cực / Chiến lược)
     *
     * Query params (giống export-pdf-2):
     *   full_name, gender, birth_date, bat_tu, address
     *   y, m, d, h, minute, g
     */
    public function exportLaSo1(Request $req)
    {
        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $finalPath = $tempDir.'/la-so-q1-'.Str::random(10).'.pdf';
        $this->buildQuyen1Pdf($req, $finalPath);

        return PdfDownloadService::download($finalPath, PdfDownloadService::FILENAME_QUYEN_1, false);
    }

    public function buildQuyen1Pdf(Request $req, string $finalPath): void
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');

        PdfExportMetrics::begin(1);

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $uid    = Str::random(10);
        $pdfDir = resource_path('views/pdfs/quyen-1');

        $q2Dir = resource_path('views/pdfs/quyen-2');

        // ── BaZi data (trang 14–17) ───────────────────────────────────────────
        $baziCore = $this->resolveBaziCore($req, 'PdfExport Q1');
        extract($baziCore, EXTR_OVERWRITE);

        $pdfsToMerge = [];
        $tempFiles   = [];
        $footerSegments = [];
        $taggedFooterSegments = [];
        $tocTracker = new PdfTocTracker();
        $headPaths  = [];
        $bodyPaths  = [];
        $hasClnc    = false;
        $phan5Slugs = [];

        // ── Trang 1: PDF tĩnh ───────────────────────────────────────────────
        self::tocAppendStatic($tocTracker, $headPaths, $pdfDir . '/page-01.pdf', 'Q1 page-01.pdf');

        // ── Trang 2: blade la-so-trang-2 (thông tin cá nhân) ─────────────────
        $batTuRaw  = $req->input('bat_tu', '');
        $batTuHtml = $batTuRaw
            ? preg_replace('/\b(Năm|Tháng|Ngày|Giờ)\s+(\S+)/u', '$1 <strong>$2</strong>', $batTuRaw)
            : '';

        $page2Path = $tempDir . '/q1p2-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-trang-2', [
            'templatePath' => resource_path('views/pdfs/quyen-2/page-02-bg.png'),
            'fullName'     => $req->input('full_name', ''),
            'gender'       => $req->input('gender', ''),
            'birthDate'    => $req->input('birth_date', ''),
            'batTu'        => $batTuHtml,
            'address'      => $req->input('address', ''),
        ], $page2Path);
        self::tocPush($tocTracker, $headPaths, $page2Path);
        $tempFiles[]     = $page2Path;

        // ── Trang 3–4 (trước mục lục) + 6–13 (sau mục lục): bundle tĩnh ─────
        $q1BeforeTocSources = [
            $pdfDir . '/page-03.pdf',
            $pdfDir . '/page-04.pdf',
        ];
        $q1AfterTocSources = [
            $pdfDir . '/page-06.png',
            $pdfDir . '/page-07.png',
            $pdfDir . '/page-08.png',
            $pdfDir . '/page-09.png',
            $pdfDir . '/page-10.png',
            $pdfDir . '/page-11.png',
            $pdfDir . '/page-12.png',
            $pdfDir . '/page-13.png',
        ];
        $beforeTocBundle = PdfStaticPageCache::resolveBundle('q1-pages-3-4-v1', $q1BeforeTocSources);
        if ($beforeTocBundle !== null) {
            self::tocPush($tocTracker, $headPaths, $beforeTocBundle);
        } else {
            Log::warning('PdfExport Q1: không tạo được bundle trang 3–4');
        }

        $afterTocBundle = PdfStaticPageCache::resolveBundle('q1-pages-6-13-v1', $q1AfterTocSources);
        if ($afterTocBundle !== null) {
            self::tocPush($tocTracker, $bodyPaths, $afterTocBundle);
        } else {
            Log::warning('PdfExport Q1: không tạo được bundle trang 6–13');
        }

        // ── Trang 14: blade la-so-bat-tu (KẾT QUẢ LÁ SỐ TỨ TRỤ) ────────────
        $page14Path = $tempDir . '/q1p14-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-bat-tu', [
            'templatePath'    => $q2Dir . '/page-12-bg.png',
            'batTu'           => $batTuData,
            'quyNhanVanXuong' => $quyNhanVanXuong,
        ], $page14Path);
        self::tocPush($tocTracker, $bodyPaths, $page14Path);
        $tempFiles[]     = $page14Path;

        // ── Trang 15: blade la-so-dai-van (Đại Vận) ─────────────────────────
        $page15Path = $tempDir . '/q1p15-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-dai-van', [
            'templatePath' => $q2Dir . '/page-13-bg.png',
            'bangDaiVan'   => $bangDaiVan,
        ], $page15Path);
        self::tocPush($tocTracker, $bodyPaths, $page15Path);
        $tempFiles[]     = $page15Path;

        // ── Trang 15b: blade la-so-nien-van (Niên Vận) ───────────────────────
        $page15bPath = $tempDir . '/q1p15b-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-nien-van', [
            'templatePath' => $q2Dir . '/page-13-bg.png',
            'nienVan'      => $nienVan,
        ], $page15bPath);
        self::tocPush($tocTracker, $bodyPaths, $page15bPath);
        $tempFiles[]     = $page15bPath;

        // ── Trang 16: blade la-so-chat-luong (Radar ngũ hành + Thập Thần) ────
        $page16Path   = $tempDir . '/q1p16-' . $uid . '.pdf';
        $nienMenhYear = !empty($nienVan[1]['nam']) ? $nienVan[1]['nam'] : (int) date('Y');
        PdfRenderService::saveView('pdfs.quyen-2.la-so-chat-luong', [
            'templatePath'      => $q2Dir . '/page-14-bg.png',
            'iconDir'           => $q2Dir . '/chat-luong-ngu-hanh',
            'bieuDoNguHanh'     => $bieuDoNguHanh,
            'chatLuongThapThan' => $chatLuongThapThan,
            'nienVanYear'       => $nienMenhYear,
        ], $page16Path);
        self::tocPush($tocTracker, $bodyPaths, $page16Path);
        $tempFiles[]     = $page16Path;

        // ── Trang 17: blade la-so-6-khia-canh (Biểu đồ 6 khía cạnh) ─────────
        $page17Path = $tempDir . '/q1p17-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-6-khia-canh', [
            'templatePath'   => $q2Dir . '/page-15-bg.png',
            'chiSoBieuDoCot' => $chiSoBieuDoCot,
            'gender'         => $req->input('g', 'male'),
        ], $page17Path);
        self::tocPush($tocTracker, $bodyPaths, $page17Path);
        $tempFiles[]     = $page17Path;

        // ── Trang 18: page-18.png (Phần 3 cover, cache) ─────────────────────
        $tocTracker->mark('phan3', 'PHẦN 3: TỔNG QUAN NGŨ HÀNH BẢN MỆNH');
        self::tocAppendStatic($tocTracker, $bodyPaths, $pdfDir . '/page-18.png', 'Q1 page-18.png');

        // ── Trang 19–21: PHẦN 3 (1 query DinhViGocNhin) ───────────────────
        $phan3Records = DinhViGocNhin::whereIn('slug', [
            'phan3_dinh_vi_goc_nhin',
            'phan3_bocuc_ngu_hanh_ii',
        ])->get()->keyBy('slug');

        $record3I = $phan3Records->get('phan3_dinh_vi_goc_nhin');
        $record3II = $phan3Records->get('phan3_bocuc_ngu_hanh_ii');
        $label3I = trim((string) ($record3I?->title ?? ''));
        if ($label3I === '') {
            $label3I = 'I. ĐỊNH VỊ VÀ GÓC NHÌN';
        }
        $label3II = trim((string) ($record3II?->title ?? ''));
        if ($label3II === '') {
            $label3II = 'II. BỐ CỤC NGŨ HÀNH BẢN MỆNH';
        }

        $phan3Salt = implode('|', [
            (string) optional($phan3Records->get('phan3_dinh_vi_goc_nhin'))->updated_at,
            (string) optional($phan3Records->get('phan3_bocuc_ngu_hanh_ii'))->updated_at,
            'phan3-section-i-v2',
            'phan3-layout-v5-page19-top66',
            'content-zone-70pct',
            'lineMm-4.5-blockGap-1.5',
            'paginator-linethreshold-95-imgguard-50',
            'content-zone-187mm-p5p8-chunk-wrap-trim-v6',
            'phan3-chapter-in-content-wrap',
            'pdf-font-v3-svn',
            'bocuc-img-v1',
            'bocuc-paginate-v3-85pct',
            'phan3-section-i-paginate-v2',
            'phan3-height-estimate-v4',
            'pdf-justify-v2',
            'trait-pill-img-v1',
            'traits-height-fix-v2',
            'traits-height-cal-v1',
            'abc-label-bold-traits-split-v1',
            'phan8-no-title-underline-v1',
            'phan8-coding-flow-with-text-v1',
            'phan8-coding-merge-bg-restore-v2',
            'footer-bottom-1mm',
            'phan8-tru-content-coding-atomic-v1',
        ]);

        $phan3SectionI = self::parsePhan3SectionI(
            $phan3Records->get('phan3_dinh_vi_goc_nhin')
        );

        $page19Path = $tempDir . '/q1p19-' . $uid . '.pdf';
        $tocTracker->mark('phan3.i', $label3I);
        self::tocPush($tocTracker, $bodyPaths, PdfViewCache::saveView('pdfs.quyen-1.la-so-tong-quan-ngu-hanh', array_merge([
            'templatePath' => $pdfDir . '/page-19-bg.png',
        ], $phan3SectionI), $page19Path, $phan3Salt));
        $tempFiles[] = $page19Path;

        $contentBg = $pdfDir . '/page-content-bg.png';

        $phan3FromSub2 = self::parsePhan3FromSub2(
            $phan3Records->get('phan3_dinh_vi_goc_nhin'),
            2
        );

        if (! empty($phan3FromSub2['subSections'])) {
            $phan3Sub2Pages = Phan3PdfPaginator::paginateBocucSection(
                ['chapterTitle' => '', 'subSections' => $phan3FromSub2['subSections']],
                $contentBg,
                $contentBg
            );

            if ($phan3Sub2Pages !== []) {
                $page20Path = $tempDir . '/q1p20-' . $uid . '.pdf';
                self::tocPush($tocTracker, $bodyPaths, PdfViewCache::saveView('pdfs.quyen-1.la-so-bocuc-ngu-hanh', [
                    'pages' => $phan3Sub2Pages,
                ], $page20Path, $phan3Salt));
                $tempFiles[] = $page20Path;
            }
        }

        $phan3SectionII = self::parsePhan3SectionFull(
            $phan3Records->get('phan3_bocuc_ngu_hanh_ii')
        );

        $phan3BocucPages = Phan3PdfPaginator::paginateBocucSection(
            $phan3SectionII,
            $contentBg,
            $contentBg
        );

        $page21Path = $tempDir . '/q1p21-' . $uid . '.pdf';
        $tocTracker->mark('phan3.ii', $label3II);
        self::tocPush($tocTracker, $bodyPaths, PdfViewCache::saveView('pdfs.quyen-1.la-so-bocuc-ngu-hanh', [
            'pages' => $phan3BocucPages,
        ], $page21Path, $phan3Salt));
        $tempFiles[] = $page21Path;

        // ── II tiếp: Ngũ hành bản mệnh (Kim → Thổ) — sau mục 2.2 Giải mã bức tranh năng lượng ─
        $nguHanhPages = HanhNoiDungService::buildPdfPages(
            $nguHanhDong,
            $pdfDir . '/ngu-hanh',
            $contentBg,
            $contentBg
        );

        if (! empty($nguHanhPages)) {
            $pageNguHanhPath = $tempDir . '/q1p-ngu-hanh-' . $uid . '.pdf';
            PdfRenderService::saveView('pdfs.quyen-1.la-so-ngu-hanh-ban-menh', [
                'pages' => $nguHanhPages,
            ], $pageNguHanhPath);
            self::tocPush($tocTracker, $bodyPaths, $pageNguHanhPath);
            $tempFiles[]   = $pageNguHanhPath;
        }

        // ── Phần 3 III: Chất lượng nhật chủ (động theo trụ tháng / nhật can) ───
        if (! empty($batTuData)) {
            $clncPages = ChatLuongNhatChuService::buildPdfPages(
                ChatLuongNhatChuService::buildFromBatTu($batTuData),
                $contentBg,
                $contentBg
            );

            if ($clncPages !== []) {
                $pageClncPath = $tempDir . '/q1p-clnc-' . $uid . '.pdf';
                $tocTracker->mark('phan3.iii', 'III. CHẤT LƯỢNG NHẬT CHỦ');
                PdfRenderService::saveView('pdfs.quyen-1.la-so-bocuc-ngu-hanh', [
                    'pages' => $clncPages,
                ], $pageClncPath);
                self::tocPush($tocTracker, $bodyPaths, $pageClncPath);
                $tempFiles[]   = $pageClncPath;
                $hasClnc = true;
            }
        }

        // ── PHẦN 5: bìa + I. Tổng quan các khía cạnh ────────────────────────
        $tocTracker->mark('phan5', 'PHẦN 5: THẬP THẦN VÀ CÁC KHÍA CẠNH TRONG CUỘC SỐNG');
        self::tocAppendStatic(
            $tocTracker,
            $bodyPaths,
            Phan5PdfService::coverImagePath(),
            'Q1 phan5-bia'
        );

        $phan5TongQuan = Phan5PdfService::buildTongQuanPageData();
        if (! empty($phan5TongQuan['pages'])) {
            $pagePhan5Path = $tempDir . '/q1p-phan5-tq-' . $uid . '.pdf';
            PdfRenderService::saveView('pdfs.phan-5.la-so-tong-quan-khia-canh', $phan5TongQuan, $pagePhan5Path);
            $tocTracker->mark('phan5.i', 'I. TỔNG QUAN CÁC KHÍA CẠNH');
            self::tocPush($tocTracker, $bodyPaths, $pagePhan5Path);
            $tempFiles[]   = $pagePhan5Path;
        }

        $phan5SuNghiep = Phan5PdfService::buildSuNghiepPageData($batTuData);
        if ($phan5SuNghiep !== null && ! empty($phan5SuNghiep['pages'])) {
            $pagePhan5SnPath = $tempDir . '/q1p-phan5-sn-' . $uid . '.pdf';
            PdfRenderService::saveView('pdfs.phan-5.la-so-su-nghiep', $phan5SuNghiep, $pagePhan5SnPath);
            $tocTracker->mark('phan5.ii', 'II. SỰ NGHIỆP');
            self::tocPush($tocTracker, $bodyPaths, $pagePhan5SnPath);
            $tempFiles[]   = $pagePhan5SnPath;
            $phan5Slugs[] = 'su_nghiep';
        }

        foreach (Phan5PdfService::buildSuNghiepThienCanPages($batTuData) as $idx => $phan5SnItemPage) {
            $pagePhan5SnItemPath = $tempDir . '/q1p-phan5-sn-item-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan5SnItemPage['view'], $phan5SnItemPage['data'], $pagePhan5SnItemPath);
            self::tocPush($tocTracker, $bodyPaths, $pagePhan5SnItemPath);
            $tempFiles[]   = $pagePhan5SnItemPath;
        }

        if (! empty($batTuData)) {
            $unknowBirthtime = $req->input('uknow_birthdate') == 1;
            $phan5Payload = app(TongQuanKhiaCanhController::class)->buildPhan5PayloadFromBatTu(
                $batTuData,
                $unknowBirthtime,
                ['chat_luong_thap_than' => $chatLuongThapThan]
            );
            $markedPhan5Slugs = [];
            foreach ($phan5Payload['khia_canh'] ?? [] as $block) {
                $slug = (string) ($block['slug'] ?? '');
                if ($slug !== '' && $slug !== 'su_nghiep') {
                    $phan5Slugs[] = $slug;
                }
            }
            $phan5Slugs = array_values(array_unique($phan5Slugs));

            foreach (Phan5PdfService::buildOtherKhiaCanhPdfPages($phan5Payload['khia_canh'] ?? [], $batTuData) as $idx => $phan5KcPage) {
                $pagePhan5KcPath = $tempDir . '/q1p-phan5-kc-' . $idx . '-' . $uid . '.pdf';
                PdfRenderService::saveView($phan5KcPage['view'], $phan5KcPage['data'], $pagePhan5KcPath);
                $layoutKey = (string) ($phan5KcPage['data']['layoutKey'] ?? '');
                if ($layoutKey === 'lbtv119') {
                    $slug = (string) ($phan5KcPage['data']['slug'] ?? '');
                    if ($slug === '' && ! empty($phan5KcPage['data']['sectionTitle'])) {
                        foreach ($phan5Payload['khia_canh'] ?? [] as $block) {
                            if (trim((string) ($block['title'] ?? '')) === trim((string) $phan5KcPage['data']['sectionTitle'])) {
                                $slug = (string) ($block['slug'] ?? '');
                                break;
                            }
                        }
                    }
                    if ($slug !== '' && ! isset($markedPhan5Slugs[$slug])) {
                        $key = self::phan5TocBookmarkKey($slug);
                        $title = trim((string) ($phan5KcPage['data']['sectionTitle'] ?? ''));
                        if ($title !== '') {
                            $tocTracker->mark($key, $title);
                        }
                        $markedPhan5Slugs[$slug] = true;
                    }
                }
                self::tocPush($tocTracker, $bodyPaths, $pagePhan5KcPath);
                $tempFiles[]   = $pagePhan5KcPath;
            }
        }

        // ── PHẦN 6: bìa + nội dung dòng chảy năng lượng ───────────────────────
        $tocTracker->mark('phan6', 'PHẦN 6: LUẬN GIẢI DÒNG NĂNG LƯỢNG TRONG LÁ SỐ');
        $phan6BiaPath         = PdfStaticPageCache::resolve(Phan6PdfService::coverImagePath());
        if ($phan6BiaPath !== null) {
            self::tocPush($tocTracker, $bodyPaths, $phan6BiaPath);
            self::pushMergeSegment($taggedFooterSegments, $phan6BiaPath, 'darkName');
        } else {
            Log::warning('PdfExport Q1: không tìm thấy bìa Phần 6');
        }

        $pagePhan6Path = null;
        $phan6Content  = Phan6PdfService::buildContentPageSpec($req);
        if ($phan6Content !== null) {
            $pagePhan6Path = $tempDir . '/q1p-phan6-content-' . $uid . '.pdf';
            PdfRenderService::saveView($phan6Content['view'], $phan6Content['data'], $pagePhan6Path);
            $phan6Start = $tocTracker->physicalPage() + 1;
            self::tocPush($tocTracker, $bodyPaths, $pagePhan6Path);
            self::tocMarkChapters($tocTracker, $phan6Content, $phan6Start, [
                'I.' => 'phan6.i',
                'II.' => 'phan6.ii',
                'III.' => 'phan6.iii',
                'IV.' => 'phan6.iv',
            ]);
            $tempFiles[]   = $pagePhan6Path;
            self::pushMergeSegment($taggedFooterSegments, $pagePhan6Path, 'darkName');
        }

        // ── PHẦN 8 (8A): bìa + Đại Vận + IV. Những năm cần chú ý ─────────────
        $tocTracker->mark('phan8', 'PHẦN 8: DỰ BÁO HẠN VẬN – ĐẠI VẬN');
        self::tocAppendStatic(
            $tocTracker,
            $bodyPaths,
            Phan8PdfService::coverImagePath(),
            'Q1 phan8-bia'
        );

        foreach (Phan8PdfService::buildPdfPages($req, '8a') as $idx => $phan8Page) {
            $pagePhan8Path = $tempDir . '/q1p-phan8-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan8Page['view'], $phan8Page['data'], $pagePhan8Path);
            $phan8Start = $tocTracker->physicalPage() + 1;
            self::tocPush($tocTracker, $bodyPaths, $pagePhan8Path);
            self::tocMarkChapters($tocTracker, $phan8Page, $phan8Start, [
                'I.' => 'phan8.i',
                'IV.' => 'phan8.iv',
            ]);
            $tempFiles[]   = $pagePhan8Path;
        }

        // ── PHẦN 9: bìa tĩnh (LBTV-236) + nội dung (LBTV-119) (y hệt Q1) ────
        $tocTracker->mark('phan9', 'PHẦN 9: GIẢI PHÁP TỐI ƯU ĐỂ KIẾN TẠO VẬN MỆNH');
        self::tocAppendStatic(
            $tocTracker,
            $bodyPaths,
            Phan9PdfService::coverImagePath(),
            'Q1 phan9-bia'
        );

        $phan9NguHanh = Phan9aService::normalizeNguHanhDong($nguHanhDong);
        foreach (Phan9PdfService::buildPdfPages($phan9NguHanh) as $idx => $phan9Page) {
            $pagePhan9Path = $tempDir . '/q1p-phan9-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan9Page['view'], $phan9Page['data'], $pagePhan9Path);
            $phan9Start = $tocTracker->physicalPage() + 1;
            self::tocPush($tocTracker, $bodyPaths, $pagePhan9Path);
            self::tocMarkChapters($tocTracker, $phan9Page, $phan9Start, [
                'I.' => 'phan9.i',
                'II.' => 'phan9.ii',
            ]);
            $tempFiles[]   = $pagePhan9Path;
        }

        // ── Kết bài: 562–564 + 566–569, 572, 581 (sau Phần 9 — tên footer đen; trang cuối PDF vẫn trắng) ─
        $phan9Dir      = resource_path('views/pdfs/phan-9');
        $q1AppendixDir = resource_path('views/pdfs/q2-appendix');
        self::appendPhan9KetBaiSegments($bodyPaths, $footerSegments, $phan9Dir, $q1AppendixDir, $tocTracker);

        $tocPath = $tempDir . '/q1-toc-' . $uid . '.pdf';
        PdfTocRenderer::renderQuyen1($tocTracker, $req, [
            'phan3_records' => $phan3Records,
            'has_clnc'      => $hasClnc,
            'phan5_slugs'   => $phan5Slugs,
        ], $tocPath);
        $tempFiles[] = $tocPath;

        $pdfsToMerge = array_merge($headPaths, [$tocPath], $bodyPaths);

        $fullName = trim((string) $req->input('full_name', ''));

        // ── Merge + footer (banner 08.png, số trang, tên người nhập) ─────────
        $mergedTemp = $tempDir.'/merged-q1-'.$uid.'.pdf';
        $tMerge     = microtime(true);
        $merged     = PdfMergeService::mergeMultiple($pdfsToMerge, $mergedTemp);
        $mergeMs    = (microtime(true) - $tMerge) * 1000;

        if (! $merged || ! file_exists($mergedTemp)) {
            foreach ($tempFiles as $tmp) {
                @unlink($tmp);
            }
            throw new \RuntimeException('Merge PDF Quyển 1 thất bại');
        }

        $allTaggedSegments = array_merge($taggedFooterSegments, $footerSegments);
        $whiteNamePages    = PdfFooterService::resolveCoverNamePagesFromFullMerge($pdfsToMerge, $allTaggedSegments);
        $darkNamePages = PdfFooterService::resolveDarkNamePagesFromFullMerge($pdfsToMerge, $allTaggedSegments);

        foreach ($tempFiles as $tmp) {
            @unlink($tmp);
        }

        $tFooter  = microtime(true);
        if (! PdfFooterService::applyToMergedPdf(
            $mergedTemp,
            $finalPath,
            $fullName,
            PdfFooterService::FIRST_FOOTER_PAGE,
            PdfFooterService::FIRST_DISPLAY_PAGE_NUMBER,
            $whiteNamePages,
            $darkNamePages
        )) {
            Log::warning('Gắn footer PDF Quyển 1 thất bại — xuất PDF không footer', [
                'uid' => $uid,
                'full_name' => $fullName,
            ]);
            @copy($mergedTemp, $finalPath);
        }
        $footerMs = (microtime(true) - $tFooter) * 1000;
        @unlink($mergedTemp);

        if (! file_exists($finalPath)) {
            throw new \RuntimeException('Gắn footer PDF Quyển 1 thất bại');
        }

        PdfExportMetrics::logFinish([
            'uid'           => $uid,
            'merge_ms'      => round($mergeMs, 1),
            'footer_ms'     => round($footerMs, 1),
            'merge_driver'  => PdfMergeService::lastMergeDriver(),
            'segments'      => count($pdfsToMerge),
        ]);
    }

    /**
     * GET /api/la-so/export-pdf-2
     *
     * Query params hiển thị thông tin cá nhân (trang 2 – cuộn lịch):
     *   full_name   string   Họ & tên
     *   gender      string   Giới tính hiển thị (Nam / Nữ)
     *   birth_date  string   Ngày sinh dương lịch (chuỗi hiển thị)
     *   bat_tu      string   Bát tự sinh thần (plain text)
     *   address     string   Địa chỉ (tuỳ chọn)
     *
     * Query params tính Bát Tự (trang 12 – la-so-bat-tu):
     *   y       int     Năm sinh (dương lịch)
     *   m       int     Tháng sinh
     *   d       int     Ngày sinh
     *   h       int     Giờ sinh (0–23), tuỳ chọn
     *   minute  int     Phút sinh (0–59), tuỳ chọn
     *   g       string  Giới tính tính lá số: 'male' | 'female'  (mặc định 'male')
     *
     * Thứ tự trang PDF xuất ra:
     *   Trang  1 : page-01.png                  → full A4
     *   Trang  2 : blade la-so-trang-2           (page-02-bg.png nền + text cá nhân)
     *   Trang  3 : page-03.pdf
     *   Trang  4 : page-04.png
     *   Trang  5 : la-so-muc-luc (LBTV-586 – mục lục động)
     *   Trang  6 : page-06.png
     *   Trang  7 : page-07.png
     *   Trang  8 : page-08.png
     *   Trang  9 : page-09.png
     *   Trang 10 : page-10.png
     *   Trang 11 : page-11.png                  → full A4 (section cover)
     *   Trang 12 : blade la-so-bat-tu            (page-12-bg.png nền + tứ trụ bát tự)
     *   Trang 13 : blade la-so-dai-van           (page-13-bg.png nền + đại vận + niên vận)
     *   Trang 14 : blade la-so-chat-luong        (page-14-bg.png nền + radar + thập thần)
     *   Trang 15 : blade la-so-6-khia-canh       (page-15-bg.png nền + biểu đồ 6 khía cạnh)
     *   Trang 16 : page-16.png                  → full A4 (PHẦN 4 cover – LBTV-540)
     *   Trang 17 : blade la-so-tong-quan-nhat-chu (page-17-bg.png + Lý tổng quan + 1. Ý nghĩa trụ ngày)
     *   Trang 18 : blade la-so-phan-tich-nhat-chu  (page-18-bg.png + 2. Phân tích hình ảnh ẩn dụ)
     *   Trang 19 : blade la-so-xu-huong-tinh-cach  (page-19-bg.png + II. Xu hướng tính cách)
     *   Trang 20 : blade la-so-chapter-iii         (page-20-bg.png + III. mục 3 la mã)
     *   Trang 21 : blade la-so-chapter-iv          (page-21-bg.png + IV. mục 4 la mã)
     */
    public function exportLaSo2(Request $req)
    {
        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $finalPath = $tempDir.'/la-so-q2-'.Str::random(10).'.pdf';
        $this->buildQuyen2Pdf($req, $finalPath);

        return PdfDownloadService::download($finalPath, PdfDownloadService::FILENAME_QUYEN_2, false);
    }

    public function buildQuyen2Pdf(Request $req, string $finalPath): void
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');

        PdfExportMetrics::begin(2);

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $uid    = Str::random(10);
        $pdfDir = resource_path('views/pdfs/quyen-2');

        $mergeSegments = [];
        $tempFiles   = [];
        $tocTracker  = new PdfTocTracker();
        $headPaths   = [];
        $bodyPaths   = [];
        $headMergeSegmentCount = 0;

        // ── Trang 1: page-01.png (cache) ────────────────────────────────────
        $page1Path = PdfStaticPageCache::resolve($pdfDir . '/page-01.png');
        if ($page1Path === null) {
            throw new \RuntimeException('Không tìm thấy page-01.png');
        }
        self::tocPushMergeSegment($tocTracker, $headPaths, $mergeSegments, $page1Path);
        $headMergeSegmentCount = count($mergeSegments);

        // ── Trang 2: blade cuộn lịch ─────────────────────────────────────────
        $batTuRaw = $req->input('bat_tu', '');
        $batTu    = $batTuRaw
            ? preg_replace('/\b(Năm|Tháng|Ngày|Giờ)\s+(\S+)/u', '$1 <strong>$2</strong>', $batTuRaw)
            : '';

        $page2Path = $tempDir . '/p2-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-trang-2', [
            'templatePath' => $pdfDir . '/page-02-bg.png',
            'fullName'     => $req->input('full_name', ''),
            'gender'       => $req->input('gender', ''),
            'birthDate'    => $req->input('birth_date', ''),
            'batTu'        => $batTu,
            'address'      => $req->input('address', ''),
        ], $page2Path);
        self::tocPushMergeSegment($tocTracker, $headPaths, $mergeSegments, $page2Path);
        $tempFiles[]     = $page2Path;
        $headMergeSegmentCount = count($mergeSegments);

        // ── Trang 3–4 (trước mục lục) + 6–11 (sau mục lục): bundle tĩnh ─────
        $q2BeforeTocSources = [
            $pdfDir . '/page-03.pdf',
            $pdfDir . '/page-04.png',
        ];
        $q2AfterTocSources = [
            $pdfDir . '/page-06.png',
            $pdfDir . '/page-07.png',
            $pdfDir . '/page-08.png',
            $pdfDir . '/page-09.png',
            $pdfDir . '/page-10.png',
            $pdfDir . '/page-11.png',
        ];
        $beforeTocBundle = PdfStaticPageCache::resolveBundle('q2-pages-3-4-v1', $q2BeforeTocSources);
        if ($beforeTocBundle !== null) {
            self::tocPushMergeSegment($tocTracker, $headPaths, $mergeSegments, $beforeTocBundle);
        } else {
            Log::warning('PdfExport Q2: không tạo được bundle trang 3–4');
        }
        $headMergeSegmentCount = count($mergeSegments);

        $afterTocBundle = PdfStaticPageCache::resolveBundle('q2-pages-6-11-v1', $q2AfterTocSources);
        if ($afterTocBundle !== null) {
            // Trang 11 trong cuốn = trang bìa section (trang thứ 6 trong bundle 6–11)
            self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $afterTocBundle, [6]);
        } else {
            Log::warning('PdfExport Q2: không tạo được bundle trang 6–11');
        }

        // ── Tính Bát Tự nếu có đủ tham số y/m/d ─────────────────────────────
        $baziCore = $this->resolveBaziCore($req, 'PdfExport');
        extract($baziCore, EXTR_OVERWRITE);

        $nhatChuTitle    = '';
        $nhatChuChapters = [];

        if ($batTuData !== []) {
            $thienCanNgay = trim((string) ($batTuData['day']['can']['thien_can'] ?? ''));
            $diaChiNgay   = trim((string) ($batTuData['day']['chi']['dia_chi'] ?? ''));
            if ($diaChiNgay === 'Tí') {
                $diaChiNgay = 'Tý';
            }

            $truNgayRecords = NhatChuTruNgay::findByThienCanDiaChi($thienCanNgay, $diaChiNgay);
            $nhatChuTitle   = $truNgayRecords->first()?->title ?? '';

            $chaptersMap = [];
            foreach ($truNgayRecords as $r) {
                $key = $r->chapter ?? '';
                if (! isset($chaptersMap[$key])) {
                    $chaptersMap[$key] = ['chapter' => $key, 'sub_sections' => []];
                }
                $chaptersMap[$key]['sub_sections'][] = [
                    'sub_title' => $r->sub_title,
                    'content'   => $r->content,
                ];
            }
            $nhatChuChapters = array_values($chaptersMap);
        }

        // ── Trang 12: blade la-so-bat-tu ─────────────────────────────────────
        $page12Path = $tempDir . '/p12-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-bat-tu', [
            'templatePath'    => $pdfDir . '/page-12-bg.png',
            'batTu'           => $batTuData,
            'quyNhanVanXuong' => $quyNhanVanXuong,
        ], $page12Path);
        self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $page12Path);
        $tempFiles[]     = $page12Path;

        // ── Trang 13: la-so-dai-van (Đại Vận) ───────────────────────────────
        $page13Path = $tempDir . '/p13-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-dai-van', [
            'templatePath' => $pdfDir . '/page-13-bg.png',
            'bangDaiVan'   => $bangDaiVan,
        ], $page13Path);
        self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $page13Path);
        $tempFiles[]     = $page13Path;

        // ── Trang 13b: la-so-nien-van (Niên Vận) ────────────────────────────
        $page13bPath = $tempDir . '/p13b-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-nien-van', [
            'templatePath' => $pdfDir . '/page-13-bg.png',
            'nienVan'      => $nienVan,
        ], $page13bPath);
        self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $page13bPath);
        $tempFiles[]     = $page13bPath;

        // ── Trang 14: la-so-chat-luong ───────────────────────────────────────
        $page14Path   = $tempDir . '/p14-' . $uid . '.pdf';
        $nienMenhYear = !empty($nienVan[1]['nam']) ? $nienVan[1]['nam'] : (int) date('Y');
        PdfRenderService::saveView('pdfs.quyen-2.la-so-chat-luong', [
            'templatePath'      => $pdfDir . '/page-14-bg.png',
            'iconDir'           => $pdfDir . '/chat-luong-ngu-hanh',
            'bieuDoNguHanh'     => $bieuDoNguHanh,
            'chatLuongThapThan' => $chatLuongThapThan,
            'nienVanYear'       => $nienMenhYear,
        ], $page14Path);
        self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $page14Path);
        $tempFiles[]     = $page14Path;

        // ── Trang 15: la-so-6-khia-canh ─────────────────────────────────────
        $page15Path = $tempDir . '/p15-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-6-khia-canh', [
            'templatePath'   => $pdfDir . '/page-15-bg.png',
            'chiSoBieuDoCot' => $chiSoBieuDoCot,
            'gender'         => $req->input('g', 'male'),
        ], $page15Path);
        self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $page15Path);
        $tempFiles[]     = $page15Path;

        $labelPhan4I = trim((string) ($nhatChuChapters[0]['chapter'] ?? 'I.'));
        $labelPhan4II = trim((string) ($nhatChuChapters[1]['chapter'] ?? 'II.'));
        $labelPhan4III = trim((string) ($nhatChuChapters[2]['chapter'] ?? 'III.'));
        $labelPhan4IV = trim((string) ($nhatChuChapters[3]['chapter'] ?? 'IV.'));

        // ── Trang 16: page-16.png (PHẦN 4 cover, cache) ─────────────────────
        $page16Cover = PdfStaticPageCache::resolve($pdfDir . '/page-16.png');
        if ($page16Cover !== null) {
            self::tocPushMergeSegment(
                $tocTracker,
                $bodyPaths,
                $mergeSegments,
                $page16Cover,
                'all',
                'phan4',
                'PHẦN 4: NHẬT CHỦ TRỤ NGÀY'
            );
        } else {
            Log::warning('PdfExport Q2: không tìm thấy page-16.png');
        }

        // ── Trang 17: la-so-tong-quan-nhat-chu (phân trang) ─────────────────
        self::appendNhatChuChapterPdf(
            $mergeSegments,
            $tempFiles,
            $tempDir,
            $uid,
            NhatChuChapterPdfService::buildTongQuanPages($nhatChuChapters, $pdfDir),
            'p17',
            'pdfs.quyen-2.la-so-tong-quan-nhat-chu',
            [
                'templatePath' => $pdfDir.'/page-17-bg.png',
                'nhatChuTitle' => $nhatChuTitle,
                'chapters'     => $nhatChuChapters,
            ],
            'pdfs.quyen-2.la-so-tong-quan-nhat-chu-paginated',
            [
                'templatePath' => $pdfDir.'/page-17-bg.png',
                'nhatChuTitle' => $nhatChuTitle,
            ],
            $tocTracker,
            $bodyPaths,
            'phan4.i',
            $labelPhan4I
        );

        // ── Trang 18: la-so-phan-tich-nhat-chu ──────────────────────────────
        $page18Path = $tempDir . '/p18-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-phan-tich-nhat-chu', [
            'templatePath' => $pdfDir . '/page-18-bg.png',
            'chapters'     => $nhatChuChapters,
        ], $page18Path);
        self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $page18Path);
        $tempFiles[]     = $page18Path;

        // ── Trang 19–21: NHẬT CHỦ chapter II–IV (phân trang, chừa footer) ───
        self::appendNhatChuChapterPdf(
            $mergeSegments,
            $tempFiles,
            $tempDir,
            $uid,
            NhatChuChapterPdfService::buildXuHuongPages($nhatChuChapters, $pdfDir),
            'p19',
            'pdfs.quyen-2.la-so-xu-huong-tinh-cach',
            ['templatePath' => $pdfDir.'/page-19-bg.png', 'chapters' => $nhatChuChapters],
            null,
            [],
            $tocTracker,
            $bodyPaths,
            'phan4.ii',
            $labelPhan4II
        );

        self::appendNhatChuChapterPdf(
            $mergeSegments,
            $tempFiles,
            $tempDir,
            $uid,
            NhatChuChapterPdfService::buildChapterIiiPages($nhatChuChapters, $pdfDir),
            'p20',
            'pdfs.quyen-2.la-so-chapter-iii',
            ['templatePath' => $pdfDir.'/page-20-bg.png', 'chapters' => $nhatChuChapters],
            null,
            [],
            $tocTracker,
            $bodyPaths,
            'phan4.iii',
            $labelPhan4III
        );

        self::appendNhatChuChapterPdf(
            $mergeSegments,
            $tempFiles,
            $tempDir,
            $uid,
            NhatChuChapterPdfService::buildChapterIvPages($nhatChuChapters, $pdfDir),
            'p21',
            'pdfs.quyen-2.la-so-chapter-iv',
            ['templatePath' => $pdfDir.'/page-21-bg.png', 'chapters' => $nhatChuChapters],
            null,
            [],
            $tocTracker,
            $bodyPaths,
            'phan4.iv',
            $labelPhan4IV
        );

        // ── PHẦN 7: bìa + trang mở đầu Mục I (tĩnh) ─────────────────────────────
        $phan7Pages = Phan7PdfService::staticPagePaths();
        $phan7Bundle = PdfStaticPageCache::resolveBundle(Phan7PdfService::bundleCacheKey(), $phan7Pages);
        if ($phan7Bundle !== null) {
            self::tocPushMergeSegment(
                $tocTracker,
                $bodyPaths,
                $mergeSegments,
                $phan7Bundle,
                'all',
                'phan7',
                'PHẦN 7: BÀI HỌC CUỘC SỐNG'
            );
        } else {
            foreach ($phan7Pages as $phan7Path) {
                $resolved = PdfStaticPageCache::resolve($phan7Path);
                if ($resolved !== null) {
                    self::tocPushMergeSegment(
                        $tocTracker,
                        $bodyPaths,
                        $mergeSegments,
                        $resolved,
                        'all',
                        'phan7',
                        'PHẦN 7: BÀI HỌC CUỘC SỐNG'
                    );
                }
            }
        }

        // ── PHẦN 7 MỤC I: nội dung từ phan7_tam_the (sheet 0) ───────────────────
        $phan7Muc1Spec = Phan7MucIPdfService::buildContentPageSpec(0);
        if ($phan7Muc1Spec !== null) {
            $pagePhan7Muc1Path = $tempDir . '/q2p-phan7-muc1-' . $uid . '.pdf';
            PdfRenderService::saveView($phan7Muc1Spec['view'], $phan7Muc1Spec['data'], $pagePhan7Muc1Path);
            self::tocPushMergeSegment(
                $tocTracker,
                $bodyPaths,
                $mergeSegments,
                $pagePhan7Muc1Path,
                null,
                'phan7.i',
                'I. TAM THẾ'
            );
            $tempFiles[]   = $pagePhan7Muc1Path;
        }

        // ── PHẦN 7 MỤC II: nội dung động theo % Thập Thần ───────────────────────
        $phan7Muc2Spec = Phan7MucIIPdfService::buildContentPageSpec($req);
        if ($phan7Muc2Spec !== null) {
            $pagePhan7Muc2Path = $tempDir . '/q2p-phan7-muc2-' . $uid . '.pdf';
            PdfRenderService::saveView($phan7Muc2Spec['view'], $phan7Muc2Spec['data'], $pagePhan7Muc2Path);
            self::tocPushMergeSegment(
                $tocTracker,
                $bodyPaths,
                $mergeSegments,
                $pagePhan7Muc2Path,
                null,
                'phan7.ii',
                'II. BÀI HỌC CUỘC SỐNG'
            );
            $tempFiles[]   = $pagePhan7Muc2Path;
        }

        // ── PHẦN 7: đoạn nối cuối (phan7_tam_the sheet 1) ───────────────────────
        $phan7Muc1CuoiSpec = Phan7MucIPdfService::buildContentPageSpec(1);
        if ($phan7Muc1CuoiSpec !== null) {
            $pagePhan7Muc1CuoiPath = $tempDir . '/q2p-phan7-muc1-cuoi-' . $uid . '.pdf';
            PdfRenderService::saveView($phan7Muc1CuoiSpec['view'], $phan7Muc1CuoiSpec['data'], $pagePhan7Muc1CuoiPath);
            self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $pagePhan7Muc1CuoiPath);
            $tempFiles[]   = $pagePhan7Muc1CuoiPath;
        }

        // ── PHẦN 8 (8B): Niên Vận tiếp theo + III. Dự báo khía cạnh ─────────
        foreach (Phan8PdfService::buildPdfPages($req, '8b') as $idx => $phan8Page) {
            $pagePhan8Path = $tempDir . '/q2p-phan8-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan8Page['view'], $phan8Page['data'], $pagePhan8Path);
            $isCover = ($phan8Page['view'] ?? '') === 'pdfs.phan-8.la-so-phan-8-nien-van-cover';
            $phan8Start = $tocTracker->physicalPage() + 1;
            if ($idx === 0) {
                self::tocPushMergeSegment(
                    $tocTracker,
                    $bodyPaths,
                    $mergeSegments,
                    $pagePhan8Path,
                    $isCover ? 'all' : null,
                    'phan8',
                    'PHẦN 8: DỰ BÁO HẠN VẬN'
                );
            } else {
                self::tocPushMergeSegment(
                    $tocTracker,
                    $bodyPaths,
                    $mergeSegments,
                    $pagePhan8Path,
                    $isCover ? 'all' : null
                );
            }
            if (($phan8Page['view'] ?? '') === 'pdfs.phan-8.la-so-phan-8-content') {
                self::tocMarkChapters($tocTracker, $phan8Page, $phan8Start, [
                    'II.' => 'phan8.ii',
                    'III.' => 'phan8.iii',
                ]);
            }
            $tempFiles[]   = $pagePhan8Path;
        }

        $phan9bSpecs = Phan9bPdfService::buildPdfPages(
            $req,
            $nguHanhDong,
            $chatLuongThapThan,
            $batTuData,
            is_array($baziData['luc_than'] ?? null) ? $baziData['luc_than'] : null
        );
        $hasPhan9b = $phan9bSpecs !== [];
        if (! $hasPhan9b) {
            Log::warning('PdfExport Q2: Phần 9B không có trang nội dung (chỉ bìa)', [
                'has_bat_tu' => $batTuData !== [],
                'ngu_hanh_dong_empty' => $nguHanhDong === [],
            ]);
        }

        // ── PHẦN 9B: bìa + cuộn thư + nội dung (Cuốn 2) ─────────────────────
        $phan9Bia = PdfStaticPageCache::resolve(Phan9bPdfService::coverImagePath());
        if ($phan9Bia !== null) {
            self::tocPushMergeSegment(
                $tocTracker,
                $bodyPaths,
                $mergeSegments,
                $phan9Bia,
                'all',
                $hasPhan9b ? 'phan9b' : null,
                $hasPhan9b ? 'PHẦN 9B: GIẢI PHÁP CÂN BẰNG' : null
            );
        } else {
            Log::warning('PdfExport Q2: không tìm thấy bìa Phần 9B');
        }

        foreach ($phan9bSpecs as $idx => $phan9Page) {
            $pagePhan9Path = $tempDir . '/q2p-phan9b-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan9Page['view'], $phan9Page['data'], $pagePhan9Path);
            $phan9Start = $tocTracker->physicalPage() + 1;
            self::tocPushMergeSegment($tocTracker, $bodyPaths, $mergeSegments, $pagePhan9Path);
            self::tocMarkChapters($tocTracker, $phan9Page, $phan9Start, [
                'I.' => 'phan9b.i',
                'II.' => 'phan9b.ii',
                'III.' => 'phan9b.iii',
                'IV.' => 'phan9b.iv',
            ]);
            $tempFiles[]   = $pagePhan9Path;
        }

        // ── Phụ lục Q2: bìa 562–564 (tên trắng) + worksheet 572, 581 (tên đen; trang cuối PDF trắng) ─
        $q2AppendixDir = resource_path('views/pdfs/q2-appendix');
        self::appendQ2AppendixSegments($mergeSegments, $q2AppendixDir, $tocTracker, $bodyPaths);

        $tocPath = $tempDir . '/q2-toc-' . $uid . '.pdf';
        PdfTocRenderer::renderQuyen2($tocTracker, $req, [
            'nhat_chu_chapters' => $nhatChuChapters,
            'has_phan9b'        => $hasPhan9b,
            'nien_van_year'     => $nienMenhYear,
        ], $tocPath);
        $tempFiles[] = $tocPath;

        array_splice($mergeSegments, $headMergeSegmentCount, 0, [['path' => $tocPath]]);
        $pdfsToMerge = array_merge($headPaths, [$tocPath], $bodyPaths);
        $whiteNamePages = PdfFooterService::resolveCoverNamePagesFromFullMerge($pdfsToMerge, $mergeSegments);
        $darkNamePages = PdfFooterService::resolveDarkNamePagesFromFullMerge($pdfsToMerge, $mergeSegments);

        // ── Merge + footer (banner 08.png, số trang, tên người nhập) ─────────
        $mergedTemp = $tempDir.'/merged-q2-'.$uid.'.pdf';
        $tMerge     = microtime(true);
        $merged     = PdfMergeService::mergeMultiple($pdfsToMerge, $mergedTemp);
        $mergeMs    = (microtime(true) - $tMerge) * 1000;

        foreach ($tempFiles as $tmp) {
            @unlink($tmp);
        }

        if (! $merged || ! file_exists($mergedTemp)) {
            throw new \RuntimeException('Merge PDF Quyển 2 thất bại');
        }

        $fullName = trim((string) $req->input('full_name', ''));
        $tFooter  = microtime(true);
        if (! PdfFooterService::applyToMergedPdf(
            $mergedTemp,
            $finalPath,
            $fullName,
            PdfFooterService::FIRST_FOOTER_PAGE,
            PdfFooterService::FIRST_DISPLAY_PAGE_NUMBER,
            $whiteNamePages,
            $darkNamePages
        )) {
            Log::warning('Gắn footer PDF Quyển 2 thất bại — xuất PDF không footer', [
                'uid' => $uid,
                'full_name' => $fullName,
            ]);
            @copy($mergedTemp, $finalPath);
        }
        $footerMs = (microtime(true) - $tFooter) * 1000;
        @unlink($mergedTemp);

        if (! file_exists($finalPath)) {
            throw new \RuntimeException('Gắn footer PDF Quyển 2 thất bại');
        }

        PdfExportMetrics::logFinish([
            'uid'           => $uid,
            'merge_ms'      => round($mergeMs, 1),
            'footer_ms'     => round($footerMs, 1),
            'merge_driver'  => PdfMergeService::lastMergeDriver(),
            'segments'      => count($pdfsToMerge),
        ]);
    }

    /**
     * @param  array<int, array{path: string, coverPages?: 'all'|array<int, int>}>  $segments
     */
    /**
     * Render NHẬT CHỦ chapter đã phân trang; fallback blade 1 trang nếu không có dữ liệu.
     *
     * @param  array<int, array<string, mixed>>  $segments
     * @param  array<int, string>  $tempFiles
     * @param  array<int, array<string, mixed>>  $pages
     * @param  array<string, mixed>  $fallbackData
     */
    private static function appendNhatChuChapterPdf(
        array &$segments,
        array &$tempFiles,
        string $tempDir,
        string $uid,
        array $pages,
        string $filePrefix,
        string $fallbackView,
        array $fallbackData,
        ?string $paginatedView = null,
        array $paginatedExtra = [],
        ?PdfTocTracker $tocTracker = null,
        ?array &$pathList = null,
        ?string $bookmarkKey = null,
        ?string $bookmarkLabel = null,
        $coverPages = null
    ): void {
        $path = $tempDir.'/'.$filePrefix.'-'.$uid.'.pdf';

        if ($pages !== []) {
            $view = $paginatedView ?? 'pdfs.quyen-1.la-so-bocuc-ngu-hanh';
            PdfRenderService::saveView($view, array_merge(['pages' => $pages], $paginatedExtra), $path);
        } else {
            PdfRenderService::saveView($fallbackView, $fallbackData, $path);
        }

        if ($tocTracker !== null && $pathList !== null) {
            self::tocPushMergeSegment(
                $tocTracker,
                $pathList,
                $segments,
                $path,
                $coverPages,
                $bookmarkKey,
                $bookmarkLabel
            );
        } else {
            self::pushMergeSegment($segments, $path, $coverPages);
        }
        $tempFiles[] = $path;
    }

    private static function pushMergeSegment(array &$segments, string $path, $coverPages = null): void
    {
        if ($path === '' || ! is_file($path)) {
            return;
        }

        $entry = ['path' => $path];
        if ($coverPages === 'darkName') {
            $entry['darkNamePages'] = 'all';
        } elseif ($coverPages !== null) {
            $entry['coverPages'] = $coverPages;
        }

        $segments[] = $entry;
    }

    /**
     * Kết bài Phần 9 — bìa tối (562–564) + worksheet nền sáng (566–569, 572, 581).
     *
     * @param  array<int, string>  $pdfsToMerge
     * @param  array<int, array{path: string, coverPages?: mixed, darkNamePages?: mixed}>  $mergeSegments
     */
    private static function appendPhan9KetBaiSegments(
        array &$pdfsToMerge,
        array &$mergeSegments,
        string $phan9Dir,
        string $appendixDir,
        ?PdfTocTracker $tocTracker = null
    ): void {
        $coverSources = [
            $appendixDir . '/page-562.png',
            $appendixDir . '/page-563.png',
            $appendixDir . '/page-564.png',
        ];

        $worksheetSources = [
            $phan9Dir . '/page-566.png',
            $phan9Dir . '/page-567.png',
            $phan9Dir . '/page-568.png',
            $phan9Dir . '/page-569.png',
            $appendixDir . '/page-572.png',
            $phan9Dir . '/page-581.png',
        ];

        $coverBundle = PdfStaticPageCache::resolveBundle('q1-ket-bai-covers-v1', $coverSources);
        if ($coverBundle !== null) {
            if ($tocTracker !== null) {
                self::tocPush($tocTracker, $pdfsToMerge, $coverBundle);
            } else {
                $pdfsToMerge[] = $coverBundle;
            }
            self::pushMergeSegment($mergeSegments, $coverBundle, 'darkName');
        } else {
            foreach ($coverSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    if ($tocTracker !== null) {
                        self::tocPush($tocTracker, $pdfsToMerge, $resolved);
                    } else {
                        $pdfsToMerge[] = $resolved;
                    }
                    self::pushMergeSegment($mergeSegments, $resolved, 'darkName');
                }
            }
        }

        $worksheetBundle = PdfStaticPageCache::resolveBundle('q1-ket-bai-worksheets-v1', $worksheetSources);
        if ($worksheetBundle !== null) {
            if ($tocTracker !== null) {
                self::tocPush($tocTracker, $pdfsToMerge, $worksheetBundle);
            } else {
                $pdfsToMerge[] = $worksheetBundle;
            }
            self::pushMergeSegment($mergeSegments, $worksheetBundle, 'darkName');
        } else {
            foreach ($worksheetSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    if ($tocTracker !== null) {
                        self::tocPush($tocTracker, $pdfsToMerge, $resolved);
                    } else {
                        $pdfsToMerge[] = $resolved;
                    }
                    self::pushMergeSegment($mergeSegments, $resolved, 'darkName');
                }
            }
        }
    }

    /**
     * Phụ lục cuối Cuốn 2 — bìa tối (562–564, tên trắng) + worksheet nền sáng (572, 581, tên đen).
     *
     * @param  array<int, array{path: string, coverPages?: mixed, darkNamePages?: mixed}>  $mergeSegments
     */
    private static function appendQ2AppendixSegments(
        array &$mergeSegments,
        string $appendixDir,
        ?PdfTocTracker $tocTracker = null,
        ?array &$pathList = null
    ): void {
        $coverSources = [
            $appendixDir . '/page-562.png',
            $appendixDir . '/page-563.png',
            $appendixDir . '/page-564.png',
        ];

        $worksheetSources = [
            $appendixDir . '/page-572.png',
            $appendixDir . '/page-581.png',
        ];

        $coverBundle = PdfStaticPageCache::resolveBundle('q2-appendix-covers-v1', $coverSources);
        if ($coverBundle !== null) {
            if ($tocTracker !== null && $pathList !== null) {
                self::tocPushMergeSegment($tocTracker, $pathList, $mergeSegments, $coverBundle, 'all');
            } else {
                self::pushMergeSegment($mergeSegments, $coverBundle, 'all');
            }
        } else {
            foreach ($coverSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    if ($tocTracker !== null && $pathList !== null) {
                        self::tocPushMergeSegment($tocTracker, $pathList, $mergeSegments, $resolved, 'all');
                    } else {
                        self::pushMergeSegment($mergeSegments, $resolved, 'all');
                    }
                }
            }
        }

        $worksheetBundle = PdfStaticPageCache::resolveBundle('q2-appendix-worksheets-v1', $worksheetSources);
        if ($worksheetBundle !== null) {
            if ($tocTracker !== null && $pathList !== null) {
                self::tocPushMergeSegment($tocTracker, $pathList, $mergeSegments, $worksheetBundle, 'darkName');
            } else {
                self::pushMergeSegment($mergeSegments, $worksheetBundle, 'darkName');
            }
        } else {
            foreach ($worksheetSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    if ($tocTracker !== null && $pathList !== null) {
                        self::tocPushMergeSegment($tocTracker, $pathList, $mergeSegments, $resolved, 'darkName');
                    } else {
                        self::pushMergeSegment($mergeSegments, $resolved, 'darkName');
                    }
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $list
     */
    private static function tocPush(PdfTocTracker $tracker, array &$list, string $path): void
    {
        if ($path === '' || ! is_file($path)) {
            return;
        }

        $list[] = $path;
        $tracker->addSegment($path);
    }

    /**
     * @param  array<int, string>  $list
     */
    private static function tocAppendStatic(
        PdfTocTracker $tracker,
        array &$list,
        string $path,
        string $label = ''
    ): void {
        $resolved = PdfStaticPageCache::resolve($path);
        if ($resolved !== null) {
            self::tocPush($tracker, $list, $resolved);

            return;
        }

        if ($label !== '') {
            Log::warning("PdfExport: $label không tồn tại hoặc không convert được");
        }
    }

    /**
     * @param  array<int, string>  $pathList
     * @param  array<int, array{path: string, coverPages?: mixed, darkNamePages?: mixed}>  $mergeSegments
     */
    private static function tocPushMergeSegment(
        PdfTocTracker $tracker,
        array &$pathList,
        array &$mergeSegments,
        string $path,
        $coverPages = null,
        ?string $bookmarkKey = null,
        ?string $bookmarkLabel = null
    ): void {
        if ($bookmarkKey !== null && $bookmarkLabel !== null && $bookmarkLabel !== '') {
            $tracker->mark($bookmarkKey, $bookmarkLabel);
        }

        self::tocPush($tracker, $pathList, $path);
        self::pushMergeSegment($mergeSegments, $path, $coverPages);
    }

    /**
     * @param  array{view?: string, data?: array<string, mixed>}  $spec
     * @param  array<string, string>  $prefixMap
     */
    private static function tocMarkChapters(
        PdfTocTracker $tracker,
        array $spec,
        int $segmentStartPhysical,
        array $prefixMap
    ): void {
        $tracker->markChaptersFromSpec($spec, $segmentStartPhysical, $prefixMap);
    }

    private static function phan5TocBookmarkKey(string $slug): string
    {
        return match ($slug) {
            'su_nghiep' => 'phan5.ii',
            'tai_chinh' => 'phan5.iii',
            'tinh_duyen' => 'phan5.iv',
            'phat_trien_ban_than' => 'phan5.vi',
            'ket_noi_xa_hoi' => 'phan5.vii',
            default => 'phan5.'.$slug,
        };
    }

    /**
     * Thêm trang PDF tĩnh (PDF gốc hoặc PNG→PDF đã cache).
     *
     * @param  array<int, string>  $list
     */
    private static function appendStaticPage(array &$list, string $path, string $label = ''): void
    {
        $resolved = PdfStaticPageCache::resolve($path);
        if ($resolved !== null) {
            $list[] = $resolved;

            return;
        }

        if ($label !== '') {
            Log::warning("PdfExport: $label không tồn tại hoặc không convert được");
        }
    }

    /**
     * Trích mục I (la mã) và các mục con 1–2 từ PHẦN 3 - Tổng quan ngũ hành.
     */
    private static function parsePhan3SectionI(?DinhViGocNhin $item): array
    {
        if (!$item) {
            return [
                'chapterTitle' => '',
                'intro'        => [],
                'subSections'  => [],
            ];
        }

        $chapterTitle = trim((string) $item->title);
        $content      = self::stripPhan3LeadingChapterLine(trim((string) $item->content));

        // Chỉ lấy phần mục I (trước II. nếu có)
        if (preg_match('/^(.*?)(?=\nII\.\s)/s', $content, $m)) {
            $content = trim($m[1]);
        }

        $intro       = '';
        $numberedRaw = $content;

        if (preg_match('/^(.*?)(^1\.\s)/ms', $content, $m)) {
            $intro       = trim($m[1]);
            $numberedRaw = trim(substr($content, strlen($m[1])));
        }

        $subSections = [];
        if ($numberedRaw !== '') {
            $blocks = preg_split('/(?=^\d+\.\s)/m', $numberedRaw);
            foreach ($blocks as $block) {
                $block = trim($block);
                if ($block === '' || ! preg_match('/^(\d+)\.\s/', $block, $numMatch)) {
                    continue;
                }
                if ((int) $numMatch[1] > 1) {
                    continue;
                }
                if (preg_match('/^(\d+\.\s[^\n]+)\n?(.*)$/s', $block, $sm)) {
                    $subSections[] = [
                        'sub_title' => trim($sm[1]),
                        'content'   => self::splitPhan3Blocks(trim($sm[2])),
                    ];
                }
            }
        }

        return [
            'chapterTitle' => $chapterTitle,
            'intro'        => self::splitParagraphs($intro),
            'subSections'  => $subSections,
        ];
    }

    private static function stripPhan3LeadingChapterLine(string $content): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        if (! empty($lines) && preg_match('/^I\.\s/iu', trim($lines[0]))) {
            array_shift($lines);
            while (! empty($lines) && trim($lines[0]) === '') {
                array_shift($lines);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Trích các mục con từ mục N trở đi (mặc định mục 2) trong PHẦN 3 - mục I.
     */
    private static function parsePhan3FromSub2(?DinhViGocNhin $item, int $fromSub = 2): array
    {
        if (!$item) {
            return ['subSections' => []];
        }

        $content = self::stripPhan3LeadingChapterLine(trim((string) $item->content));

        if (!preg_match('/\n' . $fromSub . '\.\s/s', "\n" . $content)) {
            return ['subSections' => []];
        }

        if (preg_match('/\n' . $fromSub . '\.\s(.*)$/s', "\n" . $content, $m)) {
            $content = trim($fromSub . '. ' . $m[1]);
        }

        $blocks = preg_split('/(?=^\d+\.\s)/m', $content);
        $subSections = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            if (!preg_match('/^(\d+)\.\s/', $block, $numMatch)) {
                continue;
            }

            if ((int) $numMatch[1] < $fromSub) {
                continue;
            }

            if (preg_match('/^(\d+\.\s[^\n]+)\n?(.*)$/s', $block, $sm)) {
                $subSections[] = [
                    'sub_title' => trim($sm[1]),
                    'content'   => self::splitPhan3Blocks(trim($sm[2])),
                ];
            }
        }

        return ['subSections' => $subSections];
    }

    /**
     * Trích toàn bộ mục la mã (II., III., …) kèm tất cả mục con đánh số.
     */
    private static function parsePhan3SectionFull(?DinhViGocNhin $item): array
    {
        if (!$item) {
            return [
                'chapterTitle' => '',
                'subSections'  => [],
            ];
        }

        $chapterTitle = trim((string) $item->title);
        $content      = trim((string) $item->content);

        $lines = preg_split('/\r\n|\r|\n/', $content);
        if (!empty($lines) && preg_match('/^(I|II|III|IV|V|VI|VII|VIII|IX|X)\.\s/iu', trim($lines[0]))) {
            array_shift($lines);
            while (!empty($lines) && trim($lines[0]) === '') {
                array_shift($lines);
            }
        }
        $content = implode("\n", $lines);

        $blocks      = preg_split('/(?=^\d+\.\s)/m', $content);
        $subSections = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            if (!preg_match('/^(\d+\.\s[^\n]+)\n?(.*)$/s', $block, $sm)) {
                continue;
            }

            $subSections[] = [
                'sub_title' => trim($sm[1]),
                'content'   => self::splitPhan3Blocks(trim($sm[2])),
            ];
        }

        return [
            'chapterTitle' => $chapterTitle,
            'subSections'  => $subSections,
        ];
    }

    /**
     * @return array<int, array{type: string, text?: string, path?: string}>
     */
    private static function splitPhan3Blocks(string $text): array
    {
        $blocks = [];

        foreach (self::splitParagraphs($text) as $para) {
            if (preg_match('/^\[\[image:(.+)\]\]$/', $para, $m)) {
                $path = \App\Services\DocxTextService::resolveImagePath($m[1]);
                if ($path !== '') {
                    $blocks[] = ['type' => 'image', 'path' => $path];
                }

                continue;
            }

            $blocks[] = ['type' => 'para', 'text' => $para];
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
        if (empty($paragraphs)) {
            $paragraphs = array_filter(array_map('trim', explode("\n", $text)));
        }

        return array_values($paragraphs);
    }

    /**
     * @return array{
     *   baziData: ?array<string, mixed>,
     *   batTuData: array<string, mixed>,
     *   quyNhanVanXuong: array<int, mixed>,
     *   bangDaiVan: array<int, mixed>,
     *   nienVan: array<int, mixed>,
     *   bieuDoNguHanh: array<int|string, mixed>,
     *   chatLuongThapThan: array<int, mixed>,
     *   chiSoBieuDoCot: array<string, mixed>,
     *   nguHanhDong: array<string, mixed>
     * }
     */
    private function resolveBaziCore(Request $req, string $logPrefix): array
    {
        $empty = [
            'baziData'            => null,
            'batTuData'           => [],
            'quyNhanVanXuong'     => [],
            'bangDaiVan'          => [],
            'nienVan'             => [],
            'bieuDoNguHanh'       => [],
            'chatLuongThapThan'   => [],
            'chiSoBieuDoCot'      => [],
            'nguHanhDong'         => [],
        ];

        $birthY = $req->input('y');
        $birthM = $req->input('m');
        $birthD = $req->input('d');

        if (! $birthY || ! $birthM || ! $birthD) {
            return $empty;
        }

        return PdfBaziCache::remember($req, function () use ($req, $logPrefix, $empty): array {
            $result = $empty;
            $baziT0 = microtime(true);

            try {
                $baziData = BaZiServiceV2::calc(
                    $req->input('full_name', ''),
                    (int) $req->input('y'),
                    (int) $req->input('m'),
                    (int) $req->input('d'),
                    $req->filled('h') ? (int) $req->input('h') : null,
                    $req->filled('minute') ? (int) $req->input('minute') : null,
                    $req->input('g', 'male')
                );

                $result['baziData']          = $baziData;
                $result['batTuData']           = $baziData['bat_tu'] ?? [];
                $result['quyNhanVanXuong']     = $baziData['quy_nhan_van_xuong'] ?? [];
                $result['bangDaiVan']          = $baziData['bang_dai_van'] ?? [];
                $result['nienVan']             = $baziData['nien_van'] ?? [];
                $result['bieuDoNguHanh']       = $baziData['bieu_do_ngu_hanh'] ?? [];
                $result['chatLuongThapThan']   = $baziData['chat_luong_thap_than'] ?? [];
                $result['chiSoBieuDoCot']      = $baziData['chi_so_bieu_do_cot'] ?? [];
                $result['nguHanhDong']         = $baziData['ngu_hanh_dong'] ?? [];
            } catch (\Throwable $e) {
                Log::error($logPrefix.': BaZiServiceV2::calc lỗi – '.$e->getMessage());
            }

            $this->applyPrecomputedStrengthData(
                $req,
                $result['batTuData'],
                $result['bieuDoNguHanh'],
                $result['chatLuongThapThan'],
                $result['chiSoBieuDoCot'],
                $result['nguHanhDong']
            );

            PdfExportMetrics::addBaziMs((microtime(true) - $baziT0) * 1000);

            return $result;
        });
    }

    /**
     * Ưu tiên dữ liệu chất lượng đã tính trên web (payload queue PDF) khi job nền
     * không crawl được Joey Yap hoặc cache calc thiếu strength.
     *
     * @param  array<string, mixed>  $batTuData
     * @param  array<int|string, mixed>  $bieuDoNguHanh
     * @param  array<int, mixed>  $chatLuongThapThan
     * @param  array<string, mixed>  $chiSoBieuDoCot
     * @param  array<string, mixed>  $nguHanhDong
     */
    private function applyPrecomputedStrengthData(
        Request $req,
        array $batTuData,
        array &$bieuDoNguHanh,
        array &$chatLuongThapThan,
        array &$chiSoBieuDoCot,
        array &$nguHanhDong
    ): void {
        $fromPayload = static fn (string $key): array => self::normalizeStrengthPayload($req->input($key));

        $payloadChatLuong = $fromPayload('chat_luong_thap_than');
        if ($payloadChatLuong !== [] && ! self::isStrengthDataEmpty($payloadChatLuong)) {
            $chatLuongThapThan = $payloadChatLuong;
        }

        $payloadNguHanh = $fromPayload('ngu_hanh_dong');
        if ($payloadNguHanh !== [] && ! self::isStrengthDataEmpty($payloadNguHanh)) {
            $nguHanhDong = $payloadNguHanh;
        } elseif (self::isStrengthDataEmpty($nguHanhDong)) {
            $rebuilt = self::nguHanhDongFromHanhNoiDungPayload($fromPayload('hanh_noi_dung_nien_van'));
            if ($rebuilt !== []) {
                $nguHanhDong = $rebuilt;
            }
        }

        $payloadBieuDo = $fromPayload('bieu_do_ngu_hanh');
        if ($payloadBieuDo !== [] && ! self::isStrengthDataEmpty($payloadBieuDo)) {
            $bieuDoNguHanh = $payloadBieuDo;
        }

        $payloadChiSo = $fromPayload('chi_so_bieu_do_cot');
        if ($payloadChiSo !== [] && self::chiSoBieuDoCotHasData($payloadChiSo)) {
            $chiSoBieuDoCot = $payloadChiSo;
        }

        if (
            ! self::chiSoBieuDoCotHasData($chiSoBieuDoCot)
            && ! self::isStrengthDataEmpty($chatLuongThapThan)
            && ! self::isStrengthDataEmpty($nguHanhDong)
            && $batTuData !== []
        ) {
            $chiSoBieuDoCot = BaZiServiceV2::computeChiSoBieuDoCot(
                $batTuData,
                $chatLuongThapThan,
                $nguHanhDong
            );
        }
    }

    /**
     * @param  mixed  $value
     * @return array<int|string, mixed>
     */
    private static function normalizeStrengthPayload(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_filter($value, static fn ($item) => $item !== null);
    }

    /**
     * @param  array<int, mixed>  $hanhNoiDung
     * @return array<string, int>
     */
    private static function nguHanhDongFromHanhNoiDungPayload(array $hanhNoiDung): array
    {
        $out = [];

        foreach ($hanhNoiDung as $item) {
            if (! is_array($item)) {
                continue;
            }

            $slug = trim((string) ($item['hanh_slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $out[$slug] = (int) ($item['percent'] ?? 0);
        }

        return self::isStrengthDataEmpty($out) ? [] : $out;
    }

    /**
     * @param  array<int|string, mixed>  $data
     */
    private static function isStrengthDataEmpty(array $data): bool
    {
        if ($data === []) {
            return true;
        }

        foreach ($data as $item) {
            if (is_array($item)) {
                if ((int) ($item['natal'] ?? 0) > 0 || (int) ($item['annual'] ?? 0) > 0) {
                    return false;
                }
                if (array_filter($item, static fn ($v) => is_numeric($v) && (float) $v > 0) !== []) {
                    return false;
                }
            } elseif (is_numeric($item) && (float) $item > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $chiSo
     */
    private static function chiSoBieuDoCotHasData(array $chiSo): bool
    {
        if ($chiSo === []) {
            return false;
        }

        foreach (['natal', 'annual'] as $scope) {
            $bucket = $chiSo[$scope] ?? null;
            if (! is_array($bucket)) {
                continue;
            }

            foreach ($bucket as $value) {
                if (is_numeric($value) && (float) $value > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
