<?php

namespace App\Http\Controllers;

use App\Models\CodingLogicRelationship;
use App\Models\DongChayGioiThieu;
use App\Models\DongChayNangLuong;
use App\Models\GiaiPhapThapThan;
use App\Models\HyKyThan;
use App\Models\Phan8aThapThan;
use App\Models\Phan8DuBaoKhiaCanh;
use App\Models\Phan7BaiHoc;
use App\Models\Phan7TamThe;
use App\Models\SucKhoeHyKyThan;
use App\Models\ThapThanTheoViTri;
use App\Models\TongQuanKhiaCanh;
use App\Models\Phan6LaSoBatTu;
use App\Models\Phan5Trang;
use App\Models\Phan9aNgoaiLuc;
use App\Models\Phan9aNoiLuc;
use App\Models\YNghiaTuTru;
use App\Services\BaZiServiceV2;
use App\Services\Phan5KhiaCanhService;
use App\Services\Phan8TruSectionService;
use App\Services\Phan9aService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TongQuanKhiaCanhController extends Controller
{
    public function __construct(
        protected BaZiServiceV2 $bazi,
        protected Phan5KhiaCanhService $phan5KhiaCanh
    ) {
    }

    /**
     * Thay thế các placeholder trong nội dung PHẦN 6 bằng giá trị thực từ lá số.
     *
     * Placeholder mới:
     *   [thap_than_thien_can] tại [thien_can_tru_(nam|thang|ngay|gio)]
     *   [thap_than_dia_chi]   tại [dia_chi_tru_(nam|thang|ngay|gio)]
     *   [thien_can_tru_(nam|thang|ngay|gio)]
     *   [dia_chi_tru_(nam|thang|ngay|gio)]
     *
     * @param  array<string, string>  $data  key gồm: thap_than_thien_can_{tru}, thap_than_dia_chi_{tru},
     *                                        thien_can_{tru}, dia_chi_{tru} với tru = nam|thang|ngay|gio
     */
    private function replaceIntroPlaceholders(string $text, array $data): string
    {
        // 1. [thap_than_thien_can] tại [thien_can_tru_X] → Thập Thần TC tại Thiên Can
        $text = preg_replace_callback(
            '/\[thap_than_thien_can\]\s*tại\s*\[thien_can_tru_(nam|thang|ngay|gio)\]/u',
            static function (array $m) use ($data): string {
                $key = $m[1];
                $thapThan = $data['thap_than_thien_can_' . $key] ?? '';
                $thienCan = $data['thien_can_' . $key] ?? '';

                return ($thapThan !== '' ? $thapThan : '—') . ' tại ' . ($thienCan !== '' ? $thienCan : '—');
            },
            $text
        );

        // 2. [thap_than_dia_chi] tại [dia_chi_tru_X] → Thập Thần ĐC tại Địa Chi
        $text = preg_replace_callback(
            '/\[thap_than_dia_chi\]\s*tại\s*\[dia_chi_tru_(nam|thang|ngay|gio)\]/u',
            static function (array $m) use ($data): string {
                $key = $m[1];
                $thapThan = $data['thap_than_dia_chi_' . $key] ?? '';
                $diaChi = $data['dia_chi_' . $key] ?? '';

                return ($thapThan !== '' ? $thapThan : '—') . ' tại ' . ($diaChi !== '' ? $diaChi : '—');
            },
            $text
        );

        // 3. Standalone [thien_can_tru_X] / [dia_chi_tru_X] còn lại
        $text = preg_replace_callback(
            '/\[(thien_can|dia_chi)_tru_(nam|thang|ngay|gio)\]/u',
            static function (array $m) use ($data): string {
                $val = $data[$m[1] . '_' . $m[2]] ?? '';

                return $val !== '' ? $val : '—';
            },
            $text
        );

        // 4. Fallback: xóa placeholder cũ nếu còn sót
        $text = preg_replace('/\[thap_than_thien_can\]|\[thap_than_dia_chi\]/u', '—', $text);

        return $text;
    }

    /**
     * Xóa các đoạn văn chứa placeholder [thap_than_thien_can], [thap_than_dia_chi],
     * [thien_can_tru_X], [dia_chi_tru_X] khi không có mối quan hệ.
     * Giữ lại các đoạn khác.
     */
    private function removePlaceholderParagraph(string $text): string
    {
        $placeholderPattern = '/\[thap_than_thien_can\]|\[thap_than_dia_chi\]|\[thien_can_tru_|\[dia_chi_tru_/u';
        $paragraphs = preg_split('/\n\s*\n/u', $text);
        $kept = [];
        foreach ($paragraphs as $p) {
            $trimmed = trim($p);
            if ($trimmed === '') {
                continue;
            }
            if (preg_match($placeholderPattern, $p) === 1) {
                continue;
            }
            $kept[] = $p;
        }

        return trim(implode("\n\n", $kept));
    }

    /**
     * @param  array{noi_dung?: string, image?: string|null, image_url?: string|null}  $row
     * @return array{noi_dung?: string, image?: string}|null
     */
    private function buildGioiThieuApiPayload(array $row, string $processedNoiDung): ?array
    {
        $display = \App\Services\DocxTextService::parseGioiThieuForDisplay($processedNoiDung);
        $imageUrl = trim((string) ($row['image_url'] ?? ''));
        if ($imageUrl === '' && ! empty($row['image'])) {
            $imageUrl = \App\Services\DocxTextService::publicUrlForMarkerPath((string) $row['image']);
        }

        if ($display['noi_dung'] === '' && $display['tieu_de'] === null && $imageUrl === '') {
            return null;
        }

        $payload = [];
        if ($display['tieu_de'] !== null && $display['tieu_de'] !== '') {
            $payload['tieu_de'] = $display['tieu_de'];
        }
        if ($display['noi_dung'] !== '') {
            $payload['noi_dung'] = $display['noi_dung'];
        }
        if ($imageUrl !== '') {
            $payload['image'] = $imageUrl;
        }

        return $payload;
    }

    public function index(): JsonResponse
    {
        $items = TongQuanKhiaCanh::getAllOrdered();
        $pages = [];
        foreach (Phan5Trang::getAllOrdered() as $page) {
            $image = trim((string) $page->image);
            $pages[$page->slug] = [
                'slug' => $page->slug,
                'title' => $page->title,
                'image' => $image !== '' ? \App\Services\DocxTextService::publicUrlForMarkerPath($image) : null,
                'sort_order' => $page->sort_order,
            ];
        }

        return response()->json([
            'pages' => $pages,
            'data' => $items->map(fn ($item) => [
                'slug' => $item->slug,
                'title' => $item->title,
                'content' => $item->content,
                'sort_order' => $item->sort_order,
            ])->values()->all(),
        ]);
    }

    /**
     * PHẦN 9A: Mã 1 Nội lực (ngũ hành yếu nhất từ bản mệnh) + Mã 2 Ngoại lực (full DOCX).
     */
    public function phan9GiaiPhap(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $rules = [
            'full_name' => 'nullable|string|max:255',
            'y' => 'required|numeric|min:1940|max:2031',
            'm' => 'required|numeric|min:1|max:12',
            'd' => 'required|numeric|min:1|max:31',
            'g' => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];
        if (! $unknowBirthtime) {
            $rules['h'] = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h'] = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }
        $validated = $req->validate($rules);
        $fullName = (string) ($validated['full_name'] ?? '');
        $y = (int) $validated['y'];
        $m = (int) $validated['m'];
        $d = (int) $validated['d'];
        $h = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: true);
        $nguHanhDong = $this->resolveNguHanhBanMenhForPhan9($req, $result);
        $yeuNhat = Phan9aService::resolveYeuNhatNguHanh($nguHanhDong);

        $introRows = Phan9aNoiLuc::query()
            ->where('loai', 'intro')
            ->orderBy('sort_order')
            ->get();

        $intro = [];
        foreach ($introRows as $row) {
            $text = trim((string) $row->noi_dung);
            if ($text === '') {
                continue;
            }
            if ($yeuNhat !== null) {
                $text = Phan9aService::replaceIntroPlaceholders(
                    $text,
                    $yeuNhat['ten'],
                    $yeuNhat['phan_tram']
                );
            }
            $intro[] = $text;
        }

        $hanhBlock = null;
        if ($yeuNhat !== null) {
            $hanhRows = Phan9aNoiLuc::query()
                ->where('loai', 'hanh')
                ->where('ngu_hanh', $yeuNhat['slug'])
                ->orderBy('sort_order')
                ->get();
            $display = Phan9aService::buildHanhDisplay($hanhRows);
            $hanhBlock = [
                'ngu_hanh' => $yeuNhat,
                'tieu_de_chinh' => $display['tieu_de_chinh'],
                'sections' => $display['sections'],
            ];
        }

        $ngoaiLuc = Phan9aNgoaiLuc::query()->orderBy('sort_order')->first();
        $phan2 = null;
        if ($ngoaiLuc !== null) {
            $phan2 = [
                'tieu_de' => $ngoaiLuc->tieu_de,
                'noi_dung' => trim((string) $ngoaiLuc->noi_dung),
                'paragraphs' => array_values(array_filter(
                    preg_split('/\r\n|\r|\n/', (string) $ngoaiLuc->noi_dung) ?: [],
                    static fn (string $p): bool => trim($p) !== ''
                )),
            ];
        }

        $transition = DongChayGioiThieu::query()
            ->where('tru_loai', 'transition_phan9a')
            ->first();
        $transitionPhan9a = null;
        if ($transition !== null && trim((string) $transition->noi_dung) !== '') {
            $transitionPhan9a = [
                'noi_dung' => trim((string) $transition->noi_dung),
            ];
        }

        return response()->json([
            'data' => [
                'phan_1' => [
                    'tieu_de' => 'I. NỘI LỰC TỰ THÂN',
                    'ngu_hanh_ban_menh' => $nguHanhDong,
                    'yeu_nhat' => $yeuNhat,
                    'intro' => $intro,
                    'noi_dung_hanh' => $hanhBlock,
                ],
                'phan_2' => $phan2,
                'transition_phan9a' => $transitionPhan9a,
            ],
        ]);
    }

    /**
     * % Ngũ hành bản mệnh: ưu tiên query từ UI (đồng bộ biểu đồ), không thì từ calc + crawler.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, int>
     */
    protected function resolveNguHanhBanMenhForPhan9(Request $req, array $result): array
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

        $fromCalc = is_array($result['ngu_hanh_dong'] ?? null) ? $result['ngu_hanh_dong'] : [];

        return Phan9aService::normalizeNguHanhDong($fromCalc);
    }

    /**
     * Dữ liệu Phần 6 (dùng chung API + PDF).
     *
     * @return array<string, mixed>|null
     */
    public function buildPhan6ApiDataFromRequest(Request $req): ?array
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $rules = [
            'full_name' => 'nullable|string|max:255',
            'y' => 'required|numeric|min:1940|max:2031',
            'm' => 'required|numeric|min:1|max:12',
            'd' => 'required|numeric|min:1|max:31',
            'g' => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];
        if (! $unknowBirthtime) {
            $rules['h'] = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h'] = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }

        try {
            $validated = $req->validate($rules);
        } catch (\Illuminate\Validation\ValidationException) {
            return null;
        }

        return $this->buildPhan6ApiData($validated, $unknowBirthtime);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function buildPhan6ApiData(array $validated, bool $unknowBirthtime = false): array
    {
        $mapYnghiaItem = static function ($item): array {
            $imagePath = trim((string) ($item->image ?? ''));

            return [
                'slug' => $item->slug,
                'title' => $item->title,
                'content' => $item->content,
                'sort_order' => $item->sort_order,
                'image' => $imagePath !== ''
                    ? \App\Services\DocxTextService::publicUrlForMarkerPath($imagePath)
                    : null,
            ];
        };

        $allYnghia = YNghiaTuTru::getAllOrdered();
        $transitionPhan8 = null;
        $transitionRow = $allYnghia->firstWhere('slug', 'transition_phan8');
        if ($transitionRow !== null) {
            $transitionPhan8 = $mapYnghiaItem($transitionRow);
        }

        $yNghiaTuTru = $allYnghia
            ->filter(static function ($item): bool {
                $slug = mb_strtolower((string) ($item->slug ?? ''), 'UTF-8');

                if ($slug === 'transition_phan8') {
                    return false;
                }

                return ! str_contains($slug, 'la_so_bat_tu')
                    && ! str_contains($slug, 'lá_số_bát_tự');
            })
            ->map($mapYnghiaItem)
            ->values()
            ->all();

        $fullName = (string) ($validated['full_name'] ?? '');
        $y = (int) $validated['y'];
        $m = (int) $validated['m'];
        $d = (int) $validated['d'];
        $h = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: false);
        $dongChay = null;
        $gioiThieuGrouped = DongChayGioiThieu::getAllGrouped();

        if (! empty($result) && ! empty($result['bat_tu'] ?? null)) {
            $batTu = $result['bat_tu'];
            $thienCanNam = trim((string) ($batTu['year']['can']['thien_can'] ?? ''));
            $thienCanThang = trim((string) ($batTu['month']['can']['thien_can'] ?? ''));
            $thienCanNgay = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));
            $thienCanGio = trim((string) ($batTu['hour']['can']['thien_can'] ?? ''));
            $thapThanNam = trim((string) ($batTu['year']['can']['chu_tinh'] ?? ''));
            $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh'] ?? ''));
            $thapThanNgay = trim((string) ($batTu['day']['can']['chu_tinh'] ?? ''));
            $thapThanGio = trim((string) ($batTu['hour']['can']['chu_tinh'] ?? ''));
            $diaChiNam = trim((string) ($batTu['year']['chi']['dia_chi'] ?? ''));
            $diaChiThang = trim((string) ($batTu['month']['chi']['dia_chi'] ?? ''));
            $diaChiNgay = trim((string) ($batTu['day']['chi']['dia_chi'] ?? ''));
            $diaChiGio = trim((string) ($batTu['hour']['chi']['dia_chi'] ?? ''));
            $canTangNam = $batTu['year']['can_tang'] ?? [];
            $canTangThang = $batTu['month']['can_tang'] ?? [];
            $canTangNgay = $batTu['day']['can_tang'] ?? [];
            $canTangGio = $batTu['hour']['can_tang'] ?? [];

            $thapThanMapThienCan = [
                'Trụ Năm' => $thapThanNam,
                'Trụ Tháng' => $thapThanThang,
                'Trụ Ngày' => $thapThanNgay,
                'Trụ Giờ' => $thapThanGio,
            ];

            $chiNamNorm = $this->normalizeDiaChi($diaChiNam);
            $chiThangNorm = $this->normalizeDiaChi($diaChiThang);
            $chiNgayNorm = $this->normalizeDiaChi($diaChiNgay);
            $chiGioNorm = $this->normalizeDiaChi($diaChiGio);
            $menhNam = (string) (BaZiServiceV2::getMenhDiaChi($chiNamNorm) ?? '');
            $menhThang = (string) (BaZiServiceV2::getMenhDiaChi($chiThangNorm) ?? '');
            $menhNgay = (string) (BaZiServiceV2::getMenhDiaChi($chiNgayNorm) ?? '');
            $menhGio = (string) (BaZiServiceV2::getMenhDiaChi($chiGioNorm) ?? '');
            $ttdcNam = $this->getThapThanDiaChiByMenh($canTangNam, $menhNam);
            $ttdcNam = ($ttdcNam !== null && $ttdcNam !== '') ? $ttdcNam : $this->getThapThanFromTangCanKhiChinh($canTangNam);
            $ttdcThang = $this->getThapThanDiaChiByMenh($canTangThang, $menhThang);
            $ttdcThang = ($ttdcThang !== null && $ttdcThang !== '') ? $ttdcThang : $this->getThapThanFromTangCanKhiChinh($canTangThang);
            $ttdcNgay = $this->getThapThanDiaChiByMenh($canTangNgay, $menhNgay);
            $ttdcNgay = ($ttdcNgay !== null && $ttdcNgay !== '') ? $ttdcNgay : $this->getThapThanFromTangCanKhiChinh($canTangNgay);
            $ttdcGio = $this->getThapThanDiaChiByMenh($canTangGio, $menhGio);
            $ttdcGio = ($ttdcGio !== null && $ttdcGio !== '') ? $ttdcGio : $this->getThapThanFromTangCanKhiChinh($canTangGio);
            $thapThanMapDiaChi = [
                'Trụ Năm' => $ttdcNam ?? '',
                'Trụ Tháng' => $ttdcThang ?? '',
                'Trụ Ngày' => $ttdcNgay ?? '',
                'Trụ Giờ' => $ttdcGio ?? '',
            ];

            if ($unknowBirthtime) {
                $thapThanGio = 'Tỷ Kiên';
                $ttdcGio = 'Tỷ Kiên';
                $thapThanMapThienCan['Trụ Giờ'] = 'Tỷ Kiên';
                $thapThanMapDiaChi['Trụ Giờ'] = 'Tỷ Kiên';
            }

            $replacementData = [
                'thap_than_thien_can_nam'   => $thapThanNam,
                'thap_than_thien_can_thang' => $thapThanThang,
                'thap_than_thien_can_ngay'  => $thapThanNgay,
                'thap_than_thien_can_gio'   => $thapThanGio,
                'thap_than_dia_chi_nam'     => $ttdcNam ?? '',
                'thap_than_dia_chi_thang'   => $ttdcThang ?? '',
                'thap_than_dia_chi_ngay'    => $ttdcNgay ?? '',
                'thap_than_dia_chi_gio'     => $ttdcGio ?? '',
                'thien_can_nam'             => $thienCanNam,
                'thien_can_thang'           => $thienCanThang,
                'thien_can_ngay'            => $thienCanNgay,
                'thien_can_gio'             => $thienCanGio,
                'dia_chi_nam'               => $diaChiNam,
                'dia_chi_thang'             => $diaChiThang,
                'dia_chi_ngay'              => $diaChiNgay,
                'dia_chi_gio'               => $diaChiGio,
            ];

            $getGioiThieu = function (string $tru, bool $hasMqh) use ($gioiThieuGrouped, $replacementData): ?array {
                $g = $gioiThieuGrouped[$tru] ?? null;
                if ($g === null) {
                    return null;
                }
                $raw = (string) ($g['noi_dung'] ?? '');
                $processed = $raw !== ''
                    ? ($hasMqh
                        ? $this->replaceIntroPlaceholders($raw, $replacementData)
                        : $this->removePlaceholderParagraph($raw))
                    : '';

                return $this->buildGioiThieuApiPayload($g, $processed);
            };

            $truNamThang = 'Trụ Năm - Trụ Tháng';
            $truThangNgay = 'Trụ Tháng - Trụ Ngày';
            $truNgayGio = 'Trụ Ngày - Trụ Giờ';

            $canTangGioForCoding = $unknowBirthtime ? [['pho_tinh' => 'Tỷ Kiên', 'can_tang' => '—']] : $canTangGio;
            $namThang = $this->buildCodingLogicForPillars($thienCanNam, $thienCanThang, $thapThanNam, $thapThanThang, $truNamThang);
            $thangNgay = $this->buildCodingLogicForPillars($thienCanThang, $thienCanNgay, $thapThanThang, $thapThanNgay, $truThangNgay);
            $ngayGio = $this->buildCodingLogicForPillars($thienCanNgay, $thienCanGio, $thapThanNgay, $thapThanGio, $truNgayGio, false);
            $namThangDiaChi = $this->buildCodingLogicForPillarsDiaChi($diaChiNam, $diaChiThang, $canTangNam, $canTangThang, $truNamThang);
            $thangNgayDiaChi = $this->buildCodingLogicForPillarsDiaChi($diaChiThang, $diaChiNgay, $canTangThang, $canTangNgay, $truThangNgay);
            $ngayGioDiaChi = $this->buildCodingLogicForPillarsDiaChi($diaChiNgay, $diaChiGio, $canTangNgay, $canTangGioForCoding, $truNgayGio, false);

            $hasBlockWithMqh = static function ($tc, $dc): bool {
                $blocks = array_merge(
                    is_array($tc) ? $tc : [],
                    is_array($dc) ? $dc : []
                );
                foreach ($blocks as $b) {
                    if (is_array($b) && ! empty(trim((string) ($b['moi_quan_he'] ?? '')))) {
                        return true;
                    }
                }
                return false;
            };

            $dongChay = [
                'nam_thang' => [
                    'gioi_thieu' => $getGioiThieu($truNamThang, $hasBlockWithMqh($namThang, $namThangDiaChi)),
                    'thien_can' => $namThang,
                    'dia_chi' => $namThangDiaChi,
                ],
                'thang_ngay' => [
                    'gioi_thieu' => $getGioiThieu($truThangNgay, $hasBlockWithMqh($thangNgay, $thangNgayDiaChi)),
                    'thien_can' => $thangNgay,
                    'dia_chi' => $thangNgayDiaChi,
                ],
                'ngay_gio' => [
                    'gioi_thieu' => $getGioiThieu($truNgayGio, $hasBlockWithMqh($ngayGio, $ngayGioDiaChi)),
                    'thien_can' => $ngayGio,
                    'dia_chi' => $ngayGioDiaChi,
                ],
            ];

        } else {
            $truNamThang = 'Trụ Năm - Trụ Tháng';
            $truThangNgay = 'Trụ Tháng - Trụ Ngày';
            $truNgayGio = 'Trụ Ngày - Trụ Giờ';
            $g = $gioiThieuGrouped;
            $addGt = function (string $tru) use ($g): ?array {
                if (! isset($g[$tru])) {
                    return null;
                }
                $raw = (string) ($g[$tru]['noi_dung'] ?? '');
                $processed = $raw !== '' ? $this->removePlaceholderParagraph($raw) : '';

                return $this->buildGioiThieuApiPayload($g[$tru], $processed);
            };
            $dongChay = [
                'nam_thang' => [
                    'gioi_thieu' => $addGt($truNamThang),
                    'thien_can' => null,
                    'dia_chi' => null,
                ],
                'thang_ngay' => [
                    'gioi_thieu' => $addGt($truThangNgay),
                    'thien_can' => null,
                    'dia_chi' => null,
                ],
                'ngay_gio' => [
                    'gioi_thieu' => $addGt($truNgayGio),
                    'thien_can' => null,
                    'dia_chi' => null,
                ],
            ];
        }

        return [
            'y_nghia_tu_tru' => $yNghiaTuTru,
            'transition_phan8' => $transitionPhan8,
            'la_so_bat_tu' => Phan6LaSoBatTu::getForApi(),
            'dong_chay' => $dongChay,
            'image_map' => \App\Services\DocxTextService::imageMapForPhan6(),
        ];
    }

    /**
     * Lấy năng lượng trong lá số (PHẦN 6): Ý nghĩa tứ trụ + Dòng chảy năng lượng Mã 2,3,4.
     */
    public function nangLuongTrongLaSo(Request $req): JsonResponse
    {
        $data = $this->buildPhan6ApiDataFromRequest($req);
        if ($data === null) {
            return response()->json(['data' => null], 422);
        }

        return response()->json(['data' => \App\Services\Phan6ContentService::stripImagesFromApiData($data)]);
    }

    /**
     * Lấy nội dung Sự nghiệp theo Thập Thần Thiên Can Tháng/Năm.
     * API riêng: GET /api/phan-5/su-nghiep
     */
    public function suNghiep(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;

        $rules = [
            'full_name' => 'nullable|string|max:255',
            'y' => 'required|numeric|min:1940|max:2031',
            'm' => 'required|numeric|min:1|max:12',
            'd' => 'required|numeric|min:1|max:31',
            'g' => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];

        if (! $unknowBirthtime) {
            $rules['h'] = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h'] = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }

        $validated = $req->validate($rules);

        $fullName = (string) ($validated['full_name'] ?? '');
        $y = (int) $validated['y'];
        $m = (int) $validated['m'];
        $d = (int) $validated['d'];
        $h = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: false);
        if (empty($result) || empty($result['bat_tu'] ?? null)) {
            return response()->json([
                'su_nghiep_thang' => null,
                'su_nghiep_nam' => null,
            ]);
        }

        $chatLuongThapThan = $result['chat_luong_thap_than'] ?? null;
        if ($req->filled('chat_luong_thap_than')) {
            $decoded = json_decode($req->string('chat_luong_thap_than'), true);
            if (is_array($decoded)) {
                $chatLuongThapThan = $decoded;
            }
        }

        $response = $this->buildPhan5PayloadFromBatTu($result['bat_tu'], $unknowBirthtime, [
            'chat_luong_thap_than' => $chatLuongThapThan,
        ]);

        if (! $req->boolean('include_bat_tu')) {
            unset($response['bat_tu']);
        }

        return response()->json($response);
    }

    /**
     * @param  array<string, mixed>  $batTu
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function buildPhan5PayloadFromBatTu(array $batTu, bool $unknowBirthtime = false, array $extra = []): array
    {
        $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh'] ?? ''));
        $thapThanNam = trim((string) ($batTu['year']['can']['chu_tinh'] ?? ''));
        $thapThanGio = trim((string) ($batTu['hour']['can']['chu_tinh'] ?? ''));
        $thapThanNgay = trim((string) ($batTu['day']['can']['chu_tinh'] ?? ''));
        $thienCanThang = trim((string) ($batTu['month']['can']['thien_can'] ?? ''));
        $thienCanNam = trim((string) ($batTu['year']['can']['thien_can'] ?? ''));

        $diaChiGio = trim((string) ($batTu['hour']['chi']['dia_chi'] ?? ''));
        $diaChiThang = trim((string) ($batTu['month']['chi']['dia_chi'] ?? ''));
        $chiGioNorm = $this->normalizeDiaChi($diaChiGio);
        $chiThangNorm = $this->normalizeDiaChi($diaChiThang);
        $menhGio = (string) (BaZiServiceV2::getMenhDiaChi($chiGioNorm) ?? '');
        $menhThang = (string) (BaZiServiceV2::getMenhDiaChi($chiThangNorm) ?? '');

        if ($unknowBirthtime) {
            $phatTrienThienCanGio = $this->buildPhatTrienBanThanFromThapThan('Tỷ Kiên', 'Trụ Giờ', 'Thiên Can');
            $baseTangCanGio = $this->buildPhatTrienBanThanFromThapThan('Tỷ Kiên', 'Trụ Giờ', 'Tàng Can');
            $phatTrienTangCanGio = $baseTangCanGio !== null ? [
                'can_tang' => null,
                'thap_than' => $baseTangCanGio['thap_than'] ?? 'Tỷ Kiên',
                'sections' => $baseTangCanGio['sections'] ?? [],
            ] : null;
            $baseQueryGio = ThapThanTheoViTri::query()
                ->where('thap_than', 'Tỷ Kiên')
                ->where('vi_tri', 'Trụ Giờ')
                ->where('loai_can', 'Tàng Can')
                ->orderBy('sort_order');
            $mergeContents = static function ($q): ?string {
                $records = $q->get();
                return $records->isEmpty() ? null : $records->pluck('content')->filter()->implode("\n\n");
            };
            $taiChinhGio = [[
                'can_tang' => null,
                'thap_than' => 'Tỷ Kiên',
                'tai_chinh' => $mergeContents((clone $baseQueryGio)->where('khia_canh', 'Tài Chính')),
                'moi_quan_he_xa_hoi' => $mergeContents((clone $baseQueryGio)->where('khia_canh', 'Mối quan hệ xã hội')),
            ]];
        } else {
            $taiChinhGio = $this->buildTaiChinhForCanTang($batTu['hour']['can_tang'] ?? [], 'Trụ Giờ', $menhGio);
            $phatTrienThienCanGio = $this->buildPhatTrienBanThanFromThapThan($thapThanGio, 'Trụ Giờ', 'Thiên Can');
            $phatTrienTangCanGio = $this->buildPhatTrienBanThanFromTangCanGio($batTu['hour']['can_tang'] ?? [], $menhGio);
        }
        $taiChinhThang = $this->buildTaiChinhForCanTang($batTu['month']['can_tang'] ?? [], 'Trụ Tháng', $menhThang);
        $tinhCamNgay = $this->buildTinhCamForCanTangNgay($batTu['day']['can_tang'] ?? []);

        $ketNoiXaHoiThienCanNam = $this->buildKetNoiXaHoiFromThapThan($thapThanNam, 'Trụ Năm', 'Thiên Can');
        $ketNoiXaHoiTangCanNam = $this->buildKetNoiXaHoiFromTangCanNam($batTu['year']['can_tang'] ?? []);

        $suNghiepThang = $this->buildSuNghiepFor($thapThanThang, 'Trụ Tháng');
        $suNghiepNam = $this->buildSuNghiepFor($thapThanNam, 'Trụ Năm');

        $chatLuongRaw = $extra['chat_luong_thap_than'] ?? null;
        $chatLuongBanMenhLonHonKhong = is_array($chatLuongRaw)
            ? array_values(array_filter($chatLuongRaw, static fn ($item): bool => ((int) ($item['natal'] ?? 0)) > 0))
            : [];

        $sucKhoeHyKyThan = null;
        $thapThanCoBanMenhLonHonKhong = $this->getThapThanCoBanMenhLonHonKhong($chatLuongBanMenhLonHonKhong);
        $dayStem = $batTu['day']['can']['thien_can'] ?? null;
        $monthBranch = $batTu['month']['chi']['dia_chi'] ?? null;
        if (is_string($dayStem) && $dayStem !== '' && is_string($monthBranch) && $monthBranch !== '') {
            $hyKyThanModel = HyKyThan::findByThienCanDiaChi($dayStem, $monthBranch);
            if ($hyKyThanModel) {
                $hyThanNguHanh = (string) ($hyKyThanModel->hy_than_ngu_hanh ?? '');
                $kyThanNguHanh = (string) ($hyKyThanModel->ky_than_ngu_hanh ?? '');
                if ($hyThanNguHanh !== '' || $kyThanNguHanh !== '') {
                    $sucKhoeHyKyThan = $this->buildSucKhoeHyKyThan($batTu, $hyThanNguHanh, $kyThanNguHanh, $unknowBirthtime, $thapThanCoBanMenhLonHonKhong);
                    if ($sucKhoeHyKyThan !== null) {
                        $sucKhoeHyKyThan['thien_can_ngay'] = is_string($dayStem) ? $dayStem : null;
                        $sucKhoeHyKyThan['thap_than_ngay'] = $thapThanNgay !== '' ? $thapThanNgay : null;
                    }
                }
            }
        }

        $response = [
            'thien_can_thang' => $thienCanThang,
            'thien_can_nam' => $thienCanNam,
            'thap_than_thang' => $thapThanThang,
            'thap_than_nam' => $thapThanNam,
            'tai_chinh_tang_can' => [
                'gio' => $taiChinhGio,
                'thang' => $taiChinhThang,
            ],
            'tinh_cam_tang_can_ngay' => $tinhCamNgay,
            'phat_trien_ban_than' => [
                'thien_can_gio' => $phatTrienThienCanGio,
                'tang_can_gio' => $phatTrienTangCanGio,
            ],
            'ket_noi_xa_hoi' => [
                'thien_can_nam' => $ketNoiXaHoiThienCanNam,
                'tang_can_nam' => $ketNoiXaHoiTangCanNam,
            ],
            'su_nghiep_thang' => $suNghiepThang,
            'su_nghiep_nam' => $suNghiepNam,
            'suc_khoe' => $sucKhoeHyKyThan,
        ];
        $response['giai_phap_thap_than'] = $this->buildGiaiPhapThapThanFromResponse($response, $chatLuongBanMenhLonHonKhong);
        $response['khia_canh'] = $this->phan5KhiaCanh->buildKhiaCanhBlocks($response);
        $response['bat_tu'] = $batTu;

        return $response;
    }

    public function codingLogicNamThang(Request $req): JsonResponse
    {
        return $this->nangLuongTrongLaSo($req);
    }

    /**
     * API riêng PHẦN 7: Bài học cuộc sống.
     * Mục I: trả toàn bộ nội dung từ phan7_tam_the (PHẦN 7 - I.xlsx).
     * Mục II: lọc nội dung phan7_bai_hoc theo % Thập Thần (natal) của từng người.
     */
    public function baiHocCuocSong(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $rules = [
            'full_name' => 'nullable|string|max:255',
            'y' => 'required|numeric|min:1940|max:2031',
            'm' => 'required|numeric|min:1|max:12',
            'd' => 'required|numeric|min:1|max:31',
            'g' => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];
        if (! $unknowBirthtime) {
            $rules['h'] = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h'] = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }
        $validated = $req->validate($rules);
        $fullName = (string) ($validated['full_name'] ?? '');
        $y = (int) $validated['y'];
        $m = (int) $validated['m'];
        $d = (int) $validated['d'];
        $h = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: true);
        $bieuDoNguHanh = $result['bieu_do_ngu_hanh'] ?? [];
        $diemLucThanAgg = [];
        $rowsNguHanh = is_array($bieuDoNguHanh) ? $bieuDoNguHanh : [];
        foreach ($rowsNguHanh as $row) {
            $lucThan = trim((string) ($row['luc_than'] ?? ''));
            if ($lucThan === '') {
                continue;
            }
            $diem = (int) ($row['diem_ngu_hanh'] ?? 0);
            if (! isset($diemLucThanAgg[$lucThan])) {
                $diemLucThanAgg[$lucThan] = ['sum' => 0, 'count' => 0];
            }
            $diemLucThanAgg[$lucThan]['sum'] += $diem;
            $diemLucThanAgg[$lucThan]['count']++;
        }
        $diemLucThan = [];
        foreach ($diemLucThanAgg as $lucThan => $agg) {
            $count = max(1, (int) ($agg['count'] ?? 1));
            $diemLucThan[$lucThan] = (int) round(($agg['sum'] ?? 0) / $count);
        }

        $mapTamTheRow = static fn ($r) => [
            'thu_tu'   => $r->thu_tu,
            'noi_dung' => $r->noi_dung,
            'image'    => $r->image ? asset($r->image) : null,
        ];

        // ── Mục I: sheet 1 của PHẦN 7 - I.xlsx ──
        $muc1 = Phan7TamThe::getAllOrdered(0)->map($mapTamTheRow)->all();

        // ── Đoạn nối (sheet 2): hiển thị cuối Phần 7, sau Mục II ──
        $muc1Cuoi = Phan7TamThe::getAllOrdered(1)->map($mapTamTheRow)->all();

        // ── Mục II: lọc phan7_bai_hoc theo % Thập Thần ──
        $mapThapThanToLucThan = [
            'HUYNH ĐỆ' => 'Huynh Đệ',
            'TỬ TÔN'   => 'Tử Tôn',
            'QUAN QUỶ' => 'Quan Quỷ',
            'THÊ TÀI'  => 'Thê Tài',
            'PHỤ MẪU'  => 'Phụ Mẫu',
        ];

        // Ảnh minh họa cho từng Thập Thần (hiện dưới mục a. Bản chất năng lượng)
        $mapThapThanToImage = [
            'HUYNH ĐỆ' => asset('images/phan-7/huynh-de.png'),
            'TỬ TÔN'   => asset('images/phan-7/tu-ton.png'),
            'QUAN QUỶ' => asset('images/phan-7/quan-quy.png'),
            'THÊ TÀI'  => asset('images/phan-7/the-tai.png'),
            'PHỤ MẪU'  => asset('images/phan-7/phu-mau.png'),
        ];

        $muc2 = [];
        foreach (Phan7BaiHoc::THAP_THAN_ORDER as $thapThan) {
            $lucThanKey = $mapThapThanToLucThan[$thapThan] ?? null;
            $diem = $lucThanKey !== null ? ($diemLucThan[$lucThanKey] ?? 0) : 0;

            $rows = Phan7BaiHoc::getByThapThan($thapThan);

            // Tìm ten_truong_hop khớp với bucket % của người dùng
            $matchedTruongHop = null;
            foreach ($rows as $r) {
                $bucket = $this->parseBucketFromTenTruongHop($r->ten_truong_hop ?? '');
                if ($bucket !== null && $diem >= $bucket['min'] && $diem <= $bucket['max']) {
                    $matchedTruongHop = $r->ten_truong_hop;
                    break;
                }
            }

            if ($matchedTruongHop === null) {
                continue;
            }

            // Lấy tất cả dòng thuộc trường hợp khớp, group theo tieu_de
            $matchedRows = $rows->filter(fn ($r) => $r->ten_truong_hop === $matchedTruongHop);
            $grouped = [];
            foreach ($matchedRows as $r) {
                $tieuDe = $r->tieu_de ?? '';
                $grouped[$tieuDe][] = $r->noi_dung;
            }

            $noiDungList = [];
            foreach ($grouped as $tieuDe => $lines) {
                $noiDungList[] = [
                    'tieu_de' => $tieuDe,
                    'lines'   => $lines,
                ];
            }

            $muc2[] = [
                'thap_than'      => $thapThan,
                'ten_truong_hop' => $matchedTruongHop,
                'image'          => $mapThapThanToImage[$thapThan] ?? null,
                'noi_dung'       => $noiDungList,
            ];
        }

        return response()->json([
            'muc_1'      => $muc1,
            'muc_2'      => $muc2,
            'muc_1_cuoi' => $muc1Cuoi,
        ]);
    }

    /**
     * PHẦN 8 – Đại Vận: mối quan hệ Thiên Can / Địa Chi Trụ Đại Vận hiện tại với 4 Trụ lá số.
     * Dùng lại hoàn toàn buildCodingLogicForPillars / buildCodingLogicForPillarsDiaChi với tru_tuong_tac = null.
     */
    public function daiVan(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $rules = [
            'full_name'       => 'nullable|string|max:255',
            'y'               => 'required|numeric|min:1940|max:2031',
            'm'               => 'required|numeric|min:1|max:12',
            'd'               => 'required|numeric|min:1|max:31',
            'g'               => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];
        if (! $unknowBirthtime) {
            $rules['h']      = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h']      = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }
        $validated  = $req->validate($rules);
        $fullName   = (string) ($validated['full_name'] ?? '');
        $y          = (int) $validated['y'];
        $m          = (int) $validated['m'];
        $d          = (int) $validated['d'];
        $h          = isset($validated['h'])      && $validated['h']      !== null ? (int) $validated['h']      : null;
        $minute     = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g          = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: false);

        if (empty($result) || empty($result['bat_tu'] ?? null)) {
            return response()->json(['data' => null]);
        }

        $batTu      = $result['bat_tu'];
        $bangDaiVan = $result['bang_dai_van'] ?? [];

        // --- Thiên Can / Địa Chi / Tàng Can của 4 Trụ ---
        $thienCanNam   = trim((string) ($batTu['year']['can']['thien_can']   ?? ''));
        $thienCanThang = trim((string) ($batTu['month']['can']['thien_can']  ?? ''));
        $thienCanNgay  = trim((string) ($batTu['day']['can']['thien_can']    ?? ''));
        $thienCanGio   = trim((string) ($batTu['hour']['can']['thien_can']   ?? ''));

        $thapThanNam   = trim((string) ($batTu['year']['can']['chu_tinh']    ?? ''));
        $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh']   ?? ''));
        $thapThanNgay  = trim((string) ($batTu['day']['can']['chu_tinh']     ?? ''));
        $thapThanGio   = trim((string) ($batTu['hour']['can']['chu_tinh']    ?? ''));

        $diaChiNam     = trim((string) ($batTu['year']['chi']['dia_chi']     ?? ''));
        $diaChiThang   = trim((string) ($batTu['month']['chi']['dia_chi']    ?? ''));
        $diaChiNgay    = trim((string) ($batTu['day']['chi']['dia_chi']      ?? ''));
        $diaChiGio     = trim((string) ($batTu['hour']['chi']['dia_chi']     ?? ''));

        $canTangNam    = $batTu['year']['can_tang']  ?? [];
        $canTangThang  = $batTu['month']['can_tang'] ?? [];
        $canTangNgay   = $batTu['day']['can_tang']   ?? [];
        $canTangGio    = $batTu['hour']['can_tang']  ?? [];

        if ($unknowBirthtime) {
            $thapThanGio   = 'Tỷ Kiên';
            $canTangGio    = [['pho_tinh' => 'Tỷ Kiên', 'can_tang' => '—']];
        }

        // --- Tìm Đại Vận hiện tại (tuổi khởi từ bảng bang_dai_van lá số) ---
        $currentDV = $this->findCurrentDaiVan($bangDaiVan, $y);

        if ($currentDV === null) {
            return response()->json([
                'data' => [
                    'current_dai_van' => null,
                    'bang_dai_van'    => $bangDaiVan,
                    'nam'             => null,
                    'thang'           => null,
                    'ngay'            => null,
                    'gio'             => null,
                ],
            ]);
        }

        // --- Trích xuất thông tin Đại Vận hiện tại ---
        $thienCanDV  = trim((string) ($currentDV['can']['thien_can'] ?? ''));
        $thapThanDV  = trim((string) ($currentDV['can']['chu_tinh']  ?? ''));
        $diaChiDV    = trim((string) ($currentDV['chi']['dia_chi']   ?? ''));
        $canTangDV   = $currentDV['cantang'] ?? [];

        $menhDiaChiDV  = (string) (BaZiServiceV2::getMenhDiaChi($this->normalizeDiaChi($diaChiDV)) ?? '');
        $thapThanDV_DC = $this->getThapThanDiaChiByMenh($canTangDV, $menhDiaChiDV);
        $thapThanDV_DC = ($thapThanDV_DC !== null && $thapThanDV_DC !== '')
            ? $thapThanDV_DC
            : $this->getThapThanFromTangCanKhiChinh($canTangDV);

        // --- Lấy nội dung giới thiệu từ dong_chay_gioi_thieu ---
        $gioiThieuAll = DongChayGioiThieu::whereIn('tru_loai', [
            'dai_van_y_nghia',
            'dai_van_tru_nam', 'dai_van_tru_thang', 'dai_van_tru_ngay', 'dai_van_tru_gio',
        ])->get()->keyBy('tru_loai');

        $getGt = static function (string $key) use ($gioiThieuAll): ?array {
            $row = $gioiThieuAll->get($key);
            if ($row === null) {
                return null;
            }
            $nd = trim((string) ($row->noi_dung ?? ''));
            return $nd !== '' ? ['noi_dung' => $nd] : null;
        };

        // --- Build blocks cho từng Trụ (tru_tuong_tac = null → bỏ qua filter) ---
        $buildTru = function (
            string $thienCanTru,
            string $thapThanTru,
            string $diaChiTru,
            array  $canTangTru,
            string $viTriTru,
            string $gioiThieuKey
        ) use ($thienCanDV, $thapThanDV, $thapThanDV_DC, $diaChiDV, $canTangDV, $getGt): array {
            $tc = $this->buildCodingLogicForPillars(
                $thienCanDV, $thienCanTru,
                $thapThanDV, $thapThanTru,
                null,
                true,
                $viTriTru
            );
            $dc = $this->buildCodingLogicForPillarsDiaChi(
                $diaChiDV, $diaChiTru,
                $canTangDV, $canTangTru,
                null,
                true,
                $viTriTru,
                $thapThanDV_DC
            );

            return [
                'gioi_thieu' => $getGt($gioiThieuKey),
                'thien_can'  => $tc,
                'dia_chi'    => $dc,
            ];
        };

        return response()->json([
            'data' => [
                'current_dai_van' => [
                    'age'       => (int) ($currentDV['age'] ?? 0),
                    'thien_can' => $thienCanDV,
                    'dia_chi'   => $diaChiDV,
                    'thap_than_thien_can' => $thapThanDV,
                    'thap_than_dia_chi'   => $thapThanDV_DC,
                    'can_tang'  => $canTangDV,
                ],
                'bang_dai_van' => $bangDaiVan,
                'y_nghia' => $getGt('dai_van_y_nghia'),
                'nam'   => $buildTru($thienCanNam,   $thapThanNam,   $diaChiNam,   $canTangNam,   'Trụ Năm',   'dai_van_tru_nam'),
                'thang' => $buildTru($thienCanThang, $thapThanThang, $diaChiThang, $canTangThang, 'Trụ Tháng', 'dai_van_tru_thang'),
                'ngay'  => $buildTru($thienCanNgay,  $thapThanNgay,  $diaChiNgay,  $canTangNgay,  'Trụ Ngày',  'dai_van_tru_ngay'),
                'gio'   => $buildTru($thienCanGio,   $thapThanGio,   $diaChiGio,   $canTangGio,   'Trụ Giờ',   'dai_van_tru_gio'),
            ],
        ]);
    }

    /**
     * PHẦN 8 – Niên Vận: mối quan hệ Thiên Can / Địa Chi Niên Vận (năm hiện tại + năm tiếp theo)
     * với 4 Trụ lá số. Dùng lại buildCodingLogicForPillars / buildCodingLogicForPillarsDiaChi
     * với tru_tuong_tac = null, giống hệt daiVan().
     */
    public function nienVan(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $phanBan = $req->input('phan_ban') === '8b' ? '8b' : '8a';
        $rules = [
            'full_name'       => 'nullable|string|max:255',
            'y'               => 'required|numeric|min:1940|max:2031',
            'm'               => 'required|numeric|min:1|max:12',
            'd'               => 'required|numeric|min:1|max:31',
            'g'               => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
            'phan_ban'        => 'nullable|string|in:8a,8b',
        ];
        if (! $unknowBirthtime) {
            $rules['h']      = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h']      = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }
        $validated  = $req->validate($rules);
        $fullName   = (string) ($validated['full_name'] ?? '');
        $y          = (int) $validated['y'];
        $m          = (int) $validated['m'];
        $d          = (int) $validated['d'];
        $h          = isset($validated['h'])      && $validated['h']      !== null ? (int) $validated['h']      : null;
        $minute     = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g          = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: false);

        if (empty($result) || empty($result['bat_tu'] ?? null)) {
            return response()->json(['data' => null]);
        }

        $batTu   = $result['bat_tu'];
        $nienVan = $result['nien_van'] ?? [];

        // nien_van[0]=năm trước, [1]=năm hiện tại, [2]=năm tiếp theo
        $nvHienTai  = $nienVan[1] ?? null;
        $nvTiepTheo = $nienVan[2] ?? null;

        if ($nvHienTai === null && $nvTiepTheo === null) {
            return response()->json(['data' => null]);
        }

        // --- Thiên Can / Địa Chi / Tàng Can của 4 Trụ lá số ---
        $thienCanNam   = trim((string) ($batTu['year']['can']['thien_can']  ?? ''));
        $thienCanThang = trim((string) ($batTu['month']['can']['thien_can'] ?? ''));
        $thienCanNgay  = trim((string) ($batTu['day']['can']['thien_can']   ?? ''));
        $thienCanGio   = trim((string) ($batTu['hour']['can']['thien_can']  ?? ''));

        $thapThanNam   = trim((string) ($batTu['year']['can']['chu_tinh']   ?? ''));
        $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh']  ?? ''));
        $thapThanNgay  = trim((string) ($batTu['day']['can']['chu_tinh']    ?? ''));
        $thapThanGio   = trim((string) ($batTu['hour']['can']['chu_tinh']   ?? ''));

        $diaChiNam     = trim((string) ($batTu['year']['chi']['dia_chi']    ?? ''));
        $diaChiThang   = trim((string) ($batTu['month']['chi']['dia_chi']   ?? ''));
        $diaChiNgay    = trim((string) ($batTu['day']['chi']['dia_chi']     ?? ''));
        $diaChiGio     = trim((string) ($batTu['hour']['chi']['dia_chi']    ?? ''));

        $canTangNam    = $batTu['year']['can_tang']  ?? [];
        $canTangThang  = $batTu['month']['can_tang'] ?? [];
        $canTangNgay   = $batTu['day']['can_tang']   ?? [];
        $canTangGio    = $batTu['hour']['can_tang']  ?? [];

        if ($unknowBirthtime) {
            $thapThanGio = 'Tỷ Kiên';
            $canTangGio  = [['pho_tinh' => 'Tỷ Kiên', 'can_tang' => '—']];
        }

        // --- Lấy nội dung giới thiệu ---
        $gioiThieuKeys = $phanBan === '8b'
            ? [
                'nien_van_8b_y_nghia',
                'nien_van_8b_tiep_theo_tru_nam', 'nien_van_8b_tiep_theo_tru_thang',
                'nien_van_8b_tiep_theo_tru_ngay', 'nien_van_8b_tiep_theo_tru_gio',
            ]
            : [
                'nien_van_y_nghia',
                'nien_van_hien_tai', 'nien_van_hien_tai_tru_nam', 'nien_van_hien_tai_tru_thang',
                'nien_van_hien_tai_tru_ngay', 'nien_van_hien_tai_tru_gio',
                'nien_van_tiep_theo', 'nien_van_tiep_theo_tru_nam', 'nien_van_tiep_theo_tru_thang',
                'nien_van_tiep_theo_tru_ngay', 'nien_van_tiep_theo_tru_gio',
            ];
        $gioiThieuAll = DongChayGioiThieu::whereIn('tru_loai', $gioiThieuKeys)->get()->keyBy('tru_loai');

        $getGt = static function (string $key) use ($gioiThieuAll): ?array {
            $row = $gioiThieuAll->get($key);
            if ($row === null) {
                return null;
            }
            $nd = trim((string) ($row->noi_dung ?? ''));
            return $nd !== '' ? ['noi_dung' => $nd] : null;
        };

        $replaceNvPlaceholders = function (string $text, int $namNumber): string {
            return $this->replaceNienVanPlaceholders($text, $namNumber);
        };

        // --- Helper: build blocks cho một Niên Vận item vs 4 Trụ ---
        $buildNienVanItem = function (?array $nvItem, string $gioiThieuPrefix) use (
            $thienCanNam, $thapThanNam, $diaChiNam, $canTangNam,
            $thienCanThang, $thapThanThang, $diaChiThang, $canTangThang,
            $thienCanNgay, $thapThanNgay, $diaChiNgay, $canTangNgay,
            $thienCanGio, $thapThanGio, $diaChiGio, $canTangGio,
            $getGt, $replaceNvPlaceholders
        ): ?array {
            if ($nvItem === null) {
                return null;
            }

            $namNumber  = (int) ($nvItem['nam'] ?? 0);
            $thienCanNV = trim((string) ($nvItem['can']['thien_can'] ?? ''));
            $thapThanNV = trim((string) ($nvItem['can']['chu_tinh']  ?? ''));
            $diaChiNV   = trim((string) ($nvItem['chi']['dia_chi']   ?? ''));
            $canTangNV  = $nvItem['cantang'] ?? [];

            $menhDiaChiNV  = (string) (BaZiServiceV2::getMenhDiaChi($this->normalizeDiaChi($diaChiNV)) ?? '');
            $thapThanNV_DC = $this->getThapThanDiaChiByMenh($canTangNV, $menhDiaChiNV);
            $thapThanNV_DC = ($thapThanNV_DC !== null && $thapThanNV_DC !== '')
                ? $thapThanNV_DC
                : $this->getThapThanFromTangCanKhiChinh($canTangNV);

            // Lấy gioi_thieu và thay placeholder năm / trụ
            $formatGioiThieu = function (string $key) use ($getGt, $replaceNvPlaceholders, $namNumber): ?array {
                $gt = $getGt($key);
                if ($gt === null) {
                    return null;
                }

                $nd = $replaceNvPlaceholders($gt['noi_dung'], $namNumber);
                $nd = $this->renumberPhan8DisplayLabels($nd);
                $nd = $this->filterPhan8TruTemplateBoilerplate($nd);

                return ['noi_dung' => $nd];
            };

            $buildTru = function (
                string $thienCanTru,
                string $thapThanTru,
                string $diaChiTru,
                array  $canTangTru,
                string $viTriTru,
                string $truGtKey
            ) use ($thienCanNV, $thapThanNV, $thapThanNV_DC, $diaChiNV, $canTangNV, $formatGioiThieu): array {
                // DC Thập Thần của Trụ (dùng cho replace placeholder trong gioi_thieu)
                $menhDcTru     = (string) (BaZiServiceV2::getMenhDiaChi($this->normalizeDiaChi($diaChiTru)) ?? '');
                $thapThanTruDC = $this->getThapThanDiaChiByMenh($canTangTru, $menhDcTru)
                               ?: $this->getThapThanFromTangCanKhiChinh($canTangTru);

                $tc = $this->buildCodingLogicForPillars(
                    $thienCanNV, $thienCanTru,
                    $thapThanNV, $thapThanTru,
                    null,
                    true,
                    $viTriTru
                );
                $dc = $this->buildCodingLogicForPillarsDiaChi(
                    $diaChiNV, $diaChiTru,
                    $canTangNV, $canTangTru,
                    null,
                    true,
                    $viTriTru,
                    $thapThanNV_DC
                );

                $gt = $formatGioiThieu($truGtKey);
                if ($gt !== null) {
                    $nd = $gt['noi_dung'];

                    // Replace [thap_than] theo thứ tự: NV-TC → Tru-TC → NV-DC → Tru-DC
                    $thapSeq = [$thapThanNV, $thapThanTru, $thapThanNV_DC, $thapThanTruDC];
                    $tidx    = 0;
                    $nd = preg_replace_callback('/\[thap_than\]/u', static function () use (&$tidx, $thapSeq) {
                        return $thapSeq[$tidx++] ?? '[thap_than]';
                    }, $nd);

                    // Replace [thien_can/dia_chi] theo thứ tự: TC → TC → DC → DC
                    $tcDcSeq = ['Thiên Can', 'Thiên Can', 'Địa Chi', 'Địa Chi'];
                    $cidx    = 0;
                    $nd = preg_replace_callback('/\[thien_can\/dia_chi\]/u', static function () use (&$cidx, $tcDcSeq) {
                        return $tcDcSeq[$cidx++] ?? 'Thiên Can/Địa Chi';
                    }, $nd);

                    $gt = ['noi_dung' => $nd];
                }

                return ['gioi_thieu' => $gt, 'thien_can' => $tc, 'dia_chi' => $dc];
            };

            return [
                'nam_number'          => $namNumber,
                'thien_can'           => $thienCanNV,
                'dia_chi'             => $diaChiNV,
                'thap_than_thien_can' => $thapThanNV,
                'thap_than_dia_chi'   => $thapThanNV_DC,
                'gioi_thieu'          => null,
                'nam'   => $buildTru($thienCanNam,   $thapThanNam,   $diaChiNam,   $canTangNam,   'Trụ Năm',   $gioiThieuPrefix . '_tru_nam'),
                'thang' => $buildTru($thienCanThang, $thapThanThang, $diaChiThang, $canTangThang, 'Trụ Tháng', $gioiThieuPrefix . '_tru_thang'),
                'ngay'  => $buildTru($thienCanNgay,  $thapThanNgay,  $diaChiNgay,  $canTangNgay,  'Trụ Ngày',  $gioiThieuPrefix . '_tru_ngay'),
                'gio'   => $buildTru($thienCanGio,   $thapThanGio,   $diaChiGio,   $canTangGio,   'Trụ Giờ',   $gioiThieuPrefix . '_tru_gio'),
            ];
        };

        if ($phanBan === '8b') {
            $yNghia = $getGt('nien_van_8b_y_nghia');
            if ($yNghia !== null && $nvTiepTheo !== null) {
                $nd = $replaceNvPlaceholders(
                    $yNghia['noi_dung'],
                    (int) ($nvTiepTheo['nam'] ?? 0)
                );
                $yNghia['noi_dung'] = $this->renumberPhan8DisplayLabels($nd);
            }

            return response()->json([
                'data' => [
                    'y_nghia'   => $yNghia,
                    'hien_tai'  => null,
                    'tiep_theo' => Phan8TruSectionService::normalizeNienVanItem(
                        $buildNienVanItem($nvTiepTheo, 'nien_van_8b_tiep_theo')
                    ),
                ],
            ]);
        }

        $yNghia = $getGt('nien_van_y_nghia');
        if ($yNghia !== null && $nvHienTai !== null) {
            $nd = $replaceNvPlaceholders(
                $yNghia['noi_dung'],
                (int) ($nvHienTai['nam'] ?? 0)
            );
            $yNghia['noi_dung'] = $this->renumberPhan8DisplayLabels($nd);
        }

        return response()->json([
            'data' => [
                'y_nghia'   => $yNghia,
                'hien_tai'  => Phan8TruSectionService::normalizeNienVanItem(
                    $buildNienVanItem($nvHienTai, 'nien_van_hien_tai')
                ),
                'tiep_theo' => Phan8TruSectionService::normalizeNienVanItem(
                    $buildNienVanItem($nvTiepTheo, 'nien_van_tiep_theo')
                ),
            ],
        ]);
    }

    /**
     * Thay placeholder trong giới thiệu Niên Vận (PHẦN 8A): năm hiện tại/tiếp theo, Trụ Năm/Tháng/Ngày/Giờ.
     */
    protected function replaceNienVanPlaceholders(string $text, int $namNumber): string
    {
        if ($text === '') {
            return $text;
        }

        $year = $namNumber > 0 ? (string) $namNumber : (string) date('Y');

        $text = str_replace(
            [
                '[hien_tai]', '[hien_taiI]', '[HIỆN TẠI]', '[NĂM HIỆN TẠI]',
                '[TIẾP THEO]', '[NĂM TIẾP THEO]', '[tiep_theo]',
            ],
            $year,
            $text
        );

        $truPatterns = [
            '/Trụ\s*\[NĂM\]/ui'   => 'Trụ Năm',
            '/Trụ\s*\[THÁNG\]/ui' => 'Trụ Tháng',
            '/Trụ\s*\[NGÀY\]/ui'  => 'Trụ Ngày',
            '/Trụ\s*\[GIỜ\]/ui'   => 'Trụ Giờ',
            '/\[NĂM\]/ui'         => 'Năm',
            '/\[THÁNG\]/ui'       => 'Tháng',
            '/\[NGÀY\]/ui'        => 'Ngày',
            '/\[GIỜ\]/ui'         => 'Giờ',
        ];

        foreach ($truPatterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return $text;
    }

    /**
     * Đổi số La Mã đầu dòng (I./II./III.) thành 1., 2., 3. trong khối hiển thị web (không copy số từ file Excel).
     */
    protected function renumberPhan8DisplayLabels(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $index = 0;
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $romanPattern = '/^(\s*)(I{1,3}|IV|VI{0,3}|IX|X{1,3}(?:I{1,3})?)\.\s+/u';

        foreach ($lines as $i => $line) {
            if (preg_match($romanPattern, $line, $m)) {
                $index++;
                $lines[$i] = preg_replace(
                    $romanPattern,
                    $m[1] . $index . '. ',
                    $line,
                    1
                );
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Bỏ dòng mẫu bảng Excel (Thiên Can Niên Vận / Nếu có Hợp…) khỏi giới thiệu từng Trụ.
     */
    protected function filterPhan8TruTemplateBoilerplate(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $kept = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $t = trim($line);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^(Thiên Can|Địa Chi)\s+(Niên Vận|Năm|Tháng|Ngày|Giờ)\s*$/ui', $t)) {
                continue;
            }
            if (preg_match('/^Nếu có Hợp\s*\/\s*Khắc/ui', $t)) {
                continue;
            }
            if (preg_match('/^Nếu có Hợp\s*\/\s*Xung/ui', $t)) {
                continue;
            }
            if (preg_match('/^Thập Thần\s+ở\s+(Thiên Can|Địa Chi)\s+Trụ/ui', $t)) {
                continue;
            }
            $kept[] = $line;
        }

        return implode("\n", $kept);
    }

    /**
     * PHẦN 8 - III: Dự báo các khía cạnh cuộc sống.
     * Dữ liệu lấy từ bảng phan8_du_bao_khia_canh, điều kiện đối chiếu bằng diem_nien_van của luc_than từ bieu_do_ngu_hanh.
     */
    public function duBaoKhiaCanh(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $phanBan = $req->input('phan_ban') === '8b' ? '8b' : '8a';
        $rules = [
            'full_name'       => 'nullable|string|max:255',
            'y'               => 'required|numeric|min:1940|max:2031',
            'm'               => 'required|numeric|min:1|max:12',
            'd'               => 'required|numeric|min:1|max:31',
            'g'               => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
            'phan_ban'        => 'nullable|string|in:8a,8b',
        ];
        if (! $unknowBirthtime) {
            $rules['h']      = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h']      = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }

        $validated = $req->validate($rules);
        $fullName  = (string) ($validated['full_name'] ?? '');
        $y         = (int) $validated['y'];
        $m         = (int) $validated['m'];
        $d         = (int) $validated['d'];
        $h         = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute    = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g         = (string) $validated['g'];

        $yDetail = $phanBan === '8b' ? ((int) date('Y') + 1) : null;
        $needStrength = $phanBan === '8b';
        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, $yDetail, $needStrength);
        if (empty($result) || empty($result['bieu_do_ngu_hanh'] ?? null)) {
            return response()->json(['data' => ['items' => []]]);
        }

        $lucThanScores = $this->buildLucThanNienVanScores((array) ($result['bieu_do_ngu_hanh'] ?? []));
        $records = Phan8DuBaoKhiaCanh::query()
            ->where('phan_ban', $phanBan)
            ->orderBy('khia_canh')
            ->orderBy('thu_tu')
            ->get();

        $gioiTinh = $g === 'male' ? 'NAM' : 'NỮ';
        $orderedKhiaCanh = [
            'Sự nghiệp',
            'Tài chính',
            'Tình duyên',
            'Sức khỏe',
            'Phát triển bản thân',
            'Kết nối xã hội',
        ];
        $grouped = [];
        foreach ($orderedKhiaCanh as $name) {
            $grouped[$name] = null;
        }

        foreach ($records as $row) {
            $khiaCanh = $this->normalizeKhiaCanhName((string) ($row->khia_canh ?? ''));
            if (! array_key_exists($khiaCanh, $grouped) || $grouped[$khiaCanh] !== null) {
                continue;
            }

            $rowGender = trim((string) ($row->gioi_tinh ?? ''));
            if ($khiaCanh === 'Tình duyên' && $rowGender !== '' && mb_strtoupper($rowGender) !== $gioiTinh) {
                continue;
            }

            $matched = $this->evaluatePhan8DuBaoCondition((string) ($row->dieu_kien ?? ''), $lucThanScores);
            if (! $matched) {
                continue;
            }

            $grouped[$khiaCanh] = [
                'id' => $row->id,
                'khia_canh' => $khiaCanh,
                'gioi_tinh' => $row->gioi_tinh,
                'dieu_kien' => $row->dieu_kien,
                'noi_dung' => $row->noi_dung,
                'thu_tu' => $row->thu_tu,
            ];
        }

        $items = [];
        foreach ($orderedKhiaCanh as $name) {
            $items[] = [
                'khia_canh' => $name,
                'match' => $grouped[$name],
            ];
        }

        return response()->json([
            'data' => [
                'items' => $items,
                'luc_than_diem_nien_van' => $lucThanScores,
            ],
        ]);
    }

    /**
     * PHẦN 8 – IV: Những năm cần chú ý (Niên Vận × từng Trụ; chỉ các năm >= năm hiện tại).
     */
    public function nhungNamCanChuY(Request $req): JsonResponse
    {
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;
        $rules = [
            'full_name'       => 'nullable|string|max:255',
            'y'               => 'required|numeric|min:1940|max:2031',
            'm'               => 'required|numeric|min:1|max:12',
            'd'               => 'required|numeric|min:1|max:31',
            'g'               => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];
        if (! $unknowBirthtime) {
            $rules['h']      = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h']      = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }

        $validated = $req->validate($rules);
        $fullName  = (string) ($validated['full_name'] ?? '');
        $y         = (int) $validated['y'];
        $m         = (int) $validated['m'];
        $d         = (int) $validated['d'];
        $h         = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute    = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g         = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: false);
        if (empty($result) || empty($result['bat_tu'] ?? null)) {
            return response()->json(['data' => ['dai_van_blocks' => [], 'current_year' => (int) date('Y')]]);
        }

        $batTu       = $result['bat_tu'];
        $bangDaiVan  = $result['bang_dai_van'] ?? [];
        $dayStem     = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));
        $dayBranch   = trim((string) ($batTu['day']['chi']['dia_chi'] ?? ''));

        $thienCanNam   = trim((string) ($batTu['year']['can']['thien_can'] ?? ''));
        $thienCanThang = trim((string) ($batTu['month']['can']['thien_can'] ?? ''));
        $thienCanNgay  = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));
        $thienCanGio   = trim((string) ($batTu['hour']['can']['thien_can'] ?? ''));

        $thapThanNam   = trim((string) ($batTu['year']['can']['chu_tinh'] ?? ''));
        $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh'] ?? ''));
        $thapThanNgay  = trim((string) ($batTu['day']['can']['chu_tinh'] ?? ''));
        $thapThanGio   = trim((string) ($batTu['hour']['can']['chu_tinh'] ?? ''));

        $diaChiNam   = trim((string) ($batTu['year']['chi']['dia_chi'] ?? ''));
        $diaChiThang = trim((string) ($batTu['month']['chi']['dia_chi'] ?? ''));
        $diaChiNgay  = trim((string) ($batTu['day']['chi']['dia_chi'] ?? ''));
        $diaChiGio   = trim((string) ($batTu['hour']['chi']['dia_chi'] ?? ''));

        $canTangNam   = $batTu['year']['can_tang'] ?? [];
        $canTangThang = $batTu['month']['can_tang'] ?? [];
        $canTangNgay  = $batTu['day']['can_tang'] ?? [];
        $canTangGio   = $batTu['hour']['can_tang'] ?? [];

        if ($unknowBirthtime) {
            $thapThanGio = 'Tỷ Kiên';
            $canTangGio  = [['pho_tinh' => 'Tỷ Kiên', 'can_tang' => '—']];
        }

        $truList = [
            ['key' => 'nam', 'thien_can' => $thienCanNam, 'thap_than' => $thapThanNam, 'dia_chi' => $diaChiNam, 'can_tang' => $canTangNam],
            ['key' => 'thang', 'thien_can' => $thienCanThang, 'thap_than' => $thapThanThang, 'dia_chi' => $diaChiThang, 'can_tang' => $canTangThang],
            ['key' => 'ngay', 'thien_can' => $thienCanNgay, 'thap_than' => $thapThanNgay, 'dia_chi' => $diaChiNgay, 'can_tang' => $canTangNgay],
            ['key' => 'gio', 'thien_can' => $thienCanGio, 'thap_than' => $thapThanGio, 'dia_chi' => $diaChiGio, 'can_tang' => $canTangGio],
        ];

        $currentYear = (int) date('Y');
        $daiVanBlocks = [];

        foreach ($bangDaiVan as $dv) {
            $age   = (int) ($dv['age'] ?? 0);
            $years = $dv['list_year'] ?? [];
            if ($years === []) {
                continue;
            }

            $lastNamInPeriod = (int) ($years[count($years) - 1]['nam'] ?? 0);
            if ($lastNamInPeriod < $currentYear) {
                continue;
            }

            $yearRows = [];

            foreach ($years as $ly) {
                $nam = (int) ($ly['nam'] ?? 0);

                $nvItem     = BaZiServiceV2::buildNienVanForYear($nam, $dayStem, $dayBranch);
                $thienCanNv = trim((string) ($nvItem['can']['thien_can'] ?? ''));
                $thapThanNv = trim((string) ($nvItem['can']['chu_tinh'] ?? ''));
                $diaChiNv   = trim((string) ($nvItem['chi']['dia_chi'] ?? ''));
                $canTangNv  = $nvItem['cantang'] ?? [];
                $menhDcNv   = (string) (BaZiServiceV2::getMenhDiaChi($this->normalizeDiaChi($diaChiNv)) ?? '');
                $thapThanNvDc = $this->getThapThanDiaChiByMenh($canTangNv, $menhDcNv)
                    ?: $this->getThapThanFromTangCanKhiChinh($canTangNv);

                $formulaHits = [];
                $labelsAccum = [];

                foreach ($truList as $tru) {
                    $k    = $tru['key'];
                    $cTru = $tru['thien_can'];
                    $tTru = $tru['thap_than'];
                    $dTru = $tru['dia_chi'];
                    $zTru = $tru['can_tang'];

                    if ($cTru === '' || $dTru === '') {
                        continue;
                    }

                    $khacXung = $this->hasThienCanKhac($thienCanNv, $cTru) && $this->hasDiaChiXung($diaChiNv, $dTru);
                    $trung    = $this->isNienVanTruTrung($thienCanNv, $diaChiNv, $cTru, $dTru);

                    if (! $khacXung && ! $trung) {
                        continue;
                    }

                    $formulas = [];
                    if ($khacXung) {
                        $formulas[] = 'khac_xung';
                    }
                    if ($trung) {
                        $formulas[] = 'trung';
                    }

                    $viTriTru = $this->viTriLabelForTruKey($k);
                    $tcBlock = $this->buildCodingLogicForPillars(
                        $thienCanNv, $cTru, $thapThanNv, $tTru, null, true, $viTriTru
                    );
                    $dcBlock = $this->buildCodingLogicForPillarsDiaChi(
                        $diaChiNv, $dTru, $canTangNv, $zTru, null, true, $viTriTru, $thapThanNvDc
                    );

                    $khiaCanh = $this->khiaCanhChoTru($k);
                    foreach ($khiaCanh as $lb) {
                        $labelsAccum[] = $lb;
                    }

                    foreach ($formulas as $formula) {
                        $formulaHits[] = [
                            'tru'        => $k,
                            'formula'    => $formula,
                            'khia_canh'  => $khiaCanh,
                            'thien_can'  => $tcBlock,
                            'dia_chi'    => $dcBlock,
                        ];
                    }
                }

                $chuYMerged = $this->mergeChuYOrdered(array_values(array_unique($labelsAccum)));

                $tangCanParts = [];
                foreach ($canTangNv as $ct) {
                    if (! is_array($ct)) {
                        continue;
                    }
                    $p = trim((string) ($ct['can_tang'] ?? ''));
                    if ($p !== '' && $p !== '—') {
                        $tangCanParts[] = $p;
                    }
                }
                $tangCanStr = $tangCanParts !== [] ? implode(' ', $tangCanParts) : '';

                $yearRows[] = [
                    'nam'          => $nam,
                    'thien_can'    => $thienCanNv,
                    'dia_chi'      => $diaChiNv,
                    'tang_can'     => $tangCanStr,
                    'chu_y'        => $chuYMerged,
                    'formula_hits' => $formulaHits,
                ];
            }

            $firstPeriodRow = $yearRows[0] ?? null;
            if ($firstPeriodRow === null || ($firstPeriodRow['chu_y'] ?? []) === []) {
                continue;
            }

            $hasFutureHit = false;
            foreach ($yearRows as $yr) {
                if ((int) ($yr['nam'] ?? 0) >= $currentYear && ! empty($yr['chu_y'])) {
                    $hasFutureHit = true;
                    break;
                }
            }
            if (! $hasFutureHit) {
                continue;
            }

            $daiVanBlocks[] = [
                'age'     => $age,
                'dai_van' => [
                    'thien_can' => trim((string) ($dv['can']['thien_can'] ?? '')),
                    'dia_chi'   => trim((string) ($dv['chi']['dia_chi'] ?? '')),
                ],
                'years'   => $yearRows,
            ];
        }

        $ghiChuKhacXung = DongChayGioiThieu::getByTruLoai('nhung_nam_ghi_chu_khac_xung');
        $ghiChuTrung = DongChayGioiThieu::getByTruLoai('nhung_nam_ghi_chu_trung');

        return response()->json([
            'data' => [
                'current_year'    => $currentYear,
                'dai_van_blocks'  => $daiVanBlocks,
                'ghi_chu_khac_xung' => trim((string) ($ghiChuKhacXung?->noi_dung ?? '')) !== ''
                    ? (string) $ghiChuKhacXung->noi_dung
                    : 'Hãy chủ động đón nhận sự thay đổi, giữ tâm thế linh hoạt và chuẩn bị các phương án dự phòng để biến thách thức thành một bước nhảy vọt.',
                'ghi_chu_trung' => trim((string) ($ghiChuTrung?->noi_dung ?? '')) !== ''
                    ? (string) $ghiChuTrung->noi_dung
                    : 'Hãy chậm lại, quan sát cảm xúc và tránh thực hiện những thay đổi mang tính bộc phát do áp lực tự thân.',
            ],
        ]);
    }

    /**
     * Đại Vận hiện tại theo tuổi khởi trong bang_dai_van (2, 12, 22, 32…).
     */
    protected function findCurrentDaiVan(array $bangDaiVan, int $birthYear): ?array
    {
        if ($bangDaiVan === []) {
            return null;
        }

        $currentYear = (int) date('Y');
        $currentAge  = $currentYear - $birthYear;

        foreach ($bangDaiVan as $dv) {
            $age = (int) ($dv['age'] ?? 0);
            if ($currentAge >= $age && $currentAge < $age + 10) {
                return $dv;
            }
        }

        foreach ($bangDaiVan as $dv) {
            $years = $dv['list_year'] ?? [];
            if ($years === []) {
                continue;
            }
            $firstNam = (int) ($years[0]['nam'] ?? 0);
            $lastNam  = (int) ($years[count($years) - 1]['nam'] ?? 0);
            if ($firstNam <= $currentYear && $lastNam >= $currentYear) {
                return $dv;
            }
        }

        return $bangDaiVan[count($bangDaiVan) - 1] ?? null;
    }

    private function viTriLabelForTruKey(string $key): string
    {
        return match ($key) {
            'thang' => 'Trụ Tháng',
            'ngay' => 'Trụ Ngày',
            'gio' => 'Trụ Giờ',
            default => 'Trụ Năm',
        };
    }

    private function hasThienCanKhac(string $canNv, string $canTru): bool
    {
        $canNv  = trim($canNv);
        $canTru = trim($canTru);
        if ($canNv === '' || $canTru === '') {
            return false;
        }

        return CodingLogicRelationship::findByThienCan($canNv, $canTru, null, true)
            ->contains(fn ($r) => trim((string) ($r->moi_quan_he ?? '')) === 'Khắc');
    }

    private function hasDiaChiXung(string $chiNv, string $chiTru): bool
    {
        $c1 = $this->normalizeDiaChi(trim($chiNv));
        $c2 = $this->normalizeDiaChi(trim($chiTru));
        if ($c1 === '' || $c2 === '') {
            return false;
        }

        return CodingLogicRelationship::findByDiaChi($c1, $c2, null, true)
            ->contains(fn ($r) => trim((string) ($r->moi_quan_he ?? '')) === 'Xung');
    }

    private function isNienVanTruTrung(string $canNv, string $chiNv, string $canTru, string $chiTru): bool
    {
        $canNv  = trim($canNv);
        $canTru = trim($canTru);
        if ($canNv === '' || $canTru === '') {
            return false;
        }

        return $canNv === $canTru
            && $this->normalizeDiaChi(trim($chiNv)) === $this->normalizeDiaChi(trim($chiTru));
    }

    /**
     * @param  string  $tru  nam|thang|ngay|gio
     * @return list<string>
     */
    private function khiaCanhChoTru(string $tru): array
    {
        return match ($tru) {
            'nam' => ['Sự nghiệp', 'Kết nối xã hội'],
            'thang' => ['Sự nghiệp', 'Tài chính'],
            'ngay' => ['Tình duyên', 'Sức khỏe'],
            'gio' => ['Tài chính', 'Phát triển bản thân'],
            default => [],
        };
    }

    /**
     * @param  list<string>  $labels
     * @return list<string>
     */
    private function mergeChuYOrdered(array $labels): array
    {
        $order = [
            'Sự nghiệp',
            'Tài chính',
            'Tình duyên',
            'Sức khỏe',
            'Phát triển bản thân',
            'Kết nối xã hội',
        ];
        $seen = [];
        $out  = [];
        foreach ($order as $name) {
            if (in_array($name, $labels, true) && ! isset($seen[$name])) {
                $seen[$name] = true;
                $out[] = $name;
            }
        }
        foreach ($labels as $lb) {
            if (! isset($seen[$lb])) {
                $seen[$lb] = true;
                $out[] = $lb;
            }
        }

        return $out;
    }

    private function normalizeNoAccent(string $text): string
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ạ' => 'A', 'Ả' => 'A', 'Ã' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ậ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ặ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẹ' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ệ' => 'E', 'Ể' => 'E', 'Ễ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ị' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ọ' => 'O', 'Ỏ' => 'O', 'Õ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ộ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ợ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ụ' => 'U', 'Ủ' => 'U', 'Ũ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ự' => 'U', 'Ử' => 'U', 'Ữ' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỵ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y',
            'Đ' => 'D',
        ];

        return strtr($text, $map);
    }

    private function normalizeKhiaCanhName(string $name): string
    {
        $n = trim(preg_replace('/^\d+\.\s*/u', '', $name) ?? $name);
        $u = mb_strtoupper($this->normalizeNoAccent($n));

        if (str_contains($u, 'SU NGHIEP')) return 'Sự nghiệp';
        if (str_contains($u, 'TAI CHINH')) return 'Tài chính';
        if (str_contains($u, 'TINH DUYEN')) return 'Tình duyên';
        if (str_contains($u, 'SUC KHOE')) return 'Sức khỏe';
        if (str_contains($u, 'PHAT TRIEN BAN THAN')) return 'Phát triển bản thân';
        if (str_contains($u, 'KET NOI XA HOI')) return 'Kết nối xã hội';

        return $n;
    }

    private function normalizeLucThan(string $text): string
    {
        $u = mb_strtoupper($this->normalizeNoAccent($text));
        $u = str_replace(['NIEN MENH', 'BAN MENH', '-', '–', '  '], [' ', ' ', ' ', ' ', ' '], $u);
        $u = trim(preg_replace('/\s+/u', ' ', $u) ?? $u);

        $map = [
            'HUYNH DE' => 'HUYNH_DE',
            'THE TAI' => 'THE_TAI',
            'QUAN QUY' => 'QUAN_QUY',
            'PHU MAU' => 'PHU_MAU',
            'TU TON' => 'TU_TON',
        ];
        foreach ($map as $k => $v) {
            if (str_contains($u, $k)) {
                return $v;
            }
        }
        return '';
    }

    private function buildLucThanNienVanScores(array $bieuDoNguHanh): array
    {
        $scores = [];
        foreach ($bieuDoNguHanh as $row) {
            $lucThan = $this->normalizeLucThan((string) ($row['luc_than'] ?? ''));
            if ($lucThan === '') {
                continue;
            }
            $scores[$lucThan] = (float) ($row['diem_nien_van'] ?? 0);
        }
        return $scores;
    }

    private function evaluatePhan8DuBaoCondition(string $dieuKien, array $scores): bool
    {
        $c = mb_strtoupper($this->normalizeNoAccent($dieuKien));
        $c = str_replace('–', '-', $c);
        $threshold = 0.0;
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*%/u', $c, $m) === 1) {
            $threshold = (float) str_replace(',', '.', $m[1]);
        }

        if (str_contains($c, '>')) {
            $parts = explode('>', $c, 2);
            $a = $this->normalizeLucThan($parts[0] ?? '');
            $b = $this->normalizeLucThan($parts[1] ?? '');
            if ($a === '' || $b === '' || ! isset($scores[$a], $scores[$b])) {
                return false;
            }
            return ($scores[$a] - $scores[$b]) > $threshold;
        }

        if (str_contains($c, '=')) {
            $parts = explode('=', $c, 2);
            $a = $this->normalizeLucThan($parts[0] ?? '');
            $right = $parts[1] ?? '';
            $b = $this->normalizeLucThan($right);
            if ($a === '' || $b === '' || ! isset($scores[$a], $scores[$b])) {
                return false;
            }
            return abs($scores[$a] - $scores[$b]) < $threshold;
        }

        return false;
    }

    /**
     * Parse khoảng % từ ten_truong_hop. Trả về ['min' => int, 'max' => int] hoặc null.
     * Hỗ trợ cả format cũ và format mới ("từ 30% đến dưới 60%", "từ dưới 30%", "từ trên 80%").
     */
    protected function parseBucketFromTenTruongHop(string $tenTruongHop): ?array
    {
        $t = $tenTruongHop;

        // 0%: "bị khuyết 0%"
        if (mb_strpos($t, 'bị khuyết') !== false && mb_strpos($t, '0%') !== false) {
            return ['min' => 0, 'max' => 0];
        }

        // trên 80%: kiểm tra trước để tránh nhầm với "dưới 80%"
        if (mb_strpos($t, 'trên 80%') !== false) {
            return ['min' => 81, 'max' => 100];
        }

        // 60–80%: "từ 60% đến dưới 80%" hoặc "từ 60% đến 80%"
        if (mb_strpos($t, '60%') !== false && mb_strpos($t, '80%') !== false) {
            return ['min' => 60, 'max' => 80];
        }

        // 30–60%: "từ 30% đến dưới 60%" hoặc "từ 30% đến 60%"
        if (mb_strpos($t, '30%') !== false && (mb_strpos($t, '60%') !== false || mb_strpos($t, 'dưới 60') !== false)) {
            return ['min' => 30, 'max' => 59];
        }

        // dưới 30%: "từ dưới 30%" hoặc "dưới 30%"
        if (mb_strpos($t, 'dưới 30%') !== false || mb_strpos($t, '30%') !== false) {
            return ['min' => 1, 'max' => 29];
        }

        return null;
    }

    /**
        $rules = [
            'full_name' => 'nullable|string|max:255',
            'y' => 'required|numeric|min:1940|max:2031',
            'm' => 'required|numeric|min:1|max:12',
            'd' => 'required|numeric|min:1|max:31',
            'g' => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];

        if (! $unknowBirthtime) {
            $rules['h'] = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h'] = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }

        $validated = $req->validate($rules);

        $fullName = (string) ($validated['full_name'] ?? '');
        $y = (int) $validated['y'];
        $m = (int) $validated['m'];
        $d = (int) $validated['d'];
        $h = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g = (string) $validated['g'];

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: false);
        if (empty($result) || empty($result['bat_tu'] ?? null)) {
            return response()->json(['data' => null]);
        }

        $batTu = $result['bat_tu'];
        $thienCanNam = trim((string) ($batTu['year']['can']['thien_can'] ?? ''));
        $thienCanThang = trim((string) ($batTu['month']['can']['thien_can'] ?? ''));
        $thienCanNgay = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));
        $thienCanGio = trim((string) ($batTu['hour']['can']['thien_can'] ?? ''));
        $thapThanNam = trim((string) ($batTu['year']['can']['chu_tinh'] ?? ''));
        $thapThanThang = trim((string) ($batTu['month']['can']['chu_tinh'] ?? ''));
        $thapThanNgay = trim((string) ($batTu['day']['can']['chu_tinh'] ?? ''));
        $thapThanGio = trim((string) ($batTu['hour']['can']['chu_tinh'] ?? ''));

        $diaChiNam = trim((string) ($batTu['year']['chi']['dia_chi'] ?? ''));
        $diaChiThang = trim((string) ($batTu['month']['chi']['dia_chi'] ?? ''));
        $diaChiNgay = trim((string) ($batTu['day']['chi']['dia_chi'] ?? ''));
        $diaChiGio = trim((string) ($batTu['hour']['chi']['dia_chi'] ?? ''));
        $canTangNam = $batTu['year']['can_tang'] ?? [];
        $canTangThang = $batTu['month']['can_tang'] ?? [];
        $canTangNgay = $batTu['day']['can_tang'] ?? [];
        $canTangGio = $batTu['hour']['can_tang'] ?? [];

        return response()->json([
            'data' => [
                'nam_thang' => $this->buildCodingLogicForPillars($thienCanNam, $thienCanThang, $thapThanNam, $thapThanThang, 'Trụ Năm - Trụ Tháng'),
                'thang_ngay' => $this->buildCodingLogicForPillars($thienCanThang, $thienCanNgay, $thapThanThang, $thapThanNgay, 'Trụ Tháng - Trụ Ngày'),
                'ngay_gio' => $this->buildCodingLogicForPillars($thienCanNgay, $thienCanGio, $thapThanNgay, $thapThanGio, 'Trụ Ngày - Trụ Giờ', false),
                'nam_thang_dia_chi' => $this->buildCodingLogicForPillarsDiaChi($diaChiNam, $diaChiThang, $canTangNam, $canTangThang, 'Trụ Năm - Trụ Tháng'),
                'thang_ngay_dia_chi' => $this->buildCodingLogicForPillarsDiaChi($diaChiThang, $diaChiNgay, $canTangThang, $canTangNgay, 'Trụ Tháng - Trụ Ngày'),
                'ngay_gio_dia_chi' => $this->buildCodingLogicForPillarsDiaChi($diaChiNgay, $diaChiGio, $canTangNgay, $canTangGio, 'Trụ Ngày - Trụ Giờ', false),
            ],
        ]);
    }

    /**
     * Lấy Sự nghiệp cho một Thập Thần + vị trí (Tháng/Năm).
     *
     * @return array{tich_cuc: string|null, tieu_cuc: string|null}|null
     */
    protected function buildSuNghiepFor(string $thapThan, string $viTri): ?array
    {
        return $this->buildKhiaCanhBlockFor($thapThan, $viTri, 'Thiên Can', 'Sự Nghiệp');
    }

    /**
     * @return array{sections: array<int, array{label: string, content: string}>}|null
     */
    protected function buildKhiaCanhBlockFor(
        string $thapThan,
        string $viTri,
        string $loaiCan,
        string $khiaCanh,
        ?string $labelPrefix = null
    ): ?array {
        $thapThan = trim($thapThan);
        if ($thapThan === '') {
            return null;
        }

        $sections = $this->buildKhiaCanhSectionsFor($thapThan, $viTri, $loaiCan, $khiaCanh, $labelPrefix);
        if ($sections === []) {
            return null;
        }

        return ['sections' => $sections];
    }

    /**
     * @return array<int, array{label: string, content: string}>
     */
    protected function buildKhiaCanhSectionsFor(
        string $thapThan,
        string $viTri,
        string $loaiCan,
        string $khiaCanh,
        ?string $labelPrefix = null
    ): array {
        $thapThan = trim($thapThan);
        if ($thapThan === '') {
            return [];
        }

        $records = ThapThanTheoViTri::query()
            ->where('thap_than', $thapThan)
            ->where('vi_tri', $viTri)
            ->where('loai_can', $loaiCan)
            ->where('khia_canh', $khiaCanh)
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

        $sections = [];
        foreach ($sectionOrder as $label) {
            $rec = $records->firstWhere('huong', $label);
            $content = trim((string) ($rec?->content ?? ''));
            if ($content === '') {
                continue;
            }

            $sections[] = [
                'label' => $labelPrefix !== null && $labelPrefix !== ''
                    ? $labelPrefix.' — '.$label
                    : $label,
                'content' => $content,
            ];
        }

        return $sections;
    }

    /**
     * Build dữ liệu coding logic cho cặp trụ: tra coding_logic_relationships,
     * dùng chu_tinh trụ đầu để tra dong_chay_nang_luong, và (nếu có) gộp thêm
     * nội dung từ chu_tinh trụ sau.
     * Trả về mảng các block (mỗi mối quan hệ Hợp/Khắc/Xung... một block).
     *
     * @param  string  $thienCan1  Thiên Can trụ đầu (năm hoặc tháng)
     * @param  string  $thienCan2  Thiên Can trụ sau (tháng hoặc ngày)
     * @param  string  $thapThan1  chu_tinh trụ đầu (để tra dong_chay_nang_luong)
     * @param  string|null  $thapThan2  chu_tinh trụ sau (để tra thêm dong_chay_nang_luong, có thể null)
     * @param  string|null  $truTuongTac  "Trụ Năm - Trụ Tháng", "Đại Vận - Trụ Năm", null = bỏ qua filter
     * @return array<int, array{moi_quan_he: string, thap_than: string, thien_can_1: string, thien_can_2: string, ngu_hanh_sinh_ra: string|null, noi_dung: array}>|null
     */
    protected function buildCodingLogicForPillars(
        string $thienCan1,
        string $thienCan2,
        string $thapThan1,
        ?string $thapThan2,
        ?string $truTuongTac,
        bool $allowReverse = true,
        ?string $viTriTru = null
    ): ?array {
        if ($thienCan1 === '' || $thienCan2 === '') {
            return null;
        }

        $records = CodingLogicRelationship::findByThienCan($thienCan1, $thienCan2, null, $allowReverse);
        if ($records->isEmpty()) {
            return null;
        }

        $chuTinh1 = trim($thapThan1);
        $chuTinh2 = $thapThan2 !== null ? trim($thapThan2) : '';

        $thapThanLabelParts = [];
        if ($chuTinh1 !== '') {
            $thapThanLabelParts[] = $chuTinh1;
        }
        if ($chuTinh2 !== '' && $chuTinh2 !== $chuTinh1) {
            $thapThanLabelParts[] = $chuTinh2;
        }
        $thapThanLabel = implode(', ', $thapThanLabelParts);

        $mergedMoiQuanHe = [];
        $mergedNoiDung = [];
        $phan8a = [];
        $nguHanhSinhRa = null;

        foreach ($records as $rec) {
            $moiQuanHe = (string) ($rec->moi_quan_he ?? '');
            if ($moiQuanHe === '' || in_array($moiQuanHe, $mergedMoiQuanHe, true)) {
                continue;
            }
            $mergedMoiQuanHe[] = $moiQuanHe;

            if ($nguHanhSinhRa === null && $rec->ngu_hanh_sinh_ra !== null && $rec->ngu_hanh_sinh_ra !== '') {
                $nguHanhSinhRa = (string) $rec->ngu_hanh_sinh_ra;
            }

            if ($viTriTru !== null && $chuTinh1 !== '') {
                $p8 = Phan8aThapThan::findFor($chuTinh1, $viTriTru, 'Thiên Can', $moiQuanHe);
                if ($p8 !== null) {
                    $phan8a[] = $p8->toApiArray();

                    continue;
                }
            }

            if ($chuTinh1 !== '') {
                $dongChayRecords = DongChayNangLuong::findByThapThan(
                    $chuTinh1,
                    'Thiên Can',
                    $moiQuanHe,
                    $truTuongTac
                );
                foreach ($dongChayRecords as $r) {
                    $mergedNoiDung[] = [
                        'thap_than' => (string) ($r->thap_than ?? $chuTinh1),
                        'huong' => (string) ($r->huong ?? ''),
                        'noi_dung' => (string) ($r->noi_dung ?? ''),
                    ];
                }
            }

            if ($chuTinh2 !== '' && $chuTinh2 !== $chuTinh1 && $viTriTru === null) {
                $dongChayRecords2 = DongChayNangLuong::findByThapThan(
                    $chuTinh2,
                    'Thiên Can',
                    $moiQuanHe,
                    $truTuongTac
                );
                foreach ($dongChayRecords2 as $r) {
                    $mergedNoiDung[] = [
                        'thap_than' => (string) ($r->thap_than ?? $chuTinh2),
                        'huong' => (string) ($r->huong ?? ''),
                        'noi_dung' => (string) ($r->noi_dung ?? ''),
                    ];
                }
            }
        }

        if ($mergedMoiQuanHe === [] || $thapThanLabel === '') {
            return null;
        }

        if ($phan8a === [] && $mergedNoiDung === []) {
            return null;
        }

        $mergedNoiDung = $this->mergeNoiDungByThapThanHuong($mergedNoiDung);

        return [[
            'moi_quan_he' => implode(', ', $mergedMoiQuanHe),
            'thap_than' => $thapThanLabel,
            'thien_can_1' => $thienCan1,
            'thien_can_2' => $thienCan2,
            'ngu_hanh_sinh_ra' => $nguHanhSinhRa,
            'tru_tuong_tac' => $truTuongTac,
            'noi_dung' => $mergedNoiDung,
            'phan8a' => $phan8a,
        ]];
    }

    /**
     * Gộp noi_dung có cùng (thap_than, huong): nối noi_dung, thêm prefix [moi_quan_he] để frontend lọc theo mqh.
     *
     * @param  array<int, array{thap_than?: string, huong?: string, noi_dung?: string, moi_quan_he?: string}>  $noiDung
     * @return array<int, array{thap_than: string, huong: string, noi_dung: string}>
     */
    protected function mergeNoiDungByThapThanHuong(array $noiDung): array
    {
        $grouped = [];
        foreach ($noiDung as $item) {
            $thapThan = trim((string) ($item['thap_than'] ?? ''));
            $huong = trim((string) ($item['huong'] ?? ''));
            $nd = trim((string) ($item['noi_dung'] ?? ''));
            $mqh = trim((string) ($item['moi_quan_he'] ?? ''));
            if ($nd === '') {
                continue;
            }
            $chunk = $mqh !== '' ? "[{$mqh}]\n" . $nd : $nd;
            $key = $thapThan . '|' . $huong;
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'thap_than' => $thapThan,
                    'huong' => $huong,
                    'noi_dung' => $chunk,
                ];
            } else {
                $grouped[$key]['noi_dung'] .= "\n\n" . $chunk;
            }
        }

        return array_values($grouped);
    }

    /**
     * Lấy Thập Thần từ Tàng Can khí chính: [1] nếu có 3 phần tử, [0] nếu có 1 phần tử.
     *
     * @param  array<int, array{pho_tinh?: string}>  $canTangArr
     */
    protected function getThapThanFromTangCanKhiChinh(array $canTangArr): ?string
    {
        if (empty($canTangArr)) {
            return null;
        }
        $count = count($canTangArr);
        if ($count === 1) {
            $item = $canTangArr[0];
        } elseif ($count === 3) {
            $item = $canTangArr[1];
        } else {
            return null;
        }
        $phoTinh = trim((string) ($item['pho_tinh'] ?? ''));

        return $phoTinh !== '' ? $phoTinh : null;
    }

    /**
     * Lấy Thập Thần cho Địa Chi bằng cách:
     * - Lấy mệnh Địa Chi (Kim/Mộc/Thủy/Hỏa/Thổ)
     * - Tìm phần tử trong Tàng Can có 'menh' chứa mệnh Địa Chi (vd: "Dương Thủy" chứa "Thủy")
     * - Lấy 'pho_tinh' (Thập Thần) của phần tử match đầu tiên
     * - Nếu không match: trả null
     *
     * @param  array<int, array{menh?: string, pho_tinh?: string}>  $canTangArr
     */
    protected function getThapThanDiaChiByMenh(array $canTangArr, string $menhDiaChi): ?string
    {
        $menhDiaChi = trim($menhDiaChi);
        if ($menhDiaChi === '' || empty($canTangArr)) {
            return null;
        }

        foreach ($canTangArr as $item) {
            if (! is_array($item)) {
                continue;
            }
            $menhCanTang = trim((string) ($item['menh'] ?? ''));
            if ($menhCanTang === '') {
                continue;
            }
            if (mb_strpos($menhCanTang, $menhDiaChi) === false) {
                continue;
            }
            $phoTinh = trim((string) ($item['pho_tinh'] ?? ''));
            return $phoTinh !== '' ? $phoTinh : null;
        }

        return null;
    }

    /**
     * Build coding logic Địa Chi: tra coding_logic_relationships (loại dia_chi),
     * Thập Thần = Tàng Can khí chính của trụ đầu, và (nếu có) gộp thêm
     * nội dung từ Tàng Can khí chính của trụ sau.
     * Trả về mảng các block (mỗi mối quan hệ Hợp/Khắc/Xung... một block).
     *
     * @param  array<int, array{pho_tinh?: string}>  $canTangArr1
     * @param  array<int, array{pho_tinh?: string}>  $canTangArr2
     * @return array<int, array{moi_quan_he: string, thap_than: string, dia_chi_1: string, dia_chi_2: string, ngu_hanh_sinh_ra: string|null, noi_dung: array}>|null
     */
    protected function buildCodingLogicForPillarsDiaChi(
        string $diaChi1,
        string $diaChi2,
        array $canTangArr1,
        array $canTangArr2,
        ?string $truTuongTac,
        bool $allowReverse = true,
        ?string $viTriTru = null,
        ?string $thapThanVanDc = null
    ): ?array {
        $chi1 = $this->normalizeDiaChi(trim($diaChi1));
        $chi2 = $this->normalizeDiaChi(trim($diaChi2));
        if ($chi1 === '' || $chi2 === '') {
            return null;
        }

        $records = CodingLogicRelationship::findByDiaChi($chi1, $chi2, null, $allowReverse);
        if ($records->isEmpty()) {
            return null;
        }

        // Lấy Thập Thần cho Địa Chi 1 và 2 (không phụ thuộc record)
        $menhDiaChi1 = (string) (BaZiServiceV2::getMenhDiaChi($diaChi1) ?? '');
        $thapThan1 = $this->getThapThanDiaChiByMenh($canTangArr1, $menhDiaChi1);
        if (($thapThan1 === null || $thapThan1 === '') && ! empty($canTangArr1)) {
            $thapThan1 = $this->getThapThanFromTangCanKhiChinh($canTangArr1);
        }
        $thapThan2 = null;
        if (! empty($canTangArr2)) {
            $menhDiaChi2 = (string) (BaZiServiceV2::getMenhDiaChi($diaChi2) ?? '');
            if ($menhDiaChi2 !== '') {
                $thapThan2 = $this->getThapThanDiaChiByMenh($canTangArr2, $menhDiaChi2);
                if (($thapThan2 === null || $thapThan2 === '') && ! empty($canTangArr2)) {
                    $thapThan2 = $this->getThapThanFromTangCanKhiChinh($canTangArr2);
                }
            }
        }

        $thapThanLabelParts = [];
        if ($thapThan1 !== null && $thapThan1 !== '') {
            $thapThanLabelParts[] = $thapThan1;
        }
        if ($thapThan2 !== null && $thapThan2 !== '' && $thapThan2 !== $thapThan1) {
            $thapThanLabelParts[] = $thapThan2;
        }
        $thapThanLabel = implode(', ', $thapThanLabelParts);

        $thapThanVan = ($thapThanVanDc !== null && trim($thapThanVanDc) !== '')
            ? trim($thapThanVanDc)
            : ($thapThan1 ?? '');

        $mergedMoiQuanHe = [];
        $mergedNoiDung = [];
        $phan8a = [];
        $nguHanhSinhRa = null;

        foreach ($records as $rec) {
            $moiQuanHe = (string) ($rec->moi_quan_he ?? '');
            if ($moiQuanHe === '' || in_array($moiQuanHe, $mergedMoiQuanHe, true)) {
                continue;
            }
            $mergedMoiQuanHe[] = $moiQuanHe;

            if ($nguHanhSinhRa === null && $rec->ngu_hanh_sinh_ra !== null && $rec->ngu_hanh_sinh_ra !== '') {
                $nguHanhSinhRa = (string) $rec->ngu_hanh_sinh_ra;
            }

            if ($viTriTru !== null && $thapThanVan !== '') {
                $p8 = Phan8aThapThan::findFor($thapThanVan, $viTriTru, 'Địa Chi', $moiQuanHe);
                if ($p8 !== null) {
                    $phan8a[] = $p8->toApiArray();

                    continue;
                }
            }

            if ($thapThan1 !== null && $thapThan1 !== '') {
                $dongChayRecords = DongChayNangLuong::findByThapThan(
                    $thapThan1,
                    'Địa Chi',
                    $moiQuanHe,
                    $truTuongTac
                );
                foreach ($dongChayRecords as $r) {
                    $mergedNoiDung[] = [
                        'thap_than' => (string) ($r->thap_than ?? $thapThan1),
                        'huong' => (string) ($r->huong ?? ''),
                        'noi_dung' => (string) ($r->noi_dung ?? ''),
                        'moi_quan_he' => $moiQuanHe,
                    ];
                }
            }

            if ($thapThan2 !== null && $thapThan2 !== '' && $thapThan2 !== $thapThan1 && $viTriTru === null) {
                $dongChayRecords2 = DongChayNangLuong::findByThapThan(
                    $thapThan2,
                    'Địa Chi',
                    $moiQuanHe,
                    $truTuongTac
                );
                foreach ($dongChayRecords2 as $r) {
                    $mergedNoiDung[] = [
                        'thap_than' => (string) ($r->thap_than ?? $thapThan2),
                        'huong' => (string) ($r->huong ?? ''),
                        'noi_dung' => (string) ($r->noi_dung ?? ''),
                        'moi_quan_he' => $moiQuanHe,
                    ];
                }
            }
        }

        if ($mergedMoiQuanHe === [] || $thapThanLabel === '') {
            return null;
        }

        if ($phan8a === [] && $mergedNoiDung === []) {
            return null;
        }

        $mergedNoiDung = $this->mergeNoiDungByThapThanHuong($mergedNoiDung);

        return [[
            'moi_quan_he' => implode(', ', $mergedMoiQuanHe),
            'thap_than' => $thapThanLabel,
            'dia_chi_1' => $chi1,
            'dia_chi_2' => $chi2,
            'ngu_hanh_sinh_ra' => $nguHanhSinhRa,
            'tru_tuong_tac' => $truTuongTac,
            'noi_dung' => $mergedNoiDung,
            'phan8a' => $phan8a,
        ]];
    }

    /**
     * Chuẩn hóa Địa Chi: Tí -> Tý.
     */
    protected function normalizeDiaChi(string $chi): string
    {
        if ($chi === 'Tý') {
            return 'Tí';
        }

        return $chi;
    }

    /**
     * Lấy Tài chính & Mối quan hệ xã hội theo Tàng Can (Trụ Giờ / Trụ Tháng).
     * Với Trụ Giờ/Trụ Tháng: ưu tiên Thập Thần theo mệnh Địa Chi (getThapThanDiaChiByMenh), fallback khí chính.
     *
     * @param  array<int, array{can_tang?: string, menh?: string, pho_tinh?: string}>  $canTangArr
     * @return array<int, array{can_tang: string|null, thap_than: string|null, tai_chinh: string|null, moi_quan_he_xa_hoi: string|null}>
     */
    protected function buildTaiChinhForCanTang(array $canTangArr, string $viTri, ?string $menhDiaChi = null): array
    {
        $useMenh = ($viTri === 'Trụ Giờ' || $viTri === 'Trụ Tháng') && $menhDiaChi !== null && $menhDiaChi !== '';

        if ($useMenh && ! empty($canTangArr)) {
            $thapThan = $this->getThapThanDiaChiByMenh($canTangArr, $menhDiaChi);
            if ($thapThan !== null && $thapThan !== '') {
                $chinh = null;
                foreach ($canTangArr as $item) {
                    if (is_array($item) && trim((string) ($item['pho_tinh'] ?? '')) === $thapThan) {
                        $chinh = $item;
                        break;
                    }
                }
                if ($chinh !== null) {
                    $canTang = trim((string) ($chinh['can_tang'] ?? ''));
                    $baseQuery = ThapThanTheoViTri::query()
                        ->where('thap_than', $thapThan)
                        ->where('vi_tri', $viTri)
                        ->where('loai_can', 'Tàng Can')
                        ->orderBy('sort_order');
                    $mergeContents = static function ($records): ?string {
                        if (! $records || $records->isEmpty()) {
                            return null;
                        }
                        return $records->pluck('content')->filter()->implode("\n\n");
                    };

                    return [[
                        'can_tang' => $canTang !== '' ? $canTang : null,
                        'thap_than' => $thapThan,
                        'tai_chinh' => $mergeContents((clone $baseQuery)->where('khia_canh', 'Tài Chính')->get()),
                        'moi_quan_he_xa_hoi' => $mergeContents((clone $baseQuery)->where('khia_canh', 'Mối quan hệ xã hội')->get()),
                    ]];
                }
            }
        }

        // Fallback: khí chính (1 pt → [0], 3 pt → [1])
        if (($viTri === 'Trụ Giờ' || $viTri === 'Trụ Tháng') && ! empty($canTangArr)) {
            $count = count($canTangArr);
            if ($count === 1) {
                $canTangArr = [$canTangArr[0]];
            } elseif ($count === 3) {
                $canTangArr = [$canTangArr[1]];
            }
        }

        $out = [];

        foreach ($canTangArr as $item) {
            $canTang = trim((string) ($item['can_tang'] ?? ''));
            $thapThan = trim((string) ($item['pho_tinh'] ?? ''));

            if ($thapThan === '') {
                continue;
            }

            $baseQuery = ThapThanTheoViTri::query()
                ->where('thap_than', $thapThan)
                ->where('vi_tri', $viTri)
                ->where('loai_can', 'Tàng Can')
                ->orderBy('sort_order');

            $taiChinhRecords = (clone $baseQuery)
                ->where('khia_canh', 'Tài Chính')
                ->get();

            $moiQuanHeRecords = (clone $baseQuery)
                ->where('khia_canh', 'Mối quan hệ xã hội')
                ->get();

            $mergeContents = static function ($records): ?string {
                if (! $records || $records->isEmpty()) {
                    return null;
                }

                /** @var \Illuminate\Support\Collection $records */
                return $records->pluck('content')->filter()->implode("\n\n");
            };

            $out[] = [
                'can_tang' => $canTang !== '' ? $canTang : null,
                'thap_than' => $thapThan !== '' ? $thapThan : null,
                'tai_chinh' => $mergeContents($taiChinhRecords),
                'moi_quan_he_xa_hoi' => $mergeContents($moiQuanHeRecords),
            ];
        }

        return $out;
    }

    /**
     * Lấy Tình Cảm theo Tàng Can Trụ Ngày (khía cạnh Tình Cảm).
     *
     * @param  array<int, array{can_tang?: string, menh?: string, pho_tinh?: string}>  $canTangArr
     * @return array<int, array{can_tang: string|null, thap_than: string|null, tich_cuc: string|null, tieu_cuc: string|null}>
     */
    protected function buildTinhCamForCanTangNgay(array $canTangArr): array
    {
        // Chỉ lấy khí chính: nếu có 1 phần tử lấy phần tử đó, nếu có 3 phần tử lấy phần tử ở giữa
        if (! empty($canTangArr)) {
            $count = count($canTangArr);
            if ($count === 1) {
                $canTangArr = [$canTangArr[0]];
            } elseif ($count === 3) {
                $canTangArr = [$canTangArr[1]];
            }
        }

        $out = [];

        foreach ($canTangArr as $item) {
            $canTang = trim((string) ($item['can_tang'] ?? ''));
            $thapThan = trim((string) ($item['pho_tinh'] ?? ''));

            if ($thapThan === '') {
                continue;
            }

            $sections = $this->buildKhiaCanhSectionsFor($thapThan, 'Trụ Ngày', 'Tàng Can', 'Tình Cảm');
            if ($sections === []) {
                continue;
            }

            $out[] = [
                'can_tang' => $canTang !== '' ? $canTang : null,
                'thap_than' => $thapThan !== '' ? $thapThan : null,
                'sections' => $sections,
            ];
        }

        return $out;
    }

    /**
     * Lấy Phát triển bản thân & Tính cách từ Thập Thần (Thiên Can / Tàng Can).
     *
     * @return array{thap_than: string|null, phat_trien_ban_than: ?array{tich_cuc: string|null,tieu_cuc: string|null}, tinh_cach: ?array{tich_cuc: string|null,tieu_cuc: string|null}}|null
     */
    protected function buildPhatTrienBanThanFromThapThan(?string $thapThan, string $viTri, string $loaiCan): ?array
    {
        $thapThan = trim((string) $thapThan);
        if ($thapThan === '') {
            return null;
        }

        $sections = array_merge(
            $this->buildKhiaCanhSectionsFor($thapThan, $viTri, $loaiCan, 'Phát triển bản thân', 'Phát triển bản thân'),
            $this->buildKhiaCanhSectionsFor($thapThan, $viTri, $loaiCan, 'Tính cách', 'Tính cách')
        );

        if ($sections === []) {
            return null;
        }

        return [
            'thap_than' => $thapThan,
            'sections' => $sections,
        ];
    }

    /**
     * Lấy Phát triển bản thân & Tính cách cho Tàng Can Giờ.
     * Ưu tiên Thập Thần theo mệnh Địa Chi (getThapThanDiaChiByMenh), fallback khí chính.
     *
     * @param  array<int, array{can_tang?: string, menh?: string, pho_tinh?: string}>  $canTangArr
     * @return array{can_tang: string|null, thap_than: string|null, phat_trien_ban_than: ?array{tich_cuc: string|null,tieu_cuc: string|null}, tinh_cach: ?array{tich_cuc: string|null,tieu_cuc: string|null}}|null
     */
    protected function buildPhatTrienBanThanFromTangCanGio(array $canTangArr, string $menhDiaChi = ''): ?array
    {
        if (empty($canTangArr)) {
            return null;
        }

        $thapThan = null;
        $chinh = null;

        if ($menhDiaChi !== '') {
            $thapThan = $this->getThapThanDiaChiByMenh($canTangArr, $menhDiaChi);
        }
        if ($thapThan === null || $thapThan === '') {
            $thapThan = $this->getThapThanFromTangCanKhiChinh($canTangArr);
        }

        if ($thapThan === null || $thapThan === '') {
            return null;
        }

        foreach ($canTangArr as $item) {
            if (is_array($item) && trim((string) ($item['pho_tinh'] ?? '')) === $thapThan) {
                $chinh = $item;
                break;
            }
        }
        if ($chinh === null) {
            $count = count($canTangArr);
            $chinh = $count === 3 ? $canTangArr[1] : $canTangArr[0];
        }

        $canTang = trim((string) ($chinh['can_tang'] ?? ''));

        $base = $this->buildPhatTrienBanThanFromThapThan($thapThan, 'Trụ Giờ', 'Tàng Can');
        if ($base === null) {
            return null;
        }

        return [
            'can_tang' => $canTang !== '' ? $canTang : null,
            'thap_than' => $base['thap_than'] ?? null,
            'sections' => $base['sections'] ?? [],
        ];
    }

    /**
     * Kết nối xã hội từ Thập Thần (Thiên Can/Tàng Can) – khía cạnh Mối quan hệ xã hội & Tính cách ngoài xã hội.
     *
     * @return array{thap_than: string|null, moi_quan_he_xa_hoi: ?array{tich_cuc: string|null,tieu_cuc: string|null}, tinh_cach_xa_hoi: ?array{tich_cuc: string|null,tieu_cuc: string|null}}|null
     */
    protected function buildKetNoiXaHoiFromThapThan(?string $thapThan, string $viTri, string $loaiCan): ?array
    {
        $thapThan = trim((string) $thapThan);
        if ($thapThan === '') {
            return null;
        }

        $sections = array_merge(
            $this->buildKhiaCanhSectionsFor($thapThan, $viTri, $loaiCan, 'Mối quan hệ xã hội', 'Mối quan hệ xã hội'),
            $this->buildKhiaCanhSectionsFor(
                $thapThan,
                $viTri,
                $loaiCan,
                'Tính cách: tính cách thể hiện ra ngoài xã hội',
                'Tính cách xã hội'
            )
        );

        if ($sections === []) {
            return null;
        }

        return [
            'thap_than' => $thapThan,
            'sections' => $sections,
        ];
    }

    /**
     * Kết nối xã hội từ Tàng Can Năm (có thể nhiều can_tang).
     *
     * @param  array<int, array{can_tang?: string, menh?: string, pho_tinh?: string}>  $canTangArr
     * @return array<int, array{can_tang: string|null, thap_than: string|null, moi_quan_he_xa_hoi: ?array{tich_cuc: string|null,tieu_cuc: string|null}, tinh_cach_xa_hoi: ?array{tich_cuc: string|null,tieu_cuc: string|null}}>
     */
    protected function buildKetNoiXaHoiFromTangCanNam(array $canTangArr): array
    {
        // Nếu có nhiều khí, chỉ lấy khí chính: 1 phần tử => giữ nguyên, 3 phần tử => lấy phần tử giữa
        if (! empty($canTangArr)) {
            $count = count($canTangArr);
            if ($count === 1) {
                $canTangArr = [$canTangArr[0]];
            } elseif ($count === 3) {
                $canTangArr = [$canTangArr[1]];
            }
        }

        $out = [];

        foreach ($canTangArr as $item) {
            $canTang = trim((string) ($item['can_tang'] ?? ''));
            $thapThan = trim((string) ($item['pho_tinh'] ?? ''));

            if ($thapThan === '') {
                continue;
            }

            $base = $this->buildKetNoiXaHoiFromThapThan($thapThan, 'Trụ Năm', 'Tàng Can');
            if ($base === null) {
                continue;
            }

            $out[] = [
                'can_tang' => $canTang !== '' ? $canTang : null,
                'thap_than' => $base['thap_than'] ?? null,
                'sections' => $base['sections'] ?? [],
            ];
        }

        return $out;
    }

    /**
     * Lấy danh sách tên Thập Thần có chất lượng bản mệnh (natal) > 0.
     * Dùng để điều kiện PHẦN 5: chỉ pass qua nội dung khi có ít nhất một Thập Thần bản mệnh > 0.
     *
     * @param  array<int, array{name?: string, natal?: mixed}>|null  $chatLuongThapThan
     * @return array<int, string>
     */
    protected function getThapThanCoBanMenhLonHonKhong(?array $chatLuongThapThan): array
    {
        if (! is_array($chatLuongThapThan) || count($chatLuongThapThan) === 0) {
            return [];
        }
        $out = [];
        foreach ($chatLuongThapThan as $item) {
            $natal = (int) ($item['natal'] ?? 0);
            if ($natal > 0) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $out[] = $name;
                }
            }
        }

        return $out;
    }

    /**
     * Tính Sức khỏe dựa trên đếm Hỷ Thần / Kỵ Thần trong Thiên Can và Tàng Can
     * của các trụ Năm, Tháng, Giờ (bỏ trụ Ngày) và map sang nội dung chi tiết
     * trong bảng PHAN5_V_SUC_KHOE (bảng phan5_suc_khoe).
     * Chỉ trả chi_tiet / than_trang_thai khi có ít nhất một Thập Thần bản mệnh > 0 (nếu truyền $thapThanCoBanMenhLonHonKhong).
     *
     * @param  array<string, mixed>  $batTu
     * @param  array<int, string>|null  $thapThanCoBanMenhLonHonKhong  Danh sách Thập Thần có bản mệnh > 0; null = không check
     * @return array{
     *   hy_than_ngu_hanh: string,
     *   ky_than_ngu_hanh: string,
     *   so_luong_hy_than: int,
     *   so_luong_ky_than: int,
     *   truong_hop: int|null,
     *   ket_luan: string,
     *   than_trang_thai?: string|null,
     *   chi_tiet?: array<int, array{nhom: string, content: string}>
     * }|null
     */
    protected function buildSucKhoeHyKyThan(array $batTu, string $hyThanNguHanh, string $kyThanNguHanh, bool $skipHourPillar = false, ?array $thapThanCoBanMenhLonHonKhong = null): ?array
    {
        $hyThanNguHanh = trim($hyThanNguHanh);
        $kyThanNguHanh = trim($kyThanNguHanh);

        if ($hyThanNguHanh === '' && $kyThanNguHanh === '') {
            return null;
        }

        $normalize = static function (string $value): string {
            $value = trim($value);
            // Bỏ dấu + / - ở đầu, ví dụ: "+ Hỏa", "- Hỏa" -> "Hỏa"
            $value = preg_replace('/^[+\-]\s*/u', '', $value) ?? $value;

            return mb_strtoupper($value, 'UTF-8');
        };

        $hyList = array_filter(array_map('trim', preg_split('/,/', $hyThanNguHanh) ?: []), static fn ($v) => $v !== '');
        $kyList = array_filter(array_map('trim', preg_split('/,/', $kyThanNguHanh) ?: []), static fn ($v) => $v !== '');

        $hySet = [];
        foreach ($hyList as $val) {
            $hySet[$normalize((string) $val)] = true;
        }

        $kySet = [];
        foreach ($kyList as $val) {
            $kySet[$normalize((string) $val)] = true;
        }

        $soLuongHyThan = 0;
        $soLuongKyThan = 0;

        $pillars = ['year', 'month', 'hour'];

        foreach ($pillars as $pillar) {
            if ($skipHourPillar && $pillar === 'hour') {
                continue;
            }
            if (! isset($batTu[$pillar]) || ! is_array($batTu[$pillar])) {
                continue;
            }

            // Thiên Can
            $can = $batTu[$pillar]['can']['thien_can'] ?? null;
            if (is_string($can) && $can !== '') {
                $element = BaZiServiceV2::getMenh($can);
                if ($element !== '') {
                    $key = $normalize($element);
                    if (isset($hySet[$key])) {
                        $soLuongHyThan++;
                    }
                    if (isset($kySet[$key])) {
                        $soLuongKyThan++;
                    }
                }
            }

            // Địa Chi
            $chi = $batTu[$pillar]['chi']['dia_chi'] ?? null;
            if (is_string($chi) && $chi !== '') {
                $elementChi = BaZiServiceV2::getMenhDiaChi($chi);
                if ($elementChi !== null && $elementChi !== '') {
                    $keyChi = $normalize($elementChi);
                    if (isset($hySet[$keyChi])) {
                        $soLuongHyThan++;
                    }
                    if (isset($kySet[$keyChi])) {
                        $soLuongKyThan++;
                    }
                }
            }

            // Tàng Can
            $canTangArr = $batTu[$pillar]['can_tang'] ?? null;
            if (is_array($canTangArr)) {
                foreach ($canTangArr as $item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $canTang = $item['can_tang'] ?? null;
                    if (! is_string($canTang) || $canTang === '') {
                        continue;
                    }
                    $element = BaZiServiceV2::getMenh($canTang);
                    if ($element === '') {
                        continue;
                    }
                    $key = $normalize($element);
                    if (isset($hySet[$key])) {
                        $soLuongHyThan++;
                    }
                    if (isset($kySet[$key])) {
                        $soLuongKyThan++;
                    }
                }
            }
        }

        if ($soLuongHyThan === 0 && $soLuongKyThan > 0) {
            $truongHop = 1;
            $ketLuan = 'Thân rất yếu';
        } elseif ($soLuongHyThan === 0 && $soLuongKyThan === 0) {
            $truongHop = null;
            $ketLuan = '';
        } elseif ($soLuongKyThan === 0) {
            $truongHop = 5;
            $ketLuan = 'Thân rất vững';
        } elseif ($soLuongHyThan < $soLuongKyThan) {
            $truongHop = 2;
            $ketLuan = 'Thân yếu';
        } elseif ($soLuongHyThan === $soLuongKyThan) {
            $truongHop = 3;
            $ketLuan = 'Thân trung bình';
        } else {
            $truongHop = 4;
            $ketLuan = 'Thân vững';
        }

        // Map kết luận sang trạng thái thân trong PHAN5_V_SUC_KHOE
        $thanTrangThai = null;
        $chiTietArray = [];

        $prefix = $this->mapKetLuanToThanTrangThai($ketLuan);
        // Chỉ lấy chi_tiet / than_trang_thai khi có ít nhất một Thập Thần bản mệnh > 0 (nếu đã truyền danh sách check)
        $allowPass = $thapThanCoBanMenhLonHonKhong === null || count($thapThanCoBanMenhLonHonKhong) > 0;
        if ($prefix !== null && $allowPass) {
            $rows = SucKhoeHyKyThan::query()
                ->where('than_trang_thai', 'like', $prefix.'%')
                ->orderBy('sort_order')
                ->get();

            if ($rows->isNotEmpty()) {
                if ($thapThanCoBanMenhLonHonKhong !== null && count($thapThanCoBanMenhLonHonKhong) > 0) {
                    $rows = $rows->filter(function (SucKhoeHyKyThan $row) use ($thapThanCoBanMenhLonHonKhong): bool {
                        $nhom = mb_strtoupper((string) $row->nhom, 'UTF-8');
                        foreach ($thapThanCoBanMenhLonHonKhong as $tenThapThan) {
                            $ten = mb_strtoupper(trim((string) $tenThapThan), 'UTF-8');
                            if ($ten !== '' && mb_strpos($nhom, $ten) !== false) {
                                return true;
                            }
                        }

                        return false;
                    });
                }

                if ($rows->isNotEmpty()) {
                    $thanTrangThai = (string) ($rows->first()->than_trang_thai ?? null);
                    $chiTietArray = $rows->map(static function (SucKhoeHyKyThan $row): array {
                        return [
                            'nhom' => (string) $row->nhom,
                            'content' => (string) $row->content,
                        ];
                    })->values()->all();
                }
            }
        }

        return [
            'hy_than_ngu_hanh' => $hyThanNguHanh,
            'ky_than_ngu_hanh' => $kyThanNguHanh,
            'so_luong_hy_than' => $soLuongHyThan,
            'so_luong_ky_than' => $soLuongKyThan,
            'truong_hop' => $truongHop,
            'ket_luan' => $ketLuan,
            'than_trang_thai' => $thanTrangThai,
            'chi_tiet' => $chiTietArray,
        ];
    }

    /**
     * Map kết luận ('Thân rất yếu', 'Thân yếu', ...) sang prefix trạng thái thân
     * trong cột than_trang_thai của bảng phan5_suc_khoe.
     */
    protected function mapKetLuanToThanTrangThai(?string $ketLuan): ?string
    {
        $ketLuan = trim((string) $ketLuan);
        if ($ketLuan === '') {
            return null;
        }

        return match ($ketLuan) {
            'Thân rất yếu' => 'THÂN RẤT YẾU',
            'Thân yếu' => 'THÂN YẾU',
            'Thân trung bình' => 'THÂN TRUNG BÌNH',
            'Thân vững' => 'THÂN VỮNG',
            'Thân rất vững' => 'THÂN RẤT VỮNG',
            default => null,
        };
    }

    /**
     * Thu thập tất cả Thập Thần unique từ response suNghiep, lấy nội dung từ giai_phap_thap_than.
     * Chỉ đưa vào Thập Thần có bản mệnh (natal) > 0; khi có $chatLuongThapThan thì lọc theo natal.
     *
     * @param  array<string, mixed>  $response
     * @param  array<int, array{name?: string, natal?: mixed}>|null  $chatLuongThapThan
     * @return array<int, array{thap_than: string, content: string}>
     */
    protected function buildGiaiPhapThapThanFromResponse(array $response, ?array $chatLuongThapThan = null): array
    {
        $collected = [];
        $add = static function (?string $v) use (&$collected): void {
            $v = trim((string) $v);
            if ($v !== '') {
                $collected[] = $v;
            }
        };

        $add($response['thap_than_thang'] ?? null);
        $add($response['thap_than_nam'] ?? null);
        $add($response['suc_khoe']['thap_than_ngay'] ?? null);

        $pp = $response['phat_trien_ban_than'] ?? null;
        if (is_array($pp)) {
            $add($pp['thien_can_gio']['thap_than'] ?? null);
            $tg = $pp['tang_can_gio'] ?? null;
            if (is_array($tg)) {
                $add($tg['thap_than'] ?? null);
            }
        }

        $tc = $response['tai_chinh_tang_can'] ?? null;
        if (is_array($tc)) {
            foreach (['gio', 'thang'] as $k) {
                $list = $tc[$k] ?? [];
                if (is_array($list)) {
                    foreach ($list as $item) {
                        if (is_array($item)) {
                            $add($item['thap_than'] ?? null);
                        }
                    }
                }
            }
        }

        $tinhCam = $response['tinh_cam_tang_can_ngay'] ?? [];
        if (is_array($tinhCam)) {
            foreach ($tinhCam as $item) {
                if (is_array($item)) {
                    $add($item['thap_than'] ?? null);
                }
            }
        }

        $knxh = $response['ket_noi_xa_hoi'] ?? null;
        if (is_array($knxh)) {
            $tcn = $knxh['thien_can_nam'] ?? null;
            if (is_array($tcn)) {
                $add($tcn['thap_than'] ?? null);
            }
            $tangNam = $knxh['tang_can_nam'] ?? [];
            if (is_array($tangNam)) {
                foreach ($tangNam as $item) {
                    if (is_array($item)) {
                        $add($item['thap_than'] ?? null);
                    }
                }
            }
        }

        $unique = array_values(array_unique(array_filter($collected, static fn ($v) => $v !== '')));
        if (empty($unique)) {
            return [];
        }

        if (is_array($chatLuongThapThan) && count($chatLuongThapThan) > 0) {
            $natalByThapThan = [];
            foreach ($chatLuongThapThan as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $natalByThapThan[$name] = (int) ($item['natal'] ?? 0);
                }
            }
            $unique = array_values(array_filter($unique, static function ($thapThan) use ($natalByThapThan): bool {
                $natal = $natalByThapThan[trim($thapThan)] ?? 0;

                return $natal > 0;
            }));
            if (empty($unique)) {
                return [];
            }
        }

        $orderMap = [];
        $allOrdered = GiaiPhapThapThan::getAllOrdered();
        foreach ($allOrdered as $i => $row) {
            $orderMap[$row->thap_than] = $row->sort_order;
        }

        $out = [];
        foreach ($unique as $thapThan) {
            $model = GiaiPhapThapThan::findByThapThan($thapThan);
            if ($model && trim((string) $model->content) !== '') {
                $out[] = [
                    'thap_than' => $thapThan,
                    'content' => (string) $model->content,
                    '_sort' => $orderMap[$thapThan] ?? 9999,
                ];
            }
        }
        usort($out, static fn ($a, $b) => ($a['_sort'] ?? 9999) <=> ($b['_sort'] ?? 9999));

        return array_map(static fn ($item) => [
            'thap_than' => $item['thap_than'],
            'content' => $item['content'],
        ], $out);
    }
}
