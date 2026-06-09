<?php

namespace App\Services;

use App\Models\Phan5KhiaCanh;
use App\Models\Phan5Trang;
use App\Models\ThapThanTheoViTri;
use App\Models\TongQuanKhiaCanh;

class Phan5PdfService
{
    public static function pdfDir(): string
    {
        return resource_path('views/pdfs/phan-5');
    }

    public static function coverImagePath(): string
    {
        $row = Phan5Trang::query()->where('slug', 'bia')->first();
        $path = trim((string) ($row?->image ?? ''));
        if ($path !== '' && is_file(self::resolveAssetPath($path))) {
            return self::resolveAssetPath($path);
        }

        return self::pdfDir().'/bia-phan-5.png';
    }

    public static function tongQuanBgPath(): string
    {
        $row = Phan5Trang::query()->where('slug', 'tong_quan')->first();
        $path = trim((string) ($row?->image ?? ''));
        if ($path !== '' && is_file(self::resolveAssetPath($path))) {
            return self::resolveAssetPath($path);
        }

        return self::pdfDir().'/tong-quan-bg.png';
    }

    /**
     * @return array{templatePath: string, subSections: array<int, array{sub_title: string, content: array<int, array{type: string, text?: string}>}>}
     */
    public static function buildTongQuanPageData(): array
    {
        $subSections = [];
        foreach (TongQuanKhiaCanh::getAllOrdered() as $item) {
            $slug = (string) $item->slug;
            if ($slug === 'transition_phan6') {
                continue;
            }

            $content = trim((string) $item->content);
            if ($content === '') {
                continue;
            }

            $paragraphs = array_values(array_filter(
                preg_split('/\r\n|\r|\n/', $content) ?: [],
                static fn (string $p): bool => trim($p) !== ''
            ));

            if ($paragraphs === []) {
                continue;
            }

            $blocks = array_map(
                static fn (string $p): array => ['type' => 'para', 'text' => trim($p)],
                $paragraphs
            );

            $subSections[] = [
                'sub_title' => $slug === 'intro' ? '' : trim((string) $item->title),
                'content' => $blocks,
            ];
        }

        $blocks = Phan5BlockBuilder::fromTongQuan($subSections);
        $pages = Phan5PdfPaginator::paginate(
            $blocks,
            self::tongQuanBgPath(),
            Phan5PdfPaginator::contentHeightForLayout('tong_quan')
        );

        return [
            'layoutKey' => 'tong_quan',
            'pages' => $pages,
        ];
    }

    public static function suNghiepBgPath(): string
    {
        $row = Phan5Trang::query()->where('slug', 'su_nghiep')->first();
        $path = trim((string) ($row?->image ?? ''));
        if ($path !== '' && is_file(self::resolveAssetPath($path))) {
            return self::resolveAssetPath($path);
        }

        return self::pdfDir().'/su-nghiep-bg.png';
    }

    /**
     * @param  array<string, mixed>  $batTu
     * @return array{templatePath: string, tongQuan: string, batTu: array<string, mixed>}|null
     */
    public static function buildSuNghiepPageData(array $batTu): ?array
    {
        if ($batTu === []) {
            return null;
        }

        $layout = Phan5KhiaCanh::query()->where('slug', 'su_nghiep')->first();
        $tongQuan = trim((string) ($layout?->tong_quan ?? ''));

        $data = [
            'templatePath' => self::suNghiepBgPath(),
            'tongQuan' => $tongQuan,
            'batTu' => $batTu,
            'highlightPillars' => self::highlightPillarsForSlug('su_nghiep'),
            'layoutKey' => 'su_nghiep',
        ];
        $blocks = Phan5BlockBuilder::fromSuNghiepOverview($data);
        $data['pages'] = Phan5PdfPaginator::paginate(
            $blocks,
            $data['templatePath'],
            Phan5PdfPaginator::contentHeightForLayout('su_nghiep')
        );

        return $data;
    }

    /**
     * @return array<int, string>
     */
    public static function highlightPillarsForSlug(string $slug): array
    {
        return match ($slug) {
            'su_nghiep' => ['month', 'year'],
            'tai_chinh' => ['month', 'hour'],
            'tinh_duyen' => ['day'],
            'phat_trien_ban_than' => ['hour'],
            'ket_noi_xa_hoi' => ['year'],
            default => [],
        };
    }

    public static function suNghiepItemBgPath(): string
    {
        $row = Phan5Trang::query()->where('slug', 'su_nghiep_item')->first();
        $path = trim((string) ($row?->image ?? ''));
        if ($path !== '' && is_file(self::resolveAssetPath($path))) {
            return self::resolveAssetPath($path);
        }

        return self::pdfDir().'/su-nghiep-item-bg.png';
    }

