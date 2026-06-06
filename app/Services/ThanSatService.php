<?php

namespace App\Services;

use App\Models\ThanSatRule;
use App\Models\ThanSatRuleDetail;
use App\Models\ThanSatResult;

class ThanSatService
{
    protected array $stems = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];
    protected array $branches = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];
    
    public function calcThanSat($pillars, $gender)
    {
        $yearStem = $pillars['year']['can'];
        $yearBranch = $pillars['year']['chi'];
        $monthStem = $pillars['month']['can'];
        $monthBranch = $pillars['month']['chi'];
        $dayStem = $pillars['day']['can'];
        $dayBranch = $pillars['day']['chi']; 
        $hourStem = $pillars['hour']['can'];
        $hourBranch = $pillars['hour']['chi'];

        // Lấy tất cả quy tắc thần sát
        $rules = ThanSatRule::with('details')->get();
        
        // Khởi tạo mảng kết quả theo cấu trúc mới
        $results = [
            'nam' => [],
            'thang' => [],
            'ngay' => [],
            'gio' => []
        ];

        foreach ($rules as $rule) {
            $found = false;
            $locations = [];

            foreach ($rule->details as $detail) {
                switch ($detail->loai_tra_cuu) {
                    case 'can_nam':
                        if ($yearStem === $detail->gia_tri_tra_cuu) {
                            $found = true;
                            $locations = array_merge($locations, $this->findLocations($detail->vi_tri_tim_thay, $pillars));
                        }
                        break;
                        
                    case 'can_ngay': 
                        if ($dayStem === $detail->gia_tri_tra_cuu) {
                            $found = true;
                            $locations = array_merge($locations, $this->findLocations($detail->vi_tri_tim_thay, $pillars));
                        }
                        break;
                        
                    case 'chi_nam':
                        if ($yearBranch === $detail->gia_tri_tra_cuu) {
                            $found = true;
                            $locations = array_merge($locations, $this->findLocations($detail->vi_tri_tim_thay, $pillars));
                        }
                        break;
                        
                    case 'chi_thang':
                        if ($monthBranch === $detail->gia_tri_tra_cuu) {
                            $found = true;
                            $locations = array_merge($locations, $this->findLocations($detail->vi_tri_tim_thay, $pillars));
                        }
                        break;
                        
                    case 'chi_ngay':
                        if ($dayBranch === $detail->gia_tri_tra_cuu) {
                            $found = true;
                            $locations = array_merge($locations, $this->findLocations($detail->vi_tri_tim_thay, $pillars));
                        }
                        break;

                    case 'tru_ngay':
                        $ngayCan = $dayStem;
                        $values = explode(', ', $detail->gia_tri_tra_cuu);
                        if (in_array($ngayCan, $values)) {
                            $found = true;
                            $locations = array_merge($locations, ['ngay']);
                        }
                        break;

                    case 'nap_am':
                        $napAm = $this->getNapAm($yearStem, $yearBranch);
                        if (strpos($napAm, $detail->gia_tri_tra_cuu) !== false) {
                            $found = true;
                            $locations = array_merge($locations, $this->findLocations($detail->vi_tri_tim_thay, $pillars));
                        }
                        break;
                        
                    case 'mua_sinh':
                        $season = $this->getSeason($monthBranch);
                        if ($season === $detail->gia_tri_tra_cuu) {
                            $found = true;
                            $locations = array_merge($locations, ['ngay']);
                        }
                        break;
                }
            }

            if ($found && !empty($locations)) {
                $thanSatInfo = [
                    'than_sat' => $rule->ten_than_sat,
                    'loai' => $rule->loai_than_sat
                ];
                
                // Thêm thần sát vào từng trụ tương ứng
                foreach (array_unique($locations) as $location) {
                    $results[$location][] = $thanSatInfo;
                }
            }
        }

        return [
            'year' => $results['nam'] ?? [],
            'month' => $results['thang'] ?? [],
            'day' => $results['ngay'] ?? [],
            'hour' => $results['gio'] ?? []
        ];
    }

    protected function findLocations($locations, $pillars) 
    {
        $found = [];
        $locations = explode(', ', $locations);
        
        foreach ($locations as $loc) {
            if ($loc === $pillars['year']['chi']) {
                $found[] = 'nam';
            }
            if ($loc === $pillars['month']['chi']) {
                $found[] = 'thang'; 
            }
            if ($loc === $pillars['day']['chi']) {
                $found[] = 'ngay';
            }
            if ($loc === $pillars['hour']['chi']) {
                $found[] = 'gio';
            }
        }
        
        return $found;
    }

    protected function getSeason($chi)
    {
        $seasons = [
            'Dần' => 'Xuân', 'Mão' => 'Xuân', 'Thìn' => 'Xuân',
            'Tỵ' => 'Hạ', 'Ngọ' => 'Hạ', 'Mùi' => 'Hạ',
            'Thân' => 'Thu', 'Dậu' => 'Thu', 'Tuất' => 'Thu',
            'Hợi' => 'Đông', 'Tý' => 'Đông', 'Sửu' => 'Đông'
        ];
        
        return $seasons[$chi] ?? '';
    }

    protected function getNapAm($can, $chi) 
    {
        $napAms = [
            'GiápTý' => 'Hải Trung Kim',
            'ẤtSửu' => 'Hải Trung Kim',
            'BínhDần' => 'Lư Trung Hỏa',
            'ĐinhMão' => 'Lư Trung Hỏa',
            'MậuThìn' => 'Đại Lâm Mộc',
            'KỷTỵ' => 'Đại Lâm Mộc',
            'CanhNgọ' => 'Lộ Bàng Thổ',
            'TânMùi' => 'Lộ Bàng Thổ',
            'NhâmThân' => 'Kiếm Phong Kim',
            'QuýDậu' => 'Kiếm Phong Kim',
            'GiápTuất' => 'Sơn Đầu Hỏa',
            'ẤtHợi' => 'Sơn Đầu Hỏa',
            'BínhTý' => 'Giản Hạ Thủy',
            'ĐinhSửu' => 'Giản Hạ Thủy', 
            'MậuDần' => 'Thành Đầu Thổ',
            'KỷMão' => 'Thành Đầu Thổ',
            'CanhThìn' => 'Bạch Lạp Kim',
            'TânTỵ' => 'Bạch Lạp Kim',
            'NhâmNgọ' => 'Dương Liễu Mộc',
            'QuýMùi' => 'Dương Liễu Mộc'
        ];
        
        $key = $can . $chi;
        return $napAms[$key] ?? '';
    }

    protected function isYangDay($can)
    {
        $yangStems = ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm'];
        return in_array($can, $yangStems);
    }
}
