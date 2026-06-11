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
     *   Trang  5 : page-05.png          (LBTV-146 – mục lục)
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
        $baziData            = null;
        $batTuData           = [];
        $quyNhanVanXuong     = [];
        $bangDaiVan          = [];
        $nienVan             = [];
        $bieuDoNguHanh       = [];
        $chatLuongThapThan   = [];
        $chiSoBieuDoCot      = [];
        $nguHanhDong         = [];

        $birthY = $req->input('y');
        $birthM = $req->input('m');
        $birthD = $req->input('d');

        if ($birthY && $birthM && $birthD) {
            $baziT0 = microtime(true);
            try {
                $baziData = BaZiServiceV2::calc(
                    $req->input('full_name', ''),
                    (int) $birthY,
                    (int) $birthM,
                    (int) $birthD,
                    $req->filled('h')      ? (int) $req->input('h')      : null,
                    $req->filled('minute') ? (int) $req->input('minute') : null,
                    $req->input('g', 'male')
                );
                $batTuData         = $baziData['bat_tu']              ?? [];
                $quyNhanVanXuong   = $baziData['quy_nhan_van_xuong']  ?? [];
                $bangDaiVan        = $baziData['bang_dai_van']         ?? [];
                $nienVan           = $baziData['nien_van']             ?? [];
                $bieuDoNguHanh     = $baziData['bieu_do_ngu_hanh']     ?? [];
                $chatLuongThapThan = $baziData['chat_luong_thap_than'] ?? [];
                $chiSoBieuDoCot    = $baziData['chi_so_bieu_do_cot']   ?? [];
                $nguHanhDong       = $baziData['ngu_hanh_dong']         ?? [];
            } catch (\Throwable $e) {
                Log::error('PdfExport Q1: BaZiServiceV2::calc lỗi – ' . $e->getMessage());
            }

            $this->applyPrecomputedStrengthData(
                $req,
                $batTuData,
                $bieuDoNguHanh,
                $chatLuongThapThan,
                $chiSoBieuDoCot,
                $nguHanhDong
            );

            PdfExportMetrics::addBaziMs((microtime(true) - $baziT0) * 1000);
        }

        $pdfsToMerge = [];
        $tempFiles   = [];
        $footerSegments = [];

        // ── Trang 1: PDF tĩnh ───────────────────────────────────────────────
        self::appendStaticPage($pdfsToMerge, $pdfDir . '/page-01.pdf', 'Q1 page-01.pdf');

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
        $pdfsToMerge[] = $page2Path;
        $tempFiles[]     = $page2Path;

        // ── Trang 3–13: bundle PDF tĩnh (cache) ─────────────────────────────
        $staticMid = PdfStaticPageCache::resolveBundle('q1-pages-3-13', [
            $pdfDir . '/page-03.pdf',
            $pdfDir . '/page-04.pdf',
            $pdfDir . '/page-05.png',
            $pdfDir . '/page-06.png',
            $pdfDir . '/page-07.png',
            $pdfDir . '/page-08.png',
            $pdfDir . '/page-09.png',
            $pdfDir . '/page-10.png',
            $pdfDir . '/page-11.png',
            $pdfDir . '/page-12.png',
            $pdfDir . '/page-13.png',
        ]);
        if ($staticMid !== null) {
            $pdfsToMerge[] = $staticMid;
        } else {
            Log::warning('PdfExport Q1: không tạo được bundle trang 3–13');
        }

        // ── Trang 14: blade la-so-bat-tu (KẾT QUẢ LÁ SỐ TỨ TRỤ) ────────────
        $page14Path = $tempDir . '/q1p14-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-bat-tu', [
            'templatePath'    => $q2Dir . '/page-12-bg.png',
            'batTu'           => $batTuData,
            'quyNhanVanXuong' => $quyNhanVanXuong,
        ], $page14Path);
        $pdfsToMerge[] = $page14Path;
        $tempFiles[]     = $page14Path;

        // ── Trang 15: blade la-so-dai-van (Đại Vận) ─────────────────────────
        $page15Path = $tempDir . '/q1p15-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-dai-van', [
            'templatePath' => $q2Dir . '/page-13-bg.png',
            'bangDaiVan'   => $bangDaiVan,
        ], $page15Path);
        $pdfsToMerge[] = $page15Path;
        $tempFiles[]     = $page15Path;

        // ── Trang 15b: blade la-so-nien-van (Niên Vận) ───────────────────────
        $page15bPath = $tempDir . '/q1p15b-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-nien-van', [
            'templatePath' => $q2Dir . '/page-13-bg.png',
            'nienVan'      => $nienVan,
        ], $page15bPath);
        $pdfsToMerge[] = $page15bPath;
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
        $pdfsToMerge[] = $page16Path;
        $tempFiles[]     = $page16Path;

        // ── Trang 17: blade la-so-6-khia-canh (Biểu đồ 6 khía cạnh) ─────────
        $page17Path = $tempDir . '/q1p17-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-6-khia-canh', [
            'templatePath'   => $q2Dir . '/page-15-bg.png',
            'chiSoBieuDoCot' => $chiSoBieuDoCot,
            'gender'         => $req->input('g', 'male'),
        ], $page17Path);
        $pdfsToMerge[] = $page17Path;
        $tempFiles[]     = $page17Path;

        // ── Trang 18: page-18.png (Phần 3 cover, cache) ─────────────────────
        self::appendStaticPage($pdfsToMerge, $pdfDir . '/page-18.png', 'Q1 page-18.png');

        // ── Trang 19–21: PHẦN 3 (1 query DinhViGocNhin) ───────────────────
        $phan3Records = DinhViGocNhin::whereIn('slug', [
            'phan3_dinh_vi_goc_nhin',
            'phan3_bocuc_ngu_hanh_ii',
        ])->get()->keyBy('slug');

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
        $pdfsToMerge[] = PdfViewCache::saveView('pdfs.quyen-1.la-so-tong-quan-ngu-hanh', array_merge([
            'templatePath' => $pdfDir . '/page-19-bg.png',
        ], $phan3SectionI), $page19Path, $phan3Salt);
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
                $pdfsToMerge[] = PdfViewCache::saveView('pdfs.quyen-1.la-so-bocuc-ngu-hanh', [
                    'pages' => $phan3Sub2Pages,
                ], $page20Path, $phan3Salt);
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
        $pdfsToMerge[] = PdfViewCache::saveView('pdfs.quyen-1.la-so-bocuc-ngu-hanh', [
            'pages' => $phan3BocucPages,
        ], $page21Path, $phan3Salt);
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
            $pdfsToMerge[] = $pageNguHanhPath;
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
                PdfRenderService::saveView('pdfs.quyen-1.la-so-bocuc-ngu-hanh', [
                    'pages' => $clncPages,
                ], $pageClncPath);
                $pdfsToMerge[] = $pageClncPath;
                $tempFiles[]   = $pageClncPath;
            }
        }

        // ── PHẦN 5: bìa + I. Tổng quan các khía cạnh ────────────────────────
        self::appendStaticPage(
            $pdfsToMerge,
            Phan5PdfService::coverImagePath(),
            'Q1 phan5-bia'
        );

        $phan5TongQuan = Phan5PdfService::buildTongQuanPageData();
        if (! empty($phan5TongQuan['pages'])) {
            $pagePhan5Path = $tempDir . '/q1p-phan5-tq-' . $uid . '.pdf';
            PdfRenderService::saveView('pdfs.phan-5.la-so-tong-quan-khia-canh', $phan5TongQuan, $pagePhan5Path);
            $pdfsToMerge[] = $pagePhan5Path;
            $tempFiles[]   = $pagePhan5Path;
        }

        $phan5SuNghiep = Phan5PdfService::buildSuNghiepPageData($batTuData);
        if ($phan5SuNghiep !== null && ! empty($phan5SuNghiep['pages'])) {
            $pagePhan5SnPath = $tempDir . '/q1p-phan5-sn-' . $uid . '.pdf';
            PdfRenderService::saveView('pdfs.phan-5.la-so-su-nghiep', $phan5SuNghiep, $pagePhan5SnPath);
            $pdfsToMerge[] = $pagePhan5SnPath;
            $tempFiles[]   = $pagePhan5SnPath;
        }

        foreach (Phan5PdfService::buildSuNghiepThienCanPages($batTuData) as $idx => $phan5SnItemPage) {
            $pagePhan5SnItemPath = $tempDir . '/q1p-phan5-sn-item-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan5SnItemPage['view'], $phan5SnItemPage['data'], $pagePhan5SnItemPath);
            $pdfsToMerge[] = $pagePhan5SnItemPath;
            $tempFiles[]   = $pagePhan5SnItemPath;
        }

        if (! empty($batTuData)) {
            $unknowBirthtime = $req->input('uknow_birthdate') == 1;
            $phan5Payload = app(TongQuanKhiaCanhController::class)->buildPhan5PayloadFromBatTu(
                $batTuData,
                $unknowBirthtime,
                ['chat_luong_thap_than' => $chatLuongThapThan]
            );
            foreach (Phan5PdfService::buildOtherKhiaCanhPdfPages($phan5Payload['khia_canh'] ?? [], $batTuData) as $idx => $phan5KcPage) {
                $pagePhan5KcPath = $tempDir . '/q1p-phan5-kc-' . $idx . '-' . $uid . '.pdf';
                PdfRenderService::saveView($phan5KcPage['view'], $phan5KcPage['data'], $pagePhan5KcPath);
                $pdfsToMerge[] = $pagePhan5KcPath;
                $tempFiles[]   = $pagePhan5KcPath;
            }
        }

        // ── PHẦN 6: bìa + nội dung dòng chảy năng lượng ───────────────────────
        self::appendStaticPage(
            $pdfsToMerge,
            Phan6PdfService::coverImagePath(),
            'Q1 phan6-bia'
        );

        $phan6Content = Phan6PdfService::buildContentPageSpec($req);
        if ($phan6Content !== null) {
            $pagePhan6Path = $tempDir . '/q1p-phan6-content-' . $uid . '.pdf';
            PdfRenderService::saveView($phan6Content['view'], $phan6Content['data'], $pagePhan6Path);
            $pdfsToMerge[] = $pagePhan6Path;
            $tempFiles[]   = $pagePhan6Path;
        }

        // ── PHẦN 8 (8A): bìa + Đại Vận + IV. Những năm cần chú ý ─────────────
        self::appendStaticPage(
            $pdfsToMerge,
            Phan8PdfService::coverImagePath(),
            'Q1 phan8-bia'
        );

        foreach (Phan8PdfService::buildPdfPages($req, '8a') as $idx => $phan8Page) {
            $pagePhan8Path = $tempDir . '/q1p-phan8-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan8Page['view'], $phan8Page['data'], $pagePhan8Path);
            $pdfsToMerge[] = $pagePhan8Path;
            $tempFiles[]   = $pagePhan8Path;
        }

        // ── PHẦN 9: bìa tĩnh (LBTV-236) + nội dung (LBTV-119) (y hệt Q1) ────
        self::appendStaticPage(
            $pdfsToMerge,
            Phan9PdfService::coverImagePath(),
            'Q1 phan9-bia'
        );

        $phan9NguHanh = Phan9aService::normalizeNguHanhDong($nguHanhDong);
        foreach (Phan9PdfService::buildPdfPages($phan9NguHanh) as $idx => $phan9Page) {
            $pagePhan9Path = $tempDir . '/q1p-phan9-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan9Page['view'], $phan9Page['data'], $pagePhan9Path);
            $pdfsToMerge[] = $pagePhan9Path;
            $tempFiles[]   = $pagePhan9Path;
        }

        // ── Kết bài: 562–564 (bìa) + 566–569, 572, 581 (worksheet — tên footer đen) ─
        $phan9Dir      = resource_path('views/pdfs/phan-9');
        $q1AppendixDir = resource_path('views/pdfs/q2-appendix');
        self::appendPhan9KetBaiSegments($pdfsToMerge, $footerSegments, $phan9Dir, $q1AppendixDir);

        // ── Merge + footer (banner 08.png, số trang, tên người nhập) ─────────
        $mergedTemp = $tempDir.'/merged-q1-'.$uid.'.pdf';
        $tMerge     = microtime(true);
        $merged     = PdfMergeService::mergeMultiple($pdfsToMerge, $mergedTemp);
        $mergeMs    = (microtime(true) - $tMerge) * 1000;

        foreach ($tempFiles as $tmp) {
            @unlink($tmp);
        }

        if (! $merged || ! file_exists($mergedTemp)) {
            throw new \RuntimeException('Merge PDF Quyển 1 thất bại');
        }

        $fullName = trim((string) $req->input('full_name', ''));
        $darkNamePages = PdfFooterService::resolveDarkNamePages($footerSegments);
        $tFooter  = microtime(true);
        if (! PdfFooterService::applyToMergedPdf(
            $mergedTemp,
            $finalPath,
            $fullName,
            PdfFooterService::FIRST_FOOTER_PAGE,
            PdfFooterService::FIRST_DISPLAY_PAGE_NUMBER,
            [],
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
     *   Trang  5 : page-05.png
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

        // ── Trang 1: page-01.png (cache) ────────────────────────────────────
        $page1Path = PdfStaticPageCache::resolve($pdfDir . '/page-01.png');
        if ($page1Path === null) {
            throw new \RuntimeException('Không tìm thấy page-01.png');
        }
        self::pushMergeSegment($mergeSegments, $page1Path);

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
        self::pushMergeSegment($mergeSegments, $page2Path);
        $tempFiles[]     = $page2Path;

        // ── Trang 3–11: bundle PDF tĩnh (cache) ─────────────────────────────
        $staticMid = PdfStaticPageCache::resolveBundle('q2-pages-3-11', [
            $pdfDir . '/page-03.pdf',
            $pdfDir . '/page-04.png',
            $pdfDir . '/page-05.png',
            $pdfDir . '/page-06.png',
            $pdfDir . '/page-07.png',
            $pdfDir . '/page-08.png',
            $pdfDir . '/page-09.png',
            $pdfDir . '/page-10.png',
            $pdfDir . '/page-11.png',
        ]);
        if ($staticMid !== null) {
            // Trang 11 trong cuốn = trang bìa section (trang thứ 9 trong bundle 3–11)
            self::pushMergeSegment($mergeSegments, $staticMid, [9]);
        } else {
            Log::warning('PdfExport Q2: không tạo được bundle trang 3–11');
        }

        // ── Tính Bát Tự nếu có đủ tham số y/m/d ─────────────────────────────
        $baziData            = null;
        $batTuData           = [];
        $quyNhanVanXuong     = [];
        $bangDaiVan          = [];
        $nienVan             = [];
        $bieuDoNguHanh       = [];
        $chatLuongThapThan   = [];
        $chiSoBieuDoCot      = [];
        $nguHanhDong         = [];
        $nhatChuTitle        = '';
        $nhatChuChapters     = [];

        $birthY = $req->input('y');
        $birthM = $req->input('m');
        $birthD = $req->input('d');

        if ($birthY && $birthM && $birthD) {
            $baziT0 = microtime(true);
            try {
                $baziData = BaZiServiceV2::calc(
                    $req->input('full_name', ''),
                    (int) $birthY,
                    (int) $birthM,
                    (int) $birthD,
                    $req->filled('h')      ? (int) $req->input('h')      : null,
                    $req->filled('minute') ? (int) $req->input('minute') : null,
                    $req->input('g', 'male')
                );
                $batTuData        = $baziData['bat_tu']              ?? [];
                $quyNhanVanXuong  = $baziData['quy_nhan_van_xuong']  ?? [];
                $bangDaiVan       = $baziData['bang_dai_van']         ?? [];
                $nienVan          = $baziData['nien_van']             ?? [];
                $bieuDoNguHanh    = $baziData['bieu_do_ngu_hanh']     ?? [];
                $chatLuongThapThan= $baziData['chat_luong_thap_than'] ?? [];
                $chiSoBieuDoCot   = $baziData['chi_so_bieu_do_cot']   ?? [];
                $nguHanhDong      = $baziData['ngu_hanh_dong']        ?? [];

                // Dữ liệu trang 17: NHẬT CHỦ TRỤ NGÀY – Lý tổng quan + 1. Ý nghĩa trụ ngày
                $thienCanNgay   = trim((string)($batTuData['day']['can']['thien_can'] ?? ''));
                $diaChiNgay     = trim((string)($batTuData['day']['chi']['dia_chi']   ?? ''));
                if ($diaChiNgay === 'Tí') $diaChiNgay = 'Tý';

                $truNgayRecords = NhatChuTruNgay::findByThienCanDiaChi($thienCanNgay, $diaChiNgay);
                $nhatChuTitle   = $truNgayRecords->first()?->title ?? '';

                $chaptersMap = [];
                foreach ($truNgayRecords as $r) {
                    $key = $r->chapter ?? '';
                    if (!isset($chaptersMap[$key])) {
                        $chaptersMap[$key] = ['chapter' => $key, 'sub_sections' => []];
                    }
                    $chaptersMap[$key]['sub_sections'][] = [
                        'sub_title' => $r->sub_title,
                        'content'   => $r->content,
                    ];
                }
                $nhatChuChapters = array_values($chaptersMap);
            } catch (\Throwable $e) {
                Log::error('PdfExport: BaZiServiceV2::calc lỗi – ' . $e->getMessage());
            }

            $this->applyPrecomputedStrengthData(
                $req,
                $batTuData,
                $bieuDoNguHanh,
                $chatLuongThapThan,
                $chiSoBieuDoCot,
                $nguHanhDong
            );

            PdfExportMetrics::addBaziMs((microtime(true) - $baziT0) * 1000);
        }

        // ── Trang 12: blade la-so-bat-tu ─────────────────────────────────────
        $page12Path = $tempDir . '/p12-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-bat-tu', [
            'templatePath'    => $pdfDir . '/page-12-bg.png',
            'batTu'           => $batTuData,
            'quyNhanVanXuong' => $quyNhanVanXuong,
        ], $page12Path);
        self::pushMergeSegment($mergeSegments, $page12Path);
        $tempFiles[]     = $page12Path;

        // ── Trang 13: la-so-dai-van (Đại Vận) ───────────────────────────────
        $page13Path = $tempDir . '/p13-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-dai-van', [
            'templatePath' => $pdfDir . '/page-13-bg.png',
            'bangDaiVan'   => $bangDaiVan,
        ], $page13Path);
        self::pushMergeSegment($mergeSegments, $page13Path);
        $tempFiles[]     = $page13Path;

        // ── Trang 13b: la-so-nien-van (Niên Vận) ────────────────────────────
        $page13bPath = $tempDir . '/p13b-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-nien-van', [
            'templatePath' => $pdfDir . '/page-13-bg.png',
            'nienVan'      => $nienVan,
        ], $page13bPath);
        self::pushMergeSegment($mergeSegments, $page13bPath);
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
        self::pushMergeSegment($mergeSegments, $page14Path);
        $tempFiles[]     = $page14Path;

        // ── Trang 15: la-so-6-khia-canh ─────────────────────────────────────
        $page15Path = $tempDir . '/p15-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-6-khia-canh', [
            'templatePath'   => $pdfDir . '/page-15-bg.png',
            'chiSoBieuDoCot' => $chiSoBieuDoCot,
            'gender'         => $req->input('g', 'male'),
        ], $page15Path);
        self::pushMergeSegment($mergeSegments, $page15Path);
        $tempFiles[]     = $page15Path;

        // ── Trang 16: page-16.png (PHẦN 4 cover, cache) ─────────────────────
        $page16Cover = PdfStaticPageCache::resolve($pdfDir . '/page-16.png');
        if ($page16Cover !== null) {
            self::pushMergeSegment($mergeSegments, $page16Cover, 'all');
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
            ]
        );

        // ── Trang 18: la-so-phan-tich-nhat-chu ──────────────────────────────
        $page18Path = $tempDir . '/p18-' . $uid . '.pdf';
        PdfRenderService::saveView('pdfs.quyen-2.la-so-phan-tich-nhat-chu', [
            'templatePath' => $pdfDir . '/page-19-bg.png',
            'tuyHyPath'    => $pdfDir . '/tuy hy.png',
            'chapters'     => $nhatChuChapters,
        ], $page18Path);
        self::pushMergeSegment($mergeSegments, $page18Path);
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
            ['templatePath' => $pdfDir.'/page-19-bg.png', 'chapters' => $nhatChuChapters]
        );

        self::appendNhatChuChapterPdf(
            $mergeSegments,
            $tempFiles,
            $tempDir,
            $uid,
            NhatChuChapterPdfService::buildChapterIiiPages($nhatChuChapters, $pdfDir),
            'p20',
            'pdfs.quyen-2.la-so-chapter-iii',
            ['templatePath' => $pdfDir.'/page-20-bg.png', 'chapters' => $nhatChuChapters]
        );

        self::appendNhatChuChapterPdf(
            $mergeSegments,
            $tempFiles,
            $tempDir,
            $uid,
            NhatChuChapterPdfService::buildChapterIvPages($nhatChuChapters, $pdfDir),
            'p21',
            'pdfs.quyen-2.la-so-chapter-iv',
            ['templatePath' => $pdfDir.'/page-21-bg.png', 'chapters' => $nhatChuChapters]
        );

        // ── PHẦN 7: bìa + trang mở đầu Mục I (tĩnh) ─────────────────────────────
        $phan7Pages = Phan7PdfService::staticPagePaths();
        $phan7Bundle = PdfStaticPageCache::resolveBundle(Phan7PdfService::bundleCacheKey(), $phan7Pages);
        if ($phan7Bundle !== null) {
            self::pushMergeSegment($mergeSegments, $phan7Bundle, 'all');
        } else {
            foreach ($phan7Pages as $phan7Path) {
                $resolved = PdfStaticPageCache::resolve($phan7Path);
                if ($resolved !== null) {
                    self::pushMergeSegment($mergeSegments, $resolved, 'all');
                }
            }
        }

        // ── PHẦN 7 MỤC I: nội dung từ phan7_tam_the (sheet 0) ───────────────────
        $phan7Muc1Spec = Phan7MucIPdfService::buildContentPageSpec(0);
        if ($phan7Muc1Spec !== null) {
            $pagePhan7Muc1Path = $tempDir . '/q2p-phan7-muc1-' . $uid . '.pdf';
            PdfRenderService::saveView($phan7Muc1Spec['view'], $phan7Muc1Spec['data'], $pagePhan7Muc1Path);
            self::pushMergeSegment($mergeSegments, $pagePhan7Muc1Path);
            $tempFiles[]   = $pagePhan7Muc1Path;
        }

        // ── PHẦN 7 MỤC II: nội dung động theo % Thập Thần ───────────────────────
        $phan7Muc2Spec = Phan7MucIIPdfService::buildContentPageSpec($req);
        if ($phan7Muc2Spec !== null) {
            $pagePhan7Muc2Path = $tempDir . '/q2p-phan7-muc2-' . $uid . '.pdf';
            PdfRenderService::saveView($phan7Muc2Spec['view'], $phan7Muc2Spec['data'], $pagePhan7Muc2Path);
            self::pushMergeSegment($mergeSegments, $pagePhan7Muc2Path);
            $tempFiles[]   = $pagePhan7Muc2Path;
        }

        // ── PHẦN 7: đoạn nối cuối (phan7_tam_the sheet 1) ───────────────────────
        $phan7Muc1CuoiSpec = Phan7MucIPdfService::buildContentPageSpec(1);
        if ($phan7Muc1CuoiSpec !== null) {
            $pagePhan7Muc1CuoiPath = $tempDir . '/q2p-phan7-muc1-cuoi-' . $uid . '.pdf';
            PdfRenderService::saveView($phan7Muc1CuoiSpec['view'], $phan7Muc1CuoiSpec['data'], $pagePhan7Muc1CuoiPath);
            self::pushMergeSegment($mergeSegments, $pagePhan7Muc1CuoiPath);
            $tempFiles[]   = $pagePhan7Muc1CuoiPath;
        }

        // ── PHẦN 8 (8B): Niên Vận tiếp theo + III. Dự báo khía cạnh ─────────
        // Không dùng bia-phan-8.png (ghi «DỰ BÁO ĐẠI VẬN» — chỉ dành cho 8A / Q1).
        foreach (Phan8PdfService::buildPdfPages($req, '8b') as $idx => $phan8Page) {
            $pagePhan8Path = $tempDir . '/q2p-phan8-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan8Page['view'], $phan8Page['data'], $pagePhan8Path);
            $isCover = ($phan8Page['view'] ?? '') === 'pdfs.phan-8.la-so-phan-8-nien-van-cover';
            self::pushMergeSegment($mergeSegments, $pagePhan8Path, $isCover ? 'all' : null);
            $tempFiles[]   = $pagePhan8Path;
        }

        // ── PHẦN 9B: bìa + cuộn thư + nội dung (Cuốn 2) ─────────────────────
        $phan9Bia = PdfStaticPageCache::resolve(Phan9bPdfService::coverImagePath());
        if ($phan9Bia !== null) {
            self::pushMergeSegment($mergeSegments, $phan9Bia, 'all');
        } else {
            Log::warning('PdfExport Q2: không tìm thấy bìa Phần 9B');
        }

        foreach (Phan9bPdfService::buildPdfPages($req, $nguHanhDong, $chatLuongThapThan) as $idx => $phan9Page) {
            $pagePhan9Path = $tempDir . '/q2p-phan9b-' . $idx . '-' . $uid . '.pdf';
            PdfRenderService::saveView($phan9Page['view'], $phan9Page['data'], $pagePhan9Path);
            self::pushMergeSegment($mergeSegments, $pagePhan9Path);
            $tempFiles[]   = $pagePhan9Path;
        }

        // ── Phụ lục Q2: 562–564 (bìa) + 572, 581 (worksheet — tên footer đen) ─
        $q2AppendixDir = resource_path('views/pdfs/q2-appendix');
        self::appendQ2AppendixSegments($mergeSegments, $q2AppendixDir);

        $pdfsToMerge = array_column($mergeSegments, 'path');
        $whiteNamePages = PdfFooterService::resolveCoverNamePages($mergeSegments);
        $darkNamePages = PdfFooterService::resolveDarkNamePages($mergeSegments);

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
        array $paginatedExtra = []
    ): void {
        $path = $tempDir.'/'.$filePrefix.'-'.$uid.'.pdf';

        if ($pages !== []) {
            $view = $paginatedView ?? 'pdfs.quyen-1.la-so-bocuc-ngu-hanh';
            PdfRenderService::saveView($view, array_merge(['pages' => $pages], $paginatedExtra), $path);
        } else {
            PdfRenderService::saveView($fallbackView, $fallbackData, $path);
        }

        self::pushMergeSegment($segments, $path);
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
        string $appendixDir
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
            $pdfsToMerge[] = $coverBundle;
            self::pushMergeSegment($mergeSegments, $coverBundle, 'all');
        } else {
            foreach ($coverSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    $pdfsToMerge[] = $resolved;
                    self::pushMergeSegment($mergeSegments, $resolved, 'all');
                }
            }
        }

        $worksheetBundle = PdfStaticPageCache::resolveBundle('q1-ket-bai-worksheets-v1', $worksheetSources);
        if ($worksheetBundle !== null) {
            $pdfsToMerge[] = $worksheetBundle;
            self::pushMergeSegment($mergeSegments, $worksheetBundle, 'darkName');
        } else {
            foreach ($worksheetSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    $pdfsToMerge[] = $resolved;
                    self::pushMergeSegment($mergeSegments, $resolved, 'darkName');
                }
            }
        }
    }

    /**
     * Phụ lục cuối Cuốn 2 — bìa tối (562–564) + worksheet nền sáng (572, 581).
     *
     * @param  array<int, array{path: string, coverPages?: mixed, darkNamePages?: mixed}>  $mergeSegments
     */
    private static function appendQ2AppendixSegments(array &$mergeSegments, string $appendixDir): void
    {
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
            self::pushMergeSegment($mergeSegments, $coverBundle, 'all');
        } else {
            foreach ($coverSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    self::pushMergeSegment($mergeSegments, $resolved, 'all');
                }
            }
        }

        $worksheetBundle = PdfStaticPageCache::resolveBundle('q2-appendix-worksheets-v1', $worksheetSources);
        if ($worksheetBundle !== null) {
            self::pushMergeSegment($mergeSegments, $worksheetBundle, 'darkName');
        } else {
            foreach ($worksheetSources as $source) {
                $resolved = PdfStaticPageCache::resolve($source);
                if ($resolved !== null) {
                    self::pushMergeSegment($mergeSegments, $resolved, 'darkName');
                }
            }
        }
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
