<?php

namespace App\Services;

class ThanSatServiceV2
{
    private $thienCan = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];
    private $diaChi = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

    /**
     * Tính toán tất cả thần sát cho tứ trụ
     * @param array $tuTru ['nam' => ['can' => 'Giáp', 'chi' => 'Tý'], 'thang' => ..., 'ngay' => ..., 'gio' => ...]
     * @param string $gioiTinh 'nam' hoặc 'nữ'
     * @param string $napAmNam 'Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'
     */
    public function tinhThanSat($tuTru, $gioiTinh = 'nam', $napAmNam = null)
    {
        $result = [
            'cat_than' => [],
            'trung_tinh' => [],
            'hung_than' => []
        ];

        // === CÁT THẦN ===
        $result['cat_than']['thien_at_quy_nhan'] = $this->tinhThienAtQuyNhan($tuTru);
        $result['cat_than']['thai_cuc_quy_nhan'] = $this->tinhThaiCucQuyNhan($tuTru);
        $result['cat_than']['thien_duc_quy_nhan'] = $this->tinhThienDucQuyNhan($tuTru);
        $result['cat_than']['nguyet_duc_quy_nhan'] = $this->tinhNguyetDucQuyNhan($tuTru);
        $result['cat_than']['van_xuong_quy_nhan'] = $this->tinhVanXuongQuyNhan($tuTru);
        $result['cat_than']['tam_ky_quy_nhan'] = $this->tinhTamKyQuyNhan($tuTru);
        $result['cat_than']['tuong_tinh'] = $this->tinhTuongTinh($tuTru);
        
        if ($napAmNam) {
            $result['cat_than']['hoc_duong_tu_quan'] = $this->tinhHocDuongTuQuan($tuTru, $napAmNam);
        }
        
        $result['cat_than']['quoc_an'] = $this->tinhQuocAn($tuTru);
        $result['cat_than']['kim_du'] = $this->tinhKimDu($tuTru);
        $result['cat_than']['thien_y'] = $this->tinhThienY($tuTru);
        $result['cat_than']['loc_than'] = $this->tinhLocThan($tuTru);
        $result['cat_than']['thien_xa'] = $this->tinhThienXa($tuTru);
        $result['cat_than']['am_chu_duong_thu'] = $this->tinhAmChuDuongThu($tuTru);
        $result['cat_than']['kim_than'] = $this->tinhKimThan($tuTru);
        $result['cat_than']['hong_loan_thien_hy'] = $this->tinhHongLoanThienHy($tuTru);
        $result['cat_than']['phuc_tinh'] = $this->tinhPhucTinh($tuTru);

        // === THẦN TRUNG TÍNH ===
        $result['trung_tinh']['hoa_cai'] = $this->tinhHoaCai($tuTru);
        $result['trung_tinh']['dich_ma'] = $this->tinhDichMa($tuTru);
        $result['trung_tinh']['khoi_cuong'] = $this->tinhKhoiCuong($tuTru);
        $result['trung_tinh']['hong_diem'] = $this->tinhHongDiem($tuTru);
        $result['trung_tinh']['duong_nhan'] = $this->tinhDuongNhan($tuTru);

        // === HUNG THẦN ===
        $result['hung_than']['tai_sat'] = $this->tinhTaiSat($tuTru);
        $result['hung_than']['kiep_sat'] = $this->tinhKiepSat($tuTru);
        $result['hung_than']['vong_than'] = $this->tinhVongThan($tuTru);
        $result['hung_than']['thien_la_dia_vong'] = $this->tinhThienLaDiaVong($tuTru);
        $result['hung_than']['thap_ac_dai_bai'] = $this->tinhThapAcDaiBai($tuTru);
        $result['hung_than']['co_than_qua_tu'] = $this->tinhCoThanQuaTu($tuTru);
        $result['hung_than']['am_duong_sai_tho'] = $this->tinhAmDuongSaiTho($tuTru);
        $result['hung_than']['tang_mon_dieu_khach'] = $this->tinhTangMonDieuKhach($tuTru);
        $result['hung_than']['nguyen_than'] = $this->tinhNguyenThan($tuTru, $gioiTinh);
        $result['hung_than']['cau_giao'] = $this->tinhCauGiao($tuTru, $gioiTinh);
        $result['hung_than']['huyet_nhan'] = $this->tinhHuyetNhan($tuTru);
        $result['hung_than']['quan_phu'] = $this->tinhQuanPhu($tuTru);
        $result['hung_than']['cach_goc'] = $this->tinhCachGoc($tuTru);
        $result['hung_than']['dao_hoa'] = $this->tinhDaoHoa($tuTru);
        $result['hung_than']['khong_vong'] = $this->tinhKhongVong($tuTru);

        // Loại bỏ giá trị null/empty
        $result['cat_than'] = array_filter($result['cat_than']);
        $result['trung_tinh'] = array_filter($result['trung_tinh']);
        $result['hung_than'] = array_filter($result['hung_than']);

        return $result;
    }

    // ============= CÁT THẦN =============

    private function tinhThienAtQuyNhan($tuTru)
    {
        $bang = [
            'Giáp' => ['Sửu', 'Mùi'],
            'Ất' => ['Tý', 'Thân'],
            'Bính' => ['Dậu', 'Hợi'],
            'Đinh' => ['Dậu', 'Hợi'],
            'Mậu' => ['Sửu', 'Mùi'],
            'Kỷ' => ['Tý', 'Thân'],
            'Canh' => ['Dần', 'Ngọ'],
            'Tân' => ['Dần', 'Ngọ'],
            'Nhâm' => ['Mão', 'Tỵ'],
            'Quý' => ['Mão', 'Tỵ']
        ];

        $ketQua = [];
        
        // Tra theo can năm
        $canNam = $tuTru['nam']['can'];
        if (isset($bang[$canNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if (in_array($tuTru[$tru]['chi'], $bang[$canNam])) {
                    $ketQua[] = "Thiên Ất Quý Nhân ở trụ $tru (theo can năm)";
                }
            }
        }

        // Tra theo can ngày
        $canNgay = $tuTru['ngay']['can'];
        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if (in_array($tuTru[$tru]['chi'], $bang[$canNgay])) {
                    $ketQua[] = "Thiên Ất Quý Nhân ở trụ $tru (theo can ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhThaiCucQuyNhan($tuTru)
    {
        $bang = [
            'Giáp' => ['Tý', 'Ngọ'],
            'Ất' => ['Tý', 'Ngọ'],
            'Bính' => ['Mão', 'Dậu'],
            'Đinh' => ['Mão', 'Dậu'],
            'Mậu' => ['Thìn', 'Tuất', 'Sửu', 'Mùi'],
            'Kỷ' => ['Thìn', 'Tuất', 'Sửu', 'Mùi'],
            'Canh' => ['Dần', 'Hợi'],
            'Tân' => ['Dần', 'Hợi'],
            'Nhâm' => ['Tỵ', 'Thân'],
            'Quý' => ['Tỵ', 'Thân']
        ];

        $ketQua = [];
        
        // Tra theo can năm
        $canNam = $tuTru['nam']['can'];
        if (isset($bang[$canNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if (in_array($tuTru[$tru]['chi'], $bang[$canNam])) {
                    $ketQua[] = "Thái Cực Quý Nhân ở trụ $tru (theo can năm)";
                }
            }
        }

        // Tra theo can ngày
        $canNgay = $tuTru['ngay']['can'];
        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if (in_array($tuTru[$tru]['chi'], $bang[$canNgay])) {
                    $ketQua[] = "Thái Cực Quý Nhân ở trụ $tru (theo can ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhThienDucQuyNhan($tuTru)
    {
        $bang = [
            'Dần' => 'Đinh',
            'Mão' => 'Thân',
            'Thìn' => 'Nhâm',
            'Tỵ' => 'Tân',
            'Ngọ' => 'Hợi',
            'Mùi' => 'Giáp',
            'Thân' => 'Quý',
            'Dậu' => 'Dần',
            'Tuất' => 'Bính',
            'Hợi' => 'Ất',
            'Tý' => 'Tỵ',
            'Sửu' => 'Canh'
        ];

        $chiThang = $tuTru['thang']['chi'];
        $ketQua = [];

        if (isset($bang[$chiThang])) {
            $canCanTim = $bang[$chiThang];
            
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['can'] == $canCanTim) {
                    $ketQua[] = "Thiên Đức Quý Nhân ở trụ $tru";
                }
                
                // Tra cả chi
                if ($tuTru[$tru]['chi'] == $bang[$chiThang]) {
                    $ketQua[] = "Thiên Đức Quý Nhân ở trụ $tru (chi)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhNguyetDucQuyNhan($tuTru)
    {
        $bang = [
            'Dần' => 'Bính',
            'Mão' => 'Giáp',
            'Thìn' => 'Nhâm',
            'Tỵ' => 'Canh',
            'Ngọ' => 'Bính',
            'Mùi' => 'Giáp',
            'Thân' => 'Nhâm',
            'Dậu' => 'Canh',
            'Tuất' => 'Bính',
            'Hợi' => 'Giáp',
            'Tý' => 'Nhâm',
            'Sửu' => 'Canh'
        ];

        $chiThang = $tuTru['thang']['chi'];
        $ketQua = [];

        if (isset($bang[$chiThang])) {
            $canCanTim = $bang[$chiThang];
            
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['can'] == $canCanTim) {
                    $ketQua[] = "Nguyệt Đức Quý Nhân ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhVanXuongQuyNhan($tuTru)
    {
        $bang = [
            'Giáp' => 'Tỵ',
            'Ất' => 'Ngọ',
            'Bính' => 'Thân',
            'Đinh' => 'Dậu',
            'Mậu' => 'Thân',
            'Kỷ' => 'Dậu',
            'Canh' => 'Hợi',
            'Tân' => 'Tý',
            'Nhâm' => 'Dần',
            'Quý' => 'Mão'
        ];

        $canNam = $tuTru['nam']['can'];
        $ketQua = [];

        if (isset($bang[$canNam])) {
            $chiCanTim = $bang[$canNam];
            
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $chiCanTim) {
                    $ketQua[] = "Văn Xương Quý Nhân ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhTamKyQuyNhan($tuTru)
    {
        $ketQua = [];
        $canNam = $tuTru['nam']['can'];
        $canThang = $tuTru['thang']['can'];
        $canNgay = $tuTru['ngay']['can'];

        // Thiên Thượng Tam Kỳ
        if (($canNam == 'Giáp' && $canThang == 'Mậu' && $canNgay == 'Canh') ||
            ($canNam == 'Canh' && $canThang == 'Mậu' && $canNgay == 'Giáp')) {
            $ketQua[] = 'Thiên Thượng Tam Kỳ';
        }

        // Địa Hạ Tam Kỳ
        if (($canNam == 'Ất' && $canThang == 'Bính' && $canNgay == 'Đinh') ||
            ($canNam == 'Đinh' && $canThang == 'Bính' && $canNgay == 'Ất')) {
            $ketQua[] = 'Địa Hạ Tam Kỳ';
        }

        // Nhân Trung Tam Kỳ
        if (($canNam == 'Nhâm' && $canThang == 'Quý' && $canNgay == 'Tân') ||
            ($canNam == 'Tân' && $canThang == 'Quý' && $canNgay == 'Nhâm')) {
            $ketQua[] = 'Nhân Trung Tam Kỳ';
        }

        return $ketQua;
    }

    private function tinhTuongTinh($tuTru)
    {
        $bang = [
            'Tý' => 'Tý',
            'Sửu' => 'Dậu',
            'Dần' => 'Ngọ',
            'Mão' => 'Mão',
            'Thìn' => 'Tý',
            'Tỵ' => 'Dậu',
            'Ngọ' => 'Ngọ',
            'Mùi' => 'Mão',
            'Thân' => 'Tý',
            'Dậu' => 'Dậu',
            'Tuất' => 'Ngọ',
            'Hợi' => 'Mão'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Tướng Tinh ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Tướng Tinh ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhHocDuongTuQuan($tuTru, $napAmNam)
    {
        $bang = [
            'Mộc' => ['hoc_duong' => 'Hợi', 'tu_quan' => 'Dần'],
            'Hỏa' => ['hoc_duong' => 'Dần', 'tu_quan' => 'Tỵ'],
            'Thổ' => ['hoc_duong' => 'Thân', 'tu_quan' => 'Hợi'],
            'Kim' => ['hoc_duong' => 'Tỵ', 'tu_quan' => 'Thân'],
            'Thủy' => ['hoc_duong' => 'Thân', 'tu_quan' => 'Hợi']
        ];

        $ketQua = [];
        if (isset($bang[$napAmNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$napAmNam]['hoc_duong']) {
                    $ketQua[] = "Học Đường ở trụ $tru";
                }
                if ($tuTru[$tru]['chi'] == $bang[$napAmNam]['tu_quan']) {
                    $ketQua[] = "Từ Quán ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhQuocAn($tuTru)
    {
        $bang = [
            'Giáp' => 'Tuất',
            'Ất' => 'Hợi',
            'Bính' => 'Sửu',
            'Đinh' => 'Dần',
            'Mậu' => 'Sửu',
            'Kỷ' => 'Dần',
            'Canh' => 'Thìn',
            'Tân' => 'Tỵ',
            'Nhâm' => 'Mùi',
            'Quý' => 'Thân'
        ];

        $ketQua = [];

        // Theo can năm
        $canNam = $tuTru['nam']['can'];
        if (isset($bang[$canNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNam]) {
                    $ketQua[] = "Quốc Ấn ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo can ngày
        $canNgay = $tuTru['ngay']['can'];
        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNgay]) {
                    $ketQua[] = "Quốc Ấn ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhKimDu($tuTru)
    {
        $bang = [
            'Giáp' => 'Thìn',
            'Ất' => 'Tỵ',
            'Bính' => 'Mùi',
            'Đinh' => 'Thân',
            'Mậu' => 'Mùi',
            'Kỷ' => 'Thân',
            'Canh' => 'Tuất',
            'Tân' => 'Hợi',
            'Nhâm' => 'Sửu',
            'Quý' => 'Dần'
        ];

        $ketQua = [];

        // Theo can năm
        $canNam = $tuTru['nam']['can'];
        if (isset($bang[$canNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNam]) {
                    $ketQua[] = "Kim Dư ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo can ngày
        $canNgay = $tuTru['ngay']['can'];
        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNgay]) {
                    $ketQua[] = "Kim Dư ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhThienY($tuTru)
    {
        $bang = [
            'Tý' => 'Hợi',
            'Sửu' => 'Tý',
            'Dần' => 'Sửu',
            'Mão' => 'Dần',
            'Thìn' => 'Mão',
            'Tỵ' => 'Thìn',
            'Ngọ' => 'Tỵ',
            'Mùi' => 'Ngọ',
            'Thân' => 'Mùi',
            'Dậu' => 'Thân',
            'Tuất' => 'Dậu',
            'Hợi' => 'Tuất'
        ];

        $chiThang = $tuTru['thang']['chi'];
        $ketQua = [];

        if (isset($bang[$chiThang])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiThang]) {
                    $ketQua[] = "Thiên Y ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhLocThan($tuTru)
    {
        $bang = [
            'Giáp' => 'Dần',
            'Ất' => 'Mão',
            'Bính' => 'Tỵ',
            'Đinh' => 'Ngọ',
            'Mậu' => 'Tỵ',
            'Kỷ' => 'Ngọ',
            'Canh' => 'Thân',
            'Tân' => 'Dậu',
            'Nhâm' => 'Hợi',
            'Quý' => 'Tý'
        ];

        $canNgay = $tuTru['ngay']['can'];
        $ketQua = [];

        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNgay]) {
                    $ketQua[] = "Lộc Thần ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhThienXa($tuTru)
    {
        $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
        
        $bangThienXa = [
            'Xuân' => ['Mậu Dần', ['Dần', 'Mão', 'Thìn']],
            'Hạ' => ['Giáp Ngọ', ['Tỵ', 'Ngọ', 'Mùi']],
            'Thu' => ['Mậu Thân', ['Thân', 'Dậu', 'Tuất']],
            'Đông' => ['Giáp Tý', ['Hợi', 'Tý', 'Sửu']]
        ];

        $chiThang = $tuTru['thang']['chi'];
        
        foreach ($bangThienXa as $mua => $data) {
            if (in_array($chiThang, $data[1]) && $truNgay == $data[0]) {
                return ["Thiên Xá ở trụ ngày (mùa $mua)"];
            }
        }

        return [];
    }

    private function tinhAmChuDuongThu($tuTru)
    {
        $bang = [
            'Tý' => 'Dần',
            'Sửu' => 'Sửu',
            'Dần' => 'Tý',
            'Mão' => 'Hợi',
            'Thìn' => 'Tuất',
            'Tỵ' => 'Dậu',
            'Ngọ' => 'Tuất',
            'Mùi' => 'Hợi',
            'Thân' => 'Tý',
            'Dậu' => 'Sửu',
            'Tuất' => 'Dần',
            'Hợi' => 'Mão'
        ];

        $chiThang = $tuTru['thang']['chi'];
        $ketQua = [];

        if (isset($bang[$chiThang])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiThang]) {
                    $ketQua[] = "Âm Chú Dương Thụ ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhKimThan($tuTru)
    {
        $danhSach = ['Ất Sửu', 'Kỷ Tỵ', 'Quý Dậu'];
        $ketQua = [];

        $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
        if (in_array($truNgay, $danhSach)) {
            $ketQua[] = 'Kim Thần ở trụ ngày';
        }

        $truGio = $tuTru['gio']['can'] . ' ' . $tuTru['gio']['chi'];
        if (in_array($truGio, $danhSach)) {
            $ketQua[] = 'Kim Thần ở trụ giờ';
        }

        return $ketQua;
    }

    private function tinhHongLoanThienHy($tuTru)
    {
        $bangHongLoan = [
            'Tý' => 'Mão', 'Sửu' => 'Dần', 'Dần' => 'Sửu', 'Mão' => 'Tý',
            'Thìn' => 'Hợi', 'Tỵ' => 'Tuất', 'Ngọ' => 'Dậu', 'Mùi' => 'Thân',
            'Thân' => 'Mùi', 'Dậu' => 'Ngọ', 'Tuất' => 'Tỵ', 'Hợi' => 'Thìn'
        ];

        $bangThienHy = [
            'Tý' => 'Hợi', 'Sửu' => 'Tuất', 'Dần' => 'Dậu', 'Mão' => 'Thân',
            'Thìn' => 'Mùi', 'Tỵ' => 'Ngọ', 'Ngọ' => 'Tỵ', 'Mùi' => 'Thìn',
            'Thân' => 'Mão', 'Dậu' => 'Dần', 'Tuất' => 'Sửu', 'Hợi' => 'Tý'
        ];

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bangHongLoan[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangHongLoan[$chiNam]) {
                    $ketQua[] = "Hồng Loan ở trụ $tru";
                }
            }
        }

        if (isset($bangThienHy[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangThienHy[$chiNam]) {
                    $ketQua[] = "Thiên Hỷ ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhPhucTinh($tuTru)
    {
        $danhSach = [
            'Giáp Dần', 'Ất Sửu', 'Bính Tý', 'Đinh Dậu', 'Mậu Thân',
            'Kỷ Mùi', 'Canh Ngọ', 'Tân Tỵ', 'Nhâm Thìn', 'Quý Mão'
        ];

        $ketQua = [];

        foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
            $canChi = $tuTru[$tru]['can'] . ' ' . $tuTru[$tru]['chi'];
            if (in_array($canChi, $danhSach)) {
                $ketQua[] = "Phúc Tinh ở trụ $tru";
            }
        }

        return $ketQua;
    }

    // ============= THẦN TRUNG TÍNH =============

    private function tinhHoaCai($tuTru)
    {
        $bang = [
            'Tý' => 'Thìn', 'Sửu' => 'Sửu', 'Dần' => 'Tuất', 'Mão' => 'Mùi',
            'Thìn' => 'Thìn', 'Tỵ' => 'Sửu', 'Ngọ' => 'Tuất', 'Mùi' => 'Mùi',
            'Thân' => 'Thìn', 'Dậu' => 'Sửu', 'Tuất' => 'Tuất', 'Hợi' => 'Mùi'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Hoa Cái ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Hoa Cái ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhDichMa($tuTru)
    {
        $bang = [
            'Tý' => 'Dần', 'Sửu' => 'Hợi', 'Dần' => 'Thân', 'Mão' => 'Tỵ',
            'Thìn' => 'Dần', 'Tỵ' => 'Hợi', 'Ngọ' => 'Thân', 'Mùi' => 'Tỵ',
            'Thân' => 'Dần', 'Dậu' => 'Hợi', 'Tuất' => 'Thân', 'Hợi' => 'Tỵ'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Dịch Mã ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Dịch Mã ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhKhoiCuong($tuTru)
    {
        $danhSach = ['Canh Thìn', 'Nhâm Thìn', 'Canh Tuất', 'Mậu Tuất'];
        
        $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
        if (in_array($truNgay, $danhSach)) {
            return ['Khôi Cương ở trụ ngày'];
        }

        return [];
    }

    private function tinhHongDiem($tuTru)
    {
        $bang = [
            'Giáp' => 'Ngọ', 'Ất' => 'Thân', 'Bính' => 'Dần', 'Đinh' => 'Mùi',
            'Mậu' => 'Thìn', 'Kỷ' => 'Thìn', 'Canh' => 'Thân', 'Tân' => 'Dậu',
            'Nhâm' => 'Tý', 'Quý' => 'Tuất'
        ];

        $ketQua = [];

        // Theo can năm
        $canNam = $tuTru['nam']['can'];
        if (isset($bang[$canNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNam]) {
                    $ketQua[] = "Hồng Diễm ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo can ngày
        $canNgay = $tuTru['ngay']['can'];
        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNgay]) {
                    $ketQua[] = "Hồng Diễm ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhDuongNhan($tuTru)
    {
        $bang = [
            'Giáp' => 'Mão', 'Ất' => 'Dần', 'Bính' => 'Ngọ', 'Đinh' => 'Tỵ',
            'Mậu' => 'Ngọ', 'Kỷ' => 'Tỵ', 'Canh' => 'Dậu', 'Tân' => 'Thân',
            'Nhâm' => 'Tý', 'Quý' => 'Hợi'
        ];

        $canNgay = $tuTru['ngay']['can'];
        $ketQua = [];

        if (isset($bang[$canNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$canNgay]) {
                    $ketQua[] = "Dương Nhận ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    // ============= HUNG THẦN =============

    private function tinhTaiSat($tuTru)
    {
        $bang = [
            'Tý' => 'Ngọ', 'Sửu' => 'Mão', 'Dần' => 'Tý', 'Mão' => 'Dậu',
            'Thìn' => 'Ngọ', 'Tỵ' => 'Mão', 'Ngọ' => 'Tý', 'Mùi' => 'Dậu',
            'Thân' => 'Ngọ', 'Dậu' => 'Mão', 'Tuất' => 'Tý', 'Hợi' => 'Dậu'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Tai Sát ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Tai Sát ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhKiepSat($tuTru)
    {
        $bang = [
            'Tý' => 'Tỵ', 'Sửu' => 'Dần', 'Dần' => 'Hợi', 'Mão' => 'Thân',
            'Thìn' => 'Tỵ', 'Tỵ' => 'Dần', 'Ngọ' => 'Hợi', 'Mùi' => 'Thân',
            'Thân' => 'Tỵ', 'Dậu' => 'Dần', 'Tuất' => 'Hợi', 'Hợi' => 'Thân'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Kiếp Sát ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Kiếp Sát ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhVongThan($tuTru)
    {
        $bang = [
            'Tý' => 'Hợi', 'Sửu' => 'Thân', 'Dần' => 'Tỵ', 'Mão' => 'Dần',
            'Thìn' => 'Hợi', 'Tỵ' => 'Thân', 'Ngọ' => 'Tỵ', 'Mùi' => 'Dần',
            'Thân' => 'Hợi', 'Dậu' => 'Thân', 'Tuất' => 'Tỵ', 'Hợi' => 'Dần'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Vong Thần ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Vong Thần ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhThienLaDiaVong($tuTru)
    {
        $ketQua = [];

        // Thiên La
        $chiNam = $tuTru['nam']['chi'];
        if ($chiNam == 'Tuất') {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == 'Hợi') {
                    $ketQua[] = "Thiên La ở trụ $tru (theo năm)";
                }
            }
        }

        $chiNgay = $tuTru['ngay']['chi'];
        if ($chiNgay == 'Tuất') {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == 'Hợi') {
                    $ketQua[] = "Thiên La ở trụ $tru (theo ngày)";
                }
            }
        }

        // Địa Võng
        if ($chiNam == 'Thìn') {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == 'Tỵ') {
                    $ketQua[] = "Địa Võng ở trụ $tru (theo năm)";
                }
            }
        }

        if ($chiNgay == 'Thìn') {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == 'Tỵ') {
                    $ketQua[] = "Địa Võng ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhThapAcDaiBai($tuTru)
    {
        $danhSach = [
            'Giáp Thìn', 'Ất Tỵ', 'Bính Thân', 'Đinh Hợi', 'Mậu Tuất',
            'Kỷ Sửu', 'Canh Thìn', 'Tân Tỵ', 'Nhâm Thân', 'Quý Hợi'
        ];

        $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
        if (in_array($truNgay, $danhSach)) {
            return ['Thập Ác Đại Bại ở trụ ngày'];
        }

        return [];
    }

    private function tinhCoThanQuaTu($tuTru)
    {
        $bangCoThan = [
            'Tý' => 'Dần', 'Sửu' => 'Dần', 'Dần' => 'Tỵ', 'Mão' => 'Tỵ',
            'Thìn' => 'Tỵ', 'Tỵ' => 'Thân', 'Ngọ' => 'Thân', 'Mùi' => 'Thân',
            'Thân' => 'Hợi', 'Dậu' => 'Hợi', 'Tuất' => 'Hợi', 'Hợi' => 'Dần'
        ];

        $bangQuaTu = [
            'Tý' => 'Tuất', 'Sửu' => 'Tuất', 'Dần' => 'Sửu', 'Mão' => 'Sửu',
            'Thìn' => 'Sửu', 'Tỵ' => 'Thìn', 'Ngọ' => 'Thìn', 'Mùi' => 'Thìn',
            'Thân' => 'Mùi', 'Dậu' => 'Mùi', 'Tuất' => 'Mùi', 'Hợi' => 'Tuất'
        ];

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bangCoThan[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangCoThan[$chiNam]) {
                    $ketQua[] = "Cô Thần ở trụ $tru";
                }
            }
        }

        if (isset($bangQuaTu[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangQuaTu[$chiNam]) {
                    $ketQua[] = "Quả Tú ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhAmDuongSaiTho($tuTru)
    {
        $danhSach = [
            'Bính Tý', 'Đinh Sửu', 'Mậu Dần', 'Tân Mão', 'Nhâm Thìn', 'Quý Tỵ',
            'Bính Ngọ', 'Đinh Mùi', 'Mậu Thân', 'Tân Dậu', 'Nhâm Tuất', 'Quý Hợi'
        ];

        $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
        if (in_array($truNgay, $danhSach)) {
            return ['Âm Dương Sai Thố ở trụ ngày'];
        }

        return [];
    }

    private function tinhTangMonDieuKhach($tuTru)
    {
        $bangTangMon = [
            'Tý' => 'Dần', 'Sửu' => 'Mão', 'Dần' => 'Thìn', 'Mão' => 'Tỵ',
            'Thìn' => 'Ngọ', 'Tỵ' => 'Mùi', 'Ngọ' => 'Thân', 'Mùi' => 'Dậu',
            'Thân' => 'Tuất', 'Dậu' => 'Hợi', 'Tuất' => 'Tý', 'Hợi' => 'Sửu'
        ];

        $bangDieuKhach = [
            'Tý' => 'Tuất', 'Sửu' => 'Hợi', 'Dần' => 'Tý', 'Mão' => 'Sửu',
            'Thìn' => 'Dần', 'Tỵ' => 'Mão', 'Ngọ' => 'Thìn', 'Mùi' => 'Tỵ',
            'Thân' => 'Ngọ', 'Dậu' => 'Mùi', 'Tuất' => 'Thân', 'Hợi' => 'Dậu'
        ];

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bangTangMon[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangTangMon[$chiNam]) {
                    $ketQua[] = "Tang Môn ở trụ $tru";
                }
            }
        }

        if (isset($bangDieuKhach[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangDieuKhach[$chiNam]) {
                    $ketQua[] = "Điếu Khách ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhNguyenThan($tuTru, $gioiTinh)
    {
        // Xác định âm dương của năm
        $canNam = $tuTru['nam']['can'];
        $amDuongNam = in_array($canNam, ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm']) ? 'duong' : 'am';

        // Xác định dương nam âm nữ hay âm nam dương nữ
        $laDuongNamAmNu = ($amDuongNam == 'duong' && $gioiTinh == 'nam') || 
                          ($amDuongNam == 'am' && $gioiTinh == 'nữ');

        if ($laDuongNamAmNu) {
            $bang = [
                'Tý' => 'Mùi', 'Sửu' => 'Thân', 'Dần' => 'Dậu', 'Mão' => 'Tuất',
                'Thìn' => 'Hợi', 'Tỵ' => 'Tý', 'Ngọ' => 'Sửu', 'Mùi' => 'Dần',
                'Thân' => 'Mão', 'Dậu' => 'Thìn', 'Tuất' => 'Tỵ', 'Hợi' => 'Thân'
            ];
        } else {
            $bang = [
                'Tý' => 'Tỵ', 'Sửu' => 'Ngọ', 'Dần' => 'Mùi', 'Mão' => 'Thân',
                'Thìn' => 'Dậu', 'Tỵ' => 'Tuất', 'Ngọ' => 'Hợi', 'Mùi' => 'Tý',
                'Thân' => 'Sửu', 'Dậu' => 'Dần', 'Tuất' => 'Mão', 'Hợi' => 'Thìn'
            ];
        }

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Nguyên Thần ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhCauGiao($tuTru, $gioiTinh)
    {
        // Xác định âm dương của năm
        $canNam = $tuTru['nam']['can'];
        $amDuongNam = in_array($canNam, ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm']) ? 'duong' : 'am';

        $laDuongNamAmNu = ($amDuongNam == 'duong' && $gioiTinh == 'nam') || 
                          ($amDuongNam == 'am' && $gioiTinh == 'nữ');

        if ($laDuongNamAmNu) {
            $bangCau = [
                'Tý' => 'Mão', 'Sửu' => 'Thìn', 'Dần' => 'Tỵ', 'Mão' => 'Ngọ',
                'Thìn' => 'Mùi', 'Tỵ' => 'Thân', 'Ngọ' => 'Dậu', 'Mùi' => 'Tuất',
                'Thân' => 'Hợi', 'Dậu' => 'Tý', 'Tuất' => 'Sửu', 'Hợi' => 'Dần'
            ];
            $bangGiao = [
                'Tý' => 'Dậu', 'Sửu' => 'Tuất', 'Dần' => 'Hợi', 'Mão' => 'Tý',
                'Thìn' => 'Sửu', 'Tỵ' => 'Dần', 'Ngọ' => 'Mão', 'Mùi' => 'Thìn',
                'Thân' => 'Tỵ', 'Dậu' => 'Ngọ', 'Tuất' => 'Mùi', 'Hợi' => 'Thân'
            ];
        } else {
            $bangCau = [
                'Tý' => 'Dậu', 'Sửu' => 'Tuất', 'Dần' => 'Hợi', 'Mão' => 'Tý',
                'Thìn' => 'Sửu', 'Tỵ' => 'Dần', 'Ngọ' => 'Mão', 'Mùi' => 'Thìn',
                'Thân' => 'Tỵ', 'Dậu' => 'Ngọ', 'Tuất' => 'Mùi', 'Hợi' => 'Thân'
            ];
            $bangGiao = [
                'Tý' => 'Mão', 'Sửu' => 'Thìn', 'Dần' => 'Tỵ', 'Mão' => 'Ngọ',
                'Thìn' => 'Mùi', 'Tỵ' => 'Thân', 'Ngọ' => 'Dậu', 'Mùi' => 'Tuất',
                'Thân' => 'Hợi', 'Dậu' => 'Tý', 'Tuất' => 'Sửu', 'Hợi' => 'Dần'
            ];
        }

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bangCau[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangCau[$chiNam]) {
                    $ketQua[] = "Câu ở trụ $tru";
                }
            }
        }

        if (isset($bangGiao[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bangGiao[$chiNam]) {
                    $ketQua[] = "Giảo ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhHuyetNhan($tuTru)
    {
        $bang = [
            'Tý' => 'Tuất', 'Sửu' => 'Dậu', 'Dần' => 'Thân', 'Mão' => 'Mùi',
            'Thìn' => 'Ngọ', 'Tỵ' => 'Tỵ', 'Ngọ' => 'Thìn', 'Mùi' => 'Mão',
            'Thân' => 'Dần', 'Dậu' => 'Sửu', 'Tuất' => 'Tý', 'Hợi' => 'Hợi'
        ];

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Huyết Nhẫn ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhQuanPhu($tuTru)
    {
        $bang = [
            'Tý' => 'Thìn', 'Sửu' => 'Tỵ', 'Dần' => 'Ngọ', 'Mão' => 'Mùi',
            'Thìn' => 'Thân', 'Tỵ' => 'Dậu', 'Ngọ' => 'Tuất', 'Mùi' => 'Hợi',
            'Thân' => 'Tý', 'Dậu' => 'Sửu', 'Tuất' => 'Dần', 'Hợi' => 'Mão'
        ];

        $chiNam = $tuTru['nam']['chi'];
        $ketQua = [];

        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Quan Phù ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhCachGoc($tuTru)
    {
        $bang = [
            'Tý' => 'Dần', 'Sửu' => 'Mão', 'Dần' => 'Thìn', 'Mão' => 'Tỵ',
            'Thìn' => 'Ngọ', 'Tỵ' => 'Mùi', 'Ngọ' => 'Thân', 'Mùi' => 'Dậu',
            'Thân' => 'Tuất', 'Dậu' => 'Hợi', 'Tuất' => 'Tý', 'Hợi' => 'Sửu'
        ];

        $chiNgay = $tuTru['ngay']['chi'];
        $ketQua = [];

        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Cách Góc ở trụ $tru";
                }
            }
        }

        return $ketQua;
    }

    private function tinhDaoHoa($tuTru)
    {
        $bang = [
            'Tý' => 'Dậu', 'Sửu' => 'Ngọ', 'Dần' => 'Mão', 'Mão' => 'Tý',
            'Thìn' => 'Dậu', 'Tỵ' => 'Ngọ', 'Ngọ' => 'Mão', 'Mùi' => 'Tý',
            'Thân' => 'Dậu', 'Dậu' => 'Ngọ', 'Tuất' => 'Mão', 'Hợi' => 'Tý'
        ];

        $ketQua = [];

        // Theo chi năm
        $chiNam = $tuTru['nam']['chi'];
        if (isset($bang[$chiNam])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNam]) {
                    $ketQua[] = "Đào Hoa ở trụ $tru (theo năm)";
                }
            }
        }

        // Theo chi ngày
        $chiNgay = $tuTru['ngay']['chi'];
        if (isset($bang[$chiNgay])) {
            foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                if ($tuTru[$tru]['chi'] == $bang[$chiNgay]) {
                    $ketQua[] = "Đào Hoa ở trụ $tru (theo ngày)";
                }
            }
        }

        return $ketQua;
    }

    private function tinhKhongVong($tuTru)
    {
        // Định nghĩa 6 nhóm Không Vong
        $bang = [
            [
                'tru_ngay' => ['Giáp Tý', 'Ất Sửu', 'Bính Dần', 'Đinh Mão', 'Mậu Thìn', 'Kỷ Tỵ', 
                               'Canh Ngọ', 'Tân Mùi', 'Nhâm Thân', 'Quý Dậu'],
                'khong_vong' => ['Tuất', 'Hợi']
            ],
            [
                'tru_ngay' => ['Giáp Tuất', 'Ất Hợi', 'Bính Tý', 'Đinh Sửu', 'Mậu Dần', 'Kỷ Mão',
                               'Canh Thìn', 'Tân Tỵ', 'Nhâm Ngọ', 'Quý Mùi'],
                'khong_vong' => ['Thân', 'Dậu']
            ],
            [
                'tru_ngay' => ['Giáp Thân', 'Ất Dậu', 'Bính Tuất', 'Đinh Hợi', 'Mậu Tý', 'Kỷ Sửu',
                               'Canh Dần', 'Tân Mão', 'Nhâm Thìn', 'Quý Tỵ'],
                'khong_vong' => ['Ngọ', 'Mùi']
            ],
            [
                'tru_ngay' => ['Giáp Ngọ', 'Ất Mùi', 'Bính Thân', 'Đinh Dậu', 'Mậu Tuất', 'Kỷ Hợi',
                               'Canh Tý', 'Tân Sửu', 'Nhâm Dần', 'Quý Mão'],
                'khong_vong' => ['Thìn', 'Tỵ']
            ],
            [
                'tru_ngay' => ['Giáp Thìn', 'Ất Tỵ', 'Bính Ngọ', 'Đinh Mùi', 'Mậu Thân', 'Kỷ Dậu',
                               'Canh Tuất', 'Tân Hợi', 'Nhâm Tý', 'Quý Sửu'],
                'khong_vong' => ['Dần', 'Mão']
            ],
            [
                'tru_ngay' => ['Giáp Dần', 'Ất Mão', 'Bính Thìn', 'Đinh Tỵ', 'Mậu Ngọ', 'Kỷ Mùi',
                               'Canh Thân', 'Tân Dậu', 'Nhâm Tuất', 'Quý Hợi'],
                'khong_vong' => ['Tý', 'Sửu']
            ]
        ];

        $truNgay = $tuTru['ngay']['can'] . ' ' . $tuTru['ngay']['chi'];
        $ketQua = [];

        foreach ($bang as $nhom) {
            if (in_array($truNgay, $nhom['tru_ngay'])) {
                foreach (['nam', 'thang', 'ngay', 'gio'] as $tru) {
                    if (in_array($tuTru[$tru]['chi'], $nhom['khong_vong'])) {
                        $ketQua[] = "Không Vong ở trụ $tru (" . implode(', ', $nhom['khong_vong']) . ")";
                    }
                }
                break;
            }
        }

        return $ketQua;
    }
}
