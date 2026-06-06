<?php

namespace App\Services;

use App\Models\ThanSatRule;
use App\Models\ThanSatRuleDetail;

class ThanSatCalculatorService
{
    public function tinhThanSat($tuTru, $gioiTinh = null, $amDuong = null)
    {
        $results = [];
        
        $rules = ThanSatRule::with(['details' => function($query) {
            $query->orderBy('thu_tu_uu_tien', 'asc');
        }])->get();
        
        foreach ($rules as $rule) {
            $thanSatResults = $this->kiemTraRule($rule, $tuTru, $gioiTinh, $amDuong);
            $results = array_merge($results, $thanSatResults);
        }
        
        return $this->phanLoaiTheoTru($results);
    }
    
    private function kiemTraRule($rule, $tuTru, $gioiTinh, $amDuong)
    {
        $results = [];
        
        foreach ($rule->details as $detail) {
            $matchedData = $this->kiemTraDetail($detail, $tuTru, $gioiTinh, $amDuong);
            
            foreach ($matchedData as $data) {
                $results[] = [
                    'than_sat_id' => $rule->id,
                    'ten_than_sat' => $rule->ten_than_sat,
                    'loai_than_sat' => $rule->loai_than_sat,
                    'phuong_phap_tra_cuu' => $rule->phuong_phap_tra_cuu,
                    'vi_tri_xuat_hien' => $data['position'], // Sửa: vị trí thực tế tìm thấy
                    'vi_tri_tim_thay' => $detail->vi_tri_tim_thay,
                    'loai_tra_cuu' => $detail->loai_tra_cuu,
                    'gia_tri_tra_cuu' => $detail->gia_tri_tra_cuu,
                    'tru_tim_thay' => $data['found_in'] // Thêm: tìm thấy ở trụ nào
                ];
            }
        }
        
        return $results;
    }
    
    private function kiemTraDetail($detail, $tuTru, $gioiTinh, $amDuong)
    {
        $matchedData = [];
        
        switch ($detail->loai_tra_cuu) {
            // THIÊN ẤT, THÁI CỰC - Tra cứu theo Can
            case 'can_nam':
            case 'can_ngay':
                $truType = $this->getTruType($detail->loai_tra_cuu);
                $can = $tuTru[$truType]['can'];
                
                if ($this->kiemTraGiaTri($can, $detail->gia_tri_tra_cuu)) {
                    $foundPositions = $this->timViTriThucTe($tuTru, $detail->vi_tri_tim_thay);
                    foreach ($foundPositions as $position => $tru) {
                        $matchedData[] = [
                            'position' => $position,
                            'found_in' => $tru
                        ];
                    }
                }
                break;
                
            // TANG MÔN, ĐIẾU KHÁCH, CÔ THẦN - Tra cứu theo Chi năm
            case 'chi_nam':
                $chiNam = $tuTru['nam']['chi'];
                if ($this->kiemTraGiaTri($chiNam, $detail->gia_tri_tra_cuu)) {
                    $foundPositions = $this->timViTriThucTe($tuTru, $detail->vi_tri_tim_thay);
                    foreach ($foundPositions as $position => $tru) {
                        $matchedData[] = [
                            'position' => $position,
                            'found_in' => $tru
                        ];
                    }
                }
                break;
                
            // DỊCH MÃ - Tra cứu theo Chi năm/ngày
            case 'chi_ngay':
                $chiNgay = $tuTru['ngay']['chi'];
                if ($this->kiemTraGiaTri($chiNgay, $detail->gia_tri_tra_cuu)) {
                    $foundPositions = $this->timViTriThucTe($tuTru, $detail->vi_tri_tim_thay);
                    foreach ($foundPositions as $position => $tru) {
                        $matchedData[] = [
                            'position' => $position,
                            'found_in' => $tru
                        ];
                    }
                }
                break;
                
            // ÂM DƯƠNG SAI THỐ - Tra cứu trụ ngày
            case 'tru_ngay':
                $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
                if ($this->kiemTraGiaTri($truNgay, $detail->gia_tri_tra_cuu)) {
                    $matchedData[] = [
                        'position' => 'ngay',
                        'found_in' => 'ngay'
                    ];
                }
                break;
                
            // PHÚC TINH - Tra cứu trụ giờ
            case 'tru_gio':
                $truGio = $tuTru['gio']['can'] . ' ' . $tuTru['gio']['chi'];
                if ($this->kiemTraGiaTri($truGio, $detail->gia_tri_tra_cuu)) {
                    $matchedData[] = [
                        'position' => 'gio',
                        'found_in' => 'gio'
                    ];
                }
                break;
                
            // HUYẾT NHẪN - Tra cứu theo Chi năm
            case 'chi_nam':
                $chiNam = $tuTru['nam']['chi'];
                if ($this->kiemTraGiaTri($chiNam, $detail->gia_tri_tra_cuu)) {
                    $foundPositions = $this->timViTriThucTe($tuTru, $detail->vi_tri_tim_thay);
                    foreach ($foundPositions as $position => $tru) {
                        $matchedData[] = [
                            'position' => $position,
                            'found_in' => $tru
                        ];
                    }
                }
                break;
                
            // TAI SÁT - Tra cứu theo Chi năm
            case 'chi_nam':
                $chiNam = $tuTru['nam']['chi'];
                if ($this->kiemTraGiaTri($chiNam, $detail->gia_tri_tra_cuu)) {
                    $foundPositions = $this->timViTriThucTe($tuTru, $detail->vi_tri_tim_thay);
                    foreach ($foundPositions as $position => $tru) {
                        $matchedData[] = [
                            'position' => $position,
                            'found_in' => $tru
                        ];
                    }
                }
                break;
        }
        
        return $matchedData;
    }
    
