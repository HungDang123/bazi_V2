<?php

namespace App\Http\Controllers;

use App\Services\ChatLuongNhatChuService;
use App\Models\NhatChuTruNgay;
use App\Models\Hanh;
use App\Models\HanhNoiDung;
use App\Models\Sim;
use App\Models\ViNhanNhatChu;
use App\Models\DinhViGocNhin;
use App\Services\BaZiServiceV2;
use App\Services\KinhDichService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Bazicontroller extends Controller
{
    protected $bazi;
    protected $kinhDich;

    public function __construct(BaZiServiceV2 $bazi, KinhDichService $kinhDich)
    {
        $this->bazi = $bazi;
        $this->kinhDich = $kinhDich;
    }

    public function calc(Request $req)
    {
        // Kiểm tra xem có check "không rõ giờ sinh" không
        $unknowBirthtime = $req->input('uknow_birthdate') == 1;

        // Validation
        $rules = [
            'full_name' => 'nullable|string|max:255',
            'y' => 'required|numeric|min:1940|max:2031',
            'm' => 'required|numeric|min:1|max:12',
            'd' => 'required|numeric|min:1|max:31',
            'g' => 'required|string|in:male,female',
            'uknow_birthdate' => 'nullable|in:0,1',
        ];

        // Nếu không check "không rõ giờ sinh" thì h và minute là bắt buộc
        if (!$unknowBirthtime) {
            $rules['h'] = 'required|numeric|min:0|max:23';
            $rules['minute'] = 'required|numeric|min:0|max:59';
        } else {
            $rules['h'] = 'nullable|numeric|min:0|max:23';
            $rules['minute'] = 'nullable|numeric|min:0|max:59';
        }

        $validated = $req->validate($rules, [
            'full_name.required' => 'Vui lòng nhập họ tên',
            'y.required' => 'Vui lòng nhập năm sinh',
            'y.numeric' => 'Năm sinh phải là số',
            'y.integer' => 'Năm sinh phải là số nguyên',
            'y.min' => 'Năm sinh phải từ 1940 trở lên',
            'y.max' => 'Năm sinh không hợp lệ',
            'm.required' => 'Vui lòng nhập tháng sinh',
            'm.numeric' => 'Tháng sinh phải là số',
            'm.integer' => 'Tháng sinh phải là số nguyên',
            'm.min' => 'Tháng sinh phải từ 1 đến 12',
            'm.max' => 'Tháng sinh phải từ 1 đến 12',
            'd.required' => 'Vui lòng nhập ngày sinh',
            'd.numeric' => 'Ngày sinh phải là số',
            'd.integer' => 'Ngày sinh phải là số nguyên',
            'd.min' => 'Ngày sinh phải từ 1 đến 31',
            'd.max' => 'Ngày sinh phải từ 1 đến 31',
            'h.required' => 'Vui lòng nhập giờ sinh',
            'h.numeric' => 'Giờ sinh phải là số',
            'h.integer' => 'Giờ sinh phải là số nguyên',
            'h.min' => 'Giờ sinh phải từ 0 đến 23',
            'h.max' => 'Giờ sinh phải từ 0 đến 23',
            'minute.required' => 'Vui lòng nhập phút sinh',
            'minute.numeric' => 'Phút sinh phải là số',
            'minute.integer' => 'Phút sinh phải là số nguyên',
            'minute.min' => 'Phút sinh phải từ 0 đến 59',
            'minute.max' => 'Phút sinh phải từ 0 đến 59',
            'g.required' => 'Vui lòng chọn giới tính',
            'g.in' => 'Giới tính không hợp lệ',
        ]);

        $fullName = $validated['full_name'] ?? '';
        $y = (int) $validated['y'];
        $m = (int) $validated['m'];
        $d = (int) $validated['d'];
        $h = isset($validated['h']) && $validated['h'] !== null ? (int) $validated['h'] : null;
        $minute = isset($validated['minute']) && $validated['minute'] !== null ? (int) $validated['minute'] : null;
        $g = $validated['g'];
        
        // Validate năm 1940 phải lớn hơn 1940-02-05 06:08:00
        if ($y == 1940) {
            $birthDateTime = \Carbon\Carbon::create($y, $m, $d, $h ?? 0, $minute ?? 0, 0);
            $minDateTime = \Carbon\Carbon::create(1940, 2, 5, 6, 8, 0);
            
            if ($birthDateTime->lessThanOrEqualTo($minDateTime)) {
                return response()->json([
                    'error' => 'Với năm sinh 1940, ngày giờ sinh phải lớn hơn 05/02/1940 06:08:00'
                ], 400);
            }
        }
        
        // Validate năm 2032 phải nhỏ hơn 2032-01-06 02:16:00
        if ($y == 2032) {
            $birthDateTime = \Carbon\Carbon::create($y, $m, $d, $h ?? 23, $minute ?? 59, 59);
            $maxDateTime = \Carbon\Carbon::create(2032, 1, 6, 2, 16, 0);
            
            if ($birthDateTime->greaterThanOrEqualTo($maxDateTime)) {
                return response()->json([
                    'error' => 'Với năm sinh 2032, ngày giờ sinh phải nhỏ hơn 06/01/2032 02:16:00'
                ], 400);
            }
        }
        
        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g);

        if (empty($result)) {
            return response()->json([
                'error' => 'Không có dữ liệu để tính lá số'
            ], 400);
        }

        $result['hanh_noi_dung_nien_van'] = $this->buildHanhNoiDungNienVan($result['ngu_hanh_dong'] ?? []);
        $result['nhat_chu_tru_ngay_view'] = $this->buildNhatChuTruNgayView($result['bat_tu'] ?? []);
        $phoneNumber = $req->input('phone');
        if (!empty($phoneNumber)) {
            $phoneNumber = preg_replace('/[\s\-\(\)]/', '', $phoneNumber);
            if (preg_match('/^\+84/', $phoneNumber)) {
                $phoneNumber = '0' . substr($phoneNumber, 3);
            } elseif (preg_match('/^84/', $phoneNumber)) {
                $phoneNumber = '0' . substr($phoneNumber, 2);
            }
            if (!preg_match('/^0[0-9]{9,10}$/', $phoneNumber)) {
                return response()->json([
                    'message' => 'Số điện thoại không đúng định dạng Việt Nam. Vui lòng nhập số điện thoại hợp lệ (ví dụ: 0912345678 hoặc +84912345678)'
                ], 400);
            }
            $hythan = explode(', ', $result['hy_ky_than']['hy_than_ngu_hanh']);
            $kythan = explode(', ', $result['hy_ky_than']['ky_than_ngu_hanh']);
            $diemSimKhachHang = $this->kinhDich->diemSimKhachHang($phoneNumber, $hythan, $kythan);
            if (!empty($diemSimKhachHang)) {
                $result['diem_sim_khach_hang'] = $diemSimKhachHang;
            }
        }

        // PHẦN 2: lấy qua GET /api/phan-2/chi-so-khia-canh-than-sat (tránh trùng payload với phan-2).
        unset($result['chi_so_bieu_do_cot'], $result['quy_nhan_van_xuong']);

        return response()->json($result);
    }

    /**
     * PHẦN 3 - Tổng quan ngũ hành bản mệnh (nội dung tĩnh từ DB).
     */
    public function phan3TongQuanNguHanh()
    {
        $items = DinhViGocNhin::getAllOrdered()
            ->map(fn ($item) => [
                'slug' => $item->slug,
                'title' => $item->title,
                'content' => $item->content,
                'sort_order' => $item->sort_order,
            ])->values()->all();

        return response()->json([
            'data' => $items,
            'image_map' => [
                \App\Services\DocxTextService::BO_CUC_IMAGE_NGU_HANH => \App\Services\DocxTextService::publicUrlForResourcePath(
                    \App\Services\DocxTextService::BO_CUC_IMAGE_NGU_HANH
                ),
                \App\Services\DocxTextService::BO_CUC_IMAGE_BAN_MENH => \App\Services\DocxTextService::publicUrlForResourcePath(
                    \App\Services\DocxTextService::BO_CUC_IMAGE_BAN_MENH
                ),
            ],
        ]);
    }

    /**
     * PHẦN 4 - Chất lượng nhật chủ (logic giữ nguyên như calc).
     */
    public function phan4ChatLuongNhatChu(Request $req)
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
            return response()->json(['data' => null]);
        }

        $view = ChatLuongNhatChuService::buildFromBatTu($result['bat_tu'] ?? []);

        return response()->json(['data' => $view]);
    }

    /**
     * PHẦN 2 — BIỂU ĐỒ 6 KHÍA CẠNH + Thần Sát (Quý Nhân, Văn Xương, …).
     *
     * Gọi lại toàn bộ bazi->calc như /api/bazi/calc (có thể tối ưu cache/tách hàm sau).
     */
    public function phan2ChiSoKhiaCanhThanSat(Request $req)
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

        if ($y == 1940) {
            $birthDateTime = \Carbon\Carbon::create($y, $m, $d, $h ?? 0, $minute ?? 0, 0);
            $minDateTime = \Carbon\Carbon::create(1940, 2, 5, 6, 8, 0);
            if ($birthDateTime->lessThanOrEqualTo($minDateTime)) {
                return response()->json([
                    'error' => 'Với năm sinh 1940, ngày giờ sinh phải lớn hơn 05/02/1940 06:08:00',
                ], 400);
            }
        }
        if ($y == 2032) {
            $birthDateTime = \Carbon\Carbon::create($y, $m, $d, $h ?? 23, $minute ?? 59, 59);
            $maxDateTime = \Carbon\Carbon::create(2032, 1, 6, 2, 16, 0);
            if ($birthDateTime->greaterThanOrEqualTo($maxDateTime)) {
                return response()->json([
                    'error' => 'Với năm sinh 2032, ngày giờ sinh phải nhỏ hơn 06/01/2032 02:16:00',
                ], 400);
            }
        }

        $result = $this->bazi->calc($fullName, $y, $m, $d, $h, $minute, $g);
        if (empty($result)) {
            return response()->json(['error' => 'Không có dữ liệu để tính lá số'], 400);
        }

        return response()->json([
            'data' => [
                'chi_so_bieu_do_cot' => $result['chi_so_bieu_do_cot'] ?? null,
                'quy_nhan_van_xuong' => $result['quy_nhan_van_xuong'] ?? null,
            ],
        ]);
    }

    public function calcPhanTramNguHanh(Request $req)
    {
        $menhCan = (string) $req->query('menh_can', '');
        $fullName = (string) $req->query('full_name', '');
        $y = (int) $req->query('y', 1997);
        $m = (int) $req->query('m', 10);
        $d = (int) $req->query('d', 12);
        $h = $req->query('h') ? (int) $req->query('h') : null;
        $g = (string) $req->query('g', 'male');
        $minute = $req->query('minute') ? (int) $req->query('minute') : null;
        $result = $this->bazi->chatluongnguhanh(Str::slug($menhCan), $fullName, $g, $y, $m, $d, $h, $minute);
        return response()->json($result);
    }

    public function diemSimVKB(Request $req)
    {
        $simId = $req->query('sim_id');
        $y = (int) $req->query('y', 1997);
        $m = (int) $req->query('m', 10);
        $d = (int) $req->query('d', 12);
        $h = $req->query('h') ? (int) $req->query('h') : null;
        $g = (string) $req->query('g', 'male');
        $minute = $req->query('minute') ? (int) $req->query('minute') : null;

        // Tính bát tự để lấy hỷ thần
        $result = $this->bazi->calc('', $y, $m, $d, $h, $minute, $g);
        $hythan = explode(', ', $result['hy_ky_than']['hy_than_ngu_hanh']);

        // Gọi hàm chấm điểm sim VKB
        $diemSimVKB = $this->kinhDich->diemSimVKB($simId, $hythan);

        return response()->json($diemSimVKB);
    }

    /**
     * Map percentage to HanhNoiDung slug.
     */
    private function phanTramToSlug(int $percent): string
    {
        if ($percent == 0) {
            return 'khuyet_0';
        }
        if ($percent < 30) {
            return 'duoi_30';
        }
        if ($percent < 60) {
            return '30_60';
        }
        if ($percent < 80) {
            return '60_80';
        }
        return 'tren_80';
    }

    /**
     * Build hanh_noi_dung_nien_van from ngu_hanh_dong.
     *
     * @param  array<string, int>  $phanTramNienVan  ['thuy' => 59, 'moc' => 0, 'hoa' => 33, 'tho' => 36, 'kim' => 55]
     * @return array<int, array{hanh_name: string, hanh_slug: string, percent: int, slug: string, items: array<int, array{title: string|null, content: string|null}>}>
     */
    private function buildHanhNoiDungNienVan(array $phanTramNienVan): array
    {
        $elements = ['thuy', 'moc', 'hoa', 'tho', 'kim'];
        $out = [];

        foreach ($elements as $elementSlug) {
            $percent = (int) ($phanTramNienVan[$elementSlug] ?? 0);
            $slug = $this->phanTramToSlug($percent);

            $hanh = Hanh::where('slug', $elementSlug)->first();
            $hanhName = $hanh ? $hanh->name : $elementSlug;

            $items = [];
            if ($hanh) {
                $records = HanhNoiDung::where('hanh_id', $hanh->id)
                    ->where('slug', $slug)
                    ->orderBy('sort_order')
                    ->get();

                foreach ($records as $r) {
                    $items[] = [
                        'title' => $r->title,
                        'content' => $r->content,
                    ];
                }
            }

            $out[] = [
                'hanh_name' => $hanhName,
                'hanh_slug' => $elementSlug,
                'percent' => $percent,
                'slug' => $slug,
                'items' => $items,
            ];
        }

        return $out;
    }

    /**
     * Build nhat_chu_tru_ngay_view from bat_tu.
     * Lấy Thiên Can + Địa Chi ngày -> NhatChuTruNgay (title, chapter, sub_title, content).
     *
     * @param  array  $batTu  bat_tu from BaZiServiceV2
     * @return array{tru_ngay: string, items: array<int, array{title: string|null, chapter: string|null, sub_title: string|null, content: string|null}>}
     */
    private function buildNhatChuTruNgayView(array $batTu): array
    {
        $thienCanNgay = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));
        $diaChiNgay = trim((string) ($batTu['day']['chi']['dia_chi'] ?? ''));
        if ($diaChiNgay === 'Tí') {
            $diaChiNgay = 'Tý';
        }

        $truNgay = $thienCanNgay && $diaChiNgay ? "{$thienCanNgay} {$diaChiNgay}" : '';
        $records = NhatChuTruNgay::findByThienCanDiaChi($thienCanNgay, $diaChiNgay);

        $chapters = [];
        $title = $records->first()?->title ?? null;

        foreach ($records as $r) {
            $lastIdx = array_key_last($chapters);
            $sameChapter = $lastIdx !== null && ($chapters[$lastIdx]['chapter'] ?? null) === $r->chapter;

            if ($sameChapter) {
                $chapters[$lastIdx]['sub_sections'][] = [
                    'sub_title' => $r->sub_title,
                    'content' => $r->content,
                ];
            } else {
                $chapters[] = [
                    'chapter' => $r->chapter,
                    'sub_sections' => [
                        ['sub_title' => $r->sub_title, 'content' => $r->content],
                    ],
                ];
            }
        }

        $viNhanRecords = ViNhanNhatChu::findByThienCan($thienCanNgay);
        $viNhan = $viNhanRecords->pluck('ten_nguoi')->values()->all();

        return [
            'tru_ngay' => $truNgay,
            'title' => $title,
            'chapters' => $chapters,
            'vi_nhan' => $viNhan,
        ];
    }
}
