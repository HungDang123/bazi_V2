<?php

namespace App\Http\Controllers;

use App\Models\Sim;
use App\Models\Que64;
use App\Services\BaZiServiceV2;
use App\Services\KinhDichService;
use Illuminate\Http\Request;

class SimController extends Controller
{
    protected $bazi;
    protected $kinhDich;

    public function __construct(BaZiServiceV2 $bazi, KinhDichService $kinhDich)
    {
        $this->bazi = $bazi;
        $this->kinhDich = $kinhDich;
    }

    public function index(Request $request)
    {
        // Kiểm tra xem có yêu cầu chấm điểm không
        $scoreParams = [
            'score_d' => $request->score_d,
            'score_m' => $request->score_m,
            'score_y' => $request->score_y,
            'score_h' => $request->score_h,
            'score_minute' => $request->score_minute,
            'score_g' => $request->score_g,
        ];

        // Kiểm tra xem tất cả các trường có được điền hay không
        $hasAllScoreParams = !empty($scoreParams['score_d']) 
            && !empty($scoreParams['score_m']) 
            && !empty($scoreParams['score_y']) 
            && $scoreParams['score_h'] !== null 
            && $scoreParams['score_minute'] !== null 
            && !empty($scoreParams['score_g']);

        // Nếu có 1 trường điểm được điền nhưng không đủ tất cả, báo lỗi
        $hasAnyScoreParam = !empty($scoreParams['score_d']) 
            || !empty($scoreParams['score_m']) 
            || !empty($scoreParams['score_y']) 
            || $scoreParams['score_h'] !== null 
            || $scoreParams['score_minute'] !== null;

        if ($hasAnyScoreParam && !$hasAllScoreParams) {
            return redirect()->back()->with('error', 'Vui lòng nhập đầy đủ thông tin (ngày, tháng, năm, giờ, phút, giới tính) để chấm điểm sim!');
        }

        $query = Sim::with(['que64', 'queBien']);

        // Chỉ lấy sim chưa bán
        $query->where('status', 'available');

        // Filter by network operator
        if ($request->filled('network_operator')) {
            $query->where('network_operator', $request->network_operator);
        }

        // Filter by five element
        if ($request->filled('five_element')) {
            $query->where('five_element', $request->five_element);
        }

        // Filter by que
        if ($request->filled('que_id')) {
            $query->where('que_id', $request->que_id);
        }

        // Search by phone number
        if ($request->filled('search')) {
            $query->where('phone_number', 'LIKE', '%' . $request->search . '%');
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->max_price);
        }

        // Nếu có đầy đủ thông tin chấm điểm thì tính điểm và sắp xếp
        if ($hasAllScoreParams) {
            // Tính bát tự để lấy hỷ thần
            $baziResult = $this->bazi->calc(
                '', 
                (int)$scoreParams['score_y'], 
                (int)$scoreParams['score_m'], 
                (int)$scoreParams['score_d'], 
                (int)$scoreParams['score_h'], 
                (int)$scoreParams['score_minute'], 
                $scoreParams['score_g'], 
                2025
            );
            $hyThan = explode(', ', $baziResult['hy_ky_than']['hy_than_ngu_hanh']);

            // Lấy tất cả sim theo filter (không phân trang trước)
            $allSims = $query->get();

            // Lấy danh sách số điện thoại
            $phoneNumbers = $allSims->pluck('phone_number')->toArray();

            // Tính điểm cho tất cả sim cùng lúc (batch processing)
            $scoreResults = $this->kinhDich->diemSimVKBBatch($phoneNumbers, $hyThan);

            // Gán điểm vào từng sim
            foreach ($allSims as $sim) {
                $scoreResult = $scoreResults[$sim->phone_number] ?? null;
                if ($scoreResult && $scoreResult['success']) {
                    $sim->vkb_score = $scoreResult['tong_diem'];
                    $sim->vkb_type = $scoreResult['type'];
                } else {
                    $sim->vkb_score = 0;
                    $sim->vkb_type = 'N/A';
                }
            }

            // Sắp xếp theo điểm từ cao xuống thấp
            $allSims = $allSims->sortByDesc('vkb_score')->values();

            // Phân trang thủ công
            $perPage = 50;
            $currentPage = $request->get('page', 1);
            $total = $allSims->count();
            $sims = new \Illuminate\Pagination\LengthAwarePaginator(
                $allSims->forPage($currentPage, $perPage),
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // Sorting thông thường
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortBy, ['phone_number', 'selling_price', 'network_operator', 'status', 'five_element', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Pagination
            $sims = $query->paginate(50)->withQueryString();
        }

        // Get all que for filter dropdown
        $ques = Que64::orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => Sim::count(),
            'vinaphone' => Sim::where('network_operator', 'vinaphone')->count(),
            'mobifone' => Sim::where('network_operator', 'mobifone')->count(),
            'viettel' => Sim::where('network_operator', 'viettel')->count(),
            'available' => Sim::where('status', 'available')->count(),
            'sold' => Sim::where('status', 'sold')->count(),
        ];

        // Five elements count
        $fiveElementsCount = [
            'Kim' => Sim::where('five_element', 'Kim')->count(),
            'Mộc' => Sim::where('five_element', 'Mộc')->count(),
            'Thủy' => Sim::where('five_element', 'Thủy')->count(),
            'Hỏa' => Sim::where('five_element', 'Hỏa')->count(),
            'Thổ' => Sim::where('five_element', 'Thổ')->count(),
        ];

        return view('sims.index', compact('sims', 'ques', 'stats', 'fiveElementsCount'));
    }

    public function show($id)
    {
        $sim = Sim::with(['que64', 'queBien'])->findOrFail($id);
        return view('sims.show', compact('sim'));
    }

}