    private function timViTriThucTe($tuTru, $chiCanTim)
    {
        $positions = [];
        $chiList = explode(', ', $chiCanTim);
        
        foreach ($chiList as $chi) {
            $chi = trim($chi);
            
            // Kiểm tra từng trụ và trả về đúng vị trí tìm thấy
            if ($tuTru['nam']['chi'] === $chi) {
                $positions['nam'] = 'nam';
            }
            if ($tuTru['thang']['chi'] === $chi) {
                $positions['thang'] = 'thang';
            }
            if ($tuTru['ngay']['chi'] === $chi) {
                $positions['ngay'] = 'ngay';
            }
            if ($tuTru['gio']['chi'] === $chi) {
                $positions['gio'] = 'gio';
            }
        }
        
        return $positions;
    }
    
    private function getTruType($loaiTraCuu)
    {
        $mapping = [
            'can_nam' => 'nam',
            'can_ngay' => 'ngay', 
            'chi_nam' => 'nam',
            'chi_ngay' => 'ngay',
            'chi_thang' => 'thang'
        ];
        
        return $mapping[$loaiTraCuu] ?? 'nam';
    }
    
    private function kiemTraGiaTri($giaTriInput, $giaTriRule)
    {
        if (str_contains($giaTriRule, ', ')) {
            $giaTriList = explode(', ', $giaTriRule);
            return in_array($giaTriInput, $giaTriList);
        }
        
        return $giaTriInput === $giaTriRule;
    }
    
    private function phanLoaiTheoTru($thanSatList)
    {
        $result = [
            'nam' => [],
            'thang' => [],
            'ngay' => [],
            'gio' => [],
            'multiple' => []
        ];
        
        foreach ($thanSatList as $thanSat) {
            $viTri = $thanSat['vi_tri_xuat_hien'];
            
            if (isset($result[$viTri])) {
                $result[$viTri][] = $thanSat;
            } else {
                $result['multiple'][] = $thanSat;
            }
        }
        
        return $result;
    }
}