    /** Nền LBTV-119 — dùng từ mục III (Tài chính) trở đi. */
    public static function contentBgPath(): string
    {
        $row = Phan5Trang::query()->where('slug', 'page_content')->first();
        $path = trim((string) ($row?->image ?? ''));
        if ($path !== '' && is_file(self::resolveAssetPath($path))) {
            return self::resolveAssetPath($path);
        }

        return self::pdfDir().'/page-content-bg.png';
    }

    public static function keywordFramePath(): string
    {
        $row = Phan5Trang::query()->where('slug', 'anh_tu_khoa')->first();
        $path = trim((string) ($row?->image ?? ''));
        if ($path !== '' && is_file(self::resolveAssetPath($path))) {
            return self::resolveAssetPath($path);
        }

        return self::pdfDir().'/anh-tu-khoa-frame.png';
    }

    /**
     * @param  array<string, mixed>  $batTu
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    public static function buildSuNghiepThienCanPages(array $batTu): array
    {
        $pages = [];

        $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh'] ?? ''));
        if ($thapThanThang !== '') {
            $pages = array_merge(
                $pages,
                self::buildSuNghiepThapThanItemPages($thapThanThang, 'Trụ Tháng', 'Thiên Can Trụ Tháng', 2)
            );
        }

        $thapThanNam = trim((string) ($batTu['year']['can']['chu_tinh'] ?? ''));
        if ($thapThanNam !== '') {
            $pages = array_merge(
                $pages,
                self::buildSuNghiepThapThanItemPages($thapThanNam, 'Trụ Năm', 'Thiên Can Trụ Năm', 3)
            );
        }

        return $pages;
    }

    /**
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    protected static function buildSuNghiepThapThanItemPages(
        string $thapThan,
        string $viTri,
        string $itemLabel,
        int $itemNumber
    ): array {
        $sections = self::fetchSuNghiepSections($thapThan, $viTri);
        if ($sections === []) {
            return [];
        }

        $keywords = [];
        $giaiNghia = '';
        $tichCuc = '';
        $tieuCuc = '';
        $chienLuoc = '';

        foreach ($sections as $sec) {
            $label = $sec['label'] ?? '';
            $content = trim((string) ($sec['content'] ?? ''));

            if ($label === 'Từ khóa cốt lõi') {
                $keywords = self::parseKeywords($content);

                continue;
            }

            if ($label === 'Giải nghĩa năng lượng') {
                $giaiNghia = $content;

                continue;
            }

            if ($label === 'Mặt tích cực') {
                $tichCuc = $content;

                continue;
            }

            if ($label === 'Mặt tiêu cực') {
                $tieuCuc = $content;

                continue;
            }

            if ($label === 'Chiến lược phát triển') {
                $chienLuoc = $content;
            }
        }

        $minhHoa = Phan5MinhHoaService::resolvePath('su_nghiep', $itemLabel);
        $base = [
            'templatePath' => self::suNghiepItemBgPath(),
            'keywordFramePath' => self::keywordFramePath(),
            'itemNumber' => $itemNumber,
            'itemLabel' => $itemLabel,
            'thapThanUpper' => mb_strtoupper($thapThan, 'UTF-8'),
        ];

        $pages = [];

        if ($giaiNghia !== '' || $keywords !== [] || $minhHoa !== null) {
            $pages = array_merge(
                $pages,
                self::wrapPaginatedView(
                    'pdfs.phan-5.la-so-su-nghiep-thap-than-item',
                    array_merge($base, [
                        'minhHoaPath' => $minhHoa,
                        'keywords' => $keywords,
                        'giaiNghia' => $giaiNghia,
                        'layoutKey' => 'su_nghiep_item',
                    ]),
                    Phan5BlockBuilder::fromThapThanItem(array_merge($base, [
                        'minhHoaPath' => $minhHoa,
                        'keywords' => $keywords,
                        'giaiNghia' => $giaiNghia,
                    ])),
                    'su_nghiep_item'
                )
            );
        }

        if ($tichCuc !== '' || $tieuCuc !== '' || $chienLuoc !== '') {
            $pages = array_merge(
                $pages,
                self::wrapPaginatedView(
                    'pdfs.phan-5.la-so-su-nghiep-thap-than-traits',
                    array_merge($base, [
                        'tichCuc' => $tichCuc,
                        'tieuCuc' => $tieuCuc,
                        'chienLuoc' => $chienLuoc,
                        'layoutKey' => 'traits_su_nghiep',
                    ]),
                    Phan5BlockBuilder::fromTraits([
                        'tichCuc' => $tichCuc,
                        'tieuCuc' => $tieuCuc,
                        'chienLuoc' => $chienLuoc,
                    ]),
                    'traits_su_nghiep'
                )
            );
        }

        return $pages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $khiaCanhBlocks
     * @param  array<string, mixed>  $batTu
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    public static function buildOtherKhiaCanhPdfPages(array $khiaCanhBlocks, array $batTu = []): array
    {
        $pages = [];

        foreach ($khiaCanhBlocks as $block) {
            $slug = (string) ($block['slug'] ?? '');
            if ($slug === '' || $slug === 'su_nghiep') {
                continue;
            }

            $title = trim((string) ($block['title'] ?? ''));
            $tongQuan = trim((string) ($block['tong_quan'] ?? ''));
            $viTriUrl = trim((string) ($block['image_vi_tri'] ?? ''));
            $viTriPath = null;
            if ($viTriUrl !== '') {
                $layout = Phan5KhiaCanh::query()->where('slug', $slug)->first();
                $dbPath = trim((string) ($layout?->image_vi_tri ?? ''));
                if ($dbPath !== '' && is_file(self::resolveAssetPath($dbPath))) {
                    $viTriPath = self::resolveAssetPath($dbPath);
                }
            }

            if ($batTu !== [] && ($title !== '' || $tongQuan !== '' || $viTriPath !== null)) {
                $overviewData = [
                    'templatePath' => self::contentBgPath(),
                    'sectionTitle' => $title,
                    'tongQuan' => $tongQuan,
                    'batTu' => $batTu,
                    'highlightPillars' => self::highlightPillarsForSlug($slug),
                    'imageViTriPath' => $viTriPath,
                    'layoutKey' => 'lbtv119',
                ];
                $overviewBlocks = Phan5BlockBuilder::fromKhiaCanhOverview($overviewData);
                $pages = array_merge(
                    $pages,
                    self::wrapPaginatedView(
                        'pdfs.phan-5.la-so-khia-canh-overview',
                        $overviewData,
                        $overviewBlocks,
                        'lbtv119'
                    )
                );
            }

            $itemNumber = 2;
            foreach ($block['items'] ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $pages = array_merge(
                    $pages,
                    self::buildKhiaCanhItemPdfPages($slug, $item, $itemNumber)
                );
                $itemNumber++;
            }
        }

        return $pages;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    protected static function buildKhiaCanhItemPdfPages(string $khiaSlug, array $item, int $itemNumber): array
    {
        $itemLabel = trim((string) ($item['label'] ?? ''));
        $thapThan = trim((string) ($item['thap_than'] ?? ''));
        if ($itemLabel === '' || $thapThan === '') {
            return [];
        }

        $parsed = self::parseSectionsForPdf($item['sections'] ?? []);
        $minhHoa = Phan5MinhHoaService::resolvePath($khiaSlug, $itemLabel);

        $base = [
            'templatePath' => self::contentBgPath(),
            'layoutVariant' => 'lbtv119',
            'keywordFramePath' => self::keywordFramePath(),
            'itemNumber' => $itemNumber,
            'itemLabel' => $itemLabel,
            'thapThanUpper' => mb_strtoupper($thapThan, 'UTF-8'),
        ];

        $pages = [];
        $hasIntro = $minhHoa !== null
            || $parsed['keywords'] !== []
            || $parsed['giaiNghia'] !== ''
            || $parsed['bodySections'] !== [];

        if ($hasIntro) {
            $itemData = array_merge($base, [
                'minhHoaPath' => $minhHoa,
                'keywords' => $parsed['keywords'],
                'giaiNghia' => $parsed['giaiNghia'],
                'bodySections' => $parsed['bodySections'],
                'layoutKey' => 'lbtv119',
            ]);
            $pages = array_merge(
                $pages,
                self::wrapPaginatedView(
                    'pdfs.phan-5.la-so-su-nghiep-thap-than-item',
                    $itemData,
                    Phan5BlockBuilder::fromThapThanItem($itemData),
                    'lbtv119'
                )
            );
        }

        if ($parsed['tichCuc'] !== '' || $parsed['tieuCuc'] !== '' || $parsed['chienLuoc'] !== '') {
            $traitsData = array_merge($base, [
                'tichCuc' => $parsed['tichCuc'],
                'tieuCuc' => $parsed['tieuCuc'],
                'chienLuoc' => $parsed['chienLuoc'],
                'layoutKey' => 'traits_lbtv119',
            ]);
            $pages = array_merge(
                $pages,
                self::wrapPaginatedView(
                    'pdfs.phan-5.la-so-su-nghiep-thap-than-traits',
                    $traitsData,
                    Phan5BlockBuilder::fromTraits($traitsData),
                    'traits_lbtv119'
                )
            );
        }

        return $pages;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array{view: string, data: array<string, mixed>}>
     */
    protected static function wrapPaginatedView(
        string $view,
        array $baseData,
        array $blocks,
        string $layoutKey
    ): array {
        if ($blocks === []) {
            return [];
        }

        $bgPath = (string) ($baseData['templatePath'] ?? self::contentBgPath());
        $pages = Phan5PdfPaginator::paginate(
            $blocks,
            $bgPath,
            Phan5PdfPaginator::contentHeightForLayout($layoutKey),
            $layoutKey,
            self::continuationHeaderFromBlocks($blocks)
        );

        if ($pages === []) {
            return [];
        }

        return [[
            'view' => $view,
            'data' => array_merge($baseData, [
                'pages' => $pages,
                'layoutKey' => $layoutKey,
            ]),
        ]];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return ?array{type: string, text: string}
     */
    protected static function continuationHeaderFromBlocks(array $blocks): ?array
    {
        foreach ($blocks as $block) {
            $type = (string) ($block['type'] ?? '');
            if ($type === 'item_title' || $type === 'section_title') {
                $text = trim((string) ($block['text'] ?? ''));
                if ($text === '') {
                    continue;
                }

                return ['type' => $type, 'text' => $text.' (tiếp)'];
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{label?: string, content?: string}>  $sections
     * @return array{keywords: array<int, string>, giaiNghia: string, tichCuc: string, tieuCuc: string, chienLuoc: string, bodySections: array<int, array{label: string, content: string}>}
     */
    protected static function parseSectionsForPdf(array $sections): array
    {
        $keywords = [];
        $giaiNghia = '';
        $tichCuc = '';
        $tieuCuc = '';
        $chienLuoc = '';
        $bodySections = [];

        foreach ($sections as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $label = trim((string) ($sec['label'] ?? ''));
            $content = trim((string) ($sec['content'] ?? ''));
            if ($content === '') {
                continue;
            }

            $lower = mb_strtolower($label, 'UTF-8');
            if (str_contains($lower, 'từ khóa')) {
                $keywords = self::parseKeywords($content);

                continue;
            }
            if (str_contains($lower, 'giải nghĩa')) {
                $giaiNghia = $content;

                continue;
            }
            if (str_contains($lower, 'tích cực')) {
                $tichCuc = $tichCuc === '' ? $content : $tichCuc."\n\n".$content;

                continue;
            }
            if (str_contains($lower, 'tiêu cực')) {
                $tieuCuc = $tieuCuc === '' ? $content : $tieuCuc."\n\n".$content;

                continue;
            }
            if (str_contains($lower, 'chiến lược')) {
                $chienLuoc = $chienLuoc === '' ? $content : $chienLuoc."\n\n".$content;

                continue;
            }

            $bodySections[] = ['label' => $label, 'content' => $content];
        }

        return compact('keywords', 'giaiNghia', 'tichCuc', 'tieuCuc', 'chienLuoc', 'bodySections');
    }

    /**
     * @return array<int, array{label: string, content: string}>
     */
    protected static function fetchSuNghiepSections(string $thapThan, string $viTri): array
    {
        $records = ThapThanTheoViTri::query()
            ->where('thap_than', $thapThan)
            ->where('vi_tri', $viTri)
            ->where('loai_can', 'Thiên Can')
            ->where('khia_canh', 'Sự Nghiệp')
            ->orderBy('sort_order')
            ->get();

        if ($records->isEmpty()) {
            return [];
        }

        $sectionOrder = [
            'Từ khóa cốt lõi',
            'Giải nghĩa năng lượng',
            'Mặt tích cực',
            'Mặt tiêu cực',
            'Chiến lược phát triển',
        ];

        $out = [];
        foreach ($sectionOrder as $label) {
            $rec = $records->firstWhere('huong', $label);
            $content = trim((string) ($rec?->content ?? ''));
            if ($content !== '') {
                $out[] = ['label' => $label, 'content' => $content];
            }
        }

        return $out;
    }

    /**
     * @return array<int, string>
     */
    public static function parseKeywords(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/[,，、;]+|\r\n|\r|\n/u', $text) ?: [];
        $keywords = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $keywords[] = $part;
            if (count($keywords) >= 3) {
                break;
            }
        }

        return $keywords;
    }

    public static function resolveAssetPath(string $relative): string
    {
        return Phan5AssetService::resolvePath($relative);
    }
}
