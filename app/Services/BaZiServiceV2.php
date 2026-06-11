<?php

namespace App\Services;

use Overtrue\ChineseCalendar\Calendar;
use App\Models\HyKyThan;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BaZiServiceV2

{
    protected static $stems = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];

    protected static $branches = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

    protected static $lucthan = [
        'Giáp' => [
            'moc'   => 'Huynh Đệ',
            'hoa'   => 'Tử Tôn',
            'tho'   => 'Thê Tài',
            'kim'   => 'Quan Quỷ',
            'thuy'  => 'Phụ Mẫu',
        ],
        'Ất' => [
            'moc'   => 'Huynh Đệ',
            'hoa'   => 'Tử Tôn',
            'tho'   => 'Thê Tài',
            'kim'   => 'Quan Quỷ',
            'thuy'  => 'Phụ Mẫu',
        ],
        'Bính' => [
            'moc'   => 'Phụ Mẫu',
            'hoa'   => 'Huynh Đệ',
            'tho'   => 'Tử Tôn',
            'kim'   => 'Thê Tài',
            'thuy'  => 'Quan Quỷ',
        ],
        'Đinh' => [
            'moc'   => 'Phụ Mẫu',
            'hoa'   => 'Huynh Đệ',
            'tho'   => 'Tử Tôn',
            'kim'   => 'Thê Tài',
            'thuy'  => 'Quan Quỷ',
        ],
        'Mậu' => [
            'moc'   => 'Quan Quỷ',
            'hoa'   => 'Phụ Mẫu',
            'tho'   => 'Huynh Đệ',
            'kim'   => 'Tử Tôn',
            'thuy'  => 'Thê Tài',
        ],
        'Kỷ' => [
            'moc'   => 'Quan Quỷ',
            'hoa'   => 'Phụ Mẫu',
            'tho'   => 'Huynh Đệ',
            'kim'   => 'Tử Tôn',
            'thuy'  => 'Thê Tài',
        ],
        'Canh' => [
            'moc'   => 'Thê Tài',
            'hoa'   => 'Quan Quỷ',
            'tho'   => 'Phụ Mẫu',
            'kim'   => 'Huynh Đệ',
            'thuy'  => 'Tử Tôn',
        ],
        'Tân' => [
            'moc'   => 'Thê Tài',
            'hoa'   => 'Quan Quỷ',
            'tho'   => 'Phụ Mẫu',
            'kim'   => 'Huynh Đệ',
            'thuy'  => 'Tử Tôn',
        ],
        'Nhâm' => [
            'moc'   => 'Tử Tôn',
            'hoa'   => 'Thê Tài',
            'tho'   => 'Quan Quỷ',
            'kim'   => 'Phụ Mẫu',
            'thuy'  => 'Huynh Đệ',
        ],
        'Quý' => [
            'moc'   => 'Tử Tôn',
            'hoa'   => 'Thê Tài',
            'tho'   => 'Quan Quỷ',
            'kim'   => 'Phụ Mẫu',
            'thuy'  => 'Huynh Đệ',
        ],
    ];
    
    // Bảng Quý Nhân & Văn Xương theo Thiên Can trụ ngày
    protected static $quyNhanVanXuong = [
        'Giáp' => [
            'quy_nhan' => ['Sửu', 'Mùi'],
            'van_xuong' => 'Tỵ',
        ],
        'Ất' => [
            'quy_nhan' => ['Thân', 'Tý'],
            'van_xuong' => 'Ngọ',
        ],
        'Bính' => [
            'quy_nhan' => ['Dậu', 'Hợi'],
            'van_xuong' => 'Thân',
        ],
        'Đinh' => [
            'quy_nhan' => ['Dậu', 'Hợi'],
            'van_xuong' => 'Dậu',
        ],
        'Mậu' => [
            'quy_nhan' => ['Sửu', 'Mùi'],
            'van_xuong' => 'Thân',
        ],
        'Kỷ' => [
            'quy_nhan' => ['Thân', 'Tý'],
            'van_xuong' => 'Dậu',
        ],
        'Canh' => [
            'quy_nhan' => ['Sửu', 'Mùi'],
            'van_xuong' => 'Hợi',
        ],
        'Tân' => [
            'quy_nhan' => ['Dần', 'Ngọ'],
            'van_xuong' => 'Tý',
        ],
        'Nhâm' => [
            'quy_nhan' => ['Mão', 'Tỵ'],
            'van_xuong' => 'Dần',
        ],
        'Quý' => [
            'quy_nhan' => ['Mão', 'Tỵ'],
            'van_xuong' => 'Mão',
        ],
    ];

    protected static $khong_vong_data = [
        ['tru_ngay' => 'Giáp Dần', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Ất Mão', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Bính Thìn', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Đinh Tỵ', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Mậu Ngọ', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Kỷ Mùi', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Canh Thân', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Tân Dậu', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Nhâm Tuất', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Quý Hợi', 'dia_chi_khong_vong' => ['Tý', 'Sửu']],
        ['tru_ngay' => 'Giáp Thìn', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Ất Tỵ', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Bính Ngọ', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Đinh Mùi', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Mậu Thân', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Kỷ Dậu', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Canh Tuất', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Tân Hợi', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Nhâm Tý', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Quý Sửu', 'dia_chi_khong_vong' => ['Dần', 'Mão']],
        ['tru_ngay' => 'Giáp Ngọ', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Ất Mùi', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Bính Thân', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Đinh Dậu', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Mậu Tuất', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Kỷ Hợi', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Canh Tý', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Tân Sửu', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Nhâm Dần', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Quý Mão', 'dia_chi_khong_vong' => ['Thìn', 'Tỵ']],
        ['tru_ngay' => 'Giáp Thân', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Ất Dậu', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Bính Tuất', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Đinh Hợi', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Mậu Tý', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Kỷ Sửu', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Canh Dần', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Tân Mão', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Nhâm Thìn', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Quý Tỵ', 'dia_chi_khong_vong' => ['Ngọ', 'Mùi']],
        ['tru_ngay' => 'Giáp Tuất', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Ất Hợi', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Bính Tý', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Đinh Sửu', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Mậu Dần', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Kỷ Mão', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Canh Thìn', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Tân Tỵ', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Nhâm Ngọ', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Quý Mùi', 'dia_chi_khong_vong' => ['Thân', 'Dậu']],
        ['tru_ngay' => 'Giáp Tý', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Ất Sửu', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Bính Dần', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Đinh Mão', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Mậu Thìn', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Kỷ Tỵ', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Canh Ngọ', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Tân Mùi', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Nhâm Thân', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']],
        ['tru_ngay' => 'Quý Dậu', 'dia_chi_khong_vong' => ['Tuất', 'Hợi']]
    ];
    // Can tàng (ẩn can)
    protected static $hiddenStems = [
        'Tý'  => ['Quý'],
        'Sửu' => ['Quý', 'Kỷ', 'Tân'],
        'Dần' => ['Bính', 'Giáp', 'Mậu'],
        'Mão' => ['Ất'],
        'Thìn' => ['Ất', 'Mậu', 'Quý'],
        'Tỵ'  => ['Canh', 'Bính', 'Mậu'],
        'Ngọ' => ['Kỷ', 'Đinh',],
        'Mùi' => ['Đinh', 'Kỷ',  'Ất'],
        'Thân' => ['Nhâm', 'Canh', 'Mậu'],
        'Dậu' => ['Tân'],
        'Tuất' => ['Tân', 'Mậu', 'Đinh'],
        'Hợi' => ['Giáp', 'Nhâm'],
    ];

    // Ngũ hành của Thiên Can
    protected static $stemElements = [
        'Giáp' => 'Mộc',
        'Ất' => 'Mộc',
        'Bính' => 'Hỏa',
        'Đinh' => 'Hỏa',
        'Mậu' => 'Thổ',
        'Kỷ' => 'Thổ',
        'Canh' => 'Kim',
        'Tân' => 'Kim',
        'Nhâm' => 'Thủy',
        'Quý' => 'Thủy',
    ];

    // Âm Dương của Thiên Can (0 = Dương, 1 = Âm)
    protected static $stemYinYang = [
        'Giáp' => 0,
        'Bính' => 0,
        'Mậu' => 0,
        'Canh' => 0,
        'Nhâm' => 0,
        'Ất' => 1,
        'Đinh' => 1,
        'Kỷ' => 1,
        'Tân' => 1,
        'Quý' => 1,
    ];

    protected static $truongSinhCycle = [
        'Giáp' => [
            'Tý' => 'Mộc Dục',
            'Sửu' => 'Quan Đới',
            'Dần' => 'Lâm Quan',
            'Mão' => 'Đế Vượng',
            'Thìn' => 'Suy',
            'Tỵ' => 'Bệnh',
            'Ngọ' => 'Tử',
            'Mùi' => 'Mộ',
            'Thân' => 'Tuyệt',
            'Dậu' => 'Thai',
            'Tuất' => 'Dưỡng',
            'Hợi' => 'Trường Sinh'

        ],
        'Ất' => [
            'Tý' => 'Bệnh',
            'Sửu' => 'Suy',
            'Dần' => 'Đế Vượng',
            'Mão' => 'Lâm Quan',
            'Thìn' => 'Quan Đới',
            'Tỵ' => 'Mộc Dục',
            'Ngọ' => 'Trường Sinh',
            'Mùi' => 'Dưỡng',
            'Thân' => 'Thai',
            'Dậu' => 'Tuyệt',
            'Tuất' => 'Mộ',
            'Hợi' => 'Tử'
        ],     // Mộc
        'Bính' => [
            'Tý' => 'Thai',
            'Sửu' => 'Dưỡng',
            'Dần' => 'Trường Sinh',
            'Mão' => 'Mộc Dục',
            'Thìn' => 'Quan Đới',
            'Tỵ' => 'Lâm Quan',
            'Ngọ' => 'Đế Vượng',
            'Mùi' => 'Suy',
            'Thân' => 'Bệnh',
            'Dậu' => 'Tử',
            'Tuất' => 'Mộ',
            'Hợi' => 'Tuyệt'
        ],
        'Đinh' => [
            'Tý' => 'Tuyệt',
            'Sửu' => 'Mộ',
            'Dần' => 'Tử',
            'Mão' => 'Bệnh',
            'Thìn' => 'Suy',
            'Tỵ' => 'Đế Vượng',
            'Ngọ' => 'Lâm Quan',
            'Mùi' => 'Quan Đới',
            'Thân' => 'Mộc Dục',
            'Dậu' => 'Trường Sinh',
            'Tuất' => 'Dưỡng',
            'Hợi' => 'Thai'
        ],   // Hỏa
        'Mậu' => [
            'Tý' => 'Thai',
            'Sửu' => 'Dưỡng',
            'Dần' => 'Trường Sinh',
            'Mão' => 'Mộc Dục',
            'Thìn' => 'Quan Đới',
            'Tỵ' => 'Lâm Quan',
            'Ngọ' => 'Đế Vượng',
            'Mùi' => 'Suy',
            'Thân' => 'Bệnh',
            'Dậu' => 'Tử',
            'Tuất' => 'Mộ',
            'Hợi' => 'Tuyệt'
        ],
        'Kỷ' => [
            'Tý' => 'Tuyệt',
            'Sửu' => 'Mộ',
            'Dần' => 'Tử',
            'Mão' => 'Bệnh',
            'Thìn' => 'Suy',
            'Tỵ' => 'Đế Vượng',
            'Ngọ' => 'Lâm Quan',
            'Mùi' => 'Quan Đới',
            'Thân' => 'Mộc Dục',
            'Dậu' => 'Trường Sinh',
            'Tuất' => 'Dưỡng',
            'Hợi' => 'Thai'
        ],    // Thổ
        'Canh' => [
            'Tý' => 'Tử',
            'Sửu' => 'Mộ',
            'Dần' => 'Tuyệt',
            'Mão' => 'Thai',
            'Thìn' => 'Dưỡng',
            'Tỵ' => 'Trường Sinh',
            'Ngọ' => 'Mộc Dục',
            'Mùi' => 'Quan Đới',
            'Thân' => 'Lâm Quan',
            'Dậu' => 'Đế Vượng',
            'Tuất' => 'Suy',
            'Hợi' => 'Bệnh'
        ],
        'Tân' => [
            'Tý' => 'Trường Sinh',
            'Sửu' => 'Dưỡng',
            'Dần' => 'Thai',
            'Mão' => 'Tuyệt',
            'Thìn' => 'Mộ',
            'Tỵ' => 'Tử',
            'Ngọ' => 'Bệnh',
            'Mùi' => 'Suy',
            'Thân' => 'Đế Vượng',
            'Dậu' => 'Lâm Quan',
            'Tuất' => 'Quan Đới',
            'Hợi' => 'Mộc Dục'
        ],      // Kim
        'Nhâm' => [
            'Tý' => 'Đế Vượng',
            'Sửu' => 'Suy',
            'Dần' => 'Bệnh',
            'Mão' => 'Tử',
            'Thìn' => 'Mộ',
            'Tỵ' => 'Tuyệt',
            'Ngọ' => 'Thai',
            'Mùi' => 'Dưỡng',
            'Thân' => 'Trường Sinh',
            'Dậu' => 'Mộc Dục',
            'Tuất' => 'Quan Đới',
            'Hợi' => 'Lâm Quan'
        ],
        'Quý' => [
            'Tý' => 'Lâm Quan',
            'Sửu' => 'Quan Đới',
            'Dần' => 'Mộc Dục',
            'Mão' => 'Trường Sinh',
            'Thìn' => 'Dưỡng',
            'Tỵ' => 'Thai',
            'Ngọ' => 'Tuyệt',
            'Mùi' => 'Mộ',
            'Thân' => 'Tử',
            'Dậu' => 'Bệnh',
            'Tuất' => 'Suy',
            'Hợi' => 'Đế Vượng'
        ]   // Thủy
    ];

    // Nạp Âm 60 Giáp Tý
    protected static $napAm = [
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
        'QuýMùi' => 'Dương Liễu Mộc',
        'GiápThân' => 'Tuyền Trung Thủy',
        'ẤtDậu' => 'Tuyền Trung Thủy',
        'BínhTuất' => 'Ốc Thượng Thổ',
        'ĐinhHợi' => 'Ốc Thượng Thổ',
        'MậuTý' => 'Tích Lịch Hỏa',
        'KỷSửu' => 'Tích Lịch Hỏa',
        'CanhDần' => 'Tùng Bách Mộc',
        'TânMão' => 'Tùng Bách Mộc',
        'NhâmThìn' => 'Trường Lưu Thủy',
        'QuýTỵ' => 'Trường Lưu Thủy',
        'GiápNgọ' => 'Sa Trung Kim',
        'ẤtMùi' => 'Sa Trung Kim',
        'BínhThân' => 'Sơn Hạ Hỏa',
        'ĐinhDậu' => 'Sơn Hạ Hỏa',
        'MậuTuất' => 'Bình Địa Mộc',
        'KỷHợi' => 'Bình Địa Mộc',
        'CanhTý' => 'Bích Thượng Thổ',
        'TânSửu' => 'Bích Thượng Thổ',
        'NhâmDần' => 'Kim Bạch Kim',
        'QuýMão' => 'Kim Bạch Kim',
        'GiápThìn' => 'Phúc Đăng Hỏa',
        'ẤtTỵ' => 'Phúc Đăng Hỏa',
        'BínhNgọ' => 'Thiên Hà Thủy',
        'ĐinhMùi' => 'Thiên Hà Thủy',
        'MậuThân' => 'Đại Dịch Thổ',
        'KỷDậu' => 'Đại Dịch Thổ',
        'CanhTuất' => 'Thoa Xuyến Kim',
        'TânHợi' => 'Thoa Xuyến Kim',
        'NhâmTý' => 'Tang Đố Mộc',
        'QuýSửu' => 'Tang Đố Mộc',
        'GiápDần' => 'Đại Khê Thủy',
        'ẤtMão' => 'Đại Khê Thủy',
        'BínhThìn' => 'Sa Trung Thổ',
        'ĐinhTỵ' => 'Sa Trung Thổ',
        'MậuNgọ' => 'Thiên Thượng Hỏa',
        'KỷMùi' => 'Thiên Thượng Hỏa',
        'CanhThân' => 'Thạch Lựu Mộc',
        'TânDậu' => 'Thạch Lựu Mộc',
        'NhâmTuất' => 'Đại Hải Thủy',
        'QuýHợi' => 'Đại Hải Thủy',
    ];

    protected static $menh_nap_am = [
        'Giáp Tý' => 'Kim',
        'Ất Sửu' => 'Kim',
        'Bính Dần' => 'Hỏa',
        'Đinh Mão' => 'Hỏa',
        'Mậu Thìn' => 'Mộc',
        'Kỷ Tỵ' => 'Mộc',
        'Canh Ngọ' => 'Thổ',
        'Tân Mùi' => 'Thổ',
        'Nhâm Thân' => 'Kim',
        'Quý Dậu' => 'Kim',
        'Giáp Tuất' => 'Hỏa',
        'Ất Hợi' => 'Hỏa',
        'Bính Tý' => 'Thủy',
        'Đinh Sửu' => 'Thủy',
        'Mậu Dần' => 'Thổ',
        'Kỷ Mão' => 'Thổ',
        'Canh Thìn' => 'Kim',
        'Tân Tỵ' => 'Kim',
        'Nhâm Ngọ' => 'Mộc',
        'Quý Mùi' => 'Mộc',
        'Giáp Thân' => 'Thủy',
        'Ất Dậu' => 'Thủy',
        'Bính Tuất' => 'Thổ',
        'Đinh Hợi' => 'Thổ',
        'Mậu Tý' => 'Hỏa',
        'Kỷ Sửu' => 'Hỏa',
        'Canh Dần' => 'Mộc',
        'Tân Mão' => 'Mộc',
        'Nhâm Thìn' => 'Thủy',
        'Quý Tỵ' => 'Thủy',
        'Giáp Ngọ' => 'Kim',
        'Ất Mùi' => 'Kim',
        'Bính Thân' => 'Hỏa',
        'Đinh Dậu' => 'Hỏa',
        'Mậu Tuất' => 'Mộc',
        'Kỷ Hợi' => 'Mộc',
        'Canh Tý' => 'Thổ',
        'Tân Sửu' => 'Thổ',
        'Nhâm Dần' => 'Kim',
        'Quý Mão' => 'Kim',
        'Giáp Thìn' => 'Hỏa',
        'Ất Tỵ' => 'Hỏa',
        'Bính Ngọ' => 'Thủy',
        'Đinh Mùi' => 'Thủy',
        'Mậu Thân' => 'Thổ',
        'Kỷ Dậu' => 'Thổ',
        'Canh Tuất' => 'Kim',
        'Tân Hợi' => 'Kim',
        'Nhâm Tý' => 'Mộc',
        'Quý Sửu' => 'Mộc',
        'Giáp Dần' => 'Thủy',
        'Ất Mão' => 'Thủy',
        'Bính Thìn' => 'Thổ',
        'Đinh Tỵ' => 'Thổ',
        'Mậu Ngọ' => 'Hỏa',
        'Kỷ Mùi' => 'Hỏa',
        'Canh Thân' => 'Mộc',
        'Tân Dậu' => 'Mộc',
        'Nhâm Tuất' => 'Thủy',
        'Quý Hợi' => 'Thủy',
    ];

    protected static $dau_ngu_hanh = [
        'Giáp' => '+',
        'Ất' => '-',
        'Bính' => '+',
        'Đinh' => '-',
        'Mậu' => '+',
        'Kỷ' => '-',
        'Canh' => '+',
        'Tân' => '-',
        'Nhâm' => '+',
        'Quý' => '-',
    ];

    protected static $am_duong_thien_can = [
        'Giáp' => 'Dương',
        'Ất' => 'Âm',
        'Bính' => 'Dương',
        'Đinh' => 'Âm',
        'Mậu' => 'Dương',
        'Kỷ' => 'Âm',
        'Canh' => 'Dương',
        'Tân' => 'Âm',
        'Nhâm' => 'Dương',
        'Quý' => 'Âm',
    ];

    protected static $dau_dia_chi = [
        'tý'   => '+',
        'sửu'  => '-',
        'dần'  => '+',
        'mão'  => '-',
        'thìn' => '+',
        'tỵ'   => '-',
        'ngọ'  => '+',
        'mùi'  => '-',
        'thân' => '+',
        'dậu'  => '-',
        'tuất' => '+',
        'hợi'  => '-'
    ];


    protected static $tongquantinhcach = [
        'giap' => [
            'can' => 'Giáp',
            'ngu_hanh' => 'Dương mộc',
            'hinh_tuong' => 'Cây tùng, cây bách, cây cổ thụ ngàn năm, khúc gỗ lớn.',
            'bieu_tuong' => 'Sự vươn lên, che chở, trụ cột và sự khởi đầu.',
            'tong_quan' => 'Giáp Mộc là Dương Mộc, mang khí chất của người dẫn đầu. Bạn đại diện cho lòng nhân ái (Nhân), sự thẳng thắn và ý chí vươn lên không ngừng nghỉ. Giống như cây đại thụ đứng giữa trời đất, bạn tỏa ra cảm giác vững chãi, đáng tin cậy và là chỗ dựa cho người khác.',
            'tu_duy_tinh_cach' => [
                'Kiên định và thẳng tính: Tư duy của bạn thường đi theo đường thẳng, ít vòng vo. Bạn có lập trường vững vàng, đôi khi đến mức cố chấp. Một khi đã quyết định, rất khó để ai đó lay chuyển ý chí của bạn.',
                'Nghiêm khắc và kỷ luật: Bạn tự đặt ra những tiêu chuẩn cao cho bản thân và người khác. Sự nghiêm khắc này giúp bạn xây dựng uy tín, nhưng cũng tạo ra rào cản vô hình khiến người khác cảm thấy áp lực khi tiếp cận.',
                'Tư duy một chiều: Bạn có xu hướng nhìn nhận vấn đề theo một hướng tập trung duy nhất để giải quyết triệt để. Điều này tốt cho sự chuyên sâu nhưng lại thiếu đi sự linh hoạt cần thiết trong những tình huống biến động.'
            ],
            'hanh_vi_ung_xu' => [
                'Bạn không phải là người quá hoạt bát hay khéo léo trong giao tiếp xã giao và ngoại giao. Hành động của bạn thiên về thực tế, nói ít làm nhiều.',
                'Đôi khi bạn bị nhận xét là thiếu tế nhị hoặc vô cảm, nhưng thực chất đó là do bạn không giỏi biểu đạt cảm xúc mềm mỏng. Tâm hồn bạn đa cảm, nhân hậu nhưng được bọc trong lớp vỏ xù xì, cứng rắn của cây gỗ.'
            ],
            'diem_manh' => [
                'Sự tin cậy tuyệt đối: Bạn là người giữ chữ tín hàng đầu. Trong tập thể, bạn thường được giao trọng trách giữ gìn nền tảng, gốc rễ.',
                'Khả năng che chở: Bản năng của cây lớn là vươn cao và tỏa bóng mát, bạn có xu hướng bảo vệ người yếu thế hơn.'
            ],
            'diem_yeu' => [
                'Khó thích nghi: Cây lớn thì khó di dời. Bạn sợ sự thay đổi môi trường sống hoặc công việc đột ngột. Việc bị bứng khỏi nền tảng gốc rễ quen thuộc có thể làm bạn suy sụp.',
                'Dễ gãy đổ: Vì quá cứng nhắc và thiếu sự mềm dẻo, khi gặp bão lớn như biến cố cuộc đời, nếu không chịu nghiêng mình, bạn có nguy cơ bị gãy đổ hoàn toàn thay vì uốn cong để tồn tại.'
            ],
            'chien_luoc' => 'Hãy học cách “lạt mềm buộc chặt”. Bạn nên tận dụng sự kiên định để xây dựng sự nghiệp, nhưng cần rèn luyện thêm sự linh hoạt để xử lý các mối quan hệ nhân sinh (Thủy).'
        ],
        'at' => [
            'can' => 'Ất',
            'ngu_hanh' => 'Âm mộc',
            'hinh_tuong' => 'Dây leo, cây cỏ, hoa lá, dây tơ hồng.',
            'bieu_tuong' => 'Sự thích nghi, nghệ thuật, sự kết nối và sinh tồn dẻo dai.',
            'tong_quan' => 'Ất Mộc là Âm Mộc, tượng trưng cho sự sống mềm mại nhưng mãnh liệt, tồn tại bằng cách nương tựa và liên kết. Bạn đại diện cho sự khéo léo, ngoại giao và khả năng sinh tồn trong mọi hoàn cảnh khắc nghiệt nhất.',
            'tu_duy_tinh_cach' => [
                'Linh hoạt và quyến rũ: Tư duy của bạn rất đa chiều và nhạy bén. Bạn biết cách dùng sự mềm mỏng (nhu) để thắng sự cứng rắn (cương). Bạn dễ dàng thuyết phục người khác bằng sự duyên dáng và khéo léo của mình.',
                'Dễ lung lay: Điểm yếu của sự linh hoạt là thiếu lập trường. Bạn dễ thay đổi ý định khi có tác động từ bên ngoài, giống như ngọn cỏ lùa theo chiều gió.',
            ],
            'hanh_vi_ung_xu' => [
                'Bạn là bậc thầy của sự thích nghi. Dù bị đặt vào môi trường nào, bạn cũng tìm ra cách để sống sót và phát triển.',
                'Phong thái của bạn nhẹ nhàng, dễ chịu nhưng đôi khi thiếu sự cam kết lâu dài. Bạn có thể hứa hẹn nhưng lại thay đổi vào phút chót nếu thấy tình thế bất lợi.',
                'Mặc dù bề ngoài linh hoạt, nhưng bên trong bạn vẫn khao khát một sự ổn định (bám rễ) để an tâm phát triển.'
            ],
            'diem_manh' => [
                'Ngoại giao xuất sắc: Bạn khéo léo kết nối các nguồn lực và có thể mượn sức người khác để vươn lên (như dây leo bám vào cây cổ thụ).',
                'Dễ thay đổi: Vì hay thay đổi để thích nghi, đôi khi bạn bị đánh giá là thiếu sự trung thành và tin cậy tuyệt đối.'
            ],
            'diem_yeu' => [
                'Phụ thuộc: Bạn có xu hướng dựa dẫm vào người khác hoặc môi trường. Nếu mất đi chỗ dựa, bạn dễ bị chới với.',
                'Dễ gãy đổ: Vì quá cứng nhắc và thiếu sự mềm dẻo, khi gặp bão lớn như biến cố cuộc đời, nếu không chịu nghiêng mình, bạn có nguy cơ bị gãy đổ hoàn toàn thay vì uốn cong để tồn tại.'
            ],
            'chien_luoc' => 'Bạn nên khai thác tối đa khả năng linh hoạt và mạng lưới quan hệ của mình (Thủy). Tuy nhiên, hãy rèn luyện một “bộ rễ” tâm hồn vững chắc để không bị cuốn trôi hoàn toàn theo dòng đời (Thổ).'
        ],
        'binh' => [
            'can' => 'Bính',
            'ngu_hanh' => 'Dương hỏa',
            'hinh_tuong' => 'Mặt Trời rực rỡ, ánh hào quang.',
            'bieu_tuong' => 'Sự quang minh chính đại, nhiệt huyết, công lý và sự cho đi vô điều kiện.',
            'tong_quan' => 'Bính Hỏa là nguồn năng lượng dương cực mạnh, đại diện cho ánh sáng Mặt Trời. Bạn không thể che giấu bản chất của mình; bạn sinh ra để tỏa sáng, để ban phát hơi ấm và năng lượng cho thế gian. Bạn là hiện thân của sự đam mê và lòng nhiệt thành.',
            'tu_duy_tinh_cach' => [
                'Quang minh chính đại: Bạn ghét sự mờ ám, lén lút. Tư duy của bạn rõ ràng, minh bạch như ban ngày. Bạn sống thẳng thắn, chân thành và luôn hướng về lẽ phải.',
                'Rộng lượng và bao dung: Giống như Mặt Trời chiếu sáng không phân biệt, bạn hào phóng, thích làm từ thiện và giúp đỡ người khác mà không toan tính nhỏ nhen.',
                'Nóng tính: Năng lượng của lửa khiến bạn dễ bùng nổ, nóng nảy nhưng cũng nguội rất nhanh, không để bụng.'
            ],
            'hanh_vi_ung_xu' => [
                'Thích thể hiện: Bạn muốn mình là trung tâm, thích được người khác khen ngợi và ghi nhận. Bạn có xu hướng phô trương sự sang trọng, lịch thiệp.',
                'Tuân thủ nề nếp: Mặt Trời mọc lặn có quy luật, nên bạn cũng thích duy trì các thói quen, nề nếp sinh hoạt ổn định.',
                'Truyền lửa: Sự hiện diện của bạn có khả năng xua tan sự u ám, khơi dậy tinh thần cho tập thể.'
            ],
            'diem_manh' => [
                'Sức hút tự nhiên: Bạn là người lãnh đạo tinh thần, có khả năng lan tỏa năng lượng tích cực và sự ấm áp.',
                'Độc lập: Mặt Trời chỉ có một, bạn có khả năng hoạt động độc lập rất cao và tự chủ trong cuộc sống.'
            ],
            'diem_yeu' => [
                'Cái tôi lớn: Bạn dễ bị tổn thương lòng tự trọng nếu không được coi trọng. Đôi khi sự nhiệt tình thái quá của bạn trở thành áp đặt, “thiêu đốt” người khác.',
                'Khó giữ bí mật: Vì quá “trong sáng” và bộc trực, bạn không phải là người giỏi giữ bí mật.'
            ],
            'chien_luoc' => 'Hãy tiếp tục tỏa sáng nhưng học cách tiết chế sức nóng. Hãy là ánh nắng ấm áp mùa xuân nuôi dưỡng vạn vật, đừng trở thành nắng gắt mùa hè thiêu đốt mọi thứ xung quanh.'
        ],
        'dinh' => [
            'can' => 'Đinh',
            'ngu_hanh' => 'Âm hỏa',
            'hinh_tuong' => 'Ngọn nến, ánh đèn, đốm lửa lò sưởi, sao trên trời.',
            'bieu_tuong' => 'Sự hy sinh, soi đường dẫn lối, sự tập trung và văn minh nhân tạo.',
            'tong_quan' => 'Đinh Hỏa là ngọn lửa của nhân gian, ngọn lửa của tri thức và văn minh. Bạn mang trong mình sự tinh tế, nhạy cảm và khả năng soi rọi vào những góc khuất sâu thẳm nhất của tâm hồn.',
            'tu_duy_tinh_cach' => [
                'Tỉ mỉ và chi tiết: Bạn quan sát những điều nhỏ nhặt mà người khác bỏ qua. Tư duy của bạn sắc bén, có chiều sâu và đầy tính chiến lược.',
                'Đa cảm và nội tâm: Bạn có đời sống nội tâm phong phú, hay suy nghĩ, đôi khi ủy mị. Cảm xúc của bạn dao động như ngọn lửa trước gió, lúc bùng lên, lúc hiu hắt.',
                'Hy sinh: Hình tượng “Lạp cự thành hôi lệ thuỷ can” nói lên đức tính hy sinh của bạn. Bạn sẵn sàng đốt cháy bản thân để soi sáng cho người mình yêu thương.'
            ],
            'hanh_vi_ung_xu' => [
                'Truyền cảm hứng: Bạn là người cố vấn tuyệt vời, biết cách khơi gợi tiềm năng, truyền lửa cho người khác.',
                'Tính tàn phá ngầm: Đừng đùa với lửa. Khi bị dồn ép, ngọn lửa nhỏ có thể thiêu rụi cả khu rừng. Bạn có thể trở nên rất hung hăng và phá hủy mọi thứ nếu cảm xúc bùng nổ.',
                'Bạn lễ phép, lịch sự nhưng luôn giữ một khoảng cách bí ẩn nhất định.'
            ],
            'diem_manh' => [
                'Sự tập trung: Ánh sáng của bạn là tia laser, có khả năng tập trung cao độ để giải quyết các vấn đề hóc búa.',
                'Ấm áp và Gần gũi: Bạn tạo ra cảm giác thân thuộc, dễ chịu cho người đối diện.'
            ],
            'diem_yeu' => [
                'Nhạy cảm thái quá: Bạn dễ bị tác động bởi ngoại cảnh, hay lo âu và suy diễn.',
                'Thiếu ổn định: Năng lượng của bạn cần có “nhiên liệu” (Hỏa đốt Mộc) để đốt liên tục, nếu không được khích lệ, bạn dễ bị kiệt sức và tắt lịm.'
            ],
            'chien_luoc' => 'Bạn nên chọn những nghề nghiệp mang tính chất soi đường như giáo dục, tư vấn, nghệ thuật (Mộc). Hãy giữ ngọn lửa lòng ổn định, tránh để cảm xúc tiêu cực thổi tắt ánh sáng trí tuệ của bạn.'
        ],
        'mậu' => [
            'can' => 'Mậu',
            'ngu_hanh' => 'Dương thổ',
            'hinh_tuong' => 'Núi non hùng vĩ, đê đập chắn nước, tảng đá lớn.',
            'bieu_tuong' => 'Sự vững chãi, tín nghĩa, sự bao bọc và bất biến.',
            'tong_quan' => 'Mậu Thổ như ngọn núi Thái Sơn, trầm ổn và tĩnh lặng. Bạn đại diện cho chữ “tín”. Hơn tất cả, bạn là người đáng tin cậy nhất, là chỗ dựa vững chắc nhất. Bạn có khả năng bao dung, chứa đựng vạn vật nhưng cũng rất cứng nhắc.',
            'tu_duy_tinh_cach' => [
                'Trung thành và cẩn trọng: Bạn là người giữ bí mật tuyệt vời. Tư duy của bạn chậm rãi, chắc chắn, cân nhắc kỹ lưỡng trước khi hành động.',
                'Bảo thủ và cố chấp: Núi thì không di chuyển. Bạn rất khó thay đổi quan điểm, đôi khi trở nên ù lì, chậm chạp trong việc tiếp thu cái mới.',
                'Bao dung: Lòng bạn rộng như núi, có thể chấp nhận những khuyết điểm của người khác.'
            ],
            'hanh_vi_ung_xu' => [
                'Điềm tĩnh: Bạn là trụ cột tinh thần trong những lúc khủng hoảng.',
                'Thiếu lãng mạn: Bạn thực tế, khô khan và ít biểu lộ cảm xúc ra bên ngoài. Điều này đôi khi khiến người đời cảm thấy bạn lạnh lùng hoặc vô tâm.',
                'Khó tin người: Bạn đáng tin, nhưng bạn lại khó tin người khác. Bạn cần thời gian rất dài để kiểm chứng một mối quan hệ.'
            ],
            'diem_manh' => [
                'Vững vàng: Không gì có thể xô ngã bạn, bạn có sức chịu đựng áp lực cực tốt.',
                'Quản lý tài sản: Bạn có duyên với việc tích lũy, gìn giữ tài sản và xây dựng nền móng sự nghiệp.'
            ],
            'diem_yeu' => [
                'Trì trệ: Bạn có xu hướng lười biếng, trì hoãn nếu không có động lực hoặc áp lực đủ lớn thúc đẩy.',
                'Cô độc: Sự lầm lì và đóng kín như hang động trong núi, khiến bạn dễ rơi vào trạng thái cô đơn.'
            ],
            'chien_luoc' => 'Núi không cao không phải là núi, nước không chảy không thể thành sông”. Bạn cần sự khai phá (của kim loại – Kim hoặc cây cối – Mộc) để bộc lộ kho báu bên trong. Hãy chủ động cởi mở hơn và bớt tính nghi ngờ để đón nhận cơ hội.'
        ],
        'ky' => [
            'can' => 'Kỷ',
            'ngu_hanh' => 'Âm thổ',
            'hinh_tuong' => 'Đất ruộng vườn, đất phù sa màu mỡ, đất sét.',
            'bieu_tuong' => 'Sự nuôi dưỡng, sinh sôi, bà mẹ thiên nhiên và sự chuyển hóa.',
            'tong_quan' => 'Kỷ Thổ là đất mẹ hiền hòa, nơi vạn vật sinh sôi nảy nở. Bạn vô cùng hữu ích và gần gũi. Bạn đại diện cho sự chăm sóc, giáo dục và khả năng dung chứa mọi thứ, kể cả những điều tốt lẫn xấu.',
            'tu_duy_tinh_cach' => [
                'Đa tài và tháo vát: Bạn có khả năng xoay xở tuyệt vời trong mọi tình huống. Tư duy của bạn linh hoạt hơn, biết tùy cơ ứng biến.',
                'Nhân hậu và vị tha: Bạn thích chăm sóc, nuôi dưỡng người khác. Bạn có xu hướng cho đi và giúp đỡ cộng đồng.',
                'Phức tạp: Đất chứa cả vàng ngọc lẫn rác. Tâm tính bạn đôi khi phức tạp, khó đoán, bên ngoài hiền lành nhưng bên trong có nhiều lo lắng, bạn quan tâm sự an toàn của bản thân và người thân.'
            ],
            'hanh_vi_ung_xu' => [
                'Chậm mà chắc: Bạn không vội vã, làm việc gì cũng có kế hoạch và sự chuẩn bị kỹ lưỡng.',
                'Giáo dục: Bạn có năng khiếu bẩm sinh trong việc đào tạo, hướng dẫn người khác.',
                'Dung chứa: Bạn lắng nghe giỏi, là “nơi chứa cảm xúc” cho bạn bè, nhưng cũng vì thế mà bạn dễ bị căng thăng, mệt mỏi vì ôm đồm quá nhiều chuyện của thiên hạ.'
            ],
            'diem_manh' => [
                'Sáng tạo: Bạn có khả năng biến những thứ vô tri thành hữu ích như đất nặn thành tượng.',
                'Khả năng nuôi dưỡng: Bạn có thể tạo môi trường tốt nhất để nhân tài hoặc các ý tưởng phát triển.'
            ],
            'diem_yeu' => [
                'Thiếu quyết đoán: Vì quá bao dung và suy nghĩ nhiều chiều, bạn hay do dự.',
                'Dễ bị lợi dụng: Lòng tốt của bạn thường bị người khác khai thác triệt để nếu bạn không thiết lập ranh giới rõ ràng.'
            ],
            'chien_luoc' => 'Hãy tập trung vào việc “trồng người” hoặc chăm sóc. Tuy nhiên, bạn cần học cách lọc bỏ những “tạp chất” trong các mối quan hệ để mảnh đất tâm hồn không bị ô nhiễm bởi năng lượng tiêu cực của người khác (Mộc).'
        ],
        'canh' => [
            'can' => 'Canh',
            'ngu_hanh' => 'Dương kim',
            'hinh_tuong' => 'Quặng sắt thô, kiếm lớn, rìu sắt, vũ khí.',
            'bieu_tuong' => 'Công lý, sự cải cách, sức mạnh quân sự và sự cương trực.',
            'tong_quan' => 'Canh Kim là kim loại dương, cứng rắn và sắc bén nhất. Bạn đại diện cho nghĩa khí, công bằng và sự quyết đoán. Bạn là người sinh ra để thực thi công lý, sửa đổi những sai lầm và thiết lập trật tự mới. Sức mạnh của bạn mang tính sát phạt nhưng cũng đầy dũng khí.',
            'tu_duy_tinh_cach' => [
                'Cương trực và thẳng thắn: Bạn ghét sự giả dối, nịnh nọt. Bạn “thẳng như ruột ngựa”, có sao nói vậy, không sợ mất lòng.',
                'Kiên cường: Bạn có sức chịu đựng gian khổ phi thường. Áp lực càng lớn, bạn càng được tôi luyện trở nên sắc bén.',
                'Nghĩa khí: Bạn sẵn sàng đứng ra bảo vệ kẻ yếu, coi trọng tình bạn và lời hứa, có xu hướng trọng nghĩa khinh tài.'
            ],
            'hanh_vi_ung_xu' => [
                'Hành động dứt khoát: Bạn giải quyết vấn đề nhanh gọn, triệt để, đôi khi hơi tàn nhẫn, “quân lệnh như sơn”.',
                'Lãnh đạo uy quyền: Bạn có tố chất của một vị tướng, quản lý người khác bằng kỷ luật và sự công minh.',
                'Thô ráp: Cách cư xử của bạn đôi khi thiếu sự tế nhị, dễ làm tổn thương người khác bằng lời nói sắc bén của mình dù không ác ý.'
            ],
            'diem_manh' => [
                'Khả năng thực thi: Bạn là người biến ý tưởng thành hiện thực. Không ngại khó, không ngại khổ.',
                'Trung thành: Một khi đã phò tá ai, bạn trung thành tuyệt đối như thanh kiếm trong tay dũng tướng.'
            ],
            'diem_yeu' => [
                'Hiếu thắng: Bạn có xu hướng thích tranh đấu, không chịu thua kém.',
                'Cô độc: Vì quá cứng rắn và sắc bén, người khác thường e ngại khi đến gần bạn.'
            ],
            'chien_luoc' => 'Ngọc bất trác bất thành khí, Kim bất đoán bất thành hình”. Bạn cần có áp lực, thử thách, kỷ luật (Hỏa) để tôi luyện thành vật hữu dụng (Mộc). Đừng ngại gian khổ, đó là lò luyện đơn của đời bạn. Hãy học thêm cách kiểm soát lời nói để tránh khẩu nghiệp.'
        ],
        'tan' => [
            'can' => 'Tân',
            'ngu_hanh' => 'Âm kim',
            'hinh_tuong' => 'Trang sức, đá quý, dao găm, kim tiêm, vật dụng kim loại tinh xảo.',
            'bieu_tuong' => 'Sự quý phái, giá trị bản thân, sự thu hút và sự hoàn hảo.',
            'tong_quan' => 'Tân Kim là kim loại đã được gọt giũa, mài dũa thành trang sức quý giá. Bạn đại diện cho cái đẹp, sự sang trọng và giá trị tinh hoa. Khác với mọi người, bạn mềm mỏng hơn nhưng lại sắc bén theo cách tinh tế và sâu sắc hơn, lời nói như dao cắt.',
            'tu_duy_tinh_cach' => [
                'Tự trọng cao: Bạn ý thức rất rõ giá trị của bản thân. Bạn thích được nâng niu, trân trọng và ghét bị coi thường.',
                'Nhạy cảm và tinh tế: Bạn có gu thẩm mỹ tốt, yêu cái đẹp. Tâm hồn bạn nhạy cảm, dễ bị trầy xước bởi những lời chỉ trích.',
                'Thực dụng: Bạn nhìn nhận cuộc sống qua lăng kính giá trị thực tế. Bạn thông minh và biết cách làm cho mình trở nên đắt giá.'
            ],
            'hanh_vi_ung_xu' => [
                'Thu hút sự chú ý: Bạn thích là tâm điểm, muốn được tỏa sáng, như trang sức trở nên lấp lánh. Bạn thường chăm chút ngoại hình rất kỹ.',
                'Sắc sảo: Lời nói của bạn có tính sát thương cao nếu bạn muốn công kích ai đó.',
                'Ưu thích sạch sẽ: Bạn ngại những việc nặng nhọc, lam lũ. Bạn muốn giữ hình ảnh sạch sẽ, trong sáng, hoàn hảo.'
            ],
            'diem_manh' => [
                'Quyến rũ: Bạn có sức hút giới tính và xã hội tự nhiên.',
                'Khả năng đàm phán: Bạn khéo léo, biết nhu biết cương đúng lúc để đạt được lợi ích.'
            ],
            'diem_yeu' => [
                'Phù phiếm: Đôi khi bạn quá chú trọng hình thức mà quên đi nội dung.',
                'Hay khoe khoang: Nhu cầu được công nhận khiến bạn dễ sa đà vào việc phô trương quá mức.'
            ],
            'chien_luoc' => 'Tân Kim cần nước (Thủy) để rửa sạch bụi trần và tỏa sáng. Hãy rèn luyện trí tuệ, nền tảng hệ thống kiến thức chuyên môn và kỹ năng giao tiếp (Mộc). Đừng để bản thân bị vùi lấp trong bùn đất, trong sự trì trệ. Hãy tìm những sân khấu xứng tầm để tỏa sáng (Hỏa).'
        ],
        'nham' => [
            'can' => 'Nhâm',
            'ngu_hanh' => 'Dương thuỷ',
            'hinh_tuong' => 'Biển cả, sông lớn, thác nước hùng vĩ.',
            'bieu_tuong' => 'Trí tuệ vĩ đại, sự luân chuyển, sức mạnh cuốn trôi và sự tự do.',
            'tong_quan' => 'Nhâm Thủy là nước của đại dương, mênh mông và khó lường. Bạn đại diện cho trí tuệ thông thái, sự linh hoạt tuyệt đối và sức mạnh tiềm ẩn kinh người. Bạn là người của tự do, không thích bị gò bó trong khuôn khổ.',
            'tu_duy_tinh_cach' => [
                'Thông minh và quyền biến: Bạn là người túc trí đa mưu, học một biết mười. Tư duy của bạn thoáng đạt, nhìn xa trông rộng.',
                'Hướng ngoại và nổi loạn: Bạn thích giao lưu, kết bạn bốn phương. Bạn ghét sự tù túng, luôn muốn phá vỡ các quy tắc cũ kỹ.',
                'Kiên định trong sự linh hoạt: Nước chảy đá mòn. Bạn mềm mại nhưng có mục tiêu rõ ràng, gặp chướng ngại vật sẽ tìm đường vòng để đi tiếp chứ không dừng lại.'
            ],
            'hanh_vi_ung_xu' => [
                'Thích nghi cao: Không môi trường nào làm khó được bạn. Bạn “nhập gia tùy tục” rất nhanh.',
                'Mạnh mẽ và quyết liệt: Khi cần, bạn có thể tạo ra những con sóng thần cuốn phăng mọi vật cản.',
                'Thiếu kiên nhẫn: Bạn suy nghĩ nhanh, hành động nhanh nên dễ bực mình với những người chậm chạp.'
            ],
            'diem_manh' => [
                'Trí tuệ: Khả năng tư duy chiến lược và xử lý thông tin là vũ khí mạnh nhất của bạn.',
                'Mạng lưới quan hệ: Bạn dễ dàng kết nối mọi người, tạo dựng mạng lưới xã hội rộng lớn.'
            ],
            'diem_yeu' => [
                'Khó kiểm soát: Nếu không có “đê đập” (Mậu Thổ - kỷ luật), bạn dễ sống buông thả, phóng túng hoặc trôi dạt vô định.',
                'Tâm tính thất thường: Lúc êm đềm, lúc dữ dội, cảm xúc của bạn khó đoán như thời tiết trên biển.'
            ],
            'chien_luoc' => 'Bạn cần có mục tiêu lớn để dồn sức nước vào như dòng sông chảy ra biển lớn. Nếu không có mục tiêu, bạn sẽ chỉ là vũng nước đọng hoặc dòng lũ phá hoại. Hãy học cách kỷ luật bản thân để biến trí thông minh thành thành tựu.'
        ],
        'quy' => [
            'can' => 'Quý',
            'ngu_hanh' => 'Âm thuỷ',
            'hinh_tuong' => 'Mây, mưa, sương mù, nước ngầm, dòng suối nhỏ.',
            'bieu_tuong' => 'Sự thẩm thấu, trí tưởng tượng, trực giác và sự bí ẩn.',
            'tong_quan' => 'Quý Thủy là dạng nước vô hình hoặc mềm mại nhất, nhưng lại có khả năng hiện diện ở khắp mọi nơi. Bạn đại diện cho sự tinh tế, trí tưởng tượng phong phú và thế giới nội tâm sâu sắc, bí ẩn. Bạn là người “lấy nhu thắng cương” điển hình.',
            'tu_duy_tinh_cach' => [
                'Trực giác nhạy bén: Bạn có giác quan thứ 6 cực mạnh. Bạn cảm nhận thế giới bằng trực giác hơn là logic khô khan.',
                'Sáng tạo và mơ mộng: Đầu óc bạn đầy ắp ý tưởng lạ lùng, độc đáo. Bạn thích những gì trừu tượng, tâm linh hoặc nghệ thuật.',
                'Bí ẩn: Bạn như màn sương, người khác rất khó nắm bắt suy nghĩ thật của bạn. Bạn thường che giấu cảm xúc thật bên trong vẻ ngoài trầm tĩnh.'
            ],
            'hanh_vi_ung_xu' => [
                'Thẩm thấu và ảnh hưởng: Bạn nhẹ nhàng thấm sâu vào lòng người khác, thay đổi họ từ bên trong một cách từ từ.',
                'Hay thay đổi: Như mây gặp gió thì tan, gặp lạnh thì mưa. Tâm trạng bạn thay đổi chóng mặt, sáng nắng chiều mưa.',
                'Thích phục vụ: Như nước nuôi dưỡng vạn vật trong thầm lặng, bạn thích cống hiến, chia sẻ kiến thức nhưng không cần đứng tên hay tranh công.'
            ],
            'diem_manh' => [
                'Thích ứng linh hoạt: Như nước có thể luồn lách vào những khe hở nhỏ nhất, bạn có thể chú ý những điều mà người khác bỏ qua.',
                'Tư duy chiến lược ngầm: Bạn giỏi trong việc lên kế hoạch âm thầm, đứng sau rèm nhiếp chính.'
            ],
            'diem_yeu' => [
                'U sầu: Bạn dễ rơi vào trạng thái buồn bã vô cớ, suy nghĩ tiêu cực.',
                'Thiếu thực tế: Đôi khi bạn bay bổng quá mức, xa rời thực tế, dẫn đến những quyết định phi lý.'
            ],
            'chien_luoc' => 'Bạn không hợp với môi trường gò bó, hành chính khô khan. Hãy tìm đến những công việc cho phép sự tự do, sáng tạo và di chuyển. Hãy học cách cân bằng cảm xúc, tránh để sự đa sầu đa cảm nhấn chìm lý trí của bạn.'
        ],
    ];

    protected static $cac_khia_canh_cuoc_song = [
        'at_suu' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn đẹp một cách kiêu hãnh và bền bỉ: Một bông hoa thủy tiên nở trong tuyết trắng, hay những mầm cây xanh vươn lên giữa mùa đông giá lạnh. Bạn có vẻ ngoài nhu mì, thân thiện nhưng bên trong lại ẩn chứa một sức sống mãnh liệt, khả năng chịu đựng phi thường và một tham vọng không ai ngờ tới.',
                'La Bàn Thịnh Vượng sẽ giúp bạn "nở hoa" rực rỡ nhất ngay cả trong những hoàn cảnh khắc nghiệt.'
            ],
            'su_nghiep' => [
                'Bên dưới vẻ ngoài điềm đạm, bạn là một "chiến binh" đầy tham vọng với ý chí sắt đá.',
                'Tố chất lãnh đạo ngầm: Bạn không ồn ào, nhưng bạn có khả năng quan sát và chiến lược tuyệt vời. Bạn thích nghi nhanh với mọi hoàn cảnh. Trong công việc, bạn là người có tổ chức, giải quyết vấn đề hiệu quả và khá cạnh tranh. Bạn hợp với vai trò người đứng đầu, người cầm trịch như đạo diễn hơn là một diễn viên phụ mờ nhạt.',
                'Đa tài và thích ứng: Bạn có thể tỏa sáng ở nhiều lĩnh vực. Nếu làm trong môi trường doanh nghiệp, bạn hợp với các tập đoàn lớn quốc tế. Nếu thiên về giao tiếp, bạn là ngôi sao trong ngành PR, quảng cáo, bán hàng nhờ sự quyến rũ tự nhiên. Nếu thiên về nghệ thuật, bạn có thể thành công trong âm nhạc, viết lách. Thậm chí, khả năng trực giác còn giúp bạn tiến xa trong chính trị hoặc quản lý.',
                'Động lực từ danh vọng: Bạn không làm việc chỉ vì đam mê đơn thuần. Danh tiếng và tiền bạc là động lực lớn thúc đẩy bạn cam kết và cống hiến hết mình. Bạn muốn nỗ lực của mình phải được đền đáp xứng đáng.',
                'Thách thức: Đôi khi bạn quá tự tin hoặc ngược lại, trì hoãn không hành động. Bạn cũng có xu hướng muốn kiểm soát và áp đặt ý kiến lên người khác.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc cho phép bạn độc lập và tự chủ.',
                    'Hạn chế tối đa việc đảm nhận những vai trò khiến bạn bị phụ thuộc.',
                    'Nếu bạn sinh vào ban ngày, hãy mạnh dạn chấp nhận rủi ro để theo đuổi ước mơ, cơ hội đang chờ đợi bạn.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là một phần trọng yếu trong cuộc sống của bạn. Bạn có năng khiếu bẩm sinh về quản lý tiền bạc.',
                'Tư duy tài chính sắc bén: Bạn giỏi tính toán và biết cách làm cho tiền đẻ ra tiền. Bạn không mơ mộng hão huyền mà khá thực tế. Bạn cam kết làm việc chăm chỉ để đạt được sự sung túc.',
                'Sự may mắn: Bạn thường gặp may mắn về tiền bạc và có khả năng duy trì lối sống sung túc. Càng tu tâm dưỡng tính, tài lộc của bạn sẽ càng thịnh vượng.',
                'Rủi ro: Bạn có ý thức bảo vệ tài sản cao và khá thận trọng, cần nhiều thời gian để xây dựng lòng tin với các đối tác. Cần đặc biệt cảnh giác với những lời mời gọi làm giàu nhanh chóng. Nếu bạn mất cân bằng trong cuộc sống, bạn có thể gặp rủi ro mất mát tài chính.',
                'dinh_huong' => [
                    'Hãy sử dụng sự tháo vát và trực giác của mình để thương mại hóa các tài năng.',
                    'Đầu tư vào những giá trị thực tế.',
                    'Nếu bạn cảm thấy mình đang làm việc quá sức vì tiền hoặc bạn đang nghiện công việc thì hãy chậm lại một chút để không đánh đổi các mối quan hệ quý giá.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người cho đi, tận tụy và khao khát sự ổn định.',
                'Sự tận tâm: Bạn khá nghiêm túc trong tình cảm. Khi yêu ai, bạn hướng tới sự cam kết lâu dài và hôn nhân. Bạn chu đáo, biết quan tâm và sẵn sàng hy sinh cho người mình yêu. Gia đình là bến đỗ bình yên mà bạn luôn vun đắp.',
                'Tiêu chuẩn chọn bạn đời: Bạn cần một người bạn đời có tham vọng, khéo léo và đủ bản lĩnh để theo kịp bạn. Bạn cũng cần sự an toàn và đảm bảo về mặt cảm xúc lẫn tài chính từ đối phương.',
                'Thách thức: Đôi khi bạn trở nên thiếu quyết đoán, hay lo lắng vẩn vơ về mối quan hệ. Sự quan tâm sâu sắc đôi khi biến thành mong muốn kiểm soát, điều này có thể vô tình tạo áp lực hoặc làm giảm sự thoải mái cho đối phương. Mối quan hệ của bạn đôi khi trở nên "đắt đỏ" cả về tình cảm lẫn tiền bạc.',
                'chien_luoc' => [
                    'Đừng vội vàng kết hôn.',
                    'Hãy dành thời gian tìm hiểu kỹ.',
                    'Học cách cân bằng giữa việc hy sinh cho người khác và giữ lại sự độc lập cho chính mình.',
                    'Đừng để những cảm xúc tiêu cực bị dồn nén, hãy chia sẻ chúng một cách trung thực.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn giống như loài hoa trong tuyết: Nhìn mỏng manh nhưng sức sống dai dẳng. Tuy nhiên cần môi trường phù hợp để không bị héo úa.',
                'Vấn đề tinh thần: Bạn có thể bị căng thẳng hoặc trầm cảm. Sự tham vọng quá lớn đôi khi tạo ra áp lực tâm lý nặng nề.',
                'Sức khỏe sinh sản: Đối với phụ nữ, cần đặc biệt chú ý giữ gìn thai kỳ và sức khỏe sinh sản, cần tập trung vào việc cân bằng cơ thể, tránh để cơ thể quá lạnh hoặc quá nóng.',
                'Cách thức cân bằng: Bạn cần hơi ấm và sự kết nối với mặt đất, hòa mình vào thiên nhiên. Hãy tập trung vào những điều tích cực, những phước lành trong cuộc sống để xua tan sự u ám.',
                'Liệu pháp: Thiền định là liều thuốc tuyệt vời giúp bạn nâng cao nhận thức và giảm bớt sự đa nghi.'
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, tháo vát và có tầm nhìn sâu sắc hơn vẻ bề ngoài.',
                'Tầm nhìn bao quát: Bạn có khả năng nhìn thấy bức tranh toàn cảnh và kết nối các dữ kiện rời rạc. Kỹ năng ngoại giao bẩm sinh giúp bạn tạo ra sự hài hòa trong tập thể.',
                'Trí tuệ thực tế: Bạn nắm bắt các khái niệm mới khá nhanh và biết cách áp dụng vào thực tế. Bạn không thích lý thuyết suông.',
                'Thách thức: Đôi khi bạn quá tự tin hoặc trì hoãn vì muốn tìm kiếm con đường dễ dàng hơn. Bạn có thể bị cám dỗ bởi sự thỏa mãn tức thì thay vì kỷ luật dài hạn.',
                'dinh_huong' => [
                    'Hãy rèn luyện tính kỷ luật tự giác bản thân.',
                    'Tìm một mục tiêu thực sự truyền cảm hứng để theo đuổi đến cùng.',
                    'Tin vào trực giác của mình nhưng đừng để nỗi sợ hãi làm mờ mắt.',
                    'Học tập, đặc biệt là con đường học thuật chuyên sâu, sẽ mang lại lợi ích lớn cho bạn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người có sức hút, duyên dáng và khá giỏi ngoại giao.',
                'Mạng lưới quan hệ: Bạn dễ dàng kết nối với mọi người và tạo dựng thiện cảm. Bạn biết cách sử dụng sự hỗ trợ từ bạn bè để tiến lên. Bạn thích kết giao với những người có kiến thức và kinh nghiệm.',
                'Rủi ro: Mặc dù hay giúp đỡ người khác, bạn lại có xu hướng muốn áp đặt họ. Đôi khi bên ngoài bạn cười nói vui vẻ nhưng bên trong lại che giấu sự bất an và thiếu tin tưởng.',
                'chien_luoc' => [
                    'Hãy chân thành hơn.',
                    'Đừng để sự ghen tị hay so sánh làm méo mó phán đoán của bạn.',
                    'Hãy nhớ rằng "Cỏ không phải lúc nào cũng xanh hơn ở phía bên kia hàng rào" nên bạn đừng "Đứng núi này trông núi nọ".',
                    'Sử dụng sự duyên dáng để kết nối mọi người thay vì cố gắng kiểm soát họ.'
                ]
            ]
        ],
        'at_dau' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn khá đặc biệt và mang tính thẩm mỹ cao: Một đóa hoa tươi đẹp được cắt tỉa gọn gàng cắm trong chiếc bình kim loại quý giá, hay một dây leo quấn quanh thanh kiếm. Bạn có thể sinh ra đã phải chịu áp lực và sự rèn giũa. Chính áp lực này tạo nên một con người sắc sảo, tinh tế, có gu thẩm mỹ nhưng cũng đầy những mâu thuẫn nội tâm.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến những áp lực đó thành đòn bẩy để tỏa sáng.'
            ],
            'su_nghiep' => [
                'Bạn không phải là mẫu người an phận. Bạn có tham vọng lớn và nguồn năng lượng dồi dào để chinh phục đỉnh cao.',
                'Lãnh đạo sắc sảo: Bạn có tư duy tổ chức xuất sắc và khả năng nhìn nhận vấn đề khá chi tiết. Bạn thông minh, sắc bén và có tài thuyết phục người khác. Bạn thích hợp với những vị trí lãnh đạo, quản lý hoặc những công việc đòi hỏi sự chính xác cao.',
                'Nghệ thuật và truyền thông: Bạn có khiếu thẩm mỹ và khả năng biểu đạt tuyệt vời. Các lĩnh vực như nghệ thuật, âm nhạc, thiết kế, viết lách hay truyền thông là nơi bạn có thể thỏa sức sáng tạo. Bạn cũng có thể thành công rực rỡ trong vai trò nhà sản xuất, đạo diễn - những người đứng sau ánh hào quang.',
                'Tâm lý và tư vấn: Trực giác nhạy bén giúp bạn thấu hiểu tâm lý con người. Bạn có thể trở thành chuyên gia tư vấn, nhà tâm lý học hoặc hoạt động trong các tổ chức nhân đạo.',
                'Thách thức: Bạn có thể cảm thấy bất mãn nếu phải làm những công việc dưới tầm năng lực. Sự mâu thuẫn giữa lý tưởng và thực tế đôi khi khiến bạn thiếu kiên định, hay thay đổi hoặc trì hoãn. Bạn cũng có xu hướng trở nên độc đoán, muốn kiểm soát mọi thứ theo ý mình.',
                'chien_luoc' => [
                    'Hãy chọn con đường độc lập và tự chủ.',
                    'Đừng cam chịu làm người thừa hành.',
                    'Tìm kiếm những công việc thực sự truyền cảm hứng cho bạn.',
                    'Hãy học cách lập kế hoạch chi tiết để tránh sự tùy hứng nhất thời.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là vấn đề bạn khá quan tâm và cũng là thước đo cho sự an toàn của bạn.',
                'Khả năng kiếm tiền: Bạn khá tháo vát và có đầu óc kinh doanh thực tế. Bạn biết cách biến tài năng nghệ thuật hay ý tưởng sáng tạo thành tiền bạc. Bạn có động lực mạnh mẽ để làm giàu và thường tìm thấy những cơ hội kinh doanh sinh lời.',
                'Thói quen chi tiêu: Bạn thích sự sang trọng và những thứ đẹp đẽ. Đôi khi bạn chi tiêu hào phóng cho những món đồ xa xỉ hoặc để đầu tư cho hình ảnh bản thân. Dù kiếm được tiền, bạn vẫn hay cảm thấy lo lắng, bồn chồn về sự an toàn tài chính.',
                'Rủi ro: Cần đặc biệt cẩn trọng với những kế hoạch làm giàu nhanh chóng. Sự nóng vội có thể khiến bạn mất trắng. Nếu không cẩn thận, bạn có thể bị hao tài tốn của do chi tiêu cho hình ảnh cá nhân hoặc do sự nóng vội trong đầu tư.',
                'dinh_huong' => [
                    'Hãy rèn luyện kỷ luật chi tiêu.',
                    'Đầu tư vào những lĩnh vực an toàn và có giá trị bền vững.',
                    'Tận dụng thời gian và sự chăm chỉ để tích lũy tài sản.',
                    'Đừng để vẻ hào nhoáng bên ngoài đánh lừa lý trí của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn giống như một đóa hoa hồng: đẹp, quyến rũ nhưng có gai.',
                'Sức hút và sự lãng mạn: Bạn duyên dáng, chu đáo và khá biết cách chăm sóc người khác. Bạn là người tình lãng mạn, trung thành và luôn muốn xây dựng mối quan hệ bền vững. Bạn dễ dàng thu hút người khác phái.',
                'Tiêu chuẩn chọn bạn đời: Bạn cần một người bạn đời thông minh, tham vọng và khéo léo để có thể giữ chân bạn. Bạn thích những người có tầm nhìn rộng và tri thức.',
                'Thách thức: Bạn hay thiếu quyết đoán và lo âu trong tình cảm. Đôi khi bạn muốn kiểm soát đối phương quá mức. Mối quan hệ của bạn có thể gặp sóng gió nếu bạn không học cách tiết chế sự nóng nảy và góp ý một cách khéo léo, xây dựng hơn.',
                'chien_luoc' => [
                    'Hãy cân bằng giữa sự hy sinh và sự độc lập.',
                    'Đừng vội vã kết hôn khi chưa tìm hiểu kỹ.',
                    'Học cách kiên nhẫn và bao dung hơn với người thân.',
                    'Một mối quan hệ hạnh phúc cần sự vun đắp từ hai phía, không phải sự áp đặt từ một người.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng lớn từ sự xung đột trong cơ thể.',
                'Hệ thần kinh và gan mật: Bạn có thể bị căng thẳng thần kinh, đau đầu, các vấn đề về gan hoặc chấn thương tay chân.',
                'Đối với phụ nữ, cần chú ý đặc biệt đến sức khỏe sinh sản, nguy cơ sảy thai hoặc khó khăn trong việc thụ thai.',
                'Tâm bệnh: Bạn có thể bị căng thẳng, mệt mỏi và lo âu quá mức do áp lực tự thân.',
                'Cách thức cân bằng: Hãy uống nhiều nước, sống gần nơi có nước hoặc tập bơi lội. Việc thể hiện bản thân thông qua các hoạt động sáng tạo, trình diễn cũng giúp bạn giải tỏa áp lực.',
                'Liệu pháp: Thiền định là phương pháp tốt nhất để xoa dịu hệ thần kinh nhạy cảm của bạn. Hãy học cách nhìn nhận mọi việc một cách nhẹ nhàng hơn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, học nhanh và có khả năng thích ứng tuyệt vời.',
                'Trí tuệ sắc bén: Bạn tiếp thu kiến thức mới khá nhanh và biết cách ứng dụng ngay vào thực tế. Bạn có tư duy phản biện tốt và khả năng phân tích sâu sắc.',
                'Trực giác: Bạn có trực giác nhạy bén, giúp bạn định hướng đúng trong nhiều tình huống.',
                'Thách thức: Vượt qua sự tự hoài nghi là bài học lớn nhất trên hành trình phát triển của bạn. Đôi khi bạn quá cầu toàn đến mức không dám hành động. Sự thiếu kỷ luật và hay trì hoãn cũng cản trở sự phát triển của bạn.',
                'dinh_huong' => [
                    'Hãy tin tưởng vào bản thân.',
                    'Tìm ra một mục tiêu thực sự có ý nghĩa để theo đuổi.',
                    'Học vấn và kiến thức chuyên sâu sẽ là nền tảng vững chắc giúp bạn tự tin hơn.',
                    'Hãy biến áp lực thành động lực để hoàn thiện mình.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hòa đồng, dễ mến và có khả năng kết nối tốt.',
                'Sức hút xã hội: Bạn duyên dáng, khéo léo và biết cách làm hài lòng người khác. Bạn thích giao lưu và mở rộng mạng lưới quan hệ.',
                'Quý nhân: Mối quan hệ với gia đình, bạn bè là nguồn động viên tinh thần lớn lao cho bạn.',
                'Rủi ro: Dù hòa đồng nhưng bạn cũng khá kín tiếng và hay giấu cảm xúc thật. Đôi khi sự ghen tị hoặc nghi ngờ ngầm có thể phá hỏng các mối quan hệ tốt đẹp.',
                'chien_luoc' => [
                    'Hãy chân thành và bớt tính toán.',
                    'Dùng kỹ năng ngoại giao để tạo ra sự hài hòa.',
                    'Tránh xa những thị phi hoặc những người tiêu cực.',
                    'Hãy trân trọng những người bạn trung thành luôn ở bên bạn lúc khó khăn.'
                ]
            ]
        ],
        'at_mao' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là những bụi cỏ lớn, những thảm thực vật xanh tốt trải dài, đung đưa trước gió nhưng không bao giờ bị quật ngã. Lá số bạn có một nguồn năng lượng thuần khiết, mạnh mẽ và cực kỳ dẻo dai. Bạn thông minh, dí dỏm, nhân hậu nhưng cũng đầy tham vọng và mang trong mình sức sống kiên cường, bản lĩnh của loài cỏ dại.',
                'La Bàn Thịnh Vượng sẽ giúp bạn hiểu rõ sức mạnh của sự mềm dẻo để vươn lên mọi hoàn cảnh.'
            ],
            'su_nghiep' => [
                'Bạn là những người tiên phong, tự lập và đầy sáng tạo. Bạn không thích đi theo lối mòn.',
                'Tinh thần tự lập: Bạn có ý chí tự lập khá cao. Bạn muốn thành công bằng chính đôi tay và khối óc của mình chứ không muốn dựa dẫm vào ai. Bạn không có hứng thú với những công việc nhàm chán, lặp lại hay phải phục tùng mệnh lệnh một cách mù quáng.',
                'Lãnh đạo và điều hành: Bạn có tài năng lãnh đạo bẩm sinh và khả năng tổ chức tuyệt vời. Bạn tham vọng và quyết tâm leo lên những vị trí quản lý, điều hành có quyền lực và ảnh hưởng.',
                'Lĩnh vực phù hợp: Bạn tỏa sáng trong các lĩnh vực sáng tạo như nghệ thuật, âm nhạc, văn học, thiết kế hình ảnh. Ngoài ra, trực giác nhạy bén giúp bạn thành công trong tâm lý học, chữa lành, tư vấn. Trong kinh doanh, bạn hợp làm người đàm phán, đại diện hoặc môi giới.',
                'Thách thức: Sự kiên định cao độ đôi khi khiến bạn trở nên cứng nhắc. Niềm tin mạnh mẽ vào bản thân có thể khiến bạn muốn tự mình quyết định mọi việc và ít tiếp nhận ý kiến trái chiều. Khi cảm thấy mất cảm hứng hoặc áp lực vượt ngưỡng chịu đựng, bạn có xu hướng tìm kiếm một hướng đi mới phù hợp hơn thay vì cố gắng chịu đựng.',
                'chien_luoc' => [
                    'Đừng chấp nhận những vị trí thấp hơn năng lực của mình vì bạn sẽ nhanh chóng chán nản.',
                    'Hãy tìm những công việc mang lại sự tự do và cảm hứng.',
                    'Rèn luyện sự kiên nhẫn và kỷ luật là chìa khóa để bạn không bỏ cuộc giữa chừng.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính đối với bạn gắn liền với cảm giác an toàn. Bạn hào phóng nhưng cũng khá thực tế.',
                'Tư duy tiền bạc: Bạn không ích kỷ, sẵn sàng chia sẻ của cải với đối tác. Tuy nhiên, bạn coi trọng vật chất vì nó mang lại sự an toàn. Bạn có tiềm năng đạt được thành công tài chính đáng kinh ngạc nếu biết tận dụng các cơ hội.',
                'Rủi ro: Bạn có thể chi tiêu theo cảm hứng. Khi vui hoặc căng thẳng, bạn có thể vung tiền mua sắm xa hoa bất thường. Cần cẩn thận với những người bạn xấu hoặc đối tác không trung thực gây hao hụt tài sản.',
                'dinh_huong' => [
                    'Hãy sử dụng kỹ năng và kiến thức chuyên môn để kiếm tiền một cách ổn định.',
                    'Đầu tư vào những gì tạo ra giá trị thực tế.',
                    'Cần có kế hoạch quản lý chi tiêu chặt chẽ để tránh những phút bốc đồng.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn khá phức tạp. Bạn quyến rũ, đa tình và luôn khao khát sự mới mẻ.',
                'Sức hút tự nhiên: Bạn nhân hậu, dễ tính và có ngoại hình ưa nhìn nên khá thu hút người khác phái. Bạn thoải mái trong việc mở rộng mối quan hệ.',
                'Quan điểm về hôn nhân: Gia đình khá quan trọng với bạn. Bạn yêu thương và trung thành với người thân. Bạn cần một người bạn đời có tầm nhìn rộng, có kiến thức và tôn trọng sự thông thái của bạn.',
                'Đào hoa: Bạn nhạy cảm với sự mới mẻ và dễ rung động. Năng lượng đào hoa mạnh khiến bạn có thể đứng trước nhiều cám dỗ và có thể sa vào các mối quan hệ ngoài luồng. Đôi khi năng lượng mạnh mẽ của bạn biến thành sự độc đoán, kiểm soát người yêu.',
                'chien_luoc' => [
                    'Lấy người từng đổ vỡ hoặc lớn tuổi hơn có thể mang lại sự bền vững.',
                    'Hãy học cách cân bằng giữa nhu cầu tự do cá nhân và trách nhiệm với gia đình.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn nhìn chung là tốt, dẻo dai và có tuổi thọ cao, nhưng tâm bệnh là điều đáng lo.',
                'Che giấu cảm xúc: Bạn thường tỏ ra lạc quan, vui vẻ bên ngoài nhưng bên trong lại che giấu những căng thẳng, nỗi đau hoặc sự bất mãn. Sự dồn nén này lâu ngày sẽ gây hại cho sức khỏe tinh thần.',
                'Rủi ro: Bạn có thể phải làm việc vất vả hơn người khác dẫn đến kiệt sức. Nếu là phụ nữ, cần chú ý sức khỏe sinh sản.',
                'dinh_huong' => [
                    'Hãy nhớ rằng số mệnh không phải là đá tảng không thể thay đổi.',
                    'Bạn có quyền lựa chọn thái độ sống.',
                    'Học cách quản lý cảm xúc, đừng để bản thân bị ám ảnh bởi ý kiến của người khác.'
                ],
                'lieu_phap' => [
                    'Tìm kiếm sự bình yên nội tâm.',
                    'Đừng so sánh mình với người khác.',
                    'Kỷ luật trong sinh hoạt và tập luyện sự tập trung sẽ giúp tinh thần bạn vững vàng hơn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, tò mò và có trực giác cực kỳ mạnh mẽ.',
                'Khả năng thích ứng: Giống như loài dây leo, bạn thích nghi tuyệt vời với mọi hoàn cảnh. Bạn sử dụng sự quyến rũ và trí tuệ để giải quyết vấn đề. Bạn luôn muốn nâng cao kỹ năng và chuyên môn hóa bản thân.',
                'Trực giác: Bạn thường dựa vào linh cảm để ra quyết định. Tuy nhiên, đôi khi cần phải hoài nghi và dùng lý trí để cân bằng lại, tránh bị trực giác đánh lừa.',
                'Thách thức: Bạn có quá nhiều sở thích và tài năng nên có thể bị phân tâm, thiếu tập trung, xu hướng cả thèm chóng chán. Bạn cũng cần giải quyết mâu thuẫn giữa chủ nghĩa duy vật và lý tưởng sống.',
                'dinh_huong' => [
                    'Tìm ra một mục đích sống thực sự truyền cảm hứng để bạn dấn thân.',
                    'Kỷ luật là bài học lớn nhất bạn cần tốt nghiệp.',
                    'Hãy khuyến khích bản thân thể hiện qua nghệ thuật, ngôn ngữ để giải phóng năng lượng sáng tạo.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người của đám đông, dễ gần và có sức lôi cuốn kỳ lạ.',
                'Mạng lưới hỗ trợ: Bạn dễ dàng nhận được sự giúp đỡ từ bạn bè, đồng nghiệp. Bạn hòa nhập tốt với các nhóm xã hội và thường tìm kiếm những người có quyền lực hoặc niềm tin mạnh mẽ để kết giao.',
                'Rủi ro: Mặc dù hòa đồng, bạn có thể che giấu sự bất an và thiếu tin tưởng vào người khác, đặc biệt là với cha mẹ hoặc cấp trên nếu gặp xung khắc. Bạn cũng có xu hướng trở nên độc đoán với bạn bè.',
                'chien_luoc' => [
                    'Hãy dùng kỹ năng ngoại giao tuyệt vời của mình để tạo ra sự hài hòa.',
                    'Đừng để sự ghen tị hay so sánh làm hỏng các mối quan hệ tốt đẹp.',
                    'Hãy tin tưởng vào những người bạn chân thành.'
                ]
            ]
        ],
        'at_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một loài dây leo xanh tốt hoặc những bông hoa rực rỡ đang vươn mình dưới ánh nắng mặt trời chói chang. Bạn mang trong mình nguồn năng lượng của sự sinh sôi, tỏa sáng và biến hóa khôn lường. Bạn lạc quan, hướng ngoại, thông minh và luôn khao khát được khẳng định bản thân.',
                'La Bàn Thịnh Vượng sẽ giúp bạn giữ cho "bông hoa" ấy luôn tươi thắm mà không bị héo vì nắng gắt.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu một trí tuệ sắc bén và khả năng biểu đạt tuyệt vời. Bạn không phải là người chịu ngồi yên trong bóng tối.',
                'Tư duy sáng tạo và truyền cảm hứng: Bạn có một luồng ý tưởng không bao giờ cạn. Bạn thông minh, nhanh nhạy và có khả năng truyền tải ý tưởng của mình một cách đầy thuyết phục. Bạn phù hợp với những nghề nghiệp đòi hỏi sự sáng tạo, giao tiếp và tỏa sáng như: nghệ thuật, âm nhạc, viết lách, giảng dạy, diễn thuyết hoặc marketing, quảng cáo.',
                'Tố chất lãnh đạo: Bạn có khả năng tổ chức và quản lý bẩm sinh. Bạn biết cách điều hành dự án và dẫn dắt đội nhóm bằng sự tinh tế và trực giác nhạy bén về con người. Bạn thích hợp làm quản lý, điều hành hoặc tự kinh doanh hơn là làm nhân viên thừa hành.',
                'Khát khao sự công nhận: Bạn làm việc khá chăm chỉ và có trách nhiệm, nhưng động lực lớn nhất của bạn là được mọi người ghi nhận và tán thưởng. Bạn thích đứng ở vị trí trung tâm, nơi ánh đèn sân khấu chiếu vào.',
                'Thách thức: Đôi khi bạn quá tự tin vào bản thân dẫn đến chủ quan. Nhu cầu tìm kiếm sự mới mẻ liên tục có thể khiến bạn mất tập trung vào mục tiêu dài hạn hoặc trì hoãn có thể khiến bạn bỏ dở những dự án tiềm năng. Bạn cũng có xu hướng muốn kiểm soát mọi thứ và trở nên cứng nhắc khi gặp áp lực.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc mang lại cho bạn sự tự do và không gian để sáng tạo.',
                    'Rèn luyện tính kỷ luật là chìa khóa vàng để bạn biến những ý tưởng bay bổng thành hiện thực.',
                    'Hãy học cách lắng nghe ý kiến của người khác để tránh trở nên độc đoán.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là một phần quan trọng chi phối cuộc sống của bạn. Bạn có năng khiếu bẩm sinh trong việc tạo ra của cải.',
                'Sự nhạy bén về tiền bạc: Bạn có đầu óc kinh doanh thực tế. Bạn biết cách thương mại hóa tài năng của mình để kiếm tiền. Bạn không chỉ làm nghệ thuật vì đam mê mà còn biết cách bán nó. Những người sinh vào ban ngày thường dễ dàng phát tài và làm ăn phát đạt hơn.',
                'Động lực từ vật chất: Bạn thích sự giàu có và những tiện nghi mà tiền bạc mang lại. Nhu cầu an toàn tài chính thúc đẩy bạn làm việc không ngừng nghỉ.',
                'Rủi ro: Bạn có thể bị lo lắng, bồn chồn về tiền bạc ngay cả khi đang sung túc. Đôi khi lòng tham hoặc sự nóng vội muốn làm giàu nhanh khiến bạn sa vào những kế hoạch đầu tư mạo hiểm, rủi ro cao.',
                'dinh_huong' => [
                    'Hãy học cách quản lý rủi ro.',
                    'Tránh xa những cơ hội làm giàu "ngon ăn" nhưng thiếu minh bạch.',
                    'Bạn nên tập trung phát triển kỹ năng chuyên môn sâu, đó là cỗ máy in tiền bền vững nhất của bạn.',
                    'Kỷ luật trong chi tiêu cũng là điều cần thiết để giữ gìn tài sản.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là một người tình lãng mạn, quyến rũ nhưng cũng đầy phức tạp.',
                'Sự hấp dẫn tự nhiên: Bạn hòa đồng, đáng yêu và chân thành. Bạn dễ dàng thu hút người khác phái bằng sự duyên dáng và ấm áp của mình. Khi yêu, bạn hết lòng quan tâm và hỗ trợ đối phương.',
                'Hôn nhân: Nam: Thường có xu hướng nể vợ hoặc bị vợ kiểm soát một chút. Bạn thích mẫu phụ nữ thông minh, tháo vát để có thể dựa vào. Nữ: Mạnh mẽ và thành đạt. Bạn có xu hướng là trụ cột hoặc người dẫn dắt trong gia đình. Đôi khi bạn kết hôn với người ít quyền lực hơn mình hoặc những người đã từng đổ vỡ để tìm kiếm sự cân bằng.',
                'Thách thức: Dù yêu thương gia đình, nhưng sâu thẳm bạn vẫn khao khát sự tự do và những trải nghiệm mới mẻ. Sự bồn chồn này đôi khi khiến bạn thiếu quyết đoán hoặc có thể bị dao động trước những cám dỗ bên ngoài. Bạn cũng hay nghi ngờ và thiếu kiên nhẫn với người thân.',
                'chien_luoc' => [
                    'Hãy xây dựng nền tảng lòng tin vững chắc ngay từ đầu.',
                    'Học cách cân bằng giữa nhu cầu tự do cá nhân và trách nhiệm gia đình.',
                    'Đừng để sự kiểm soát giết chết sự lãng mạn.',
                    'Hãy tìm một người bạn đời có tầm nhìn rộng, trân trọng trí tuệ của bạn để cùng nhau phát triển.'
                ]
            ],
            'suc_khoe' => [
                'Bạn giống như loài cây cỏ có sức sống dẻo dai, nhưng ngọn lửa ngầm bên trong luôn âm thầm đốt cháy năng lượng của bạn.',
                'Căng thẳng và mệt mỏi: Bạn là người hay lo âu, suy nghĩ nhiều về danh vọng và tiền bạc. Sự căng thẳng kéo dài là thách thức lớn nhất đối với sức khỏe của bạn, có thể dẫn đến kiệt sức hoặc các vấn đề về thần kinh.',
                'Sức khỏe sinh sản: Đối với phụ nữ, cần đặc biệt chú ý đến sức khỏe sinh sản và thai kỳ, nên tìm kiếm sự chăm sóc y tế sớm khi có tin vui.',
                'Rủi ro tai nạn: Nếu trong lá số gặp các yếu tố xung khắc, bạn có thể gặp phải chấn thương hoặc tai nạn nhỏ do tính cách vội vàng, hấp tấp.',
                'Cách thức cân bằng: Hãy uống nhiều nước, đi bơi hoặc dành thời gian thư giãn, thiền định để xoa dịu tâm trí.',
                'lieu_phap' => [
                    'Học cách buông bỏ những lo âu không cần thiết.',
                    'Hãy trân trọng những điều nhỏ bé, tích cực trong cuộc sống để đẩy lùi nguy cơ trầm cảm.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người thông minh, học nhanh và có cái nhìn sâu sắc về cuộc đời.',
                'Trí tuệ thực tế: Bạn nắm bắt các khái niệm mới khá nhanh và biết cách ứng dụng chúng vào thực tế để kiếm tiền hoặc tạo danh tiếng. Bạn có khả năng tự học và thích nghi cao.',
                'Trực giác mạnh mẽ: Bạn có giác quan thứ 6 nhạy bén. Tuy nhiên, đôi khi sự tự tin thái quá vào bản năng khiến bạn bỏ qua những lời khuyên hữu ích từ người khác.',
                'Thách thức: Sự bướng bỉnh và bảo thủ có thể cản trở sự phát triển của bạn. Nếu gặp khó khăn, bạn có xu hướng tìm đến tâm linh hoặc tôn giáo để tìm câu trả lời.',
                'dinh_huong' => [
                    'Hãy kết hợp giữa trực giác và lý trí.',
                    'Học cách lắng nghe và tiếp thu ý kiến trái chiều.',
                    'Tìm kiếm sự cân bằng giữa lý tưởng cao đẹp và thực tế cuộc sống.',
                    'Những trải nghiệm khó khăn chính là bài học quý giá giúp bạn trưởng thành vượt bậc.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người quảng giao, dễ mến và có khả năng kết nối tuyệt vời.',
                'Mạng lưới xã hội: Bạn dễ dàng hòa nhập với mọi người và tạo được ấn tượng tốt. Bạn thích kết giao với những người có tri thức, hiểu biết để học hỏi.',
                'Quan hệ gia đình: Bạn là người bảo vệ và trung thành với người thân. Gia đình là nơi bạn tìm thấy sự an toàn.',
                'Những mối quan hệ tốt đẹp, hay còn gọi là Quý nhân, sẽ mang lại cho bạn sự hỗ trợ lớn trong sự nghiệp.',
                'Rủi ro: Bạn có thể che giấu sự bất an và thiếu tin tưởng đằng sau vẻ ngoài hòa đồng. Đôi khi sự ghen tị ngầm hoặc so sánh bản thân với người khác làm bạn mất vui.',
                'chien_luoc' => [
                    'Hãy xây dựng những mối quan hệ sâu sắc thay vì hời hợt.',
                    'Tránh xa sự đố kỵ.',
                    'Hãy dùng sự chân thành và lòng tốt của mình để đối đãi với mọi người, bạn sẽ nhận lại sự hỗ trợ tương xứng.'
                ]
            ]
        ],
        'at_hoi' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một khúc gỗ, một chiếc bè hay một đoá hoa sen đang trôi nhẹ nhàng trên dòng sông lớn hoặc mặt biển mênh mông. Bạn được dòng nước nâng đỡ, nuôi dưỡng và đưa đi xa. Điều này tạo nên một con người cực kỳ linh hoạt, thông minh, có khả năng thích nghi và sinh tồn mạnh mẽ, nhưng cũng mang chút gì đó phiêu du, gắn liên với sự di chuyển, thay đổi và không cố định một chỗ.',
                'La Bàn Thịnh Vượng sẽ giúp bạn tìm được "bến đỗ" vững chắc để phát triển tài năng.'
            ],
            'su_nghiep' => [
                'Bạn là những bậc thầy về kết nối và thích nghi. Bạn không bao giờ bị đóng khung trong một khuôn mẫu nào.',
                'Người kết nối và ngoại giao: Bạn có kỹ năng giao tiếp và xây dựng mạng lưới quan hệ tuyệt vời. Bạn nhanh trí, tư duy sắc bén và luôn tìm ra giải pháp khi người khác bế tắc. Bạn phù hợp với các công việc trong tập đoàn đa quốc gia, truyền thông, chính trị hoặc luật pháp - nơi cần sự linh hoạt và tư duy chiến lược.',
                'Tố chất lãnh đạo: Bạn có khả năng tổ chức và lãnh đạo tiềm ẩn. Bạn biết cách dùng người và tận dụng các mối quan hệ để đạt được mục tiêu. Bạn tham vọng và luôn muốn thăng tiến.',
                'Lòng nhân ái: Bạn có sự thấu cảm sâu sắc. Các nghề nghiệp như tư vấn, chữa lành, bác sĩ tâm lý khá phù hợp với bạn.',
                'Thách thức: Bạn có thể bị dao động bởi ý kiến của người khác, có xu hướng linh hoạt quá mức để làm hài lòng mọi người hoặc dễ bị ảnh hưởng bởi môi trường xung quanh mà thiếu đi chính kiến riêng. Đôi khi bạn thiếu sự kiên định, hay trì hoãn hoặc thay đổi mục tiêu liên tục. Nếu bị dồn vào thế bí, bạn có thể trở nên toan tính và thao túng người khác.',
                'chien_luoc' => [
                    'Hãy tìm một lý tưởng sống hoặc một mục tiêu nghề nghiệp thực sự ý nghĩa để neo giữ tâm trí bạn.',
                    'Sự độc lập và tự chủ là môi trường tốt nhất để bạn phát triển.',
                    'Rèn luyện sự kiên định là bài học quan trọng nhất cho sự nghiệp của bạn.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là động lực lớn của bạn. Bạn có tư duy làm giàu sắc bén.',
                'Nhạy bén kinh doanh: Bạn có khả năng kinh doanh bẩm sinh. Bạn biết cách sử dụng trí tuệ và các mối quan hệ để tạo ra tiền bạc. Bạn thường tìm thấy cơ hội ở những nơi người khác không thấy.',
                'Tiềm năng thịnh vượng: Tài lộc của bạn sẽ khá vượng, bạn có khả năng sống sung túc và thoải mái.',
                'Rủi ro: Cảm xúc thất thường là kẻ thù của túi tiền bạn. Khi vui hoặc buồn, bạn có thể đưa ra các quyết định tài chính sai lầm. Bạn cũng có thể bị dụ dỗ vào các kế hoạch làm giàu nhanh đầy rủi ro.',
                'dinh_huong' => [
                    'Ưu tiên sự ổn định.',
                    'Đầu tư vào kiến thức và giáo dục là cách làm giàu bền vững nhất cho bạn.',
                    'Hãy tìm kiếm các yếu tố sáng tạo và sự ổn định trong công việc kinh doanh để cân bằng lại tính "trôi nổi" của mình.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người duyên dáng, ngọt ngào nhưng cũng đầy bất an.',
                'Sự quyến rũ: Bạn lôi cuốn, hòa đồng và khá biết cách chiều chuộng đối phương. Bạn là người yêu lãng mạn, biết quan tâm và chăm sóc.',
                'Khao khát sự an toàn: Bạn cần một người bạn đời vững chãi, một bến đỗ an toàn để bạn dựa vào. Bạn thường bị thu hút bởi những người có quyền lực, tham vọng hoặc có khả năng che chở cho bạn.',
                'Thách thức: Bạn hay thiếu quyết đoán, lo âu và nghi ngờ trong tình cảm. Đôi khi bạn muốn kiểm soát người yêu vì cảm giác bất an. Bạn cũng có thể cam kết quá sớm khi chưa tìm hiểu kỹ, dẫn đến những đổ vỡ không đáng có.',
                'chien_luoc' => [
                    'Đừng vội vã.',
                    'Hãy dành thời gian để xây dựng niềm tin.',
                    'Học cách tự đứng trên đôi chân của mình về mặt cảm xúc thay vì phụ thuộc hoàn toàn vào đối phương.',
                    'Sự kiên nhẫn và giao tiếp chân thành sẽ giúp mối quan hệ bền vững hơn.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng lớn từ cảm xúc.',
                'Nguy cơ trầm cảm: Dòng cảm xúc dạt dào dễ khiến bạn sinh ra u sầu, buồn bã. Bạn có thể bị trầm cảm hoặc suy nghĩ tiêu cực nếu không kiểm soát tốt cảm xúc.',
                'Sức sống dẻo dai: Tuy nhiên, bản chất của bạn là loài cây có sức sống mạnh mẽ, khả năng phục hồi tốt sau biến cố.',
                'Cách thức cân bằng: Bạn cần ánh nắng để sưởi ấm và giúp cây quang hợp. Hãy hướng suy nghĩ về những điều tích cực. Tham gia các hoạt động vui chơi, giải trí để xua tan sự u ám.',
                'lieu_phap' => [
                    'Thiền định giúp bạn tĩnh tâm và bớt dao động.',
                    'Chú ý giữ ấm cơ thể, tránh để bị lạnh.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người ham học, hiểu biết rộng và có chiều sâu.',
                'Trí tuệ uyên bác: Nhờ được dòng nước nuôi dưỡng, bạn có khả năng tiếp thu kiến thức tuyệt vời. Bạn thích hợp với con đường học thuật, nghiên cứu chuyên sâu.',
                'Tầm nhìn xa: Bạn có cái nhìn sâu sắc về cuộc đời và con người. Bạn hiểu được những quy luật ngầm của xã hội.',
                'Thách thức: Sự trì hoãn và thiếu kỷ luật. Bạn thông minh nhưng cần có động lực mạnh mẽ để hành động. Đôi khi bạn có xu hướng hài lòng với hiện tại và trì hoãn việc phấn đấu. Bạn hay che giấu những cảm xúc thật bên trong vẻ ngoài xa cách.',
                'dinh_huong' => [
                    'Kỷ luật tự giác là chìa khóa thành công.',
                    'Hãy tìm một người thầy hoặc một môi trường nghiêm túc để rèn luyện bản thân.',
                    'Đừng để sự thông minh khiến bạn chủ quan.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người của công chúng, đi đến đâu cũng có bạn bè.',
                'Hòa đồng và dễ mến: Bạn kết bạn khá dễ dàng với đủ mọi tầng lớp. Bạn tạo cảm giác dễ chịu và thoải mái cho người đối diện.',
                'Được quý nhân hỗ trợ: Bạn thường xuyên nhận được sự giúp đỡ từ người khác, đặc biệt là những người lớn tuổi, có quyền lực hoặc có tri thức.',
                'Rủi ro: Bạn cần cẩn thận trong việc thiết lập ranh giới. Đôi khi vì quá cả nể hoặc muốn làm hài lòng người khác mà bạn bị cuốn vào những rắc rối không đáng có.',
                'chien_luoc' => [
                    'Hãy trân trọng những người bạn chia sẻ tri thức và kinh nghiệm sống với bạn.',
                    'Học cách kiểm soát sự nóng nảy khi cảm thấy bất an.',
                    'Sự chân thành sẽ giúp bạn giữ được những mối quan hệ quý giá lâu dài.'
                ]
            ]
        ],
        'at_mui' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một loài thực vật thân mềm, dây leo hoặc cây xương rồng kiên cường bám rễ và sinh tồn trên vùng đất khô cằn. Bạn là người có bản năng sinh tồn mạnh mẽ. Bề ngoài bạn có vẻ mềm mỏng, duyên dáng, nhưng bên trong là một sức sống mãnh liệt, khả năng thích nghi phi thường và một ý chí không gì khuất phục được.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến sự khắc nghiệt thành sức mạnh để vươn lên.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tham vọng ngầm và khả năng làm việc chăm chỉ đáng nể.',
                'Chiến binh bền bỉ: Bạn không ngại khó khăn. Bạn có khả năng chịu đựng áp lực và kiên trì theo đuổi mục tiêu đến cùng. Bạn có tố chất lãnh đạo tiềm ẩn, biết cách quản lý và điều hành công việc hiệu quả.',
                'Kỹ năng đàm phán: Bạn sở hữu trí tuệ sắc bén và khả năng giao tiếp khéo léo. Bạn là người đàm phán, thương thuyết xuất sắc. Bạn phù hợp với các lĩnh vực kinh doanh, luật pháp, thương mại.',
                'Sáng tạo và nhân đạo: Bạn cũng có năng khiếu nghệ thuật và khả năng biểu đạt tốt như giải trí, mỹ thuật, văn chương. Bên cạnh đó, tính cách nhân hậu thúc đẩy bạn tham gia vào các hoạt động cộng đồng, từ thiện.',
                'Thách thức: Bạn có xu hướng muốn kiểm soát mọi thứ. Sự cầu toàn và việc đặt ra tiêu chuẩn cao trong công việc đôi khi vô tình tạo áp lực cho đồng nghiệp. Bạn cũng có thể bị mất kiên nhẫn hoặc làm việc quá sức đến mức kiệt quệ.',
                'chien_luoc' => [
                    'Hãy học cách ủy quyền và tin tưởng người khác.',
                    'Đừng ôm đồm mọi việc vào mình.',
                    'Đặt ra ranh giới giữa công việc và nghỉ ngơi.',
                    'Sự nghiệp lý tưởng của bạn là nơi bạn có sự độc lập và tự chủ.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là điểm sáng của bạn. Bạn được mệnh danh là người khéo léo về tiền bạc.',
                'Trực giác tài chính: Bạn ngồi trên kho tàng tài chính. Bạn có trực giác cực tốt về tiền bạc và đầu tư. Bạn biết cách kiếm tiền từ những cơ hội mà người khác không thấy, kể cả những nguồn thu nhập phụ.',
                'Lối sống: Bạn thích sự xa hoa và tiện nghi. Bạn làm việc chăm chỉ để chi trả cho những nhu cầu hưởng thụ của mình. Khả năng tự tạo ra tài sản của bạn khá cao.',
                'Rủi ro: Bạn hay lo lắng thái quá về tiền bạc. Đôi khi vì quá tập trung vào bức tranh lớn mà bạn bỏ qua các chi tiết nhỏ trong quản lý dòng tiền. Cần đặc biệt cẩn thận với những kế hoạch làm giàu nhanh chóng, đó có thể là cái bẫy.',
                'dinh_huong' => [
                    'Hãy giữ sự thận trọng.',
                    'Phát triển kỹ năng quản lý chi tiết.',
                    'Xây dựng tính kỷ luật để quản lý tài sản chặt chẽ hơn.',
                    'Đầu tư vào những lĩnh vực bạn am hiểu và có sự an toàn cao.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là một người lãng mạn, sâu sắc nhưng cũng đầy tính sở hữu.',
                'Yêu hết mình: Bạn là người tình cảm, chu đáo và hào phóng. Khi yêu, bạn trung thành và tận tụy. Bạn khao khát sự gần gũi và một mái ấm ổn định.',
                'Tiêu chuẩn: Bạn cần một người bạn đời có thể mang lại sự an toàn về tài chính và cảm xúc. Bạn bị thu hút bởi những người thông minh, tham vọng.',
                'Thách thức: Bạn có tính sở hữu cao và hay ghen tuông. Đôi khi sự quan tâm của bạn biến thành sự kiểm soát, áp đặt ý kiến lên đối phương. Bạn cũng có thể rơi vào trạng thái thiếu quyết đoán hoặc lo lắng vẩn vơ về mối quan hệ.',
                'chien_luoc' => [
                    'Hãy học cách tôn trọng không gian riêng của nhau.',
                    'Đừng cố gắng thay đổi hay kiểm soát người bạn đời.',
                    'Sự tin tưởng là liều thuốc giải cho tính hay ghen của bạn.',
                    'Hãy giao tiếp thẳng thắn thay vì giữ sự nghi ngờ trong lòng.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe gắn liền với trạng thái thăng trầm cảm xúc của bạn.',
                'Dao động cảm xúc: Bạn có thể chuyển từ trạng thái cực kỳ hưng phấn sang thất vọng, u sầu. Sự bất ổn này gây ra căng thẳng thần kinh và nguy cơ trầm cảm.',
                'Kiệt sức: Vì tham vọng và làm việc không ngừng nghỉ, bạn có thể bị kiệt sức về thể chất.',
                'Đối với phụ nữ, cần đặc biệt chú ý giữ gìn thai kỳ và sức khỏe sinh sản.',
                'Cách thức cân bằng: Thiền định và các hoạt động tâm linh là phương pháp tốt nhất để bạn tìm lại sự cân bằng nội tâm. Hãy tập trung vào những điều tích cực, biết ơn những gì mình đang có.',
                'nghi_ngoi' => [
                    'Hãy cho phép bản thân được lười biếng đôi chút.',
                    'Nghỉ ngơi không phải là lãng phí thời gian, mà là nạp năng lượng để đi xa hơn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, thực tế và có khả năng tự học khá tốt.',
                'Tự lực cánh sinh: Bạn tin vào năng lực của chính mình. Bạn học hỏi nhanh và biết cách áp dụng kiến thức vào thực tế để giải quyết vấn đề. Bạn có sự hiểu biết bẩm sinh về tâm lý con người.',
                'Đam mê học thuật: Bạn có tiềm năng lớn trong con đường học thuật, nghiên cứu hoặc các lĩnh vực chuyên sâu như y học, triết học, tôn giáo.',
                'Thách thức: Đôi khi bạn quá tin vào phán đoán cá nhân mà trở nên bảo thủ, bỏ qua những góc nhìn giá trị từ người khác. Hoặc ngược lại, bạn bị phân tâm bởi quá nhiều sở thích mà thiếu sự tập trung.',
                'dinh_huong' => [
                    'Kỷ luật là bài học cốt lõi.',
                    'Hãy kết hợp trực giác nhạy bén với sự lắng nghe khiêm tốn.',
                    'Tìm ra một mục đích sống cao cả để cống hiến, bạn sẽ thấy cuộc đời mình ý nghĩa hơn nhiều.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hòa đồng, thân thiện và có sức hút xã hội.',
                'Người kết nối: Bạn giỏi xây dựng mạng lưới quan hệ. Bạn quyến rũ, dễ mến và biết cách tạo ấn tượng tốt. Bạn thường được bạn bè tìm đến để xin lời khuyên.',
                'Quý nhân: Nhờ tấm lòng hào hiệp, bạn thường thu hút được những người có quyền lực và tri thức đến giúp đỡ mình.',
                'Rủi ro: Bạn có xu hướng muốn thống trị trong nhóm bạn bè. Đôi khi bạn giữ bí mật quá nhiều khiến người khác cảm thấy bạn khó hiểu hoặc lạnh lùng.',
                'chien_luoc' => [
                    'Hãy xây dựng những mối quan hệ dựa trên sự bình đẳng và chân thành.',
                    'Kiên nhẫn hơn với mọi người.',
                    'Hãy tiếp tục trau dồi tri thức để thu hút những người bạn cùng tần số.'
                ]
            ]
        ],
        'binh_tuat' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp tráng lệ, huy hoàng nhưng đầy tâm sự: Đó là ánh hoàng hôn rực đỏ nhuộm hồng vùng núi non hùng vĩ. Hay sâu sắc hơn, đó là ngọn núi lửa đang ngủ yên, bề ngoài tĩnh lặng nhưng bên trong là nham thạch nóng bỏng đang âm ỉ chảy. Bạn nhân hậu, sâu sắc, sống nặng tình và có trực giác tâm linh kỳ lạ. Cuộc đời bạn là hành trình tìm sự bình yên giữa biến động cảm xúc.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khơi dậy ngọn lửa ấm áp để sưởi ấm chính mình.'
            ],
            'su_nghiep' => [
                'Bạn không thích tranh giành hào quang nơi tiền tuyến, nhưng lại có sức ảnh hưởng sâu rộng và bền bỉ.',
                'Lãnh đạo bằng đắc nhân tâm: Bạn không dùng quyền lực áp đặt. bạn thu phục nhân tâm bằng sự tử tế và thấu hiểu. bạn hợp nhất với vai trò cố vấn chiến lược, mentor, người thầy hoặc lãnh đạo tinh thần. sự trung thành khiến bạn được kính trọng tuyệt đối.',
                'Sáng tạo từ chiều sâu: Bạn có nguồn năng lượng sáng tạo dồi dào. Bạn có khiếu thẩm mỹ tinh tế, cảm thụ văn chương sâu sắc. Sản phẩm của bạn thường mang đậm triết lý nhân sinh và chạm đến trái tim. Lĩnh vực phù hợp: viết lách, thiết kế, kiến trúc, tôn giáo, tâm lý học.',
                'Cầu toàn và trách nhiệm: Bạn làm việc kỹ lưỡng, chu đáo từng chi tiết. Bạn thường là người ở lại cuối cùng để kiểm tra mọi thứ. Tuy nhiên, sự cầu toàn khiến bạn hay ôm đồm.',
                'Thách thức: Cảm xúc chi phối: Một lời chê bai nhỏ cũng làm bạn mất tinh thần cả ngày. Sự do dự: Vì quá lo lắng rủi ro, bạn suy nghĩ quá nhiều và chậm chân hơn đối thủ.',
                'chien_luoc' => [
                    'Tin tưởng trực giác: Lắng nghe tiếng nói bên trong giác quan thứ 6 của bạn thay vì nỗi sợ của lý trí.',
                    'Tìm cộng sự: Cần đồng đội có năng lượng quyết đoán cao để bổ khuyết cho sự thận trọng của bạn và thúc đẩy bạn hành động.',
                    'Định vị giá trị: Chọn con đường chuyên gia, tư vấn chuyên sâu thay vì quản lý vận hành. giá trị của bạn nằm ở cái đầu và trái tim.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn hiếm khi bùng nổ kiểu tỷ phú một đêm nhưng cực kỳ vững chắc và gia tăng đều đặn.',
                'Tích lũy bền bỉ: Bạn không giàu xổi. Tài sản đến từ lao động chăm chỉ, tiết kiệm và đầu tư an toàn. Bạn là bậc thầy vun vén tài chính gia đình, biết quý trọng đồng tiền.',
                'Duyên nợ với điền sản: Bạn mát tay với đất đai, bất động sản hoặc tài sản cố định. Đầu tư vào đất là cách làm giàu an toàn và phù hợp nhất. Bạn được hưởng thừa kế từ gia đình.',
                'Tâm lý lo xa: Dù có tiền, bạn vẫn cảm thấy chưa đủ an toàn. Tâm lý này giúp tránh rủi ro nhưng khiến cuộc sống đôi khi khắc khổ, thiếu tận hưởng.',
                'Rủi ro: Lòng tốt đặt nhầm chỗ, bạn có thể mủi lòng cho vay mượn hoặc bị lừa gạt dưới danh nghĩa giúp đỡ vì quá tin người.',
                'dinh_huong' => [
                    'Đầu tư ăn chắc mặc bền: Kiên định với bất động sản, tiết kiệm. Tránh xa chứng khoán lướt sóng hay tiền ảo.',
                    'Lan toả năng lượng tiền bạc: Trích một phần lợi nhuận để làm từ thiện. Sự hào phóng đúng chỗ sẽ giúp bạn kích hoạt thêm tài lộc.',
                    'Tận hưởng hiện tại: Dùng tiền chăm sóc bản thân ngay hôm nay, đừng chỉ sống cho 20 năm sau.'
                ]
            ],
            'tinh_duyen' => [
                'Bạn là người sống rất nặng tình. Tình cảm và gia đình là ưu tiên số một trong cuộc đời bạn.',
                'Sự chung thủy: Bạn là mẫu người yêu lý tưởng của truyền thống, chân thành, chung thủy sắt son. Bạn yêu bằng cả sinh mệnh, sẵn sàng hy sinh tất cả để làm hậu phương vững chắc.',
                'Tâm hồn nhạy cảm: Bạn cực kỳ nhạy cảm với thái độ người khác. Bạn hay nuốt nước mắt vào trong, giữ ấm ức vì sợ rạn nứt. Sự chịu đựng này tạo thành vết thương tâm lý khó lành.',
                'Bạn có thể gặp trắc trở, yêu xa, bị ngăn cấm hoặc yêu người có hoàn cảnh phức tạp cần che chở. Đường tình thường đẫm nước mắt trước khi tìm thấy hạnh phúc.',
                'chien_luoc' => [
                    'Yêu bản thân trước: Ngưng hy sinh mù quáng. Giữ lại cho mình khoảng trời riêng.',
                    'Tìm bạn đời vững chãi: Cần người thực tế, bao dung để làm chỗ dựa, kéo bạn ra khỏi bi quan.',
                    'Giao tiếp thẳng thắn: Học cách nói ra nhu cầu, đừng bắt đối phương phải tự hiểu.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe là phản chiếu của tinh thần. Tâm bệnh sinh thân bệnh.',
                'Hệ tiêu hóa: Thói quen hay lo nghĩ hại tỳ vị. Bạn có thể đau dạ dày, trào ngược, rối loạn chuyển hóa. Bạn có thể béo hoặc quá gầy.',
                'Sức khỏe tinh thần: Đây là vấn đề lớn nhất. Bạn dễ bị trầm cảm, u uất, rối loạn lo âu hoặc mất ngủ. Những suy nghĩ tiêu cực cứ lặp đi lặp lại trong đầu khiến bạn mệt mỏi mãn tính.',
                'Hệ tim mạch: Lo âu gây áp lực lên tim, có thể hồi hộp, cao huyết áp.',
                'cach_thuc_can_bang' => [
                    'Liệu pháp thiên nhiên: Dành thời gian đi dạo, trồng cây. Màu xanh lá là màu chữa lành của bạn.',
                    'Thiền định & tôn giáo: Đây là liều thuốc cứu rỗi tâm hồn, giúp bạn buông bỏ gánh nặng tâm trí.',
                    'Chế độ ăn: Ăn thực phẩm dễ tiêu hoá, chia nhỏ bữa. Tránh ăn quá no buổi tối.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn không lanh lợi kiểu nhảy số nhanh, mà sở hữu trí tuệ của sự chiêm nghiệm.',
                'Tư duy triết học: Bạn luôn trăn trở về ý nghĩa cuộc đời, thích tìm hiểu tôn giáo, huyền học. Bạn nhìn thấy nỗi đau sau nụ cười của người khác.',
                'Học qua trải nghiệm: Trí tuệ được đúc kết từ va vấp và nỗi đau. Bạn có khả năng tự chữa lành phi thường và dùng nó giúp người khác.',
                'Thách thức: Sự bảo thủ và bi quan. Bạn đôi khi quá tin vào định kiến của mình.',
                'dinh_huong' => [
                    'Con đường tâm linh, nghệ thuật: Phát triển theo chiều sâu như viết sách, chữa lành, tâm lý.',
                    'Mở lòng: Tập lắng nghe quan điểm trái chiều để bớt khổ đau.',
                    'Viết lách: Viết ra suy nghĩ để tách biệt và quan sát nỗi buồn khách quan hơn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn thân thiện, dễ mến nhưng lại khá khép kín trong các mối quan hệ thân thiết.',
                'Người bạn chân thành: Bạn đối xử với bạn bè rất tốt, nhiệt tình và không toan tính. Tuy nhiên, bạn chỉ thực sự mở lòng với một vài người tri kỷ.',
                'Thu hút người khác: Sự ấm áp và lòng trắc ẩn của bạn khiến mọi người muốn đến gần. Bạn thường là thùng rác cảm xúc cho bạn bè trút bầu tâm sự.',
                'Rủi ro: Bạn có thể bị lợi dụng lòng tốt bởi những kẻ tiêu cực, họ sẽ hút cạn năng lượng tích cực của bạn. Đôi khi bạn cảm thấy cô đơn giữa đám đông vì không ai thực sự hiểu mình.',
                'chien_luoc' => [
                    'Thiết lập ranh giới: Học cách từ chối lắng nghe khi mệt mỏi.',
                    'Kết giao người tích cực: Tìm người lạc quan để cân bằng lại sự trầm lắng.',
                    'Chân thành nhưng tỉnh táo: Trao sự tử tế cho người biết trân trọng.'
                ]
            ]
        ],
        'binh_dan' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật hùng vĩ và tráng lệ: Một vầng dương quang đỏ rực đang từ từ nhô lên, chiếu rọi những tia nắng ấm áp xuyên qua tán lá của cánh rừng già nguyên sinh. Cảnh tượng này tượng trưng cho sự khởi đầu mới, niềm hy vọng tràn trề và tinh thần quang minh chính đại. Bạn sở hữu nguồn năng lượng dự trữ gần như vô tận, giống như ngọn lửa luôn có củi khô tiếp thêm liên tục. Dù cuộc đời có ném bạn vào bóng tối, bản năng sinh tồn và tinh thần lạc quan sẽ luôn giúp bạn tìm thấy ánh sáng để vươn lên.',
                'La Bàn Thịnh Vượng sẽ giúp bạn định hướng nguồn năng lượng cuộn trào ấy để kiến tạo di sản.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra không phải để làm người thừa hành, càng không phải để đi theo lối mòn. Trong dòng máu của bạn tuôn chảy tố chất của một nhà tiên phong.',
                'Tư duy đổi mới: Bạn thông minh, sáng tạo và sở hữu trí tuệ học thuật sắc bén. Bạn ghét cay ghét đắng sự trì trệ.',
                'Lãnh đạo bằng nhiệt huyết: Bạn lãnh đạo bằng sức ảnh hưởng và năng lượng lan tỏa.',
                'Lĩnh vực phù hợp: Bạn thăng hoa ở nơi cần sự tiên phong và giáo dục. Lãnh đạo & quản lý: CEO, chủ doanh nghiệp, khởi nghiệp. Giáo dục & đào tạo: Bạn rất phù hợp với nghề giáo, diễn giả hoặc tư vấn nhờ sở hữu khả năng truyền đạt lôi cuốn. Nghiên cứu & công nghệ: Khoa học, kỹ thuật, nhờ khả năng tập trung cao độ.',
                'Thách thức: Cả thèm chóng chán là điểm yếu cốt lõi của bạn. Bạn thường khởi đầu khá hoành tráng nhưng có thể mất lửa khi gặp khó khăn kéo dài. Bạn cũng hay ôm đồm vì nghĩ mình làm cho nhanh, dẫn đến sự độc đoán.',
                'chien_luoc' => [
                    'Rèn luyện kỷ luật: Học cách kết thúc những gì đã bắt đầu. Đừng để dự án dang dở làm mất uy tín.',
                    'Nghệ thuật ủy quyền: Bạn là tướng quân, hãy giao việc chi tiết cho cộng sự để tập trung vào chiến lược lớn.',
                    'Kiên nhẫn: Thành công lớn cần thời gian nuôi dưỡng như cây cổ thụ, đừng nóng vội đốt cháy giai đoạn.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là người có phước khí lớn về tài lộc, thường được hưởng lộc tự nhiên.',
                'Tiềm năng thịnh vượng: Bạn kiếm tiền khá tốt, thường gặp may mắn và có quý nhân phù trợ. Tài sản của bạn đến từ chất xám, ý tưởng và kinh doanh nhạy bén chứ không phải lao động chân tay. Hậu vận thường khá sung túc, điền sản dồi dào.',
                'Phong cách quý tộc: Bạn thích cuộc sống chất lượng cao, sang trọng. Bạn không tiếc tiền cho học hành, du lịch hay công nghệ. Với bạn, tiền bạc là công cụ để phục vụ cuộc sống và mở mang tầm mắt.',
                'Rủi ro: Sự hào phóng và đôi chút bốc đồng có thể khiến bạn vung tay quá trán. Những phút ngẫu hứng mua sắm hoặc đầu tư theo cảm xúc có thể làm thâm hụt ngân sách.',
                'dinh_huong' => [
                    'Đầu tư vào tri thức: Đây là khoản sinh lời vĩnh cửu của bạn.',
                    'Tích sản bền vững: Đầu tư vào bất động sản là cách giữ tiền an toàn nhất.',
                    'Quản lý cảm xúc: Tuyệt đối không đầu tư khi đang quá hưng phấn. Hãy tìm cố vấn tài chính lạnh lùng để kìm hãm sự bốc đồng của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Bề ngoài rạng rỡ tự tin, nhưng bên trong bạn là một trái tim nhạy cảm và khao khát yêu thương mãnh liệt.',
                'Yêu sâu sắc & hy sinh: Tình yêu của bạn nồng nàn, ấm áp như nắng ấm. Bạn trung thành, ân cần và sẵn sàng hy sinh cái tôi để che chở đối phương. Bạn tìm kiếm sự kết nối mật thiết cả về thể xác, tâm hồn lẫn trí tuệ.',
                'Tiêu chuẩn cao: Bạn bị thu hút bởi người thông minh, độc lập, có lý tưởng sống cao đẹp. Người đó phải vừa là người yêu, vừa là tri kỷ, vừa là đồng minh cùng bạn chinh phục thế giới.',
                'Thách thức: Cảm xúc của bạn có thể thất thường như biểu đồ hình sin: lúc nồng nhiệt quấn quýt, lúc lạnh lùng xa cách. Sâu thẳm bên trong, bạn có nỗi sợ bị bỏ rơi nên đôi khi nảy sinh tính sở hữu và kiểm soát cao.',
                'chien_luoc' => [
                    'Kết hôn muộn: Sự chín chắn của thời gian là liều thuốc tốt nhất cho hôn nhân của bạn.',
                    'Chia sẻ sự yếu đuối: Đừng cố tỏ ra mạnh mẽ, hãy mở lòng chia sẻ nỗi sợ hãi với bạn đời để gắn kết hơn.',
                    'Tôn trọng khoảng trời riêng: Lùi lại một bước để tình yêu có thêm oxy mà cháy bền bỉ hơn.'
                ]
            ],
            'suc_khoe' => [
                'Bạn sở hữu nguồn sinh lực dồi dào nhưng đôi khi khá dễ kiệt sức.',
                'Hội chứng kiệt quệ cảm xúc: Bạn làm việc quá sức, ôm đồm nhiều việc cùng lúc. Rủi ro lớn nhất là sự sụp đổ năng lượng đột ngột dẫn đến suy nhược cơ thể.',
                'Tâm bệnh: Tâm trí hoạt động không ngừng nghỉ gây căng thẳng thần kinh, đau đầu, mất ngủ hoặc vấn đề tim mạch. Sự dồn nén cảm xúc cũng gây hại cho hệ tiêu hóa.',
                'cach_thuc_can_bang' => [
                    'Sống chậm lại: Đây là mệnh lệnh quan trọng nhất. Hãy dành thời gian không làm gì cả.',
                    'Tìm nơi trú ẩn: Biến ngôi nhà thành trạm sạc pin yên tĩnh với nhiều cây xanh.',
                    'Xả năng lượng: Tham gia nghệ thuật hoặc thể thao để giải phóng cảm xúc tiêu cực.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là mẫu người Học tập suốt đời, tìm thấy niềm vui và bình yên trong tri thức.',
                'Trí tuệ & trực giác: Bạn có trí tưởng tượng phong phú, tư duy phân tích sắc bén và trực giác cực mạnh như một giác quan thứ 6. Bạn khá thích hợp với con đường nghiên cứu chuyên sâu.',
                'Thách thức: Sự độc lập thái quá có thể biến thành sự kiêu ngạo về trí tuệ và không chịu lắng nghe những sự góp ý. Tính nổi loạn ngầm đôi khi cản trở bạn tiếp thu kinh nghiệm từ tiền nhân.',
                'dinh_huong' => [
                    'Khiêm tốn học hỏi: Hãy mở lòng đón nhận ý kiến trái chiều.',
                    'Chuyên sâu hóa: Tập trung năng lượng để trở thành chuyên gia hàng đầu thay vì biết mỗi thứ một chút.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là thỏi nam châm thu hút mọi người, đi đến đâu cũng mang lại tiếng cười và năng lượng tích cực.',
                'Ngôi sao của đám đông: Mọi người ngưỡng mộ sự thông minh, hào sảng của bạn. Bạn ngoại giao tuyệt vời, dễ dàng kết bạn với mọi tầng lớp xã hội.',
                'Quý nhân: Bạn khá kén chọn tri kỷ. Những người có tri thức, đạo đức sẽ là quý nhân giúp đỡ bạn.',
                'chien_luoc' => [
                    'Hãy dùng sức ảnh hưởng để giúp đỡ cộng đồng.',
                    'Khi bạn cho đi giá trị, bạn sẽ nhận lại sự kính trọng.',
                    'Cẩn trọng lời nói quá thẳng thắn để tránh mất lòng cấp trên hoặc những người quan trọng.'
                ]
            ]
        ],
        'binh_ngo' => [
            'tong_quan' => [
                'Bạn giống như một con chiến mã toàn thân rực lửa đang phi nước đại trên thảo nguyên, mang sức mạnh của sự chinh phục và tự do tuyệt đối. Bạn sở hữu trường năng lượng cực thịnh, đại diện cho đỉnh cao của đam mê và quyền lực. Tuy nhiên, vật cực tất phản, ánh nắng quá gay gắt sẽ làm khô cằn vạn vật. Cuộc đời bạn là hành trình học cách kiểm soát ngọn lửa khổng lồ bên trong, để nó trở thành ánh hào quang sưởi ấm nhân gian chứ không phải ngọn lửa hủy diệt.',
                'La Bàn Thịnh Vượng sẽ giúp bạn cầm cương con chiến mã này.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra không phải để làm kẻ vô danh. Trong bạn đang chảy dòng máu của một người dẫn đầu bẩm sinh.',
                'Lãnh đạo quyền uy: Bạn mang khí chất của một vị đại tướng quân. Bạn quyết đoán, mạnh mẽ, dám nghĩ dám làm và dám chịu trách nhiệm. Khi bạn lãnh đạo, mọi người thường tuân theo bởi sức mạnh uy quyền tỏa ra từ bạn và kết quả thực tế mà bạn tạo ra.',
                'Năng lượng vô tận: Bạn làm việc với cường độ mà người khác khó theo kịp. Bạn ghét sự rề rà. Bạn nhanh nhẹn, hiệu quả và luôn muốn nhìn thấy kết quả ngay lập tức. Bạn là người tiên phong khai phá những thị trường mới, lĩnh vực mới. Một khi xác định mục tiêu, bạn cày nát mọi trở ngại. Càng áp lực, bạn càng tỏa sáng.',
                'Cạnh tranh khốc liệt: Bạn có tính cạnh tranh rất cao. Bạn không ngại đối đầu và luôn muốn là người chiến thắng. Tính hiếu thắng giúp bạn đánh bại những đối thủ sừng sỏ nhất.',
                'Thách thức: Độc đoán: Bạn có thể trở thành kẻ độc tài, không chấp nhận phản biện. Thiếu kiên nhẫn: có thể đốt cháy giai đoạn dẫn đến sai lầm. Cái tôi quá lớn cũng có thể khiến bạn đánh mất những đồng minh thân tín và cô độc trên đỉnh vinh quang.',
                'chien_luoc' => [
                    'Nhu thắng cương: Hãy học cách kiềm chế cơn giận và khích lệ nhân viên.',
                    'Một vị tướng tài luôn cần một quân sư giỏi: Bạn cần những cộng sự và trợ lý điềm tĩnh hoặc bao dung để làm dịu cái đầu nóng của bạn.',
                    'Điềm tĩnh: Tập đếm đến 10 trước khi bác bỏ ý kiến người khác để lắng nghe nhiều hơn và kiềm chế sự nóng giận.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn hiếm khi bình lặng. Nó giống biểu đồ nhịp tim: Có lúc lên đỉnh vinh quang, có lúc tụt dốc không phanh.',
                'Khả năng kiếm tiền khủng: Bạn có khả năng kiếm tiền cực nhanh và nhiều. Bạn có máu làm giàu rất lớn. Bạn thích những thương vụ để đời và dám chấp nhận rủi ro cao để đạt lợi nhuận lớn. Bạn có duyên với đầu cơ, chứng khoán, bất động sản dự án. Sự quyết đoán giúp bạn thu về những khoản lợi nhuận kếch xù trong thời gian ngắn.',
                'Chi tiêu phóng khoáng: Bạn sống sang chảnh và hào phóng. Bạn không tiếc tiền cho bản thân và anh em bạn bè, bạn sẵn sàng chi tiền tấn cho hàng hiệu, siêu xe để giữ gìn hình ảnh đẳng cấp. Đôi khi bạn chi tiêu chỉ vì sướng hoặc vì sĩ diện.',
                'Rủi ro mất mát: Tiền đến nhanh nhưng đi cũng nhanh. Rủi ro đến từ sự chủ quan, đầu tư theo cảm xúc, tính cách kiếm tiền một cách liều lĩnh và các nguy cơ bị người xung quanh lợi dụng. Phúc chốc, họa chầy.',
                'dinh_huong' => [
                    'Chiến lược két sắt an toàn: Chuyển đổi ngay tiền mặt thành các tài sản cố định có tính thanh khoản cao như Bất động sản hay Vàng để khóa chặt tiền lại.',
                    'Tránh xa cờ bạc: Với tính cách được ăn cả ngã về không, bạn cần tuyệt đối tránh xa cờ bạc hay các hình thức đỏ đen.',
                    'Quản lý rủi ro: Hãy thuê chuyên gia tài chính hoặc để người bạn đời cẩn trọng quản lý tài sản giúp bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn giống bộ phim bom tấn: Kịch tính, nồng nhiệt nhưng cũng đầy tổn thương.',
                'Yêu cuồng nhiệt: Bạn sở hữu sức quyến rũ xao xuyến người. Khi yêu, bạn lao vào như con thiêu thân, dồn toàn bộ tâm trí và tiền bạc cho đối phương. Bạn muốn sở hữu trọn vẹn người yêu.',
                'Sự cả thèm chóng chán: Bạn có thể bị thu hút bởi cái mới nhưng cũng nhanh chán khi cảm xúc nguội lạnh. Đối với nam giới, có nguy cơ cao vướng vào quan hệ ngoài luồng.',
                'Thách thức hôn nhân: Tính gia trưởng, muốn kiểm soát và những lời nói sát thương khi nóng giận có thể giết chết tình cảm. Vì năng lượng dương của bạn quá mạnh. Đối với nam giới, có thể tạo áp lực lớn lên người bạn đời, gây ra sự xa cách nếu không biết tiết chế. Đối với phụ nữ, có thể làm lận đận tình duyên.',
                'chien_luoc' => [
                    'Bạn cần một người bạn đời cực kỳ bao dung, nhu mì để dung hòa. Lạt mềm buộc chặt là bí quyết.',
                    'Kết hôn muộn: Sau 30 tuổi, sự chín chắn sẽ giúp hôn nhân bền vững hơn.',
                    'Hạ cái tôi: Hãy để uy quyền ngoài cửa trước khi bước vào nhà.'
                ]
            ],
            'suc_khoe' => [
                'Cơ thể bạn như cỗ siêu xe chạy tốc độ tối đa: Ít hỏng vặt, nhưng hỏng là hỏng lớn phải đại tu sửa.',
                'Hệ tim mạch & máu huyết: Bạn chú ý các nguy cơ cao mắc cao huyết áp, đột quỵ, tai biến, đặc biệt khi gặp cú sốc hoặc ở tuổi trung niên.',
                'Hệ thần kinh: Não bộ luôn trong trạng thái kích thích gây mất ngủ, khó thư giãn.',
                'Tai nạn thương tích: Tính vội vàng có thể dẫn đến tai nạn xe cộ hoặc chấn thương.',
                'cach_thuc_can_bang' => [
                    'Liệu pháp hạ nhiệt: Bơi lội là môn thể thao cứu tinh giúp hạ nhiệt toàn thân.',
                    'Thiền định: Hít thở sâu để điều hòa nhịp tim, tránh để ngọn lửa sân hận thiêu đốt cơ thể.',
                    'Kiêng chất kích thích: Hạn chế rượu bia, đồ cay nóng, những thứ đổ thêm dầu vào lửa.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn tài năng xuất chúng nhưng thường thiếu chiều sâu văn hóa và sự kiên nhẫn.',
                'Tư duy thực chiến: Bạn học qua trải nghiệm, nắm bắt vấn đề cực nhanh và hùng biện xuất sắc. Tư duy của bạn là tư duy đột phá.',
                'Thách thức: Sự nóng vội muốn đi tắt đón đầu tạo ra lỗ hổng kiến thức. Sự tự tin thái quá khiến bạn chủ quan và coi thường đối thủ.',
                'dinh_huong' => [
                    'Học cách cúi đầu: Lúa chín cúi đầu. Sự khiêm tốn sẽ khiến người khác kính nể bạn thực sự.',
                    'Học về chữ nhẫn: Tập những việc tỉ mỉ như câu cá, thư pháp để rèn luyện tính nhẫn nại và kìm hãm con ngựa hoang trong mình.',
                    'Tìm minh sư: Bạn cần một người thầy nghiêm khắc và đủ tầm để rèn giũa bạn vào khuôn khổ.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao sáng nhất nhưng ánh hào quang ấy đôi khi khá cô độc.',
                'Thủ lĩnh hội nhóm: Bạn hào sảng, chịu chơi, đi đến đâu cũng có bạn bè vây quanh và được tung hô.',
                'Thị phi: Cây cao đón gió lớn. Sự nổi bật và phô trương khiến bạn có thể bị ghen ghét, đố kỵ. Bạn cũng có thể vướng vào tranh chấp pháp lý do tính hiếu thắng.',
                'chien_luoc' => [
                    'Chọn bạn mà chơi: Phân biệt rõ bạn bè chân chính và bè lũ xu nịnh.',
                    'Kết giao quý nhân: Người mang năng lượng thực tế và trí tuệ.',
                    'Tránh tranh cãi vô bổ: Chiến thắng trong tranh luận thường đồng nghĩa với mất đi một mối quan hệ.'
                ]
            ]
        ],
        'binh_than' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp lung linh, kỳ ảo nhưng đầy phù hoa và khó nắm bắt: Đó là Mặt Trời đang lặn dần về phương Tây, chiếu rọi những tia nắng vàng rực cuối ngày xuống mặt hồ lấp lánh. Bạn sinh ra không đơn giản một chiều. Bạn thông minh, hóm hỉnh, đa tài và linh hoạt khôn lường. Nhưng sâu thẳm bên trong là những biến động nội tâm, sự cô đơn của kẻ đứng trên đỉnh cao danh vọng và mâu thuẫn giữa khát vọng tự do và nhu cầu vật chất',
                'La Bàn Thịnh Vượng sẽ giúp bạn nắm bắt sự biến ảo ấy để kiến tạo cuộc đời vững chãi.'
            ],
            'su_nghiep' => [
                'Bạn không sinh ra để làm việc rập khuôn. Dòng máu của bạn là sự chuyển động, đổi mới và thích nghi.',
                'Đa tài và linh hoạt tuyệt đối: Bạn như phù thủy với khả năng xoay sở tình thế thần sầu. Bạn sở hữu tư duy đa nhiệm xuất sắc, có thể làm nhiều việc cùng lúc mà không quá tải. Bạn hợp với công việc đòi hỏi di chuyển và giao tiếp rộng: kinh doanh, marketing, ngoại giao, du lịch, giải trí.',
                'Tư duy doanh nhân bẩm sinh: Bạn có tố chất ông chủ, biết tính toán lợi ích nhanh như chớp và nhìn thấy cơ hội kiếm tiền ở khắp nơi. Tư duy của bạn khá thực tế: Làm là phải ra tiền.',
                'Khát khao tự do: Bạn làm việc hiệu quả nhất khi được trao quyền tự quyết. Môi trường lý tưởng là tự kinh doanh, freelance hoặc làm việc theo dự án.',
                'Thách thức: Thiếu kiên định: Bạn có quá nhiều ý tưởng, có thể bị phân tâm và bỏ dở giữa chừng. Tính cả thèm chóng chán khiến bạn biết nhiều nhưng không sâu. Sự láu cá: Đôi khi sự khôn khéo biến thành thực dụng, khiến đối tác cảm thấy thiếu tin tưởng.',
                'chien_luoc' => [
                    'Rèn luyện sự tập trung: Ép mình hoàn thành trọn vẹn một dự án trước khi bắt đầu cái mới.',
                    'Định vị chuyên gia: Chọn một mục tiêu lớn và cam kết theo đuổi đến cùng. Sự kiên trì sẽ biến bạn từ một người biết rộng nhưng không sâu thành một chuyên gia thực thụ.',
                    'Minh bạch hóa: Giữ chữ Tín làm đầu. Sự khôn khéo giúp đi nhanh, nhưng sự chân thành mới giúp đi xa.'
                ]
            ],
            'tai_chinh' => [
                'Nếu nói về tài lộc, bạn là một trong những trụ ngày được ưu ái nhất. Bạn đang ngồi trên đống vàng theo đúng nghĩa đen. Chỉ cần thân thể bạn khỏe mạnh thì tiền sẽ tự đến.',
                'Duyên nợ với tiền lớn: Bạn có giác quan thứ 6 nhạy bén về lợi nhuận. Bạn kiếm tiền khá tốt từ đầu tư, kinh doanh thương mại hoặc nghề tay trái. Với bạn, kiếm tiền là trò chơi trí tuệ thú vị.',
                'Lối sống hưởng thụ: Bạn kiếm tiền để tiêu, thích sự sang trọng, hàng hiệu và những trải nghiệm 5 sao. Bạn không ngại chi mạnh tay để duy trì hình ảnh đẳng cấp.',
                'Rủi ro: Quy luật dễ đến dễ đi luôn hiện hữu. Thói quen chi tiêu hoang phí và mong muốn làm giàu nhanh khiến bạn có thể sa vào các kế hoạch đầu tư rủi ro cao. Bạn có thể kiếm bạc tỷ buổi sáng nhưng mất trắng vào buổi chiều.',
                'dinh_huong' => [
                    'Quản lý chặt chẽ: Thuê chuyên gia hoặc lập ngân sách nghiêm ngặt nếu bạn không giỏi giữ tiền.',
                    'Đầu tư giá trị thực: Chuyển hóa tiền mặt thành tài sản bền vững như Bất động sản, Vàng, Cổ phiếu đầu ngành.',
                    'Tạo nguồn thu thụ động: Xây dựng hệ thống kinh doanh tự vận hành để duy trì lối sống xa hoa mà không áp lực cày cuốc.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình yêu, bạn giống nhân vật chính tiểu thuyết ngôn tình: Quyến rũ, thú vị nhưng đầy rắc rối.',
                'Sức hút mãnh liệt: Bạn duyên dáng, hài hước và biết rót mật vào tai. Bạn tạo ra những bất ngờ lãng mạn và dễ dàng chinh phục người khác phái.',
                'Tâm lý thợ săn: Bạn coi tình yêu là hành trình chinh phục. Càng khó khăn bạn càng hứng thú. Nhưng khi đã sở hữu, bạn lại có thể cảm thấy nhàm chán và khao khát sự mới mẻ.',
                'Thách thức: Thiếu ổn định: Bạn hay đứng núi này trông núi nọ. Tính thích tự do và đôi chút trăng hoa, đặc biệt là nam giới, gây sóng gió cho mối quan hệ. Thực dụng: Đôi khi sự tính toán len lỏi vào tình yêu làm tổn thương đối phương.',
                'chien_luoc' => [
                    'Tìm bạn đời: Bạn cần người yêu thông minh, độc lập, bí ẩn và biết làm mới bản thân. Người quá ngoan hiền sẽ không giữ chân được bạn.',
                    'Học trân trọng hiện tại: Hiểu rằng tình yêu đích thực là cam kết khi cảm xúc lắng xuống.',
                    'Chung thủy là sự lựa chọn bản lĩnh: Hãy nhắc nhở bản thân về giá trị của sự gắn kết sâu sắc.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng từ sự giao tranh giữa nhiệt và hàn trong cơ thể.',
                'Hệ hô hấp & tim mạch: Bạn có thể mắc bệnh viêm họng, viêm phế quản, dị ứng. Tim mạch cũng cần chú ý khi làm việc quá sức.',
                'Nguy cơ tai nạn: Tính hiếu động và tốc độ khiến bạn có thể gặp tai nạn xe cộ, té ngã hoặc bị vật sắc nhọn gây thương tích.',
                'Tâm bệnh: Bề ngoài vui vẻ nhưng bên trong lo âu. Suy nghĩ quá nhiều dẫn đến căng thẳng thần kinh, mất ngủ.',
                'cach_thuc_can_bang' => [
                    'Tập hít thở: Yoga, thiền định là liều thuốc tiên cho phổi và tâm trí.',
                    'Vận động nhẹ nhàng: Tránh các môn đối kháng quá mạnh có thể gây chấn thương.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn học nhanh, hiểu rộng, tư duy thực tế và có khả năng sao chép, cải tiến tuyệt vời.',
                'Trí tuệ ứng dụng: Bạn học để kiếm tiền và giải quyết vấn đề, không thích lý thuyết hàn lâm. Bạn tiếp thu cái mới cực nhanh.',
                'Sự đa năng: Bạn là người đa tiềm năng, có thể làm tốt nhiều lĩnh vực cùng lúc.',
                'Thách thức: Sự biết nhiều thường đi kèm không sâu. Bạn có thể mắc kẹt trong sự thông minh vặt mà thiếu chiều sâu triết lý.',
                'dinh_huong' => [
                    'Một nghề cho chín: Dũng cảm gạt bỏ sở thích phù phiếm để tập trung toàn lực vào một lĩnh vực thế mạnh nhất.',
                    'Kết hợp kỷ luật: Sự thông minh cộng với kỷ luật sẽ giúp bạn bất khả chiến bại.',
                    'Đọc sách sâu: Tìm đọc tâm lý học, triết học để nâng tầm tư duy từ chiến thuật sang chiến lược.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là linh hồn của các bữa tiệc, là người kết nối đại tài.',
                'Quảng giao & sức hút: Bạn có mạng lưới bạn bè rộng khắp, biết tạo dựng quan hệ phục vụ công việc. Bạn hào phóng, chơi đẹp nên khá được lòng mọi người.',
                'Quý nhân: Bạn thường gặp quý nhân mang lại cơ hội làm ăn. Bạn cũng khá có duyên với người khác phái.',
                'Rủi ro: Bạn có nhiều bạn nhưng ít tri kỷ. Mọi người đến vì sự vui vẻ hoặc lợi ích. Đôi khi bạn bị đánh giá là thực dụng.',
                'chien_luoc' => [
                    'Sống chân thành hơn: Đừng chỉ nhìn vào lợi ích, hãy quan tâm vô tư.',
                    'Xây dựng niềm tin: Trân trọng những người bạn dám nói thật và ở bên lúc khó khăn.',
                    'Tránh thị phi: Học cách khiêm tốn và kín tiếng hơn về thành công của mình.'
                ]
            ]
        ],
        'binh_thin' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp tráng lệ, ấm áp và đầy hứa hẹn: Một vầng Mặt Trời buổi sớm đang từ từ nhô lên, chiếu rọi xuống vùng đất đai màu mỡ, ẩm ướt sau mưa. Không gay gắt như nắng trưa, ánh sáng của bạn là sự khởi đầu, là hơi ấm nuôi dưỡng vạn vật sinh sôi. Bạn là sự kết hợp kỳ diệu giữa năng lượng nhiệt huyết và sự bao dung. Cốt lõi cuộc đời bạn là sự cho đi. Bạn sinh ra không chỉ để tỏa sáng cho riêng mình, mà sứ mệnh là sưởi ấm và kiến tạo giá trị cho những người xung quanh.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến lòng tốt thành di sản thịnh vượng thay vì gánh nặng cuộc đời.'
            ],
            'su_nghiep' => [
                'Bạn không làm việc chỉ vì miếng cơm manh áo hay danh vọng cá nhân. Động lực của bạn luôn gắn liền với việc tạo ra giá trị cho cộng đồng.',
                'Triết lý lãnh đạo phục vụ: Bạn có tố chất lãnh đạo bẩm sinh nhưng theo phong cách hoàn toàn khác biệt. Bạn không chỉ tay năm ngón, mà đứng sau nâng đỡ nhân viên. Bạn dẫn dắt bằng sự thấu hiểu và hỗ trợ tận tâm. Mọi người đi theo bạn vì họ cảm thấy được trân trọng và an toàn.',
                'Sáng tạo & tầm nhìn kiến tạo: Bạn có trí tưởng tượng phong phú và ghét khuôn khổ cũ kỹ. Bạn hợp nhất với vai trò người kiến tạo: Lên kế hoạch, định hướng chiến lược hoặc xây dựng nền móng.',
                'Lĩnh vực phù hợp: Bạn có duyên nợ lớn với các ngành nghề mang tính nuôi dưỡng và kiến tạo. Giáo dục, đào tạo & tư vấn: Mảnh đất màu mỡ để gieo trồng hạt giống nhân ái như giáo viên, diễn giả, coaching. Nghệ thuật & kiến trúc: Kết hợp thẩm mỹ và tư duy không gian. Quản trị & kinh doanh: Bất động sản, nông nghiệp công nghệ cao hoặc nhân sự.',
                'Thách thức: Lòng tốt đôi khi là gót chân Achilles của bạn. Ôm đồm: Bạn hay lo lắng và không nỡ từ chối, dẫn đến kiệt sức vì gánh việc thay người khác. Thiếu quyết đoán: Bạn hay chần chừ và bỏ lỡ thời cơ.',
                'chien_luoc' => [
                    'Tập trung vào thế mạnh: Định vị mình là người định hướng chiến lược và phát triển con người.',
                    'Xây dựng đội ngũ: Tìm cộng sự có tính quyết đoán để xử lý chi tiết. Đừng cố làm tất cả.',
                    'Học nghệ thuật từ chối: Đặt ranh giới rõ ràng để bảo vệ năng lượng của bản thân.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được mệnh danh là trụ ngày có lộc đất đai và khả năng tích sản bền vững bậc nhất. Tài chính của bạn vững chãi như núi Thái Sơn.',
                'Kho tàng tiềm ẩn: Bạn có duyên cực lớn với bất động sản, tài nguyên thiên nhiên. Tài sản thường đến từ tích lũy bền bỉ và đầu tư dài hạn. Nhiều người còn được hưởng thừa kế từ gia đình.',
                'Kiếm tiền từ dịch vụ: Bạn thương mại hóa tài năng khá tốt. Sự nhạy bén cảm xúc giúp bạn kinh doanh thuận lợi trong các ngành dịch vụ, giải trí vấn đề cho người khác.',
                'Thách thức: Tiền bạc của bạn cần sự nỗ lực để khai mở. Rủi ro lớn nhất là sự hào phóng và nhẹ dạ. Bạn có thể mủi lòng cho vay mượn hoặc đầu tư vào dự án bánh vẽ vì tình nghĩa, để tình cảm lấn át lý trí.',
                'dinh_huong' => [
                    'Đầu tư chắc chắn: Ưu tiên dồn vốn vào Bất động sản hoặc tài sản cố định.',
                    'Tránh xa đỏ đen: Tuyệt đối không tham gia đầu cơ mạo hiểm hay tiền ảo. Hãy đi chậm mà chắc.',
                    'Quản lý ngân sách: Tách biệt tài chính cá nhân và tiền giúp đỡ người khác để tránh thâm hụt.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn giống như lò sưởi ấm áp giữa mùa đông: Bao dung, che chở nhưng đôi khi quá ấm gây ngột ngạt.',
                'Người vun vén & hy sinh: Bạn là mẫu người của gia đình. Bạn yêu bằng trách nhiệm, luôn đặt nhu cầu đối phương lên trên bản thân. Bạn sẵn sàng hy sinh sở thích cá nhân để vun vén tổ ấm.',
                'Sự lãng mạn nhẹ nhàng: Tình yêu của bạn không ồn ào mà thể hiện qua sự quan tâm nhỏ nhặt: bữa cơm ngon, sự lắng nghe kiên nhẫn. Bạn hướng tới hôn nhân bền vững.',
                'Thách thức: Sự ngột ngạt: Quan tâm thái quá có thể biến thành kiểm soát vô hình, khiến đối phương mất tự do. Sự chịu đựng ngầm: Bạn hay nuốt nỗi buồn vào trong để giữ hòa khí, lâu ngày dẫn đến bùng nổ cảm xúc. Có thể bị lấn lướt: Sự hiền lành khiến bạn có thể bị lép vế, phải chạy theo người khác.',
                'chien_luoc' => [
                    'Yêu bản thân trước: Bạn chỉ che chở được cho người khác khi chính bạn vững chãi, hãy học cách yêu thương bản thân trước khi yêu thương người khác.',
                    'Thiết lập ranh giới: Trao đổi thẳng thắn để đối phương hiểu và tôn trọng giới hạn của bạn.',
                    'Đối với nam giới, thường lấy được vợ đảm đang là hậu phương vững chắc.',
                    'Đối với phụ nữ, là vợ hiền, dâu thảo, hết lòng vì chồng con nhưng cần giữ độc lập tài chính và cảm xúc, tránh luỵ tình.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu sự chi phối mạnh mẽ bởi mối liên hệ thân - tâm.',
                'Hệ tiêu hóa: Bạn thường có lộc ăn uống, sành ăn và có gu ẩm thực nên bạn cần cẩn thận các bệnh về dạ dày, trào ngược hoặc béo phì do hấp thụ quá tốt.',
                'Sức khỏe tim mạch: Áp lực công việc hoặc lo âu kéo dài có thể ảnh hưởng lên tim, gây các vấn đề về huyết áp hoặc tim mạch.',
                'Tinh thần: Bạn là người hay lo nghĩ. Bạn dễ bị stress do hay lo lắng cho người khác. Sự dồn nén cảm xúc lâu ngày có thể gây ra các chứng bệnh tâm lý nhẹ và cảm giác nặng nề trong lòng ngực.',
                'cach_thuc_can_bang' => [
                    'Chế độ ăn: Tăng cường rau xanh để hỗ trợ tiêu hóa. Hạn chế đồ ngọt.',
                    'Kết nối thiên nhiên: Đi bộ chân trần, làm vườn, dã ngoại là cách phục hồi tốt nhất cho bạn.',
                    'Giải phóng cảm xúc: Đừng giữ trong lòng, hãy chia sẻ hoặc viết nhật ký.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn không lanh lợi bề mặt mà sở hữu trí tuệ sâu sắc, thực tế và giàu trải nghiệm.',
                'Trí tuệ cảm xúc EQ vượt trội: Bạn hiểu đời qua sự quan sát và lòng trắc ẩn. Khả năng thấu cảm là siêu năng lực giúp bạn gây ảnh hưởng sâu rộng.',
                'Tư duy đa chiều: Bạn không thích lý thuyết suông. Mọi kiến thức đều được bạn trăn trở tìm cách áp dụng vào thực tế để giúp ích cuộc sống.',
                'Thách thức: Sự phân tâm: Quá nhiều mối quan tâm dẫn đến thiếu tập trung, biết nhiều nhưng không sâu. Sức ì: Bạn có thể đôi khi khá thụ động, ngại bước ra khỏi vùng an toàn.',
                'dinh_huong' => [
                    'Phát triển kỹ năng mềm: Kỹ năng lãnh đạo và quản lý con người. Đọc sách về tâm lý học, nhân sự hoặc quản trị sẽ rất hữu ích cho bạn.',
                    'Rèn luyện sự tập trung: Học cách từ chối và kỷ luật bản thân là chìa khóa để bạn nâng tầm bản thân.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là trạm kết nối của cộng đồng, được yêu mến vì sự thân thiện và uy tín.',
                'Sự quảng giao: Bạn hòa nhập tự nhiên, không phân biệt sang hèn. Sự chân thành, nhất quán giúp bạn xây dựng lòng tin tuyệt đối. Mọi người tìm đến bạn như một chỗ dựa tinh thần.',
                'Mạng lưới quý nhân: Bạn gieo nhân lành nên gặp quả ngọt. Quý nhân thường mang đến cơ hội hợp tác và chiến lược giá trị.',
                'Rủi ro: Cảnh giác với kẻ đục nước béo cò. Lòng tốt của bạn có thể bị kẻ xấu lợi dụng. Đôi khi bạn sẽ thấy cô đơn vì cho đi nhiều nhưng nhận lại ít.',
                'chien_luoc' => [
                    'Duy trì sự tử tế nhưng có chọn lọc. Chỉ đầu tư thời gian cho những mối quan hệ chất lượng.',
                    'Tìm kiếm cộng đồng cùng tần số như thiện nguyện, hay có cùng chuyên môn, để tìm thấy những người bạn đồng hành đích thực.'
                ]
            ]
        ],
        'binh_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang đầy chất thơ nhưng cũng chứa đựng những nghịch lý nội tâm sâu sắc: Một vầng mặt trời rực rỡ đang soi mình xuống mặt hồ nước tĩnh lặng. Bạn mang trong mình khát vọng tỏa sáng cháy bỏng, nhưng lại bị kiềm chế, bao bọc bởi sự thâm trầm. Chính sự tương tác này tạo nên một nhân cách phức tạp: Vẻ ngoài nhu mì, lịch thiệp, hoạt bát nhưng bên trong lại ẩn chứa một cái tôi kiêu hãnh và nhiều lo âu.',
                'La Bàn Thịnh Vượng sẽ giúp bạn hóa giải mâu thuẫn để làm chủ vận mệnh huy hoàng.'
            ],
            'su_nghiep' => [
                'Bên dưới vẻ ngoài hòa nhã là một bộ óc chiến lược đang vận hành liên tục. Bạn dùng mưu lược thay vì sức mạnh cơ bắp để chinh phục mục tiêu.',
                'Chiến lược gia ẩn mình: Sự kết hợp giữa tầm nhìn xa và sự khôn ngoan giúp bạn nhìn thấu bản chất vấn đề. Bạn có tố chất lãnh đạo nhưng không thích ồn ào nơi đầu sóng ngọn gió. Bạn hợp với vai trò người cầm cương phía sau cánh gà: điều phối, sắp xếp và hoạch định. Bạn là người nắm giữ bàn cờ, trong khi người khác là quân cờ.',
                'Trực giác nhân sự sắc bén: Bạn có khả năng đọc vị người khác tuyệt vời. Chỉ qua ánh mắt, bạn có thể nhìn thấu động cơ của đối tác. Điều này biến bạn thành một nhà quản lý nhân sự, nhà đàm phán hoặc chuyên gia ngoại giao đại tài.',
                'Lĩnh vực phù hợp: Bạn tỏa sáng ở nơi đòi hỏi tư duy logic kết hợp sự nhạy cảm tinh tế. Doanh nghiệp: Cố vấn cấp cao, trợ lý chiến lược. Sáng tạo: Truyền thông, báo chí, biên kịch, nhờ ngôn ngữ sắc sảo. Tài chính: Ngân hàng, kiểm toán, nhờ sự cẩn trọng với con số.',
                'Thách thức: Rào cản lớn nhất là cái tôi nhạy cảm. Bạn khao khát sự công nhận mãnh liệt. Khi không được đánh giá đúng, bạn có thể sinh bất mãn hoặc dùng lời lẽ mỉa mai. Sự cầu toàn đôi khi biến bạn thành người độc đoán.',
                'chien_luoc' => [
                    'Hãy tìm kiếm công việc cho phép bạn có không gian độc lập để tư duy.',
                    'Học cách ủy quyền và tin tưởng cấp dưới, đừng ôm đồm.',
                    'Duy trì nhiệt huyết bằng các dự án mới, tránh để sự nhàm chán giết chết sự nghiệp.'
                ]
            ],
            'tai_chinh' => [
                'Bạn không mơ mộng hão huyền. Bạn tiếp cận tài chính với tư duy thực tế, thận trọng nhưng cũng đầy tham vọng về mặt hình ảnh.',
                'Tư duy tích lũy: Bạn hiểu rằng giàu có bền vững đến từ tích lũy. Tiền bạc với bạn là thước đo an toàn và danh dự. Bạn thường có xu hướng lo xa và xây dựng quỹ dự phòng vững chắc. Hậu vận của bạn thường khá sung túc.',
                'Kiếm tiền từ cái đẹp: Bạn có duyên với những lĩnh vực thẩm mỹ, nghệ thuật. Sự nhạy cảm giúp bạn nhận ra giá trị của những thứ người khác bỏ qua.',
                'Rủi ro của sự phù phiếm: Điểm yếu cốt lõi của bạn là sĩ diện. Bạn có thể bị cám dỗ bởi những món đồ xa xỉ để khẳng định đẳng cấp. Đôi khi sự đa nghi quá mức cũng khiến bạn do dự và tuột mất cơ hội đầu tư tốt.',
                'dinh_huong' => [
                    'Hãy đầu tư vào giá trị thực và dài hạn như bất động sản, vàng.',
                    'Tránh xa đầu cơ lướt sóng mạo hiểm vì hệ thần kinh của bạn không chịu được áp lực cao.',
                    'Lập quỹ riêng cho việc hưởng thụ để thỏa mãn nhu cầu thẩm mỹ mà không ảnh hưởng cấu trúc tài chính.'
                ]
            ],
            'tinh_duyen' => [
                'Đây là khía cạnh phức tạp nhất. Bạn sở hữu sức hút khó cưỡng nhưng nội tâm luôn dậy sóng.',
                'Sức hút đào hoa: Bạn không cần cố gắng phô diễn. Vẻ rạng rỡ pha chút trầm tư bí ẩn của bạn tự nhiên khiến người khác muốn chinh phục. Bạn trẻ trung, lãng mạn và biết cách làm người khác say mê.',
                'Mâu thuẫn nội tâm: Bạn khao khát tri kỷ thấu hiểu tâm hồn, nhưng tâm trạng lại thất thường sáng nắng chiều mưa. Sự thay đổi từ nồng nhiệt sang lạnh lùng xa cách của bạn thường khiến đối phương hoang mang.',
                'Thách thức: Bạn có xu hướng đứng núi này trông núi nọ hoặc có thể dao động nếu mối quan hệ hiện tại trở nên nhàm chán. Đôi khi bạn ưu tiên danh vọng hơn gia đình, gây ra những rạn nứt ngầm.',
                'chien_luoc' => [
                    'Ưu tiên sự kết nối tâm hồn hơn là hào nhoáng bên ngoài khi chọn bạn đời.',
                    'Học cách chia sẻ cảm xúc bình tĩnh thay vì giận dỗi vô cớ.',
                    'Sự minh bạch là chìa khóa để xây dựng lòng tin.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn là sự cân bằng mong manh giữa tình trạng nóng nhiệt bên trong và sự lạnh lẽo, kém lưu thông của khí huyết.',
                'Tâm bệnh: Bạn hay suy nghĩ, dằn vặt và lo âu thái quá. Điều này có thể dẫn đến căng thẳng thần kinh, mất ngủ hoặc đau đầu mãn tính.',
                'Vấn đề thể chất: Cần lưu ý các vấn đề về thận, tiết niệu, tim, mắt, máu huyết. Cẩn trọng với nhịp tim hoặc thị lực nếu làm việc quá sức.',
                'cach_thuc_can_bang' => [
                    'Bạn cần những khoảng lặng để làm nguội cái đầu nóng.',
                    'Thiền định, yoga hoặc đi dạo gần hồ nước là liều thuốc tuyệt vời.',
                    'Hãy ngủ sớm để dưỡng âm và đón nắng sớm để dưỡng dương.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thuộc nhóm người có trí tuệ thanh tú, sáng suốt hơn người.',
                'Trí tuệ sắc sảo: Bạn học nhanh, nhớ lâu và phân tích vấn đề tận gốc rễ. Bạn không học vẹt mà luôn tìm cách ứng dụng vào thực tế.',
                'Trực giác mạnh mẽ: Đây là tài sản quý giá nhất giúp bạn đi tắt đón đầu trong cuộc sống.',
                'Thách thức: Cái tôi lớn và sự tự mãn. Đôi khi bạn đóng lòng trước sự góp ý vì nghĩ mình đã biết đủ. Bạn cũng có thể bị phân tâm bởi quá nhiều sở thích.',
                'dinh_huong' => [
                    'Chọn một lĩnh vực đam mê nhất và đào sâu để trở thành chuyên gia.',
                    'Tu dưỡng nội tâm qua sách vở, triết học để vững chãi trước biến cố.',
                    'Học cách lắng nghe và khiêm tốn – đó mới là đỉnh cao của trí tuệ.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là một người chủ nhà hiếu khách, một người bạn vui vẻ nhưng đầy bí ẩn.',
                'Mặt nạ xã giao: Bạn biết cách khuấy động không khí, hài hước trong các buổi tiệc. Tuy nhiên, đó thường chỉ là lớp vỏ bọc. Bên trong, bạn khá kén chọn và chỉ thực sự mở lòng với rất ít tri kỷ.',
                'Rủi ro: Cẩn trọng trong quan hệ với cấp trên. Sự kiêu ngạo ngầm hoặc lời nói quá thẳng thắn của bạn có thể khiến họ cảm thấy bị đe dọa.',
                'chien_luoc' => [
                    'Hãy giữ thái độ khiêm tốn và chân thành.',
                    'Đừng dùng sự sắc sảo để mỉa mai người khác.',
                    'Tìm kiếm những quý nhân mang năng lượng bao dung hoặc vững chãi để giúp bạn cân bằng.'
                ]
            ]
        ],
        'canh_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp lạnh lùng nhưng đầy khí chất: Một thanh kiếm sắc bén nằm yên dưới lớp tuyết trắng, hay một khối kim loại quý chìm sâu trong dòng nước lạnh lẽo.',
                '“La Bàn Thịnh Vượng” sẽ giúp bạn mài giũa thanh kiếm ấy để nó sắc bén đúng lúc, đúng chỗ.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu nguồn năng lượng mạnh mẽ và tầm nhìn bao quát. Bạn không sinh ra để đi theo tư duy lối mòn.',
                'Tư duy chiến lược: Bạn là nhà lãnh đạo bẩm sinh với tư duy sắc bén. Bạn độc lập, sáng tạo và luôn nhìn thấy bức tranh lớn mà người khác bỏ qua. Bạn có khả năng đi thẳng vào cốt lõi vấn đề và tìm ra giải pháp thực tế nhất.',
                'Phong cách làm việc: Bạn coi trọng tri thức và tin rằng kiến thức là sức mạnh. Sự kết hợp giữa tư duy logic và tâm hồn nghệ sĩ giúp bạn trở nên độc đáo. Những trở ngại mà người khác sợ hãi lại chính là nguồn cảm hứng để bạn chinh phục.',
                'Lĩnh vực phù hợp: Sáng tạo và truyền thông: Viết lách, báo chí, marketing, giải trí hoặc nghệ thuật là nơi bạn tỏa sáng nhờ khả năng ngôn ngữ. Chính trị và thương mại: Sự sắc sảo trong đàm phán giúp bạn thành công trong kinh doanh hoặc chính trị. Tư vấn: Bạn có khả năng nhìn thấu vấn đề và đưa ra chiến lược đắt giá.',
                'Thách thức: Thách thức của bạn là nhu cầu tìm kiếm cảm hứng mới liên tục. Bạn ghét sự nhàn rỗi nhưng lại thiếu kiên nhẫn với những việc đòi hỏi sự tỉ mỉ lâu dài. Tính cách bướng bỉnh, thẳng thắn quá mức đôi khi gây mất lòng đồng nghiệp. Bạn khó chấp nhận sự chỉ đạo từ người khác.',
                'chien_luoc' => [
                    'Hãy tìm kiếm môi trường có áp lực, tính cạnh tranh hoặc liên quan đến truyền thông, để rèn sự sắc bén của bạn trở nên hữu dụng bạn có thể tìm cách tự tin tỏa sáng.',
                    'Rèn luyện kỷ luật: Cam kết theo đuổi mục tiêu đến cùng là bài học lớn nhất.',
                    'Học cách hợp tác: Dùng ngoại giao để thu phục nhân tâm thay vì dùng dùng tư duy phản biện để phán xét.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có tham vọng về vật chất và sở hữu giác quan thứ 6 về tiền bạc.',
                'Tư duy làm giàu: Bạn nhìn thấy cơ hội kiếm tiền ở những nơi người khác không thấy. Bạn hào phóng tự nhiên nhưng cũng có tham vọng sở hữu tài sản lớn. Mạng lưới bạn bè thường là nguồn hỗ trợ đắc lực giúp bạn thành công.',
                'Tiềm năng thịnh vượng: Bạn có tiềm năng trở nên cực kỳ giàu có. Bạn khá hợp với kinh doanh và thương mại.',
                'Rủi ro: Mâu thuẫn giữa sự hào phóng và khát vọng tích lũy tài sản đôi khi làm bạn bối rối. Sự thiếu kiên nhẫn có thể dẫn đến những quyết định đầu tư nóng vội. Tâm tính thất thường cũng khiến tài chính trồi sụt.',
                'dinh_huong' => [
                    'Đầu tư vào tài sản: Tập trung vào các lĩnh vực tăng trưởng như thời trang, thiết kế, nông nghiệp hoặc đầu tư dài hạn.',
                    'Tăng năng lượng ổn định: Bất động sản là kênh giúp bạn giữ tiền và giảm bớt sự bồn chồn.',
                    'Điều tiết mong muốn kiểm soát: Trong hợp tác, hãy linh hoạt hơn trong các tiêu chuẩn để duy trì mối quan hệ lâu dài.'
                ]
            ],
            'tinh_duyen' => [
                'Bề ngoài bạn có vẻ điềm tĩnh, thận trọng, nhưng bên trong lại khao khát tình yêu mãnh liệt.',
                'Sức hút bí ẩn: Bạn là người tình quyến rũ, gợi cảm nhưng đầy bí ẩn. Bạn cần một người bạn đời có thể truyền cảm hứng và kích thích trí tuệ cho bạn liên tục. Bạn có thể bị thu hút bởi những người có uy quyền, khôn ngoan và thành đạt.',
                'Thách thức: Bạn có xu hướng giấu kín cảm xúc, khiến đối phương cảm thấy xa cách. Sự bất an ngầm khiến bạn thiếu quyết đoán. Bạn yêu tự do và bận rộn, sợ bị ràng buộc bởi gia đình. Đối với phụ nữ, bạn phù hợp với người đàn ông có trái tim rộng mở và sự thấu hiểu sâu sắc để chung sống hòa hợp.',
                'chien_luoc' => [
                    'Học cách buông bỏ quá khứ: Đừng để tổn thương cũ ám ảnh hiện tại.',
                    'Giữ ấm: Thể hiện sự nồng nhiệt, quan tâm rõ ràng hơn với người bạn đời để cân bằng lại.',
                    'Lùi lại một bước: Đừng quá nghiêm trọng hóa vấn đề, hãy nhìn nhận mối quan hệ nhẹ nhàng hơn.'
                ]
            ],
            'suc_khoe' => [
                'Cơ thể bạn bị ảnh hưởng bởi tính hàn lạnh.',
                'Thể chất: Thận, khí huyết và hệ hô hấp khá nhạy cảm. Bạn dễ bị lạnh tay chân hoặc giảm sức đề kháng khi thời tiết thay đổi.',
                'Tinh thần: Sự nhạy cảm cao dễ gây căng thẳng thần kinh nếu bạn suy nghĩ hoặc dồn nén cảm xúc quá nhiều.',
                'Cách thức cân bằng: Sưởi ấm: Hãy tích cực vận động dưới ánh nắng và giữ ấm cơ thể. Tránh ở lâu nơi ẩm thấp, u tối. Tìm sự ổn định: Thiền định giúp tâm trí bình yên, giảm bớt sự bồn chồn lo âu.',
                'Liệu pháp: Nghỉ ngơi hợp lý, đừng để những cơn hứng khởi nhất thời làm bạn kiệt sức.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người theo đuổi tri thức vĩ đại và có khả năng tự học tuyệt vời.',
                'Trí tuệ tự thân: Nếu không học trường lớp chính quy, bạn vẫn sẽ tự nghiên cứu thành tài những lĩnh vực mình yêu thích. Bạn luôn đặt câu hỏi về những bí ẩn của cuộc sống như triết học, siêu hình học.',
                'Thách thức: Sự thiếu tập trung và kỷ luật là rào cản lớn nhất. Bạn thông minh nhưng nếu không có kỷ luật, bạn sẽ lãng phí tài năng. Bạn cũng hay kỳ vọng quá cao vào bản thân dẫn đến thất vọng.',
                'dinh_huong' => [
                    'Kỷ luật là sức mạnh: Rèn luyện thói quen làm việc có kế hoạch.',
                    'Chuyển hóa năng lượng: Biến sự bồn chồn dư thừa thành hành động sáng tạo cụ thể như viết lách, nghệ thuật.',
                    'Tìm kiếm triết lý sống: Điều này giúp bạn có điểm tựa tinh thần vững chắc.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn hướng ngoại, hòa đồng và khá thu hút trong đám đông.',
                'Phong cách giao tiếp: Bạn có khả năng thấu cảm và trực giác tốt về người khác. Bạn là người bạn tốt, sẵn sàng hỗ trợ và đưa ra chiến lược hữu ích.',
                'Quý nhân: Mạng lưới xã hội là chìa khóa thành công. Bạn thường thu hút những người thông minh, thú vị, bạn sẽ có mạng lưới bạn bè quyền lực giúp đỡ đạt mục tiêu.',
                'Rủi ro: Bạn khao khát sự công nhận. Nếu cảm thấy không được đánh giá cao, bạn có thể trở nên bất mãn. Đôi khi sự thẳng thắn quá mức, lời nói như dao sắc làm thẳng thắn, sắc bén có thể vô tình gây áp lực cho người khác.',
                'chien_luoc' => [
                    'Ngoại giao khéo léo: Cân bằng giữa sự thẳng thắn và sự tế nhị.',
                    'Lắng nghe: Sống đúng với trực giác nhưng đừng quên lắng nghe ý kiến của người xung quanh.',
                    'Chân thành: Duy trì các mối quan hệ dựa trên sự cao thượng và chính trực.'
                ]
            ]
        ],
        'canh_thin' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp hùng tráng và đầy khí chất: Một con rồng khoác áo giáp vàng đang cuộn mình trên vùng đất ẩm, hay một khối kim loại quý nằm sâu trong lòng núi chờ ngày khai phá.',
                '“La Bàn Thịnh Vượng” sẽ giúp bạn chuyển hóa sức mạnh uy quyền này thành di sản để đời.'
            ],
            'su_nghiep' => [
                'Bạn không sinh ra để phục tùng. Bạn mang tư duy của người đứng đầu, người kiến tạo và cải cách.',
                'Tố chất lãnh đạo bẩm sinh: Bạn sở hữu cái uy tự nhiên khiến người khác phải nể trọng. Bạn có tầm nhìn xa và khả năng chịu áp lực phi thường. Càng trong hoàn cảnh khắc nghiệt, bản lĩnh của bạn càng tỏa sáng. Bạn phù hợp với vai trò người cầm trịch, người ra quyết định trong các tổ chức lớn, quân đội, chính trị hoặc tự mình làm chủ doanh nghiệp.',
                'Tư duy cải cách: Bạn nhìn thấy những lỗ hổng trong hệ thống cũ và có đủ dũng khí để phá bỏ nó, xây dựng trật tự mới tốt đẹp hơn. Bạn làm việc với tinh thần của một chiến binh: kỷ luật, chăm chỉ và không bao giờ lùi bước.',
                'Công lý và thực thi: Với ý thức mạnh mẽ về lẽ phải, bạn có thể tỏa sáng trong các lĩnh vực pháp luật như luật sư, thẩm phán, hoặc các công việc đòi hỏi sự quyết đoán như quản lý dự án, điều hành sản xuất.',
                'Thách thức: Thách thức nằm ở việc dung hòa các luồng ý kiến khác biệt. Bạn quá tin vào phán đoán của mình mà bỏ qua ý kiến người khác, dẫn đến sự cô lập. Tính cách quá thẳng thắn, bộc trực có thể khiến bạn vướng vào thị phi hoặc tranh chấp quyền lực nơi công sở.',
                'chien_luoc' => [
                    'Hãy học chữ “nhu”. Sự uy nghiêm kết hợp với lòng nhân ái sẽ giúp bạn thu phục nhân tâm bền vững hơn là mệnh lệnh.',
                    'Tìm kiếm những sứ mệnh cao cả để theo đuổi, tránh sa đà vào các tranh đấu danh lợi vụn vặt.',
                    'Hãy lắng nghe nhiều hơn để có cái nhìn đa chiều.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là thế mạnh của bạn. Bạn có nền tảng vững chắc và khả năng tích lũy tài sản lớn.',
                'Năng lực tích lũy: Điều này báo hiệu bạn có duyên lớn với tài sản, có khả năng làm giàu và giữ tiền cực tốt. Bạn thường nhận được sự hỗ trợ từ gia đình hoặc thừa hưởng di sản, nhưng phần lớn đế chế của bạn là do tự tay gây dựng.',
                'Tư duy thực tế: Bạn không tin vào may mắn ngẫu nhiên. Bạn làm giàu bằng chiến lược, kế hoạch và sự đầu tư dài hạn. Bạn có xu hướng tích trữ các tài sản có giá trị bền vững như bất động sản, vàng bạc, nhà xưởng.',
                'Rủi ro: Mặc dù giỏi kiếm tiền, bạn có thể vướng vào các tranh chấp pháp lý về tài chính do tính cách cứng rắn, không chịu nhường nhịn. Tham vọng quá lớn hoặc sự quyết liệt chưa tính toán kỹ trong các thương vụ lớn có thể là cái bẫy. Đặc biệt cẩn trọng vào những thời điểm xung khắc có thể gây hao tài.',
                'dinh_huong' => [
                    'Hãy đảm bảo mọi giao dịch đều minh bạch về pháp lý, giấy trắng mực đen.',
                    'Đầu tư vào bất động sản là kênh an toàn và phù hợp nhất với năng lượng của bạn.',
                    'Hãy chia sẻ sự giàu có của mình thông qua từ thiện để tích phúc đức và giảm bớt sự đố kỵ của người đời.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người chung thủy, trách nhiệm nhưng cũng đầy thách thức vì sự mạnh mẽ của mình.',
                'Sự che chở: Bạn yêu bằng hành động. Bạn muốn trở thành cây tùng cây bách để người yêu dựa vào. Bạn coi trọng sự ổn định gia đình và sẵn sàng làm mọi thứ để bảo vệ người thân.',
                'Tiêu chuẩn chọn bạn đời: Bạn cần một hậu phương vững chắc, một người biết lắng nghe, thấu hiểu và chấp nhận tính cách cương nghị của bạn. Bạn hợp với những người có tính cách nhu mì, điềm đạm để dung hòa sự cứng rắn.',
                'Thách thức: Xu hướng muốn bảo bọc và dẫn dắt tuyệt đối là rào cản lớn nhất. Bạn thường muốn mọi việc trong nhà phải theo ý mình, khiến đối phương cảm thấy ngột ngạt. Những cuộc tranh luận nảy lửa để chứng minh mình đúng có thể làm rạn nứt tình cảm. Đặc biệt đối với phụ nữ, sự quá mạnh mẽ và giỏi giang đôi khi vô tình tạo áp lực lên người chồng.',
                'chien_luoc' => [
                    'Hãy học cách tôn trọng và bình đẳng trong mối quan hệ.',
                    'Đừng mang sự uy quyền ở nơi làm việc về nhà.',
                    'Một chút lãng mạn và mềm mỏng sẽ giúp hâm nóng tình cảm và làm mềm hóa bầu không khí.',
                    'Nhẫn nhịn là bài học đắt giá để giữ gìn hạnh phúc.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn có nền tảng tốt, dẻo dai như mình đồng da sắt, nhưng không nên chủ quan.',
                'Vấn đề hô hấp và da: Bạn có thể gặp các vấn đề về phổi, hô hấp hoặc các bệnh ngoài da mãn tính.',
                'Hệ tiêu hóa và sự trì trệ: Bạn có thể gặp các vấn đề về dạ dày, tiêu hóa hoặc tăng cân.',
                'Căng thẳng thần kinh: Tham vọng lớn và áp lực công việc liên tục có thể dẫn đến đau đầu, mất ngủ hoặc tính khí thất thường.',
                'Cách thức cân bằng: Bạn cần vận động thường xuyên, đặc biệt là các môn thể thao cường độ cao để khí huyết lưu thông.',
                'Liệu pháp: Chế độ ăn thanh lọc cơ thể, uống nhiều nước và dành thời gian thư giãn thực sự để xả bớt áp lực là điều cần thiết.'
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ sắc sảo, tư duy logic và khả năng tự học tuyệt vời.',
                'Tư duy thực chiến: Bạn thích những kiến thức thực tế, có tính ứng dụng cao. Bạn có kỷ luật tự giác, tự nghiên cứu và rèn luyện mà không cần ai thúc ép. Bạn có tiềm năng trở thành chuyên gia hoặc bậc thầy trong lĩnh vực mình chọn.',
                'Tâm linh tiềm ẩn: Dù thực tế, nhưng bạn cũng có duyên với tâm linh, tôn giáo. Tìm hiểu về đạo lý sẽ giúp tâm tính bạn nhu hòa hơn, bớt đi sự hiếu thắng.',
                'Thách thức: Sự tin tưởng tuyệt đối vào kinh nghiệm cá nhân. Bạn khá khó chấp nhận quan điểm trái chiều nếu nó đi ngược lại kinh nghiệm bản thân. Điều này có thể khiến tư duy của bạn bị đóng khung.',
                'dinh_huong' => [
                    'Hãy mở rộng tư duy, tập lắng nghe và tiếp thu cái mới.',
                    'Đầu tư vào các kỹ năng mềm như giao tiếp, quản lý cảm xúc (EQ) để hoàn thiện khả năng lãnh đạo.',
                    'Học cách khiêm tốn, lúa chín cúi đầu để tiến xa hơn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn có uy tín tự nhiên, lời nói có trọng lượng và được nể trọng.',
                'Mạng lưới quan hệ: Bạn không khéo léo hay thảo mai, bạn thu hút người khác bằng sự chính trực và năng lực thực sự. Bạn thường kết giao với những người có địa vị, quyền lực hoặc cùng chí hướng làm ăn lớn.',
                'Rủi ro: Sự nổi bật và thẳng thắn quá mức có thể khiến bạn dễ vướng phải những ý kiến trái chiều hoặc sự cạnh tranh không lành mạnh sau lưng. Bạn cũng có thể làm mất lòng người khác vì những lời nói thật lòng nhưng thiếu tế nhị.',
                'chien_luoc' => [
                    'Tránh xa những kẻ xu nịnh, hãy trân trọng những người dám nói thẳng sự thật với bạn.',
                    'Xây dựng quan hệ dựa trên sự chân thành nhưng cần khéo léo hơn trong ứng xử để tránh thị phi không đáng có.'
                ]
            ]
        ],
        'canh_than' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn toát lên vẻ uy nghiêm và sức mạnh vô song: Một khối kim loại nguyên chất cứng cáp nhất, hay một thanh bảo kiếm sắc lẹm được tôi luyện qua lửa đỏ.',
                '“La Bàn Thịnh Vượng” sẽ giúp bạn sử dụng thanh kiếm báu của mình để rẽ lối thành công chứ không phải để gây thương tích cho chính mình và người khác.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra với khí chất của người chinh phục. Bạn không chấp nhận sự tầm thường hay vị trí thứ hai.',
                'Người thực thi xuất sắc: Bạn có khả năng biến ý tưởng thành hành động ngay lập tức. Bạn không nói suông. Khi đã đặt mục tiêu, bạn lao vào công việc với tốc độ và sự quyết liệt khiến người khác phải kinh ngạc. Tư duy logic, thực tế giúp bạn giải quyết vấn đề nhanh gọn và triệt để.',
                'Lãnh đạo tiên phong: Bạn phù hợp với vai trò người chỉ huy, người đứng đầu. Bạn ghét sự vòng vo, lề mề. Phong cách làm việc của bạn là trực diện, hiệu quả và hướng tới kết quả cuối cùng.',
                'Lĩnh vực phù hợp: Cạnh tranh và chiến lược: Bạn tỏa sáng trong môi trường khắc nghiệt như kinh doanh, thể thao, thị trường chứng khoán. Quân sự và pháp luật: Sự cương trực, dũng cảm và kỷ luật giúp bạn thành công rực rỡ trong quân đội, công an, luật sư tranh tụng. Kỹ thuật và công nghiệp: Sự chính xác và khả năng làm chủ máy móc giúp bạn làm tốt trong ngành kỹ thuật, cơ khí, xây dựng.',
                'Thách thức: Thách thức nằm ở việc dung hòa tính nguyên tắc với sự linh hoạt. Bạn quá tự tin vào bản thân nên thường bỏ qua ý kiến người khác. Bạn có thể trở nên bảo thủ khi tình thế thay đổi. Sự hiếu thắng đôi khi khiến bạn tạo ra những kẻ thù không đáng có.',
                'chien_luoc' => [
                    'Học cách ôn nhu, mềm mỏng: Thanh kiếm quá cứng thì có thể gãy, bạn cần sự mềm mại, trí tuệ để trở nên linh hoạt hơn.',
                    'Tìm người làm phó tướng: Hãy tìm những cộng sự giỏi chi tiết, khéo léo về ngoại giao để bù đắp cho sự thẳng thắn của bạn.',
                    'Rèn luyện đạo đức: Sức mạnh không có đạo đức sẽ trở thành sự tàn phá, hãy dùng năng lực để bảo vệ lẽ phải.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn thường khá mạnh nhưng cũng đầy biến động.',
                'Tư duy làm giàu: Bạn có khả năng kiếm tiền cực giỏi. Bạn nhạy bén với cơ hội và dám chấp nhận rủi ro lớn để đạt lợi nhuận khổng lồ. Bạn coi tiền bạc là công cụ để khẳng định vị thế. Bạn hào phóng, rộng rãi, kiểu xởi lởi trời cho, không tiếc tiền cho anh em, bạn bè, chính điều này mang lại nhiều cơ hội làm ăn.',
                'Tiềm năng thịnh vượng: Bạn có thể làm giàu nhanh chóng nhờ sự quyết đoán và năng lực vượt trội. Bạn khá phù hợp với việc tự kinh doanh hoặc đầu tư mạo hiểm.',
                'Rủi ro: Cần chú ý kiểm soát dòng tiền, tránh để sự hào phóng quá mức ảnh hưởng đến tích lũy hoặc bị bạn bè lợi dụng. Bạn coi trọng uy tín và thể diện, đôi khi điều này dẫn đến chi tiêu vượt kế hoạch. Xu hướng đầu tư tất tay cũng tiềm ẩn rủi ro mất trắng nếu không tính toán kỹ.',
                'dinh_huong' => [
                    'Quản lý chặt chẽ: Hãy nhờ người thân tin cậy hoặc chuyên gia quản lý tài chính giúp bạn. Hạn chế giữ nhiều tiền mặt.',
                    'Đầu tư tài sản cố định: Chuyển tiền mặt thành nhà đất, vàng bạc để khóa tài sản lại.',
                    'Học cách nói không: Cẩn trọng với những lời đề nghị vay mượn, dù là chỗ thân tình.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình yêu, bạn mãnh liệt, chủ động nhưng tính cách chiếm hữu khá cao.',
                'Sự bảo vệ: Bạn yêu ghét rõ ràng. Khi yêu, bạn sẵn sàng làm mọi thứ để bảo vệ người mình thương. Bạn thích những người có cá tính mạnh, độc lập và thông minh để cùng bạn chinh chiến trong cuộc đời.',
                'Thách thức: Đây là khía cạnh khó khăn nhất của bạn. Bạn có xu hướng muốn che chở và định hướng tuyệt đối cho đối phương và muốn đối phương phải nghe theo ý mình. Bạn ít khi bộc lộ sự dịu dàng, lãng mạn, khiến mối quanện trở nên khô khan. Sự thẳng thắn quá mức đôi khi làm tổn thương người đầu ấp tay gối.',
                'Lưu ý đặc biệt: Đối với nam giới, cần đặc biệt quan tâm đến cảm xúc và sức khỏe người bạn đời để duy trì sự hòa hợp lâu dài, nếu không biết cách tu dưỡng tính nết, học sự nhu hòa.',
                'chien_luoc' => [
                    'Nhường nhịn là thắng lợi, thắng trong tranh cãi với đối phương thì bạn thua trong tình cảm. Hãy lùi một bước.',
                    'Tôn trọng bình đẳng: Coi người bạn đời là đối tác, không phải cấp dưới.',
                    'Chọn người phù hợp: Bạn hợp với những người tính cách nhu mì, bao dung để làm dịu đi sự nóng nảy của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn có nền tảng tốt, dẻo dai như sắt đá, ít ốm vặt.',
                'Vấn đề tai nạn: Cẩn thận, đề phòng nguy cơ về tai nạn và chấn thương. Do tính cách hiếu động, thích mạo hiểm, bạn có thể bị ngã, gãy xương hoặc tổn thương do vật sắc nhọn.',
                'Gan và mật: Bạn cần đặc biệt chú ý đến các bệnh về gan, mật và gân cơ. Sự nóng giận thường xuyên sẽ bào mòn lá gan của bạn.',
                'Hệ hô hấp: Phổi và đại tràng cũng có thể bị tổn thương nếu sinh hoạt không điều độ, hút thuốc hoặc uống rượu nhiều.',
                'Cách thức cân bằng: An toàn là trên hết: Cẩn trọng khi lái xe, chơi thể thao hoặc làm việc với máy móc. Kiềm chế cảm xúc: Giảm bớt nóng giận để bảo vệ gan. Chế độ ăn: Ăn nhiều rau xanh, đồ mát để cân bằng Kim khí và thanh lọc cơ thể.'
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, nhanh trí và học qua thực hành khá tốt.',
                'Tư duy ứng dụng thực tiễn: Bạn không thích lý thuyết suông. Bạn muốn áp dụng kiến thức ngay vào thực tế để thấy kết quả. Tư duy logic, phản biện sắc bén là vũ khí lợi hại của bạn.',
                'Thách thức: Sự tự tin thái quá vào kinh nghiệm sẵn có. Bạn thường cho rằng mình đã biết hết, không chịu học hỏi thêm. Tính cách nóng vội khiến bạn khó nghiên cứu sâu những vấn đề học thuật khô khan.',
                'dinh_huong' => [
                    'Rèn luyện sự khiêm tốn: Luôn giữ tâm thế núi cao còn có núi cao hơn.',
                    'Phát triển kỹ năng mềm: Học kỹ năng giao tiếp, lắng nghe, quản lý cảm xúc (EQ). Đây là mảnh ghép bạn thiếu để trở thành nhà lãnh đạo hoàn hảo.',
                    'Đọc sách: Giúp bạn tĩnh tâm và mở rộng chiều sâu tư duy.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người huynh đệ tốt đúng nghĩa, sống tình nghĩa và hào sảng.',
                'Mạng lưới quan hệ: Bạn có khá nhiều bạn bè, anh em chiến hữu. Đi đâu bạn cũng có người quen. Mọi người nể phục bạn vì sự trượng nghĩa, nói được làm được và đáng tin cậy.',
                'Quý nhân: Bạn bè, đồng nghiệp chính là nguồn lực lớn nhất của bạn. Sự hợp tác dựa trên tin tưởng sẽ mang lại thành công rực rỡ.',
                'Rủi ro: Vì quá tin bạn và hào phóng, bạn có thể bị lợi dụng hoặc bị bạn xấu lôi kéo vào rắc rối. Sự thẳng thắn bộc trực đôi khi thiếu sự tế nhị cũng có thể làm mất lòng người khác.',
                'chien_luoc' => [
                    'Hãy chọn bạn mà chơi, tránh xa những nhóm bạn tiêu cực.',
                    'Giữ gìn sự chừng mực trong các mối quan hệ tiền bạc.',
                    'Sự chân thành kết hợp với sự khôn ngoan sẽ giúp bạn có được những tri kỷ thực sự.'
                ]
            ]
        ],
        'canh_dan' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn đầy sức mạnh và sự quyết liệt: Một chiếc rìu kim loại đang đốn hạ cây cổ thụ trong rừng, hay một con hổ trắng dũng mãnh. Đây là hình ảnh của sự lao động, sự khai phá và chinh phục. Bạn không ngồi chờ sung rụng, bạn tự tay kiến tạo cuộc đời mình.',
                '“La Bàn Thịnh Vượng” sẽ giúp bạn sử dụng "chiếc rìu" của mình một cách hiệu quả nhất.'
            ],
            'su_nghiep' => [
                'Bạn là những chiến binh không biết mệt mỏi. Bạn tin rằng không có gì là không thể.',
                'Người tiên phong và truyền cảm hứng: Bạn có tài năng thiên bẩm, trí thông minh và trí nhớ tốt. Bạn là người truyền cảm hứng, luôn thúc đẩy người khác tiến lên. Phong thái tự tin giúp bạn dễ dàng thu hút sự chú ý.',
                'Lãnh đạo quyền uy: Bạn có xu hướng nắm giữ các vị trí quyền lực. Bạn tham vọng, có trách nhiệm và dám chấp nhận rủi ro. Bạn nhìn xa trông rộng, thấy được đích đến trước cả khi bắt đầu hành trình.',
                'Đa tài: Bạn có thể thành công trong nhiều lĩnh vực: kinh doanh, chính trị, luật pháp nhờ tư duy lý luận và nghệ thuật, sáng tạo nhờ khiếu thẩm mỹ hoặc các công việc nhân đạo, xã hội.',
                'Thách thức: Bạn hay thiếu kiên nhẫn, bướng bỉnh và đôi khi thiếu tập trung. Bạn có thể thay đổi ý định. Tham vọng quá lớn nếu không kiểm soát sẽ biến thành sự tự ám ảnh. Sự thẳng thắn và tập trung cao độ đôi khi khiến người khác cảm thấy bạn hơi nghiêm khắc hoặc xa cách.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc mang lại sự tự do nhưng đòi hỏi trách nhiệm cao.',
                    'Cần kỷ luật và danh tiếng để tôi luyện bản thân.',
                    'Sự nghiệp của bạn sẽ không đến dễ dàng, nhưng chính quá trình gian khổ sẽ mài giũa bạn thành một viên ngọc sáng.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn đến từ sự nỗ lực và nỗ lực phát triển không ngừng.',
                'Tự tạo ra của cải: Tiền bạc không tự nhiên đến, mà bạn phải cật lực, phải làm việc, phải hành động mới có. Bạn thực tế và ý thức rõ giá trị của đồng tiền.',
                'Tiềm năng lớn: Bạn có cơ hội khá lớn để trở nên giàu có bền vững. Bạn biết cách tiết kiệm và đầu tư hợp lý.',
                'Rủi ro: Bạn có xu hướng mạo hiểm. Nếu gặp vận xấu, bạn cần tuân thủ chặt chẽ các quy định pháp luật, tránh những vùng xám rủi ro trong kinh doanh. Sự thay đổi liên tục cũng có thể làm gián đoạn quá trình tích lũy.',
                'dinh_huong' => [
                    'Hãy tập trung vào việc xây dựng nền tảng vững chắc.',
                    'Đầu tư vào bất động sản là một lựa chọn tốt.',
                    'Hãy luôn tuân thủ pháp luật và đạo đức trong kinh doanh.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người trung thành, tình cảm và sẵn sàng hy sinh.',
                'Sự tận tụy: Khi yêu đúng người, bạn sẵn sàng làm tất cả để hỗ trợ đối phương. Bạn bảo vệ người yêu một cách quyết liệt. Bạn hài hước, cuốn hút và lãng mạn.',
                'Đối tượng thu hút: Bạn thích những người độc lập, có kiến thức và thành công. Mối quan hệ của bạn thường dựa trên sự tôn trọng lẫn nhau.',
                'Thách thức: Tính cách của bạn lúc thì nồng nhiệt, lúc thì thờ ơ, xa cách. Điều này làm đối phương bối rối. Bạn cũng hay ghen tuông, nghi ngờ và đôi khi muốn kiểm soát hoặc đóng vai nạn nhân, cảm thấy mình chịu thiệt thòi. Đối với phụ nữ, thường mạnh mẽ, hay tranh luận bảo vệ quan điểm, cần học cách nhu hòa để giữ lửa hôn nhân, có thể gây bất ổn trong hôn nhân nếu không biết nhường nhịn.',
                'chien_luoc' => [
                    'Học cách buông bỏ sự kiểm soát.',
                    'Giao tiếp chân thành là chìa khóa để xây dựng lòng tin.',
                    'Kết hôn muộn sẽ tốt hơn cho bạn.',
                    'Hãy chấp nhận sự nhạy cảm của bản thân và chia sẻ nó với người bạn đời.'
                ]
            ],
            'suc_khoe' => [
                'Mâu thuẫn nội tâm có thể ảnh hưởng đến sức khỏe. Về thể chất, bạn cần lưu ý bảo vệ hệ vận động như gân, cơ, xương khớp và hệ hô hấp',
                'Rủi ro chấn thương: Sự xung khắc này thường báo hiệu nguy cơ tai nạn, chấn thương tay chân hoặc xương khớp. Bạn cần chú ý an toàn trong sinh hoạt.',
                'Sức khỏe tinh thần: Bạn có thể bị trầm cảm nếu gặp thất bại hoặc bị chỉ trích. Cảm xúc chi phối mạnh mẽ đến sức khỏe của bạn. Sự lo lắng và nghi ngờ bản thân gây ra căng thẳng mãn tính.',
                'Giải pháp cân bằng: Thiền định, đi bộ, tiếp xúc với đất đai khá tốt cho bạn.',
                'Liệu pháp: Sử dụng nghệ thuật hoặc thể thao để giải phóng năng lượng dư thừa và cảm xúc tiêu cực.'
            ],
            'phat_trien_ban_than' => [
                'Bạn tò mò, khao khát kiến thức và có trí tuệ sắc bén.',
                'Học qua tranh luận: Bạn thích những cuộc đấu trí. Bạn khá cầu toàn và đặt tiêu chuẩn cao về trí tuệ đối với người xung quanh. Bạn học nhanh và nhớ lâu.',
                'Trực giác: Giác quan thứ 6 của bạn khá tốt.',
                'Thách thức: Sự bướng bỉnh là rào cản lớn nhất. Khi bạn tin mình đúng, bạn không chịu thỏa hiệp. Bạn cũng thiếu kiên nhẫn với những vấn đề dai dẳng.',
                'dinh_huong' => [
                    'Bài học quan trọng nhất là học cách thỏa hiệp.',
                    'Hãy tin tưởng vào trực giác nhưng cũng cần lắng nghe người khác.',
                    'Thay vì tranh giành thắng thua ở bên ngoài, hãy chuyển hóa nguồn năng lượng chinh phục mạnh mẽ vào việc học hỏi, nghiên cứu hay suy ngẫm triết học.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hướng ngoại, nổi tiếng và có sức hút tự nhiên.',
                'Mạng lưới rộng: Bạn dễ dàng kết bạn và hòa nhập. Bạn hào phóng, hiếu khách và hay giúp đỡ người khác.',
                'Gia đình: Mối quan hệ với gia đình có thể gặp những khác biệt quan điểm, thúc đẩy bạn sớm tự lập.',
                'Quý nhân: Bạn thu hút được sự hỗ trợ từ những người có cùng chí hướng, tham vọng. Hãy chủ động kết nối với những người tài năng.',
                'chien_luoc' => [
                    'Kiểm soát tính khí thất thường để giữ gìn các mối quan hệ.',
                    'Hãy dùng sự thẳng thắn của mình một cách ngoại giao hơn.',
                    'Đừng để sự hào phóng quá mức bị lợi dụng.'
                ]
            ]
        ],
        'canh_tuat' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thâm trầm và đầy nội lực: Một mỏ quặng kim loại quý hiếm nằm sâu trong lòng núi đá, hay một thanh gươm báu sắc bén được cất giữ kỹ lưỡng trong bao da.',
                '“La Bàn Thịnh Vượng” sẽ giúp bạn mở khóa kho báu nội tâm để biến tiềm năng ẩn giấu thành thành tựu rực rỡ.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tư duy sâu sắc, khác biệt và không bao giờ đi theo đám đông.',
                'Sự tập trung và bền bỉ: Bạn làm việc với sự tập trung cao độ và tinh thần trách nhiệm tuyệt đối. Bạn kiên trì, vững chãi như ngọn núi. Một khi đã xác định mục tiêu, bạn sẽ âm thầm nỗ lực, đào sâu nghiên cứu cho đến khi đạt được thành quả, bất chấp mọi khó khăn hay thời gian.',
                'Tố chất chuyên gia: Bạn có tư duy của một nhà nghiên cứu, một cố vấn chiến lược. Lời nói của bạn ít nhưng trọng lượng lớn, đi thẳng vào trọng tâm. Bạn nhìn thấy những điều cốt lõi mà người khác thường bỏ qua.',
                'Lĩnh vực phù hợp: Chuyên môn cao: Bạn phù hợp với các ngành nghề đòi hỏi sự nghiên cứu sâu, kỹ thuật cao như công nghệ thông tin, kỹ thuật, y học, khoa học. Tâm linh và Tôn giáo: Với trực giác mạnh mẽ và chiều sâu nội tâm, bạn có thể thành công lớn trong các lĩnh vực tôn giáo, triết học, huyền học hoặc tâm lý học. Nghệ thuật: Sự nhạy cảm và độc đáo giúp bạn tạo ra những tác phẩm nghệ thuật có chiều sâu.',
                'Thách thức: Tính cách của bạn khá khép kín, cô độc và đôi khi có những ý tưởng và phong cách sống độc đáo khác biệt số đông. Bạn khó hòa nhập với môi trường công sở ồn ào, náo nhiệt hay những cuộc xã giao hời hợt. Bạn có thể bị hiểu lầm là kiêu ngạo hoặc khó gần. Sự kiên định với nguyên tắc riêng đôi khi làm giảm tính linh hoạt.',
                'chien_luoc' => [
                    'Làm việc độc lập: Hãy tìm kiếm những công việc cho phép bạn có không gian riêng tĩnh lặng để tư duy và sáng tạo.',
                    'Mở lòng hơn: Hãy cố gắng giao tiếp và chia sẻ ý tưởng đắt giá của mình với người khác. Đừng để tài năng bị chôn vùi trong sự im lặng.',
                    'Khai phá bản thân: Bạn cần áp lực và thử thách để tôi luyện quặng thô thành vật dụng hữu ích. Đừng ngại khó khăn, đó chính là lò luyện của bạn.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn mang tính chất tích lũy âm thầm nhưng vô cùng vững chắc.',
                'Tư duy tích sản: Bạn có duyên lớn với đất đai, khoáng sản hoặc những tài sản ngầm, ít ai biết. Bạn không phải là người tiêu xài hoang phí. Bạn biết cách tiết kiệm, vun vén và quản lý tài chính chặt chẽ. Tài sản của bạn thường đến từ chuyên môn sâu hoặc đầu tư dài hạn.',
                'Tiềm năng thịnh vượng: Bạn có khả năng trở thành đại gia ngầm. Bạn không thích khoe khoang sự giàu có. Bạn có thể kiếm tiền từ những lĩnh vực ngách, chuyên biệt. Nếu vận trình thuận lợi bạn có thể giàu lên bất ngờ.',
                'Rủi ro: Tính lo xa khiến bạn đôi khi quá khắt khe trong chi tiêu cá nhân. Sự bảo thủ và quá an toàn trong đầu tư có thể khiến bạn bỏ lỡ những cơ hội đột phá. Cần thận trọng với những tranh chấp pháp lý liên quan đến đất đai.',
                'dinh_huong' => [
                    'Đầu tư bất động sản: Đây là kênh sinh lời an toàn và phù hợp nhất với bạn.',
                    'Đầu tư vào tri thức: Kiến thức chuyên môn chính là cỗ máy in tiền bền vững nhất của bạn.',
                    'Hào phóng hơn: Hãy học cách cho đi và chi tiêu hợp lý để dòng tiền được lưu thông, kích hoạt tài lộc.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình yêu, bạn là người cực kỳ chung thủy, đáng tin cậy nhưng cách thể hiện tình cảm lại thiên về hành động hơn lời nói.',
                'Sự cam kết: Bạn không dễ yêu, nhưng đã yêu là xác định gắn bó trọn đời. Bạn là người kín đáo, ít thể hiện cảm xúc lãng mạn sến súa ra bên ngoài. Bạn yêu bằng sự quan tâm thầm lặng, bằng trách nhiệm bảo vệ và sự trung thành tuyệt đối.',
                'Thách thức: Bạn khá khô khan và cứng nhắc, ít khi bộc lộ sự lãng mạn và ngọt ngào theo cách thông thương, mà đối phương mong đợi. Sự cô độc nội tâm khiến bạn khó chia sẻ những suy nghĩ sâu kín với bạn đời, tạo ra khoảng cách vô hình. Bạn cũng có xu hướng ghen tuông ngầm và kiểm soát. Đối với phụ nữ, thường độc lập, mạnh mẽ, cần tìm người bạn đời thấu hiểu và tôn trọng cá tính này.',
                'chien_luoc' => [
                    'Học cách lãng mạn: Một lời khen, một món quà nhỏ hay cử chỉ âu yếm sẽ giúp hâm nóng tình cảm.',
                    'Chia sẻ: Hãy mở lòng tâm sự với người bạn đời, cho phép họ bước vào thế giới nội tâm của bạn để thấu hiểu nhau hơn.',
                    'Bao dung: Bớt khắt khe và nguyên tắc với người thân, hãy chấp nhận những thói quen khác biệt của họ.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn liên quan nhiều đến sự tắc nghẽn khí huyết và các vấn đề do nhiệt.',
                'Da liễu và hô hấp: Bạn cần chú ý các bệnh về da như khô da, nứt nẻ, dị ứng và phổi như ho khan, viêm họng.',
                'Tinh thần: Sự cô đơn, suy nghĩ nhiều và hay dằn vặt bản thân có thể dẫn đến trầm cảm, u uất hoặc căng thẳng thần kinh.',
                'Liệu pháp: Uống nhiều nước là điều bắt buộc để làm ẩm đất và mát kim, giúp cơ thể cân bằng. Vận động: Tập thể dục thường xuyên để khí huyết lưu thông, tránh sự trì trệ. Chăm sóc da: Chú ý dưỡng ẩm và bảo vệ da. Thư giãn tinh thần: Tham gia các hoạt động xã hội, chia sẻ với bạn bè tin cậy để giải tỏa nỗi cô đơn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ sâu sắc và khả năng tư duy trừu tượng tuyệt vời.',
                'Năng lực nghiên cứu: Bạn thích tìm hiểu về cội nguồn, lịch sử, văn hóa và tôn giáo. Bạn có khả năng tự học và nghiên cứu độc lập xuất sắc. Bạn kiên trì đào sâu vấn đề đến tận cùng gốc rễ để tìm ra chân lý.',
                'Thách thức: Sự kiên định bảo vệ quan điểm đôi khi trở thành rào cản tiếp nhận cái mới. Bạn khó thay đổi quan điểm đã định hình. Bạn cũng hay hoài nghi và thiếu niềm tin vào người khác, dẫn đến việc bỏ lỡ những kiến thức mới mẻ.',
                'dinh_huong' => [
                    'Mở rộng tư duy: Hãy tập lắng nghe những quan điểm trái chiều với tâm thế cởi mở.',
                    'Phát triển tâm linh: Tìm hiểu về thiền định, tôn giáo hoặc triết học sẽ giúp bạn cân bằng nội tâm, giảm bớt sự khô cứng và phát huy tối đa trí tuệ tiềm ẩn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn ít nói, trầm tính, không thích những chỗ ồn ào náo nhiệt.',
                'Phong cách giao tiếp: Bạn có ít bạn bè, nhưng đó đều là những người bạn chất lượng, tri kỷ, chất lượng hơn số lượng. Bạn được người khác kính nể vì sự uyên bác, tính cách chính trực và nói ít làm nhiều.',
                'Quý nhân: Quý nhân của bạn thường là những người thầy, những bậc cao nhân, chuyên gia đầu ngành hoặc những người có cùng đam mê nghiên cứu sâu sắc.',
                'chien_luoc' => [
                    'Hãy bước ra khỏi vùng an toàn tĩnh lặng, hãy bước ra ngoài và kết nối với thế giới.',
                    'Học cách mỉm cười và thân thiện hơn.',
                    'Sự chân thành của bạn sẽ được đền đáp xứng đáng bằng những mối quan hệ bền vững theo thời gian.'
                ]
            ]
        ],
        'canh_ngo' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một quá trình đầy thử thách nhưng vinh quang: Một khối kim loại đang được nung trong lò lửa đỏ rực để trở thành thanh kiếm báu sắc bén, hay một bức tượng vàng đang được đúc khuôn.',
                '“La Bàn Thịnh Vượng” sẽ giúp bạn vượt qua "lò lửa" cuộc đời để trở thành phiên bản tốt nhất của chính mình.'
            ],
            'su_nghiep' => [
                'Bạn không sợ áp lực. Ngược lại, áp lực chính là môi trường để bạn trưởng thành và thành công.',
                'Kỷ luật và chính trực: Bạn làm việc với tinh thần trách nhiệm cao độ. Bạn tôn trọng quy tắc, luật lệ và đạo đức nghề nghiệp. Bạn là người đáng tin cậy, luôn giữ lời hứa. Danh tiếng của bạn được xây dựng trên sự chính trực và năng lực thực sự.',
                'Lãnh đạo gương mẫu: Bạn có tố chất làm quan, làm quản lý. Bạn lãnh đạo bằng cách làm gương. Bạn nghiêm khắc với bản thân và cả nhân viên, nhưng luôn công bằng.',
                'Lĩnh vực phù hợp: Bạn phù hợp với môi trường nhà nước, chính phủ, tập đoàn lớn có quy trình rõ ràng. Các ngành nghề như quân đội, cảnh sát, luật pháp, quản lý hành chính khá hợp với bạn. Ngoài ra, sự tinh tế và khéo léo do quá trình tôi luyện cũng giúp bạn thành công trong kỹ thuật cao, thẩm mỹ hoặc thời trang.',
                'Thách thức: Đôi khi bạn quá cứng nhắc, thiếu linh hoạt. Bạn có thể bị căng thẳng vì tự đặt ra tiêu chuẩn quá cao. Sự cẩn trọng đôi khi khiến bạn bỏ lỗ cơ hội thử nghiệm những hướng đi mới mẻ.',
                'chien_luoc' => [
                    'Hãy cho phép bản thân được sai lầm.',
                    'Sự hoàn hảo là đích đến, nhưng linh hoạt là phương tiện.',
                    'Hãy học cách thích nghi với những thay đổi.',
                    'Hãy linh hoạt hơn trong tiêu chuẩn làm việc nhóm, hãy tạo không khí làm việc thoải mái hơn.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn thường ổn định và đến từ những nguồn thu nhập chính đáng.',
                'Kiếm tiền bằng năng lực: Bạn không trông chờ vào may mắn hay những trò đỏ đen. Tiền bạc của bạn là kết quả của sự nỗ lực, chăm chỉ và cống hiến. Bạn thăng tiến theo lộ trình vững chắc, thu nhập tăng dần theo thời gian và địa vị.',
                'Quản lý chặt chẽ: Bạn chi tiêu có kế hoạch, không hoang phí. Bạn biết cách tiết kiệm và đầu tư an toàn. Bạn coi trọng sự ổn định tài chính hơn là sự giàu có bất ngờ nhưng rủi ro.',
                'Rủi ro: Sự cẩn trọng là ưu điểm, nhưng hãy mở lòng với những cơ hội đầu tư có tính toán kỹ lưỡng. Áp lực về tiền bạc đôi khi khiến bạn lo lắng thái quá.',
                'dinh_huong' => [
                    'Hãy duy trì sự ổn định nhưng cũng nên dành một phần nhỏ để đầu tư vào các kênh tiềm năng hơn.',
                    'Mở rộng kiến thức tài chính để tự tin hơn trong các quyết định.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn thường nghiêm túc, chỉn chu và hướng đến sự lâu dài.',
                'Sức hút thanh lịch: Bạn có vẻ ngoài lịch thiệp, gọn gàng và cư xử đúng mực. Điều này tạo nên sức hút đặc biệt với người khác phái. Bạn là mẫu người yêu lý tưởng: chung thủy, trách nhiệm và biết quan tâm., chú đáo, tận tâm.',
                'Tìm kiếm sự hoàn hảo: Bạn khá kén chọn. Bạn muốn tìm một người bạn đời môn đăng hộ đối về tri thức, đạo đức và lối sống. Bạn không thích những mối quan hệ hời hợt, chóng vánh.',
                'Đào hoa: Dù nghiêm túc, bạn vẫn có sức hấp dẫn giới tính mạnh mẽ. Tuy nhiên, bạn biết giữ mình và coi trọng danh dự gia đình.',
                'Thách thức: Bạn hay mang áp lực công việc về nhà. Sự nghiêm túc và mong muốn mọi thứ theo quy chuẩn của bạn đôi khi làm không khí gia đình căng thẳng. Bạn có xu hướng thể hiện tình yêu qua hành động thực tế hơn là những lời nói hoa mỹ.',
                'chien_luoc' => [
                    'Hãy cởi bỏ lớp áo giáp khi về nhà.',
                    'Hãy lãng mạn và hài hước hơn.',
                    'Dành thời gian chất lượng cho gia đình.',
                    'Sự bao dung và thấu hiểu sẽ giúp hôn nhân của bạn bền vững và ấm áp.'
                ]
            ],
            'suc_khoe' => [
                'Sự lo âu và áp lực kéo dài có thể ảnh hưởng đến sức khỏe, làm giảm sự dẻo dai của cơ thể',
                'Hệ hô hấp và da: Bạn cần chú ý các bệnh về phổi, phế quản, ho khan, viêm họng hoặc các bệnh ngoài da như dị ứng, mụn nhọt.',
                'Tim mạch và mắt: Bạn cẩn thận vấn đề tim, huyết áp và thị lực.',
                'Căng thẳng: Áp lực từ sự cầu toàn và kỷ luật bản thân có thể dẫn đến stress, mất ngủ.',
                'Cách thức cân bằng: Làm mát: Uống nhiều nước, ăn đồ mát là bắt buộc. Bơi lội là môn thể thao tuyệt vời cho bạn. Dưỡng phổi: Tập hít thở sâu, tránh nơi khói bụi. Thư giãn: Học cách xả hơi. Đừng lúc nào cũng căng như dây đàn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người học tập nghiêm túc và có nền tảng kiến thức vững chắc.',
                'Học để ứng dụng: Bạn coi trọng bằng cấp và chứng chỉ vì nó khẳng định năng lực của bạn. Bạn học để phục vụ công việc và thăng tiến.',
                'Tư duy logic: Bạn có tư duy mạch lạc, rõ ràng. Bạn thích những kiến thức có tính hệ thống, quy luật.',
                'Thách thức: Vượt qua vùng an toàn để đổi mới tư duy. Bạn thường đi theo những lối mòn an toàn đã được kiểm chứng.',
                'dinh_huong' => [
                    'Hãy thử thách bản thân ở những lĩnh vực mới mẻ, nghệ thuật hoặc sáng tạo để cân bằng não bộ.',
                    'Đọc sách về văn học, tâm lý để làm phong phú tâm hồn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn có những mối quan hệ chất lượng, dựa trên sự tôn trọng và uy tín.',
                'Hình ảnh xã hội: Trong mắt mọi người, bạn là người đáng kính, lịch sự và thành đạt. Bạn thường được bầu làm nhóm trưởng hoặc người đại diện.',
                'Quý nhân: Bạn thường được cấp trên, người lớn tuổi hoặc những người có chức quyền nâng đỡ. Mối quan hệ với đồng nghiệp thường tốt đẹp nếu bạn bớt khắt khe.',
                'Rủi ro: Phong thái nghiêm nghị đôi khi tạo cảm giác xa cách với người mới quen. Sự thẳng thắn quá mức đôi khi làm mất lòng người khác.',
                'chien_luoc' => [
                    'Hãy thân thiện và hòa đồng hơn.',
                    'Nụ cười và sự hài hước sẽ giúp bạn kết nối dễ dàng hơn với mọi người.',
                    'Xây dựng mạng lưới quan hệ rộng rãi nhưng chân thành.'
                ]
            ]
        ],
        'dinh_mui' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp bình yên, sâu lắng và đầy tính nuôi dưỡng: Một đống lửa trại ấm áp cháy sáng giữa vùng thảo nguyên bao la, hay ánh đèn lồng lung linh trong đêm tĩnh mịch. Bạn không chói chang, gay gắt như ánh nắng ban trưa, mà lan tỏa hơi ấm dịu dàng, xua tan lạnh lẽo. Bạn thông minh, tinh tế, giàu lòng trắc ẩn và sở hữu một sức sống bền bỉ đáng kinh ngạc.',
                'La Bàn Thịnh Vượng sẽ giúp bạn nuôi dưỡng ngọn lửa tâm hồn ấy để sưởi ấm cuộc đời mình và những người xung quanh.'
            ],
            'su_nghiep' => [
                'Bạn chinh phục thành công không phải bằng sự ồn ào, tranh đấu mà bằng con đường của sự sáng tạo, khéo léo và kiên trì bền bỉ.',
                'Nghệ sĩ tài hoa: Bạn có những ý tưởng độc đáo, gu thẩm mỹ tinh tế và khả năng nhìn nhận cái đẹp xuất sắc. Trong công việc, bạn tỉ mỉ, chi tiết và luôn hướng tới sự hoàn hảo, hài hòa.',
                'Lãnh đạo mềm dẻo: Phong cách quản lý của bạn là lạt mềm buộc chặt. Bạn biết cách dùng lời nói nhẹ nhàng, sự thấu hiểu và kỹ năng ngoại giao khéo léo để thuyết phục người khác. Uy tín của bạn được xây dựng trên năng lực thực sự và sự tử tế, khiến người khác nể trọng chứ không sợ hãi.',
                'Lĩnh vực phù hợp: Bất cứ ngành nào cần đến cái đẹp và sự tinh tế đều là đất diễn cho bạn: viết lách, thiết kế, hội họa, thời trang, ẩm thực. Ngoài ra, lòng trắc ẩn giúp bạn thành công rực rỡ trong các nghề chăm sóc, chữa lành như y tế, tâm lý, giáo dục. Hoặc kinh doanh dịch vụ như nhà hàng, khách sạn.',
                'Thách thức: Điểm yếu lớn nhất là sự thiếu quyết đoán và hay do dự. Bạn suy nghĩ quá nhiều trước khi hành động, có thể bỏ lỡ thời cơ. Cảm xúc chi phối công việc khá nhiều; khi buồn chán, bạn có xu hướng buông xuôi. Sự cầu toàn thái quá đôi khi khiến bạn ôm đồm việc và trở nên khó tính.',
                'chien_luoc' => [
                    'Hãy rèn luyện tính kỷ luật: Đặt ra thời hạn và nghiêm túc tuân thủ để tránh sự trì hoãn.',
                    'Tập trung vào thế mạnh sáng tạo, tránh xa những công việc quá khô khan, máy móc.',
                    'Học cách từ chối, đừng vì cả nể mà gánh vác trách nhiệm không thuộc về mình.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là người có số hưởung lộc, biết kiếm tiền và cũng biết cách tận hưởng cuộc sống.',
                'Duyên với tiền bạc: Bạn có lộc ăn uống và sự may mắn tự nhiên về tài chính. Bạn thường không phải lo lắng quá nhiều về cơm áo gạo tiền. Bạn có khả năng kiếm tiền tốt nhờ sự tháo vát, siêng năng và đặc biệt phát tài nếu sinh vào mùa xuân hoặc hạ.',
                'Phong cách tiêu tiền: Bạn quan niệm tiền bạc là phương tiện phục vụ cuộc sống. Bạn sẵn sàng chi trả cho những trải nghiệm ẩm thực, du lịch và nghệ thuật để nuôi dưỡng tinh thần. Bạn ưa chuộng sự an toàn và biết cách vun vén tài chính gia đình khéo léo.',
                'Rủi ro: Nguy cơ lớn nhất đến từ việc chi tiêu theo cảm hứng. Những món đồ đẹp đẽ, xa xỉ luôn có sức cám dỗ lớn với bạn.',
                'dinh_huong' => [
                    'Lập kế hoạch tài chính rõ ràng: Tách biệt quỹ hưởng thụ và quỹ tiết kiệm.',
                    'Đầu tư an toàn: Bất động sản hoặc tích lũy dài hạn là kênh phù hợp nhất với bạn.',
                    'Giữ sự độc lập tài chính, hạn chế chung đụng tiền bạc quá nhiều để bảo vệ các mối quan hệ.'
                ]
            ],
            'tinh_duyen' => [
                'Tình cảm là nguồn sống, là ngọn lửa sưởi ấm tâm hồn bạn. Bạn lãng mạn, sâu sắc nhưng cũng đầy nhạy cảm.',
                'Sự tận tụy: Khi yêu, bạn dốc hết tâm can, yêu hết mình và chăm sóc đối phương chu đáo. Bạn khao khát một mái ấm hạnh phúc, bình yên. Bạn là mẫu người lý tưởng của gia đình, thích nấu ăn, trang trí tổ ấm và chăm sóc con cái.',
                'Tiêu chuẩn chọn bạn đời: Bạn có thể bị thu hút bởi những người thông minh, có trí tuệ sâu sắc và tâm hồn đồng điệu để cùng chia sẻ những giá trị tinh thần.',
                'Thách thức: Sự nhạy cảm quá mức là con dao hai lưỡi. Bạn có thể bị tổn thương bởi những lời nói vô tình và hay suy diễn, ghen tuông ngầm. Sự im lặng kéo dài của bạn là hình phạt đáng sợ. Tính sở hữu cao đôi khi biến sự quan tâm thành kiểm soát, gây ngột ngạt cho đối phương.',
                'chien_luoc' => [
                    'Học cách chia sẻ cảm xúc: Đừng bắt đối phương phải đoán ý, hãy nói ra mong muốn của mình nhẹ nhàng.',
                    'Tự tạo niềm vui: Đừng đặt chìa khóa hạnh phúc vào tay người khác. Hãy giữ những sở thích riêng để cân bằng.',
                    'Thực hành sự bao dung, chấp nhận sự không hoàn hảo của người bạn đời.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn nhìn chung dẻo dai, nhưng cần chú ý sự cân bằng năng lượng bên trong.',
                'Nhiệt và khô: Bạn có thể gặp tình trạng nóng trong, nhiệt miệng, các vấn đề về tiêu hóa, dạ dày hoặc tim mạch, huyết áp.',
                'Tâm bệnh: Sự tham vọng ngầm và thói quen lo nghĩ nhiều có thể dẫn đến kiệt sức về tinh thần. Ngoài ra, do có lộc ăn, bạn cần kiểm soát chế độ ăn uống để tránh tăng cân mất kiểm soát.',
                'Cách thức cân bằng: Hãy uống nhiều nước, ăn thực phẩm có tính hàn, mát.',
                'Liệu pháp: Massage, bấm huyệt và châm cứu là những phương pháp tuyệt vời giúp bạn giải tỏa tắc nghẽn năng lượng. Đừng quên dành thời gian ngủ đủ giấc để phục hồi.'
            ],
            'phat_trien_ban_than' => [
                'Bạn học hỏi qua trải nghiệm thực tế và quan sát tinh tế hơn là qua sách vở khô khan.',
                'Trí tuệ cảm xúc: Bạn có tư duy sâu sắc, thích tìm hiểu về tâm lý, triết học và nghệ thuật. Trực giác nhạy bén giúp bạn thấu hiểu những điều không lời. Khả năng tự học và bắt chước của bạn khá đáng nể.',
                'Thách thức: Sự thiếu kiên nhẫn và có thể phân tâm là rào cản lớn. Bạn có quá nhiều mối quan tâm nên khó tập trung sâu. Nỗi sợ thất bại cũng khiến bạn e ngại khi đứng trước những thử thách mới.',
                'dinh_huong' => [
                    'Hãy dũng cảm thử nghiệm cho đến khi tìm ra đam mê đích thực, rồi dồn toàn lực cho nó.',
                    'Rèn luyện sự kiên trì: Hiểu rằng thành công lớn được xây đắp từ những bước chân nhỏ nhưng vững chắc mỗi ngày.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người bạn tâm giao tuyệt vời, luôn lắng nghe và thấu hiểu.',
                'Sức hút nhẹ nhàng: Bạn hòa đồng, thân thiện và khéo léo làm hài lòng người khác. Mọi người yêu mến bạn vì sự ân cần, nhẹ nhàng và cảm giác bình yên bạn mang lại.',
                'Quý nhân: Bạn thường thu hút được sự giúp đỡ từ những người tài giỏi, có địa vị.',
                'chien_luoc' => [
                    'Hãy thiết lập ranh giới lành mạnh, đừng để sự cả nể khiến bạn phải gánh vác việc không tên.',
                    'Chọn lọc bạn bè kỹ lưỡng: Một môi trường tích cực sẽ là đòn bẩy giúp bạn phát triển vượt bậc.',
                    'Hãy trân trọng sự giúp đỡ và đáp lại bằng lòng chân thành vốn có.'
                ]
            ]
        ],
        'dinh_hoi' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp thanh tao, quý phái và đầy tính tâm linh: Một ngọn đèn hoa đăng trôi nhẹ nhàng trên dòng nước, hay ánh sao lung linh phản chiếu dưới mặt hồ tĩnh lặng. Bạn sở hữu trực giác sắc bén, sự tinh tế và một phong thái quý tộc tự nhiên. Bạn không dùng sức mạnh cơ bắp để dẫn đường, bạn dẫn đường bằng ánh sáng của lòng nhân ái và sự thấu hiểu.',
                'La Bàn Thịnh Vượng sẽ giúp bạn giữ cho ngọn đèn nội tâm ấy luôn cháy sáng để định vị cuộc đời mình.'
            ],
            'su_nghiep' => [
                'Bạn là mẫu nhà lãnh đạo khai phóng, dùng tầm nhìn và sự thấu cảm để thu phục nhân tâm.',
                'Tố chất lãnh đạo: Bạn không áp đặt hay dùng quyền lực cứng nhắc. Bạn có khả năng kết nối mọi người và xây dựng mạng lưới dựa trên sự tin tưởng. Trực giác nhạy bén giúp bạn nhận diện người tài và nhìn thấy cơ hội tốt trước người khác.',
                'Quý nhân phù trợ: Trong sự nghiệp, bạn thường gặp dữ hóa lành, được cấp trên nâng đỡ hoặc nhận được những cơ hội bất ngờ vào phút chót.',
                'Lĩnh vực phù hợp: Khả năng thuyết phục bẩm sinh và phong thái uyên bác giúp bạn tỏa sáng trong các lĩnh vực: giáo dục, tôn giáo, tâm lý, hành chính, hoặc các tổ chức phi chính phủ. Bạn là người truyền cảm hứng tuyệt vời.',
                'Thách thức: Điểm yếu lớn nhất là sự thiếu kiên nhẫn và nóng vội ở giai đoạn đầu. Bạn muốn thấy kết quả ngay lập tức. Sự nhạy cảm thái quá khiến bạn có thể bị tổn thương bởi những lời phê bình, đôi khi phản ứng bằng cách rút lui hoặc trở nên kiểm soát.',
                'chien_luoc' => [
                    'Dục tốc bất đạt: Hãy rèn luyện sự kiên nhẫn và đi từng bước vững chắc.',
                    'Sử dụng trí tuệ: Không ngừng học hỏi để củng cố vị thế.',
                    'Dùng sự duyên dáng để lãnh đạo thay vì mệnh lệnh.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn đến từ trí tuệ, trực giác và các mối quan hệ chất lượng.',
                'Giác quan thứ 6: Bạn có trực giác cực kỳ tốt về tiền bạc. Bạn nên tin tưởng vào những linh cảm ban đầu của mình trong đầu tư và hợp tác. Bạn không quá coi trọng vật chất, nhưng biết cách tạo ra sự thịnh vượng để nuôi dưỡng lý tưởng sống.',
                'Nguồn tài lộc: Bạn có khả năng kiếm tiền tốt thông qua việc làm trung gian, kết nối hoặc đầu tư vào những lĩnh vực mới mẻ.',
                'Rủi ro: Mối nguy lớn nhất là sự thất thoát tiền bạc do tình cảm. Bạn hào phóng và dễ tin người, có thể mất tiền vì bạn bè hoặc người thân. Sự lo lắng thái quá về an toàn tài chính đôi khi cũng ngăn cản bạn nắm bắt cơ hội lớn.',
                'dinh_huong' => [
                    'Tìm mục đích cao cả: Khi bạn theo đuổi một sứ mệnh ý nghĩa, tiền bạc sẽ tự động đi kèm như một phần thưởng.',
                    'Quản lý cảm xúc: Tránh chi tiêu hào phóng khi quá vui hoặc mua sắm khi quá buồn.',
                    'Học cách nói không với những lời vay mượn thiếu rõ ràng.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình yêu, bạn là người tình lãng mạn, sâu sắc và coi trọng sự cam kết thiêng liêng.',
                'Sự tận hiến: Bạn chu đáo, chung thủy và sẵn sàng hy sinh cho người mình yêu. Bạn khao khát tìm kiếm một người bạn đời tri kỷ, một người có thể thấu hiểu thế giới nội tâm phong phú và chia sẻ cùng lý tưởng sống với bạn.',
                'Thách thức: Vì khao khát sự hòa hợp và ổn định, bạn giống như dòng nước, dễ dàng thay đổi hình dạng để chiều lòng đối phương nhưng lại vô tình đánh mất bản sắc của chính mình. Điều này lâu dần tạo ra sự ấm ức dồn nén. Khi tổn thương, bạn có xu hướng trở nên lạnh lùng và xa cách đột ngột, khiến đối phương hoang mang.',
                'chien_luoc' => [
                    'Giao tiếp là chìa khóa: Hãy nói rõ nhu cầu của mình, đừng mong đợi đối phương tự đọc được suy nghĩ của bạn.',
                    'Giữ vững bản sắc: Duy trì sự độc lập và không gian riêng là cách để giữ gìn sự hấp dẫn.',
                    'Dùng sự thấu hiểu để hóa giải mâu thuẫn thay vì im lặng chiến tranh lạnh.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn là tấm gương phản chiếu trạng thái tinh thần. Bạn là điển hình của người tâm sinh bệnh.',
                'Nhạy cảm thần kinh: Những căng thẳng nội tâm, lo âu dồn nén có thể dẫn đến kiệt sức, trầm cảm hoặc rối loạn giấc ngủ.',
                'Tim mạch và khí huyết: Bạn có thể mắc các vấn đề về tim, huyết áp hoặc thị lực.',
                'Cách thức cân bằng: Duy trì sự cân bằng giữa công việc và cuộc sống là mệnh lệnh bắt buộc. Đừng để sự nghiệp nuốt chửng đời sống cá nhân.',
                'Liệu pháp: Tâm linh là liều thuốc tốt nhất cho bạn. Thiền định, Yoga hoặc tìm hiểu về tôn giáo sẽ giúp bạn tìm thấy sự bình yên, tĩnh tại sâu thẳm.'
            ],
            'phat_trien_ban_than' => [
                'Bạn có khả năng học tập và tiếp thu tri thức xuất sắc.',
                'Trí tuệ uyên bác: Bạn thích khám phá những chân trời tri thức mới, đặc biệt là các lĩnh vực nghiên cứu, học thuật hoặc tâm linh. Bạn có khả năng biến lý thuyết sách vở thành kỹ năng thực tế và nhân sinh quan sâu sắc.',
                'Thách thức: Đôi khi bạn quá độc lập đến mức tự cô lập mình khỏi tập thể. Sự thiếu kiên nhẫn ban đầu hoặc thiếu một kim chỉ nam rõ ràng có thể khiến bạn đi lạc hướng hoặc bỏ cuộc giữa chừng.',
                'dinh_huong' => [
                    'Lập kế hoạch chi tiết: Kỷ luật là cây cầu dẫn đến ước mơ.',
                    'Học từ thất bại: Đừng sợ sai. Hãy coi rủi ro là học phí để thúc đẩy sự trưởng thành về trí tuệ.',
                    'Tìm một người thầy, mentor dẫn dắt để phát huy tối đa tiềm năng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn có sức hấp dẫn bẩm sinh, quyến rũ và lôi cuốn lòng người một cách nhẹ nhàng.',
                'Sức hút quý nhân: Bạn thường thu hút được những người có địa vị cao, giàu có hoặc học thức uyên bác đến giúp đỡ mình. Mọi người tin tưởng và tôn trọng ý kiến của bạn.',
                'Rủi ro: Đôi khi bạn thể hiện hai mặt tính cách: Lúc vui vẻ nhiệt tình, lúc lại lạnh lùng xa cách khiến người khác khó nắm bắt. Bạn cũng có thể bị ảnh hưởng bởi áp lực từ bạn bè.',
                'chien_luoc' => [
                    'Hãy chọn bạn mà chơi: Môi trường giao tiếp ảnh hưởng lớn đến vận mệnh của bạn.',
                    'Biết ơn nhưng không phụ thuộc: Trân trọng sự giúp đỡ của quý nhân nhưng hãy tự đứng trên đôi chân mình.',
                    'Dùng sự chân thành để kết nối, bạn sẽ luôn có những người bạn tuyệt vời bên cạnh.'
                ]
            ]
        ],
        'dinh_mao' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật đẹp, thanh tao và đầy chất nghệ thuật: Một ngọn lửa từ nén hương trầm thơm ngát, hay ánh lửa bập bùng trong lò sưởi. Bạn sinh ra đã có sẵn nguồn năng lượng nuôi dưỡng dồi dào cho trí tuệ và tâm hồn. Bạn thông minh, tinh tế, nhạy cảm, thẳng thắn, độc lập và sở hữu một trực giác cực kỳ sắc bén.',
                'La Bàn Thịnh Vượng sẽ giúp bạn thổi bùng ngọn lửa sáng tạo ấy để tỏa sáng rực rỡ.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu nguồn năng lượng nội tại mạnh mẽ, độc lập và tài năng thiên bẩm về ngôn ngữ.',
                'Tư duy tiên phong: Bạn được trời phú cho kho tàng trí tuệ và sự sáng tạo dồi dào. Bạn thường là người đi tiên phong trong lĩnh vực mình chọn. Sự nhạy bén và đầu óc phân tích sắc sảo giúp bạn phát hiện sớm các xu hướng kinh doanh, kinh tế để biến cơ hội thành lợi nhuận.',
                'Đa tài và tham vọng: Trong công việc, bạn tham vọng và có kỹ năng giao tiếp xuất sắc. Bạn giải quyết vấn đề tốt, thực tế và khéo léo. Kỹ năng tổ chức và điều hành giúp bạn trở thành nhà quản lý hoặc doanh nhân xuất sắc. Bạn có khả năng biến bất kỳ cơ hội nào thành hiện thực.',
                'Lĩnh vực phù hợp: tài chính, quản lý nhờ nắm bắt số liệu tốt; Sáng tạo, truyền thông như âm nhạc, nghệ thuật, viết lách, quảng cáo; Khởi nghiệp do khao khát tự do.',
                'Thách thức: Bạn có thể nhàm chán với công việc lặp lại, đơn điệu. Bạn cần sự kích thích liên tục. Đôi khi bạn có xu hướng nổi loạn, kiểm soát, bướng bỉnh hoặc thiếu kỹ năng lập kế hoạch. Mối quan hệ công việc có thể hời hợt hoặc vướng vào chính trị công sở nếu gặp vận xấu.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc mang lại sự tự do và thử thách trí tuệ.',
                    'Rèn luyện tính kỷ luật là chìa khóa để bạn duy trì sự tập trung.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn thường thịnh vượng nhờ vào tài năng, sự nhạy bén và sự trân trọng những điều tinh tế.',
                'Tiềm năng giàu có: Bạn có tiềm năng trở nên khá giàu có. Bạn sẵn sàng làm việc chăm chỉ để xây dựng nền tảng tài chính vững chắc và đảm bảo tương lai. Khả năng phân tích mạnh mẽ giúp bạn nắm bắt thị trường và đầu tư thông minh.',
                'Cơ hội lớn: Sự quyết đoán hành động nhanh chóng ở giai đoạn đầu có thể mang lại thành công lớn.',
                'Hưởng thụ cuộc sống: Bạn yêu thích những điều đẹp đẽ, tinh tế, ẩm thực ngon và nghệ thuật. Điều này cho thấy sự kết nối với sự thịnh vượng.',
                'Rủi ro: Cẩn thận với rủi ro mất mát tài chính do tin người hoặc do chi tiêu nhiều khi giao thiệp xã hội. Cần quản lý mức độ căng thẳng để không ảnh hưởng đến tài lộc.',
                'dinh_huong' => [
                    'Kỷ luật trong đầu tư dài hạn và xây dựng nền tảng vững chắc.',
                    'Tránh sự bồn chồn dẫn đến đầu tư mạo hiểm.',
                    'Quản lý chặt chẽ chi tiêu cá nhân.'
                ]
            ],
            'tinh_duyen' => [
                'Tình cảm đối với bạn là sự tận tâm, chu đáo và trách nhiệm cao với gia đình.',
                'Sự tận tâm: Bạn là người có sức hút, thân thiện và hòa đồng. Trong tình yêu, bạn chu đáo, biết hy sinh và khá trung thành. Bạn coi trọng sự ổn định và hài hòa trong hôn nhân.',
                'Mối quan hệ đặc biệt: đối với nam giới, thường có xu hướng kết hôn với người phụ nữ lớn tuổi hơn hoặc người mang vai trò chăm sóc như người mẹ. Bạn tận tâm với đối phương. Nếu gặp vận tốt, bạn sẽ có mối quan hệ khá tích cực, hạnh phúc và may mắn trong tình yêu.',
                'Thách thức: Đôi khi sự bướng bỉnh và không sẵn lòng thay đổi khiến bạn có vẻ thiếu cam kết. Sự ưu tiên quá mức cho sự nghiệp bận rộn khiến bạn ít thời gian cho lãng mạn. Tính cầu toàn cũng có thể dẫn đến sự thiếu tin tưởng và đa nghi người thân.',
                'chien_luoc' => [
                    'Hãy dành nhiều thời gian chất lượng hơn cho gia đình và chủ động nuôi dưỡng sự lãng mạn.',
                    'Tìm kiếm sự cân bằng giữa tham vọng và đời sống cá nhân.',
                    'Duy trì sự bình tĩnh và tập trung vào những điều tích cực trong mối quan hệ.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn cần được chú trọng ngay từ sớm để đảm bảo tương lai.',
                'Tiềm năng thọ: Bạn có ý thức chăm sóc sức khỏe, ăn uống tốt và tập thể dục.',
                'Rủi ro: Tuy nhiên, bạn vẫn có thể mắc bệnh nếu thiếu các yếu tố cân bằng. Căng thẳng nội tại và căng thẳng có thể bị giấu kín sau vẻ ngoài hạnh phúc. Việc quá ham công tiếc việc cũng ảnh hưởng đến sức khỏe và các mối quan hệ.',
                'Sức khỏe: Đối với phụ nữ, cần chú ý sức khỏe sinh sản.',
                'dinh_huong' => [
                    'Duy trì thói quen sống tích cực, không để công việc chiếm hết thời gian.',
                    'Tìm kiếm sự giác ngộ tâm linh để cân bằng cảm xúc và giảm bớt căng thẳng nội tâm.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu nền tảng trí tuệ vững chắc và khả năng học hỏi tuyệt vời.',
                'Trí nhớ siêu phàm: Bạn có trí nhớ tốt, khả năng hấp thụ và xử lý thông tin vượt trội. Bạn được đánh giá là khá thông minh, hiểu biết và thông thái.',
                'Sáng tạo và tiến bộ: Tư duy của bạn giàu tính phát minh, luôn hướng về phía trước. Bạn có kho tàng khả năng trí tuệ để trở thành người dẫn đầu.',
                'Thách thức: Mâu thuẫn nội tâm, tính cầu toàn và kỳ vọng quá cao có thể khiến bạn nghi ngờ chính mình. Bạn có thể bị nhàm chán hoặc bồn chồn nếu mất hứng thú. Bạn cần sự yêu thương và hỗ trợ để cảm thấy an toàn khám phá tiềm năng bản thân.',
                'dinh_huong' => [
                    'Tìm kiếm một người thầy hoặc cố vấn để dẫn dắt.',
                    'Duy trì sự kích thích liên tục bằng các dự án mới.',
                    'Phát triển tâm linh để tìm sự hài hòa.',
                    'Tập trung vào mục tiêu và tăng cường trí tuệ.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hướng ngoại, lôi cuốn và có khả năng xây dựng mạng lưới quan hệ rộng.',
                'Sức hút xã hội: Bạn dễ gần, thân thiện và có khả năng truyền cảm hứng. Bạn thường thu hút những người tham vọng, thành công xung quanh mình.',
                'Gia đình là chỗ dựa: Bạn là trụ cột hào phóng, yêu thương của gia đình. Con cái ngưỡng mộ và tôn trọng bạn.',
                'Quý nhân: Bạn có số được quý nhân phù trợ.',
                'Rủi ro: Các xung đột trong lá số có thể gây ra hiểu lầm, căng thẳng ngầm hoặc sự mất lòng tin với người thân, đồng nghiệp. Bạn có thể cảm thấy kế hoạch bị cản trở hoặc có khoảng cách với gia đình.',
                'chien_luoc' => [
                    'Nuôi dưỡng lòng tin, buông bỏ suy nghĩ tiêu cực.',
                    'Tận dụng khả năng kết nối để xây dựng đội nhóm.',
                    'Giữ thái độ tích cực và không để sự ghen tị làm mờ mắt khi gặp khó khăn trong quan hệ.'
                ]
            ]
        ],
        'dinh_dau' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn vô cùng lộng lẫy và sắc sảo: Một ngọn lửa đang chiếu rọi lên bề mặt kim loại được đánh bóng, ánh đèn sân khấu lung linh, hay ngọn lửa đang tôi luyện vàng ròng trong lò nung. Bạn sinh ra để rèn giũa, tỏa sáng và chinh phục những đỉnh cao bằng tài năng thực lực. Tuy nhiên, vì là lửa thử vàng, bạn mang trong mình sự mâu thuẫn nội tại: Vừa khao khát tôi luyện để thành công, vừa dễ bị tiêu hao năng lượng nội tại trong quá trình đó.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến áp lực ấy thành hào quang chói lọi nhất.'
            ],
            'su_nghiep' => [
                'Bạn không chấp nhận sự tầm thường. Bạn sinh ra với khát vọng được đứng ở vị trí trung tâm và được công nhận tài năng.',
                'Tư duy đột phá: Bạn sở hữu tư duy độc bản, luôn tìm ra những giải pháp đột phá cho các vấn đề phức tạp mà người khác bó tay. Bạn là một nhà tổ chức đại tài, biết cách sắp xếp mọi thứ vào trật tự và tối ưu hóa quy trình để đạt hiệu quả cao nhất.',
                'Phong cách lãnh đạo: Bạn không ồn ào hay áp đặt. Bạn lãnh đạo bằng sự sắc sảo, năng lực chuyên môn và sự hoàn hảo trong từng chi tiết. Bạn tận tâm, đáng tin cậy và có ý thức kinh doanh bẩm sinh, biết cách đoàn kết mọi người hướng tới mục tiêu chung.',
                'Lĩnh vực phù hợp: Với sự tự tin và tầm nhìn, bạn tỏa sáng ở các vị trí quản lý cấp cao, giám đốc điều hành. Năng khiếu về hình ảnh và ngôn từ giúp bạn thành công trong lĩnh vực sáng tạo như viết lách, điện ảnh, thiết kế, quảng cáo. Khả năng truyền đạt cuốn hút cũng biến bạn thành nhà tư vấn, giáo viên hoặc huấn luyện viên xuất sắc.',
                'Thách thức: Rào cản lớn nhất là sự cầu toàn thái quá. Bạn quá khắt khe với bản thân và đồng nghiệp, có thể trở nên soi mói, chỉ trích, tạo không khí căng thẳng. Sự nhàm chán là kẻ thù số một; nếu công việc thiếu kích thích, bạn sẽ nhanh chóng mất lửa và muốn bỏ cuộc.',
                'chien_luoc' => [
                    'Rèn luyện sự linh hoạt: Hãy học cách chấp nhận những sai sót nhỏ, nhìn vào bức tranh tổng thể thay vì tiểu tiết.',
                    'Tìm kiếm môi trường năng động: Chọn công việc cho phép bạn thay đổi và sáng tạo liên tục. Sự ổn định quá mức sẽ giết chết nhiệt huyết của bạn.',
                    'Biết lắng nghe: Đừng để tính bướng bỉnh khiến bạn bỏ ngoài tai những chiến lược hữu ích.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là người có duyên với tiền bạc lớn, nhưng cũng đối mặt với nhiều cám dỗ.',
                'Tiềm năng thịnh vượng: Bạn có tiềm năng giàu có vượt trội. Bạn có trực giác tài chính nhạy bén, nhìn đâu cũng thấy cơ hội sinh lời. Bạn không chỉ muốn giàu, bạn muốn cuộc sống sang trọng, tiện nghi và đẳng cấp.',
                'Phong cách kiếm tiền: Bạn kiếm tiền xuất sắc thông qua kinh doanh, đầu tư, liên doanh hoặc hợp tác. Quý nhân thường xuất hiện giúp đỡ bạn về tài chính hoặc chỉ dẫn con đường làm giàu.',
                'Rủi ro: Bạn có thể bị thất thoát tiền bạc do tin người, hoặc bị rủ rê chi tiêu hoang phí. Lòng tham và sự nóng vội muốn làm giàu nhanh có thể dẫn đến những cú ngã đau đớn. Thói quen chi tiêu xa xỉ để duy trì hình ảnh hào nhoáng cũng là một lỗ hổng tài chính.',
                'dinh_huong' => [
                    'Quản lý rủi ro chặt chẽ: Tránh xa đầu cơ đỏ đen. Chỉ đầu tư vào lĩnh vực bạn thực sự am hiểu.',
                    'Kỷ luật chi tiêu: Lập ngân sách cho sự hưởng thụ, đừng vung tay quá trán vì cảm xúc nhất thời.',
                    'Hợp tác thận trọng: Chọn đối tác kỹ lưỡng và giấy tờ minh bạch.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn nồng nhiệt, tận tâm nhưng cũng đầy lý trí và đặt ra tiêu chuẩn cao.',
                'Sự cống hiến: Khi tìm được đúng người, bạn trung thành, hào phóng và sẵn sàng làm tất cả để vun vén tổ ấm. Bạn coi trọng sự ổn định, an ninh và biết cách giữ lửa để mối quan hệ luôn thú vị, không bao giờ nhàm chán.',
                'Tiêu chuẩn chọn bạn đời: Bạn bị thu hút bởi những người thông minh, có khả năng kích thích trí tuệ của bạn, đồng thời có ngoại hình hoặc phong cách cuốn hút.',
                'Thách thức: Bạn gặp khó khăn trong việc bày tỏ cảm xúc thật, đôi khi tỏ ra lạnh lùng, tính toán khiến đối phương cảm thấy xa cách. Đối với nam giới, có thể xung khắc với gia đình vợ hoặc có xu hướng thích người trẻ tuổi hơn, gây ra khoảng cách thế hệ. Sự cầu toàn khiến bạn hay soi xét, đánh giá người yêu, gây mâu thuẫn.',
                'chien_luoc' => [
                    'Học cách chia sẻ: Giao tiếp nhẹ nhàng, bớt chỉ trích là chìa khóa hạnh phúc.',
                    'Giữ vững tinh thần: Đừng để dao động cảm xúc làm lung lay mối quan hệ. Kiên nhẫn và bao dung hơn với người bạn đời.',
                    'Tìm người thấu hiểu sự ấm áp ẩn sâu bên trong vỏ bọc lạnh lùng của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu áp lực từ sự xung khắc nội tại và tinh thần căng thẳng.',
                'Căng thẳng thần kinh: Sự lo âu, suy nghĩ quá nhiều và áp lực tự tạo có thể dẫn đến mất ngủ, đau đầu hoặc suy nhược thần kinh.',
                'Hệ hô hấp và xương khớp: Bạn có thể gặp vấn đề về phổi, họng, da liễu hoặc xương khớp. Nguy cơ chấn thương tay chân cũng cần được lưu ý.',
                'Cách thức cân bằng: Hãy uống đủ nước, tiếp xúc với thiên nhiên để làm dịu tâm trí.',
                'Liệu pháp: Thiền định, yoga là liều thuốc tốt nhất cho hệ thần kinh của bạn. Đừng làm việc đến kiệt sức, hãy nghỉ ngơi để tái tạo năng lượng bền bỉ.'
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ sắc bén và khả năng tự học tuyệt vời, biến kiến thức thành tiền bạc.',
                'Tư duy phân tích: Bạn hấp thụ kiến thức mới cực nhanh và có tư duy giải quyết vấn đề xuất sắc. Bạn hứng thú với các lĩnh vực đòi hỏi logic và sự chính xác như nghiên cứu, kỹ thuật, công nghệ.',
                'Thách thức: Chủ nghĩa hoàn hảo là con dao hai lưỡi; nó giúp bạn giỏi giang nhưng cũng có thể khiến bạn thất vọng, trầm cảm nếu không đạt chuẩn tự đặt ra. Bạn cũng có thể phân tâm bởi quá nhiều sở thích hời hợt. Sự thiếu tự tin ngầm đôi khi khiến bạn tự phá hoại thành quả của mình.',
                'dinh_huong' => [
                    'Tập trung cao độ: Rèn luyện kỷ luật, đặt ra mục đích sống rõ ràng và cống hiến trọn vẹn cho nó.',
                    'Học cách chấp nhận sự không hoàn hảo: Đây là bước tiến lớn giúp bạn trưởng thành và bớt áp lực.',
                    'Tin tưởng vào trực giác nhạy bén của mình.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người giải trí tuyệt vời trong các cuộc vui, thu hút mọi người bằng sự hóm hỉnh.',
                'Sức hút tự nhiên: Bạn hòa đồng, ấm áp và dễ dàng kết bạn. Mọi người yêu mến bạn vì sự thông minh và duyên dáng.',
                'Quý nhân: Bạn thường được những người thông minh, uyên bác và có địa vị giúp đỡ. Bạn thích kết giao với người cầu tiến, mang lại giá trị tri thức hoặc tài chính.',
                'Rủi ro: Bạn có xu hướng giấu kín cảm xúc thật, đôi khi bị coi là khó gần hoặc tính toán. Bạn cũng có thể bị lôi kéo vào rắc rối của người khác mà quên mất bản thân.',
                'chien_luoc' => [
                    'Chọn lọc bạn bè cẩn thận, đừng để lòng tốt bị lợi dụng.',
                    'Duy trì sự chân thành và giữ lời hứa để xây dựng uy tín.',
                    'Luôn biết ơn những người đã giúp đỡ mình để duy trì vận may dài lâu'
                ]
            ]
        ],
        'dinh_suu' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn khá đặc biệt và đầy chiều sâu: Một ngọn đèn dầu nhỏ bé nhưng bền bỉ cháy trong đêm đông lạnh giá, hoặc một đốm lửa ấm áp ủ trong lòng đất ẩm ướt. Bạn có vẻ ngoài trầm tĩnh, hiền lành, đôi khi hơi chậm rãi và kín đáo, nhưng bên trong lại chứa đựng một trí tuệ sắc sảo, khả năng sáng tạo độc đáo và một nội tâm phong phú.',
                'La Bàn Thịnh Vượng sẽ giúp bạn thắp sáng ngọn lửa để sưởi ấm và soi rọi con đường thành công của mình.'
            ],
            'su_nghiep' => [
                'Bạn không phải là người ồn ào, phô trương. Bạn là nhà chiến lược thầm lặng với khả năng giải quyết vấn đề xuất sắc.',
                'Tư duy đổi mới và sắc bén: Bạn sở hữu trực giác kinh doanh nhạy bén, có khả năng nhìn thấy cơ hội ở những nơi người khác bỏ qua. Bạn thích khám phá những xu hướng mới, công nghệ mới. Khả năng nhìn nhận vấn đề thấu đáo giúp bạn tìm ra những giải pháp độc đáo, đặc biệt là trong những lúc khủng hoảng, bạn có thể giữ được cái đầu lạnh và tính kỷ luật để xử lý tình huống.',
                'Tố chất lãnh đạo: Bạn có tiềm năng lớn trở thành nhà điều hành hoặc quản lý năng động trong các tập đoàn lớn hoặc tự khởi nghiệp. Bạn biết cách tạo ra môi trường làm việc tích cực, khích lệ mọi người tin vào khả năng của họ để đạt được những điều phi thường.',
                'Lĩnh vực phù hợp: Nhu cầu tự thể hiện mạnh mẽ dẫn lối bạn đến các ngành nghề liên quan đến viết lách, nghệ thuật, âm nhạc, diễn xuất, thiết kế. Đặc biệt, bạn thường có năng khiếu bẩm sinh về ẩm thực, bếp núc và có thể trở thành đầu bếp giỏi. Ngoài ra, lòng trắc ẩn cũng giúp bạn thành công trong tâm lý học, tư vấn, tôn giáo hoặc huyền học.',
                'Thách thức: Khi còn trẻ, bạn có thể thiếu một chút khát vọng và động lực mạnh mẽ so với người khác, dẫn đến những thành tựu ban đầu có thể nhỏ bé và dễ đổ vỡ. Đôi khi bạn trở nên quá độc đoán, cố chấp và tự ý khi sự quyết tâm vượt quá giới hạn, dẫn đến thất bại.',
                'chien_luoc' => [
                    'Hãy học cách duy trì sự tập trung vào một mục tiêu duy nhất, rèn luyện sự kiên trì và kiên nhẫn.',
                    'Đừng ngại đón nhận những lời phê bình mang tính xây dựng để hoàn thiện bản thân.',
                    'Khi gặp khó khăn, hãy tĩnh tâm lại và buông bỏ những thôi thúc muốn từ bỏ hoặc chọn con đường dễ dàng.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được mệnh danh là người ngồi trên kho tiền, báo hiệu tiềm năng tài chính khá lớn và bền vững.',
                'Tích lũy bền vững: Bạn có khả năng tích lũy tài sản và của cải cực tốt, đặc biệt là trong cuộc sống sau này. Khi còn trẻ, bạn thường sung túc hơn bạn bè đồng trang lứa. Bạn có duyên với sự giàu có.',
                'Nhạy bén kinh doanh: Bạn có khiếu kinh doanh bẩm sinh và thực sự tận hưởng việc kiếm tiền. Nếu kết hợp được với người hợp tuổi hoặc gặp vận tốt, tài lộc của bạn sẽ được kích hoạt mạnh mẽ, mang lại một thập kỷ tài chính xuất sắc.',
                'Rủi ro: Bạn cần cẩn trọng với những lời khuyên đầu tư không chất lượng từ người khác hoặc xu hướng chi tiêu quá mức khi ở bên cạnh họ.',
                'dinh_huong' => [
                    'Kiểm soát lòng tham, đừng để nó vượt quá giới hạn.',
                    'Việc lựa chọn đối tác kỹ lưỡng và cảnh giác với những người có thể gây tổn thất tài chính là chìa khóa để bảo vệ tài sản của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là một người tình lãng mạn, ấm áp, thân thiện và khá trân trọng các mối quan hệ thân mật.',
                'Sức hút: Bạn vui vẻ, khao khát phiêu lưu và sự hứng thú trong cuộc sống. Thái độ táo bạo và năng động giúp bạn thu hút khá nhiều người ngưỡng mộ. Bạn là người bạn đời đáng mơ ước vì sự duyên dáng, hào phóng và biết cho đi.',
                'Nội tâm phức tạp: Dù yêu thương sâu sắc, bạn lại khó bày tỏ cảm xúc thật của mình do sự mâu thuẫn nội tâm và cảm giác bị ức chế. Bạn là người không khoa trương, nhạy cảm và quan tâm sâu sắc đến phúc lợi gia đình. Bạn thường kết hôn muộn nhưng cuộc hôn nhân thường bền vững và tràn đầy yêu thương.',
                'Hôn nhân: Đối với nam giới, thường lấy được vợ đảm đang, mang lại may mắn cho chồng. Đối với phụ nữ, mối quan hệ với chồng có thể thiếu gắn bó hơn.',
                'Rủi ro: Có nguy cơ người bạn đời rời bỏ vào những khoảnh khắc tốt đẹp hoặc mâu thuẫn ngầm nếu gặp vận xấu. Mối quan hệ với con cái có thể xa cách về mặt địa lý dù tình cảm vẫn tốt đẹp.',
                'chien_luoc' => [
                    'Hãy phát triển tự nhận thức, cho phép cuộc sống diễn ra tự nhiên để giảm bớt xung đột nội tâm và bày tỏ cảm xúc dễ dàng hơn.',
                    'Tận dụng các mối quan hệ hợp tác và hỗ trợ để củng cố hạnh phúc gia đình.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn cần được chăm sóc kỹ lưỡng, đặc biệt là hệ tiêu hóa.',
                'Vấn đề tiêu hóa: Bạn có thể mắc các bệnh liên quan đến dạ dày, cần chú ý ăn uống điều độ.',
                'Năng lượng yếu: Nguy cơ gặp tai nạn hoặc tổn thất sinh lực cũng cần được lưu ý nếu gặp cấu hình xấu.',
                'dinh_huong' => [
                    'Chú trọng chế độ ăn uống lành mạnh để bảo vệ cho dạ dày.',
                    'Cẩn thận trong sinh hoạt và đi lại vào những năm vận hạn.'
                ],
                'Liệu pháp: Thiền định và tự phản tỉnh là bài học quan trọng giúp bạn cân bằng giữa sự độc lập và nhạy cảm, giảm bớt sự bồn chồn, lo lắng, từ đó cải thiện sức khỏe tổng thể.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người có trí tuệ và khát vọng kiến thức mạnh mẽ.',
                'Tư duy độc đáo: Bạn tò mò, thích khám phá những bí ẩn cuộc sống. Bạn quan tâm sâu sắc đến tâm lý học, luật tự nhiên, siêu hình học. Khả năng tư duy độc đáo giúp bạn trở thành những nhà tư tưởng khác biệt.',
                'Năng khiếu: Bạn có tài năng thiên bẩm về nghệ thuật, thủ công và khả năng đổi mới mang tính cách mạng. Bạn phát triển tốt nhất khi được thử nghiệm các ý tưởng mới.',
                'Thách thức: Đôi khi bạn thiếu sự rõ ràng trong mục tiêu, thiếu kiên nhẫn hoặc bị ảnh hưởng quá nhiều bởi môi trường và bạn bè xung quanh. Mâu thuẫn nội tâm giữa sự độc lập và nhạy cảm cũng là rào cản.',
                'dinh_huong' => [
                    'Hãy tin tưởng vào trực giác và cảm xúc của chính mình.',
                    'Duy trì sự kiên trì, kiên nhẫn và tập trung cao độ vào mục tiêu để đạt được thành công bền vững.',
                    'Tận dụng những thời điểm thuận lợi để đẩy nhanh việc học tập và phát triển.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người có khả năng xã giao bẩm sinh, thân thiện và dễ mến.',
                'Người bạn trung thành: Bạn xây dựng được mạng lưới xã hội rộng lớn và thu hút nhiều người hâm mộ. Bạn tạo ra những người bạn trung thành, đáng tin cậy, sẵn sàng giúp đỡ bạn hết mình.',
                'Tính cách độc đáo: Bạn thích kết giao với những người thông minh, sắc sảo, có tư duy mới lạ, không theo lề thói cũ. Sự trung thực và thẳng thắn của bạn khiến mọi người tin tưởng và tìm đến xin lời khuyên.',
                'Quý nhân: Bạn có thể nhận được sự hỗ trợ lớn từ những người bạn có ảnh hưởng, giàu kinh nghiệm khi gặp vận tốt. Những mối quan hệ hợp tác bền chặt sẽ mang lại thành công cho bạn.',
                'Rủi ro: Cần cảnh giác với những kẻ muốn lợi dụng lòng tốt của bạn. Đôi khi bạn có thể vô thức tự phá hoại thành công của mình bằng thái độ tiêu cực hoặc để bị ảnh hưởng bởi những lời khuyên không tốt.',
                'chien_luoc' => [
                    'Hãy duy trì tính cách cởi mở, không định kiến để thu hút những người cùng chí hướng.',
                    'Tỉnh táo phân biệt bạn bè và kẻ lợi dụng.',
                    'Cố gắng kiểm soát cảm xúc và phản ứng của mình để tránh gây căng thẳng trong các mối quan hệ.'
                ]
            ]
        ],
        'dinh_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn đẹp một cách rực rỡ và đầy mê hoặc: Một ngọn lửa cháy không gì cản nổi, hay ánh sáng lấp lánh phản chiếu từ những viên ngọc quý dưới ánh mặt trời rực rỡ. Bạn không sinh ra để làm người vô hình hay hòa lẫn vào đám đông; bạn sinh ra để tỏa sáng và dẫn lối. Bạn thông minh, sắc sảo, quyến rũ và sở hữu một nội lực cạnh tranh vô cùng mạnh mẽ.',
                'La Bàn Thịnh Vượng sẽ giúp bạn kiểm soát ngọn lửa ấy để trở thành ngọn hải đăng soi đường vững chãi thay vì ngọn lửa thiêu rụi mọi thứ.'
            ],
            'su_nghiep' => [
                'Ẩn sau vẻ ngoài hào nhoáng, bạn là một nhà lãnh đạo bẩm sinh với tư duy độc bản.',
                'Tư duy khác biệt: Bạn là người có tư duy nguyên bản, không bao giờ chấp nhận những lối mòn cũ kỹ. Trong công việc, bạn luôn nhìn thấy những góc độ mà người khác bỏ qua. Bạn có khả năng truyền cảm hứng tuyệt vời, biết cách sử dụng ngôn từ và phong thái tự tin để thuyết phục người khác đi theo tầm nhìn của mình.',
                'Tố chất thủ lĩnh: Bạn thích hợp với vai trò người đứng đầu, người cầm trịch. Bạn làm việc với cường độ cao và luôn đặt ra những tiêu chuẩn khắt khe cho bản thân cũng như đội ngũ. Bạn tỏa sáng rực rỡ nhất ở các vị trí quản lý cấp cao, điều hành doanh nghiệp hoặc người sáng lập.',
                'Lĩnh vực phù hợp: Với khả năng giao tiếp sắc sảo và sức hút tự nhiên, bạn là ngôi sao trong ngành truyền thông, marketing, diễn giả hoặc quan hệ công chúng. Ngoài ra, sự hiện diện của tư duy thực tế giúp bạn thành công trong kinh doanh, đầu tư hoặc cố vấn chiến lược.',
                'Thách thức: Rủi ro lớn nhất của bạn là sự cả thèm chóng chán. Bạn khao khát sự mới mẻ và có thể mất lửa nếu công việc lặp lại. Sự tự tin thái quá đôi khi biến thành sự độc đoán, áp đặt, khiến bạn có thể mất kiên nhẫn với những cộng sự không theo kịp tốc độ của mình.',
                'chien_luoc' => [
                    'Hãy tìm kiếm môi trường làm việc năng động, cho phép bạn di chuyển và đổi mới liên tục.',
                    'Rèn luyện sự kiên định: Khi bắt đầu một dự án, hãy cam kết đi đến cùng dù hứng thú ban đầu đã giảm.',
                    'Lãnh đạo bằng sự thấu cảm: Học cách lắng nghe và trao quyền thay vì chỉ ra lệnh và kiểm soát.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là thước đo quan trọng cho sự thành công của bạn. Bạn có tư duy làm giàu thực tế và sắc bén.',
                'Tư duy tài chính: Bạn không mơ mộng hão huyền. Bạn có khả năng tạo ra nguồn thu nhập bền vững và có duyên với việc tích lũy tài sản. Bạn có con mắt tinh đời để nhìn ra giá trị thương mại của một thương vụ mà người khác bỏ qua. Dù làm nghệ thuật hay kỹ thuật, bạn đều biết cách bán tài năng của mình với giá cao nhất.',
                'Phong cách sống: Bạn yêu thích sự xa hoa và những trải nghiệm sang trọng. Đối với bạn, tiền bạc là phương tiện để khẳng định vị thế và tận hưởng cuộc sống.',
                'Rủi ro: Mối nguy lớn nhất nằm ở sự chi tiêu theo cảm xúc. Khi vui hoặc buồn, bạn đều có xu hướng mua sắm những món đồ hào nhoáng để giải tỏa. Ngoài ra, bạn có thể tin tưởng nhầm chỗ, hào phóng quá mức với bạn bè hoặc đối tác dẫn đến thất thoát tiền bạc, dễ rơi vào cảnh tin bạn mất bò.',
                'dinh_huong' => [
                    'Quản lý dòng tiền chặt chẽ, đừng để tiền nằm im mà hãy chuyển thành các kênh đầu tư hoặc tài sản cố định có giá trị gia tăng.',
                    'Thực hiện nguyên tắc Tiền bạc phân minh, hạn chế hùn hạp với bạn bè thân thiết nếu thiếu giấy tờ pháp lý.',
                    'Đầu tư vào giá trị thực, tránh xa các mô hình làm giàu nhanh chóng nhưng rỗng tuếch.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn như một ngọn lửa ấm áp, nồng nàn, quyến rũ nhưng cũng đầy tính chiếm hữu.',
                'Sự nồng nhiệt: Bạn có sức hút giới tính tự nhiên, dễ dàng chinh phục người khác phái bằng sự duyên dáng và hoạt ngôn. Khi yêu, bạn lãng mạn, hào phóng và muốn dành cho đối phương những điều tốt đẹp nhất. Bạn muốn một mối quan hệ mà ở đó cả hai cùng tỏa sáng, xứng đôi vừa lứa.',
                'Tiêu chuẩn chọn bạn đời: Bạn thường bị thu hút bởi những người tài giỏi, có địa vị, ngoại hình nổi bật hoặc những người có thể mang lại niềm tự hào cho bạn khi sánh bước cùng nhau.',
                'Thách thức: Tính chiếm hữu và kiểm soát là gót chân Achilles của bạn. Vì yêu nhiều và nỗi bất an ngầm, bạn thường muốn kiểm soát mọi hoạt động của đối phương. Cảm xúc của bạn đôi khi thất thường, lúc nóng như lửa, lúc lạnh lùng xa cách khiến người bạn đời cảm thấy ngột ngạt.',
                'chien_luoc' => [
                    'Hãy học cách tôn trọng khoảng cách, hiểu rằng tình yêu bền vững cần có sự tự do cá nhân.',
                    'Giao tiếp cởi mở và thẳng thắn thay vì suy diễn ghen tuông.',
                    'Tìm người bạn đời có tính cách điềm đạm, bao dung để dung hòa ngọn lửa trong bạn.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn giống như ngọn đèn dầu: Cháy khá sáng nhưng cũng tiêu hao nhiên liệu khá nhanh.',
                'Với nguồn năng lượng như thiêu đốt, bạn cần chú ý hệ tim mạch, huyết áp và thần kinh. Sự nóng nảy và căng thẳng kéo dài là kẻ thù số một của trái tim bạn.',
                'Vấn đề thần kinh: Do não bộ hoạt động liên tục và hay lo âu, bạn dễ gặp các vấn đề về mất ngủ, đau nửa đầu hoặc suy nhược thần kinh. Thị lực cũng là điều bạn cần lưu tâm.',
                'Cách thức cân bằng: Hãy uống đủ nước, ăn thực phẩm có tính hàn và thường xuyên đi bơi hoặc tắm thư giãn.',
                'Liệu pháp: Thiền định và các bài tập hít thở sâu là phương pháp tốt nhất để bạn bình ổn tâm trí, hạ nhiệt những cơn nóng giận bốc đồng. Hãy nghỉ ngơi chủ động trước khi cơ thể kiệt quệ.'
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí thông minh sắc sảo và khả năng học hỏi qua quan sát cực tốt.',
                'Trí tuệ thực chiến: Bạn không cần phải dùi mài kinh sử theo cách truyền thống. Bạn học nhanh nhất qua trải nghiệm thực tế, qua va chạm và tương tác xã hội. Trực giác mạnh mẽ giúp bạn thấu hiểu tâm lý con người một cách sâu sắc.',
                'Thách thức: Sự thiếu tập trung và cái tôi lớn là rào cản phát triển của bạn. Bạn biết nhiều nhưng thường chỉ ở bề mặt. Khi gặp khó khăn, bạn dễ dàng bỏ cuộc để tìm cái mới thú vị hơn. Bạn cũng khó chấp nhận việc mình sai để học hỏi từ người khác.',
                'dinh_huong' => [
                    'Hãy chọn một lĩnh vực đam mê nhất và cam kết đi chuyên sâu vào nó.',
                    'Học cách khiêm tốn và lắng nghe.',
                    'Hãy nhớ câu Tam nhân hành, tất hữu ngã sư nghĩa là trong ba người đi cùng, ắt có người là thầy ta.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao của các buổi tiệc, luôn là tâm điểm của sự chú ý.',
                'Mạng lưới quan hệ: Bạn hoạt ngôn, hài hước và biết cách làm chủ cuộc trò chuyện. Bạn dễ dàng kết bạn với mọi tầng lớp xã hội và xây dựng được mạng lưới quan hệ rộng lớn.',
                'Quý nhân: Những người có quyền lực, địa vị hoặc trí tuệ thường bị thu hút bởi năng lượng của bạn và sẵn sàng nâng đỡ bạn.',
                'Rủi ro: Sự nổi bật quá mức của bạn có thể gây ra sự đố kỵ. Tiểu nhân hoặc những kẻ hay soi mói có thể tìm cách hạ bệ bạn. Bạn cũng cần cẩn trọng với những lời nịnh nọt thiếu chân thành.',
                'chien_luoc' => [
                    'Hãy chân thành hơn trong các mối quan hệ, đừng chỉ coi đó là công cụ tiến thân.',
                    'Sự khiêm tốn là tấm khiên bảo vệ tốt nhất cho bạn trước thị phi.',
                    'Hãy trân trọng những người bạn dám nói thẳng sự thật với bạn thay vì chỉ những người tâng bốc bạn.'
                ]
            ]
        ],
        'giap_dan' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là “cây cổ thụ khổng lồ” vững chãi nhất, cứng cỏi nhất trong rừng già. Bạn mang hình ảnh của một chú hổ dũng mãnh ẩn mình dưới tán cây. Lá số bạn tạo nên một nguồn năng lượng cực thịnh, tượng trưng cho sự sinh trưởng mạnh mẽ, tính cạnh tranh và ý chí không gì lay chuyển nổi.',
                'La Bàn Thịnh Vượng sẽ giúp bạn kiểm soát sức mạnh của “mãnh hổ” để kiến tạo đại nghiệp.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra để làm lãnh đạo. Bạn sở hữu năng lượng của người dẫn đầu bẩm sinh. Bạn phát huy tối đa tiềm năng khi ở vị trí lãnh đạo hoặc có quyền tự quyết cao, thay vì chỉ đơn thuần thực hiện mệnh lệnh.',
                'Tố chất thủ lĩnh: Bạn có đầy đủ kỹ năng của một nhà lãnh đạo tài ba. Bạn đầy tham vọng, sức sống, trực giác và khả năng quản lý con người. Bạn có tầm nhìn xa trông rộng, luôn nhìn vào bức tranh toàn cảnh thay vì những chi tiết vụn vặt. Bạn khao khát thành công và sẵn sàng làm việc chăm chỉ để đạt được nó.',
                'Chiến lược gia đại tài: Bạn là một nhà chiến lược xuất sắc. Bạn biết cách lập kế hoạch dài hạn và giải quyết vấn đề một cách thông minh. Trí tưởng tượng phong phú giúp bạn tìm ra những con đường mới mà người khác không thấy.',
                'Độc lập và tự chủ: Bạn ghét bị ra lệnh. Bạn làm việc tốt nhất khi tự làm chủ hoặc điều hành doanh nghiệp riêng. Bạn muốn để lại dấu ấn cá nhân trong mọi việc mình làm.',
                'Lĩnh vực phù hợp: Với khả năng giao tiếp và thuyết phục tuyệt vời, bạn có thể tỏa sáng trong truyền thông, bán hàng, xuất bản. Với tư duy sâu sắc, bạn hợp với giáo dục, triết học, chính trị. Với sự sáng tạo, bạn có thể làm nghệ thuật, giải trí.',
                'Thách thức: Bạn cực kỳ kiên định và có chính kiến mạnh mẽ. Đôi khi, sự tập trung cao độ vào tầm nhìn cá nhân khiến bạn gặp khó khăn trong việc thỏa hiệp hoặc chưa cân nhắc đầy đủ đến quan điểm của người khác. Phương pháp tiếp cận trực diện của bạn đôi khi quá gay gắt, gây mất lòng người khác.',
                'chien_luoc' => [
                    'Hãy học nghệ thuật đàm phán và nhượng bộ.',
                    'Một nhà lãnh đạo giỏi không chỉ biết ra lệnh mà còn biết lắng nghe.',
                    'Hãy tin tưởng vào trực giác nhưng đừng để cái tôi che mờ lý trí.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có khả năng kiếm tiền khá tốt nhờ trí tuệ và bản lĩnh, nhưng tiền bạc không phải là thứ duy nhất bạn theo đuổi.',
                'Năng lực kiếm tiền: Bạn có kỹ năng kinh doanh sắc sảo và khả năng thuyết phục thiên bẩm. Bạn biết cách thương mại hóa các kỹ năng của mình để mang về những thỏa thuận giá trị. Nếu làm chủ, bạn biết cách chèo lái doanh nghiệp phát triển thịnh vượng.',
                'Động lực phi vật chất: Điều thú vị là bạn không bị thúc đẩy bởi tiền bạc hay quyền lực đơn thuần. Bạn kiếm tiền một cách đạo đức, dựa trên sự chính trực. Điều này giúp tài sản của bạn bền vững và an toàn.',
                'May mắn: Vận may thường mỉm cười với bạn, mở ra nhiều cơ hội kinh doanh tốt. Bạn là nhân viên mang lại may mắn cho công ty, hoặc là người chủ mang lại thịnh vượng cho tổ chức.',
                'Rủi ro: Bạn có máu liều và thích mạo hiểm. Đôi khi sự thiếu kiên nhẫn khiến bạn đưa ra những quyết định đầu tư chớp nhoáng, rủi ro cao. Vì đặt kỳ vọng cao vào bản thân, những thất bại tài chính có thể ảnh hưởng sâu sắc đến tinh thần của bạn.',
                'dinh_huong' => [
                    'Hãy dùng sự kiên định để theo đuổi các mục tiêu tài chính dài hạn.',
                    'Tìm kiếm những lời khuyên từ chuyên gia hoặc thiết lập các cấu trúc đầu tư ổn định để kìm hãm sự bốc đồng.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người lãng mạn, mãnh liệt nhưng cũng đầy mâu thuẫn.',
                'Tận tụy và lý tưởng: Bạn trung thực, chung thủy và đặt kỳ vọng khá cao vào tình yêu. Bạn yêu ai là yêu hết mình, sẵn sàng làm mọi thứ vì người đó. Bạn coi trọng gia đình và gắn bó sâu sắc với người thân.',
                'Tính cách hai mặt: Bạn khá thất thường. Lúc thì âu yếm, nồng nhiệt, lúc lại thờ ơ, lạnh lùng thu mình lại. Sự thay đổi tâm trạng này khiến đối phương đôi khi cảm thấy hoang mang, bối rối và khó nắm bắt tâm lý của bạn.',
                'Độc lập và nghi ngờ: Dù yêu sâu đậm, bạn vẫn cần sự tự do tuyệt đối. Bạn hay hoài nghi và ghen tuông. Niềm tin là nền tảng cốt lõi. Nếu để sự hoài nghi lấn át, nó có thể bào mòn và gây rạn nứt nghiêm trọng cho mối quan hệ.',
                'Nam: Thường có xu hướng nể vợ hoặc sợ vợ một chút.',
                'Nữ: Mạnh mẽ, có khả năng kiểm soát chồng.',
                'chien_luoc' => [
                    'Nên kết hôn muộn để chín chắn hơn.',
                    'Hãy tìm một người bạn đời thông minh, độc lập, hiểu và tôn trọng nhu cầu tự do của bạn.',
                    'Hãy học cách tin tưởng và chia sẻ thẳng thắn thay vì giữ sự nghi ngờ trong lòng.'
                ]
            ],
            'suc_khoe' => [
                'Bạn sở hữu sức sống dồi dào của một cây cổ thụ, nhưng tâm bệnh là điều đáng lo ngại.',
                'Sức sống mãnh liệt: Bạn năng động, dũng cảm và luôn tràn đầy nhiệt huyết. Bạn có sự ổn định và ấm áp hơn so với người khác.',
                'Trầm cảm do thất bại: Điểm yếu lớn nhất là tinh thần. Bạn cực kỳ sợ thất bại và chỉ trích. Một cú vấp ngã có thể khiến tinh thần bạn suy giảm nghiêm trọng hoặc mất đi động lực trong một khoảng thời gian. Tuy nhiên, bạn hồi phục khá nhanh.',
                'Cảm xúc chi phối: Bạn có thể bị cảm xúc dẫn dắt, dễ nổi nóng, cáu kỉnh khi đối mặt với các vấn đề dai dẳng. Điều này làm bạn mắc kẹt trong nghi ngờ và lo lắng gây căng thẳng thần kinh mãn tính.',
                'dinh_huong' => [
                    'Bạn cần giải phóng năng lượng dư thừa.',
                    'Hãy tham gia các môn thể thao mạo hiểm, hoạt động nghệ thuật hoặc sáng tạo để giải tỏa năng lượng.'
                ],
                'Tư duy khách quan: Học cách nhìn nhận vấn đề một cách khách quan để không bị cảm xúc cuốn đi. Hãy cho phép bản thân có thời gian nghỉ ngơi, tự chữa lành sau mỗi thất bại để trở lại mạnh mẽ hơn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người khao khát tri thức và có trí tuệ sắc bén.',
                'Học hỏi không ngừng: Bạn thông minh, chu đáo và luôn theo đuổi kiến thức mới. Bạn có khả năng nghiên cứu sâu trong các lĩnh vực triết học, khoa học.',
                'Trực giác nhạy bén: Bạn có giác quan thứ sáu cực tốt, giúp bạn hiểu người và giải quyết vấn đề hóc búa. Hãy tin tưởng vào bản năng của mình.',
                'Thách thức: Sự bướng bỉnh. Khi bạn tin mình đúng, không ai có thể lay chuyển bạn. Bạn thiếu kiên nhẫn với những vấn đề dai dẳng. Bạn cũng khao khát sự chú ý và không thích bị phớt lờ.',
                'dinh_huong' => [
                    'Bài học lớn nhất của bạn là học cách thỏa hiệp.',
                    'Sẵn sàng đàm phán sẽ giúp bạn tiến xa hơn.',
                    'Hãy chuyển hóa năng lượng tranh đấu vào các cuộc tranh luận trí tuệ thay vì xung đột cá nhân.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao sáng trong các mối quan hệ xã hội, cực kỳ nổi tiếng và được yêu mến.',
                'Hòa đồng và quyến rũ: Bạn thân thiện, thú vị và luôn là tâm điểm của sự chú ý. Bạn coi trọng tình bạn và sống khá nghĩa khí.',
                'Sức mạnh thuyết phục: Bạn có khả năng thu hút và thuyết phục người khác một cách tinh tế. Bạn là người nhân đạo, sẵn sàng cống hiến cho cộng đồng, điều này giúp bạn thu hút nhiều quý nhân giúp đỡ trong đời.',
                'Cái tôi lớn và sự cố chấp: Dù có thể tha thứ nhưng bạn hay có xu hướng ghi nhớ những tổn thương. Bạn không bao giờ quên những ai đã xúc phạm mình. Sự thẳng thắn thiếu ngoại giao của bạn đôi khi làm tổn thương người khác.',
                'chien_luoc' => [
                    'Hãy duy trì sự trung thực và đạo đức, đó là nam châm hút quý nhân của bạn.',
                    'Kiểm soát cái tôi, học cách buông bỏ hận thù để tâm hồn thanh thản.',
                    'Hãy dùng sức ảnh hưởng của mình để dẫn dắt các hoạt động cộng đồng, bạn sẽ nhận được sự chú ý tích cực mà bạn khao khát.'
                ]
            ]
        ],
        'giap_thin' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một cây đại thụ hùng vĩ, xanh tốt đứng vững chãi trên sườn đồi. Bạn không phải là loài cây yếu ớt mọc trong nhà kính, mà là loài cây đã trải qua mưa nắng để bám rễ sâu vào lòng đất, vươn mình chạm tới bầu trời. Bạn sở hữu tầm nhìn xa trông rộng, tinh thần tiên phong và một tham vọng không giới hạn. Bạn sinh ra để làm những điều lớn lao.',
                'La Bàn Thịnh Vượng sẽ giúp bạn định hướng để “cây đại thụ” của mình ngày càng vững chãi và tỏa bóng mát.'
            ],
            'su_nghiep' => [
                'Bạn mang trong mình dòng máu của người tiên phong. Bạn không chấp nhận sự tầm thường hay lặp lại.',
                'Tầm nhìn và sáng tạo: Bạn có khả năng tư duy “bên ngoài chiếc hộp”. Trí tưởng tượng phong phú giúp bạn nhìn thấy cơ hội ở những nơi người khác chỉ thấy khó khăn. Bạn là người đưa ra những ý tưởng đột phá, những giải pháp mới mẻ làm thay đổi cục diện.',
                'Tinh thần cầu toàn: Khi đã nhận nhiệm vụ, bạn sẽ làm bằng tất cả tâm huyết. Bạn không chấp nhận kết quả “tạm được” mà bạn luôn nỗ lực để mọi thứ đạt tiêu chuẩn cao nhất có thể.',
                'Phong cách lãnh đạo: Bạn phù hợp nhất với vị trí người đứng đầu. Bạn cần không gian và quyền lực để hiện thực hóa những ý tưởng táo bạo của mình. Tuy nhiên, bạn không phải là người sếp độc tài. Ngược lại, bạn khéo léo, giỏi ngoại giao và biết cách thuyết phục lòng người.',
                'Lĩnh vực phù hợp: Bạn tỏa sáng ở những nơi bạn được “đứng dưới ánh đèn sân khấu”. Nghệ thuật, âm nhạc, quan hệ công chúng, quảng cáo, tư vấn hoặc quản lý cấp cao là những mảnh đất màu mỡ dành cho bạn.',
                'Thách thức: Đôi khi bạn quá tập trung vào công việc đến mức quên mất mọi thứ xung quanh. Bạn có thể bị kiệt sức vì tự đặt áp lực quá lớn lên bản thân.',
                'chien_luoc' => [
                    'Hãy xây dựng một đội ngũ cố vấn tin cậy xung quanh mình.',
                    'Dù tài giỏi đến đâu, bạn cũng cần những người hỗ trợ để xử lý các vấn đề chi tiết, giúp bạn tập trung vào bức tranh lớn.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn gắn liền với sự kiên trì và nỗ lực không ngừng nghỉ. Bạn được xem là người may mắn về tài lộc, nhưng may mắn đó đến từ sự chuẩn bị kỹ càng.',
                'Sự kiên trì tạo ra của cải: Bạn giống như cái cây không ngừng vươn lên. Dù gặp bao nhiêu thất bại, bạn vẫn đứng dậy và đi tiếp. Chính ý chí kiên cường này đảm bảo cho bạn sự thành công về tài chính trong bất kỳ lĩnh vực nào.',
                'Sức hút kinh doanh: Bạn có nhân cách cuốn hút, khiến người khác khó lòng từ chối các đề nghị hợp tác của bạn. Khả năng gọi vốn hoặc tìm kiếm đối tác là thế mạnh tuyệt vời giúp bạn xây dựng cơ đồ.',
                'Hậu vận sung túc: Cuộc sống ban đầu của bạn có thể gặp nhiều tranh đấu, nhưng càng về sau, bạn càng trưởng thành và thịnh vượng. Nền tảng hệ thống gốc rễ càng vững, cây càng to lớn.',
                'Rủi ro: Bạn hào phóng và đôi khi tiêu xài hơi phô trương. Bạn cũng có thể gặp sự cạnh tranh gay gắt từ đồng nghiệp hoặc đối thủ. Sự cạnh tranh này có thể làm hao hụt nguồn lực của bạn nếu không cẩn trọng.',
                'dinh_huong' => [
                    'Hãy đầu tư vào tri thức và xây dựng thương hiệu cá nhân, đó là những tài sản không ai lấy đi được.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người quyến rũ, hào phóng nhưng lại khá thận trọng khi trao gửi con tim.',
                'Sức hút khó cưỡng: Bạn có vẻ ngoài thu hút và phong thái tự tin khiến người khác giới dễ dàng bị chinh phục. Tuy nhiên, bạn không tin vào “tình yêu sét đánh”. Bạn cần thời gian để tìm hiểu và xây dựng lòng tin.',
                'Sự kén chọn: Bạn đặt tiêu chuẩn khá cao cho người bạn đời. Bạn luôn cảm thấy “người tốt nhất vẫn chưa xuất hiện”, điều này khiến bạn thường kết hôn muộn hoặc do dự trong việc cam kết lâu dài. Sự thận trọng và chần chừ của bạn đôi khi có thể vô tình gây ra sự thất vọng hoặc tổn thương sâu sắc cho những người quan tâm đến bạn.',
                'Khi đã yêu: Một khi đã chọn được người xứng đáng, bạn là người yêu tuyệt vời: lãng mạn, ân cần và hết lòng hỗ trợ đối phương. Hôn nhân của bạn thường khá bền vững vì nó được xây dựng trên nền tảng của sự lựa chọn kỹ càng.',
                'Thách thức: Cái tôi của bạn khá lớn. Bạn ngại nhận sự giúp đỡ từ người yêu vì sợ mất đi sự độc lập. Đôi khi cơn nóng giận bất chợt của bạn có thể làm tổn thương đối phương.',
                'chien_luoc' => [
                    'Hãy tìm một người bạn đời dũng cảm, kiên định, người dám đứng lên tranh luận với bạn và cũng đủ bao dung để thấu hiểu bạn.',
                    'Học cách hạ bớt cái tôi, chấp nhận dựa vào người khác không phải là yếu đuối mà là sự gắn kết.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chủ yếu bị đe dọa bởi sự tham công tiếc việc và những cảm xúc dồn nén.',
                'Nguy cơ kiệt sức: Bạn là người cầu toàn, luôn muốn hoàn thành công việc bằng mọi giá. Việc làm việc cật lực không nghỉ ngơi sẽ bào mòn cả thể chất lẫn tinh thần của bạn, giống như cái cây bị vắt kiệt nhựa sống.',
                'Cảm xúc u sầu: Bạn có thể mắc phải những cơn u sầu, buồn bã vô cớ nếu không tìm được lối thoát cho cảm xúc.',
                'Cách thức cân bằng: Bạn cần học cách nghỉ ngơi. Hãy đặt ra giới hạn rõ ràng giữa công việc và đời sống cá nhân.',
                'Liệu pháp: Hãy tìm kiếm những hoạt động giải trí sáng tạo, linh hoạt để giải tỏa căng thẳng. Đừng ngại chia sẻ gánh nặng công việc với người khác. Sự hỗ trợ từ cộng đồng là liều thuốc bổ cho tinh thần của bạn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người có khát vọng tri thức mãnh liệt và luôn muốn vươn lên những tầm cao mới về trí tuệ.',
                'Đam mê học hỏi: Bạn tò mò về mọi thứ. Bạn thích trải nghiệm những điều mới lạ và không ngừng trau dồi bản thân. Bạn có xu hướng tìm hiểu sâu về triết học, tâm linh hoặc những kiến thức siêu hình để làm giàu thế giới nội tâm.',
                'Học từ sai lầm: Bạn có tinh thần trách nhiệm cao. Bạn không trốn tránh thất bại mà xem đó là bài học để trưởng thành. Mỗi chướng ngại vật đều là một nấc thang để bạn bước lên cao hơn.',
                'Thách thức: Bạn khó chấp nhận lời chỉ trích. Cái tôi lớn khiến bạn đôi khi bỏ ngoài tai những lời khuyên hữu ích. Bạn cũng có thể bị phân tâm do đầu óc luôn bận rộn với quá nhiều ý tưởng.',
                'dinh_huong' => [
                    'Hãy mở lòng đón nhận những phản hồi trái chiều.',
                    'Lắng nghe những lời phê bình mang tính xây dựng sẽ giúp bạn hoàn thiện bản thân nhanh hơn.',
                    'Hãy phát triển trực giác nhạy bén của mình, đó là la bàn dẫn đường chính xác nhất cho bạn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hướng ngoại, đi đến đâu cũng tỏa ra sức hút của một nhà lãnh đạo hoặc một người có tầm ảnh hưởng.',
                'Nhà ngoại giao tài ba: Bạn có khả năng kết nối mọi người và giải quyết xung đột một cách êm đẹp. Bạn dễ dàng hòa nhập vào nhiều nhóm xã hội khác nhau. Trực giác tốt giúp bạn đọc vị tính cách người khác khá nhanh.',
                'Tinh thần nhân đạo: Bạn không thể đứng nhìn sự bất công. Bạn sẵn sàng đứng lên bảo vệ kẻ yếu hoặc tham gia các hoạt động thiện nguyện. Điều này giúp bạn nhận được sự kính trọng và yêu mến từ cộng đồng.',
                'Rủi ro: Đôi khi bạn có thể trở nên kiêu ngạo hoặc hống hách mà không tự biết. Bạn khá lý trí, có xu hướng chia sẻ ý kiến trước khi chia sẻ cảm xúc, khiến người khác cảm thấy bạn hơi lạnh lùng.',
                'chien_luoc' => [
                    'Hãy giữ thái độ khiêm tốn.',
                    'Sự chân thành và lòng tốt của bạn chính là nam châm hút những quý nhân thực sự đến với cuộc đời bạn.'
                ]
            ]
        ],
        'giap_tuat' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một cái cây kiên cường bám rễ và sinh trưởng trên vùng đất khô cằn hoặc núi đá. Điều kiện sống không thuận lợi không làm bạn gục ngã, trái lại, nó tôi luyện cho bạn một ý chí sắt đá, sự bền bỉ và khả năng sinh tồn mãnh liệt. Bạn độc lập, thực tế, thẳng thắn và mang trong mình một trái tim nhân hậu.',
                'La Bàn Thịnh Vượng sẽ giúp bạn vững vàng bám rễ và vươn cao.'
            ],
            'su_nghiep' => [
                'Bạn là hình mẫu của sự nỗ lực tự thân. Bạn không chờ đợi may mắn, bạn tự tạo ra nó.',
                'Ý chí và độc lập: Bạn có lòng quyết tâm cao độ. Bạn thích tự mình làm mọi việc và không muốn dựa dẫm vào ai. Bạn chủ động tìm kiếm cơ hội và sẵn sàng đối mặt với thử thách mới. Tinh thần dám nghĩ dám làm giúp bạn đạt được những thành tựu đáng nể.',
                'Người cầu toàn: Bạn làm việc khá kỹ lưỡng và có trách nhiệm. Bạn tự hào về những gì mình làm ra. Khả năng tư duy logic và chiến lược giúp bạn giải quyết công việc nhanh chóng và hiệu quả.',
                'Đa năng: Bạn có máu kinh doanh, khả năng đàm phán tốt và tư duy sáng tạo. Bạn phù hợp với nhiều lĩnh vực: kinh doanh, quản lý, cố vấn, nghệ thuật, giáo dục hoặc các hoạt động xã hội nhân đạo.',
                'Thách thức: Bạn khá thẳng thắn, đôi khi bộc trực quá mức khiến người khác phật ý. Sự kiên định cao độ cũng có thể biến thành cố chấp, khiến bạn khó linh hoạt khi cần thỏa hiệp.',
                'chien_luoc' => [
                    'Hãy học cách lắng nghe và mềm mỏng hơn trong giao tiếp.',
                    'Sự nghiệp của bạn sẽ thuận lợi hơn nếu bạn biết kết hợp giữa ý chí cá nhân và sức mạnh tập thể.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có tiềm năng tài chính khá lớn, đặc biệt là về hậu vận.',
                'Tích tiểu thành đại: Bạn biết cách tiết kiệm và lo xa cho tương lai. Bạn không làm giàu bằng những trò may rủi mà bằng sự chăm chỉ và tích lũy bền bỉ. Sự quyết tâm giúp bạn kiếm tiền giỏi và quản lý tài chính cũng rất chặt chẽ.',
                'Hành trình tài chính: Tuổi trẻ có thể bạn sẽ gặp khó khăn, thiếu thốn như cây trên đất nghèo dinh dưỡng. Trung vận vẫn còn nhiều thử thách. Nhưng đến hậu vận, bạn sẽ được hưởng thành quả rực rỡ, sung túc và thịnh vượng.',
                'Nếu là phụ nữ, bạn thường được xem là “tay hòm chìa khóa” xuất sắc, mang lại sự thịnh vượng cho gia đình.',
                'Rủi ro: Xu hướng lo xa đôi khi khiến bạn quá chặt chẽ trong chi tiêu, thậm chí quên chăm sóc bản thân. Ngược lại, cũng có lúc bạn chi tiêu bốc đồng để giải tỏa cảm xúc.',
                'dinh_huong' => [
                    'Hãy chọn con đường đầu tư an toàn, chắc chắn.',
                    'Tránh những đường tắt hay làm giàu nhanh.',
                    'Sự ổn định là chìa khóa cho tài sản của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người chân thành, tận tụy nhưng cũng đòi hỏi sự tự do khá lớn.',
                'Yêu thương sâu sắc: Bạn không hời hợt trong tình cảm. Khi yêu, bạn dành trọn tâm trí và sự quan tâm cho đối phương. Bạn luôn cố gắng giữ gìn sự hòa hợp và êm ấm trong gia đình.',
                'Nhu cầu tự do: Dù yêu sâu đậm, bạn không phải là người thích ru rú ở nhà. Bạn cần không gian xã hội, cần được giao lưu và thể hiện bản thân. Nếu bị kìm kẹp, bạn sẽ trở nên khó chịu và muất thoát ra.',
                'Sự thận trọng: Bạn thường do dự và suy nghĩ khá kỹ trước khi cam kết lâu dài. Bạn sợ chọn sai người. Nhưng một khi đã chọn, bạn sẽ khá chung thủy.',
                'Nam: Thích phụ nữ mạnh mẽ, có tinh thần chiến đấu.',
                'Nữ: Đảm đang, vượng phu ích tử, nhưng nên có sự nghiệp riêng thay vì làm chung với chồng để tránh xung đột.',
                'chien_luoc' => [
                    'Hãy tìm một người bạn đời hiểu và tôn trọng sự độc lập của bạn.',
                    'Cân bằng giữa cái tôi cá nhân và trách nhiệm gia đình là bài học quan trọng nhất để giữ gìn hạnh phúc.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn liên quan nhiều đến yếu tố tinh thần và sự ổn định nội tâm.',
                'Nhạy cảm và căng thẳng: Bạn có tâm hồn cực kỳ nhạy cảm. Bạn có thể bị ảnh hưởng bởi lời nói của người khác và dễ nản lòng. Việc dồn nén cảm xúc có thể dẫn đến tình trạng căng thẳng hoặc suy nhược tinh thần.',
                'Vấn đề thể chất: Chú ý các bệnh về dạ dày, tiêu hóa và gan, mật.',
                'dinh_huong' => [
                    'Bạn cần sự ổn định.',
                    'Hãy tránh xa những môi trường độc hại.'
                ],
                'Liệu pháp: Đi du lịch, khám phá những vùng đất mới là cách tốt nhất để bạn giải tỏa căng thẳng và nạp lại năng lượng. Bạn cần yếu tố nước để tưới mát tâm hồn và niềm vui để sưởi ấm tinh thần.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người ham học hỏi, tò mò và có tư duy sâu sắc.',
                'Khát khao tri thức: Bạn thích khám phá những ý tưởng mới, những lĩnh vực lạ lẫm như tâm linh, triết học hay siêu hình học. Bạn có khả năng tự học và nắm bắt các lý thuyết phức tạp khá tốt.',
                'Học từ trải nghiệm: Những chuyến đi, những cuộc gặp gỡ với người lạ mang lại cho bạn nhiều bài học hơn là sách vở. Bạn trưởng thành qua sự va chạm với thực tế.',
                'Thách thức: Sự bướng bỉnh và cái tôi lớn ngăn cản bạn tiếp thu lời khuyên. Nếu sinh vào ban ngày, bạn cũng có thể bị mơ mộng viển vông hoặc thiếu quyết đoán.',
                'dinh_huong' => [
                    'Hãy rèn luyện sự khiêm tốn.',
                    'Học cách lắng nghe những lời phê bình xây dựng.',
                    'Đặt ra mục tiêu thực tế và kiên định theo đuổi nó đến cùng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hướng ngoại, nhân hậu và được nhiều người yêu mến.',
                'Sức hút xã hội: Bạn thân thiện, dễ mến và có khiếu hài hước. Bạn thích làm cho người khác vui vẻ. Bạn dễ dàng hòa nhập vào nhiều nhóm người khác nhau.',
                'Tấm lòng nhân ái: Bạn không thể làm ngơ trước sự bất công hay nỗi đau của người khác. Bạn sẵn sàng đứng lên bảo vệ kẻ yếu và tham gia các hoạt động thiện nguyện. Điều này giúp bạn tích được nhiều phước đức và thu hút quý nhân.',
                'chien_luoc' => [
                    'Hãy xây dựng sự tự tin từ bên trong.',
                    'Đừng để cảm xúc của người khác chi phối cuộc đời bạn.',
                    'Hãy cứ sống tốt, sống chân thành, những người xứng đáng sẽ tự tìm đến và ở lại bên bạn.'
                ]
            ]
        ],
        'giap_ngo' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn rực rỡ và đầy sức sống: Một cây gỗ lớn đang bùng cháy để tỏa sáng, hay một cái cây vững chãi dưới ánh mặt trời chói chang. Bạn mang bản chất của sự cống hiến, hy sinh bản thân để tạo ra ánh sáng, tri thức và giá trị cho đời. Bạn thông minh, sắc sảo, tiến bộ và luôn khao khát tự do.',
                'La Bàn Thịnh Vượng sẽ giúp bạn giữ cho ngọn lửa nhiệt huyết luôn cháy sáng mà không thiêu rụi chính mình.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tư duy đột phá và khả năng lãnh đạo bẩm sinh. Bạn là người đi trước thời đại.',
                'Tư duy đổi mới: Bạn có những ý tưởng khá sáng tạo, tiến bộ, thậm chí là khác biệt hoàn toàn với số đông. Bạn ghét sự rập khuôn, cũ kỹ. Tư duy của bạn kết hợp với kỹ năng tổ chức tốt giúp bạn tạo ra những thành tựu đáng nể.',
                'Kỹ năng giao tiếp đỉnh cao: Bạn có tài ăn nói và viết lách tuyệt vời. Bạn biết cách truyền đạt thông tin một cách thú vị và lôi cuốn. Khả năng thuyết phục và ngoại giao giúp bạn trở thành ngôi sao trong các lĩnh vực như bán hàng, truyền thông, diễn thuyết hay nghệ thuật.',
                'Khát khao tự do: Tự do là nguồn sống của bạn. Bạn làm việc hiệu quả nhất khi được tự do thể hiện phương pháp riêng. Bạn ghét bị ra lệnh hay kiểm soát. Vì vậy, các vị trí quản lý cấp cao, tự kinh doanh hoặc làm việc tự do như freelancer là lựa chọn hoàn hảo.',
                'Thách thức: Kẻ thù lớn nhất của bạn chính là bản thân bạn. Bạn có thể tự phá hoại thành công vì tính cách cố chấp, nổi loạn hoặc quá nuông chiều cảm xúc cá nhân. Sự kiêu ngạo hoặc độc đoán có thể tạo ra rào cản trong sự nghiệp.',
                'chien_luoc' => [
                    'Hãy tìm những công việc cho phép bạn được tỏa sáng và tự chủ.',
                    'Kết nối với những người có tính kỷ luật cao để rèn luyện sự chính xác, và trau dồi trí tuệ để giữ cho cái đầu luôn lạnh và bình tĩnh.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có trực giác tài chính nhạy bén và khả năng nhìn xa trông rộng về tiền bạc.',
                'Năng khiếu kinh doanh: Bạn có khiếu làm ăn. Bạn biết cách lập kế hoạch tài chính dài hạn và nhìn thấy cơ hội kiếm tiền mà người khác bỏ qua. Bạn có tiềm năng trở thành doanh nhân xuất sắc và giàu có về tài sản, nhà cửa, đất đai, của cải.',
                'Thực tế và chiến lược: Bạn không mơ mộng hão huyền. Bạn tiếp cận tiền bạc với tư duy thực tế. Nhu cầu ổn định tài chính thúc đẩy bạn làm việc chăm chỉ và tích lũy.',
                'Rủi ro từ sự nóng vội: Bạn có thể đưa ra những quyết định đầu tư sai lầm trong lúc nóng vội hoặc tức giận.',
                'dinh_huong' => [
                    'Hãy học cách quản lý cảm xúc trước khi quản lý tiền bạc.',
                    'Trước những quyết định lớn, hãy chậm lại một nhịp.',
                    'Ưu tiên đầu tư vào những tài sản có giá trị bền vững.',
                    'Tìm kiếm cơ hội liên quan đến bất động sản hoặc những nơi gần nguồn nước sẽ mang lại may mắn cho bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn là cuộc đấu tranh giữa sự lãng mạn lý tưởng và tính thực dụng.',
                'Tình yêu nồng nhiệt: Bạn yêu khá lãng mạn và hết mình. Bạn tự hào về người mình yêu và sẵn sàng bảo vệ họ bằng mọi giá. Bạn là người chu đáo, tử tế và biết cách chăm sóc đối phương.',
                'Nếu là phụ nữ, bạn là người vợ mang lại thịnh vượng cho chồng (vượng phu). Bạn tiết kiệm, đảm đang và biết cách vun vén gia đình.',
                'Nhu cầu độc lập: Dù yêu sâu đậm, bạn vẫn cần không gian riêng. Tự do là điều kiện tiên quyết trong mối quan hệ của bạn. Khi không gian tự do bị xâm phạm, bạn sẽ có xu hướng phản ứng mạnh mẽ để bảo vệ ranh giới của mình.',
                'Tính thực dụng: Bạn không chỉ yêu bằng tim mà còn bằng đầu. Địa vị, uy tín và an ninh tài chính của đối phương là những yếu tố quan trọng đối với bạn.',
                'chien_luoc' => [
                    'Hãy tìm một người bạn đời tôn trọng sự tự do của bạn.',
                    'Học cách cân bằng giữa việc yêu thương bản thân và hy sinh cho người khác.',
                    'Đừng để sự toan tính làm mất đi vẻ đẹp của cảm xúc chân thật.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng lớn bởi nguồn năng lượng nhiệt huyết nhưng có thể bùng nổ bên trong.',
                'Nguy cơ căng thẳng, mệt mỏi: Bạn luôn muốn làm mọi thứ thật nhanh và thật nhiều. Sự nhiệt tình thái quá có thể dẫn đến kiệt sức và căng thẳng thần kinh. Bạn hay thiếu kiên nhẫn và dễ nổi nóng.',
                'Tâm bệnh: Sự căng thẳng kéo dài có thể khiến bạn cảm thấy vỡ mộng và mệt mỏi. Tính cách cố chấp cũng làm cho bạn bị đóng khung.',
                'Cách thức cân bằng: Bạn cần yếu tố nước để làm dịu ngọn lửa trong lòng. Hãy thường xuyên đi bơi, tắm thư giãn hoặc uống đủ nước.',
                'Liệu pháp: Thực hành chánh niệm, thiền định hoặc đơn giản là sống chậm lại để tận hưởng những khoảnh khắc nhỏ bé. Hãy lên kế hoạch làm việc hợp lý để tránh bị quá tải.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người có tư duy sắc bén, học nhanh và có trực giác tâm linh mạnh mẽ.',
                'Tư duy sáng tạo: Đầu óc bạn luôn tràn ngập những ý tưởng mới. Bạn có khả năng tổng hợp thông tin từ nhiều nguồn và biến nó thành kiến thức của riêng mình.',
                'Học đi đôi với hành: Bạn không thích lý thuyết suông. Ngay khi học được điều gì mới, bạn muốn áp dụng nó vào thực tế ngay lập tức.',
                'Hướng về tâm linh: Theo thời gian, bạn có xu hướng quan tâm đến các vấn đề tâm linh, đạo đức và những giá trị tinh thần cao đẹp. Bạn có lòng tự trọng cao và ý thức mạnh mẽ về phẩm giá.',
                'Thách thức: Cái tôi quá lớn, sự kiêu ngạo hoặc nổi loạn có thể khiến bạn mất tập trung vào mục tiêu phát triển. Nếu không biết lắng nghe, bạn sẽ bỏ lỡ nhiều bài học quý giá.',
                'dinh_huong' => [
                    'Hãy tận dụng khả năng giao tiếp để học hỏi từ mọi người.',
                    'Tin tưởng vào trực giác nhưng cần kiểm chứng bằng lý trí.',
                    'Học cách lắng nghe sâu để thấu hiểu bản chất của tri thức.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người quảng giao, duyên dáng và có sức hút đặc biệt trong các mối quan hệ xã hội.',
                'Mạng lưới rộng lớn: Bạn thích gặp gỡ người mới và biết cách khuấy động không khí. Bạn là người kết nối mọi người lại với nhau. Đời sống xã hội của bạn khá phong phú và sôi động.',
                'Hào phóng: Bạn chơi đẹp và hào phóng với những người mình quý mến. Bạn thích kết giao với những người có phẩm chất tốt, tham vọng và thành đạt.',
                'Rủi ro: Nếu bạn không chịu lắng nghe, sự kiêu ngạo có thể gây ra hiểu lầm và xung đột, làm rạn nứt các mối quan hệ quý giá.',
                'chien_luoc' => [
                    'Hãy thực hành sự kiên nhẫn và khoan dung.',
                    'Lắng nghe nhiều hơn nói.',
                    'Dùng khả năng ngoại giao bẩm sinh để hòa giải và xây dựng những mối quan hệ bền vững dựa trên sự tôn trọng lẫn nhau.'
                ]
            ]
        ],
        'giap_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật đẹp và kiên cường: “Một cây cổ thụ xanh tốt, khỏe mạnh đứng vững trên mặt đất phủ đầy tuyết trắng”. Dù hoàn cảnh có khắc nghiệt hay lạnh giá, bạn vẫn vươn lên đầy sức sống nhờ được dòng nước ngầm nuôi dưỡng. Bạn sở hữu nội lực dồi dào, trí thông minh sắc sảo và khả năng thích ứng linh hoạt đáng kinh ngạc.',
                'La Bàn Thịnh Vượng sẽ giúp bạn hiểu rõ cách vận hành “bộ rễ” của mình để cây đời luôn xanh tốt.'
            ],
            'su_nghiep' => [
                'Bạn là người đa năng, tháo vát và sở hữu những tố chất lãnh đạo bẩm sinh.',
                'Tố chất lãnh đạo và quản lý: Bạn có khả năng quan sát tinh tường, không bỏ sót chi tiết nào. Bạn là người có khả năng làm nhiều việc cùng lúc một cách xuất sắc với sự tập trung cao độ. Sự tháo vát giúp bạn thích ứng nhanh chóng với mọi tình huống, kể cả khủng hoảng. Bạn kiên nhẫn, chăm chỉ và luôn có động lực tự thân mạnh mẽ.',
                'Sự nghiệp đa dạng: Nhờ trí thông minh và kỹ năng đối nhân xử thế khéo léo, bạn có thể tỏa sáng ở nhiều lĩnh vực:',
                'Xã hội & ngoại giao: Bạn có thể là một nhân viên bán hàng xuất sắc, chuyên gia quan hệ công chúng, luật sư, hoặc nhà tâm lý học nhờ khả năng ngoại giao tuyệt vời.',
                'Tài chính & quản lý: Sự thực tế giúp bạn thành công trong kinh doanh, ngân hàng, bảo hiểm.',
                'Tri thức & độc lập: Trí tuệ nhạy bén dẫn lối bạn trở thành nhà văn, giáo viên, nhà soạn nhạc hoặc nhà quản trị.',
                'Tinh thần độc lập: Bạn sở hữu một tâm hồn tự do. Bạn muốn nắm quyền kiểm soát vận mệnh của chính mình. Do đó, con đường khởi nghiệp hoặc tự làm chủ là lý tưởng nhất đối với bạn.',
                'Danh tiếng gắn liền đạo đức: Bạn có hệ giá trị đạo đức khá mạnh. Bạn ưu tiên danh dự và phẩm giá hơn là của cải vật chất. Bạn có những nguyên tắc đạo đức rất vững vàng và luôn ưu tiên việc xây dựng danh tiếng dựa trên sự chính trực. Vì vậy, danh tiếng của bạn thường khá uy tín và sạch đẹp.',
                'Thách thức: Bạn ghét sự gò bó và cấu trúc cứng nhắc. Đôi khi bạn thích mạo hiểm nhưng lại có thể cả thèm chóng chán. Khi hết hứng thú, bạn có xu hướng bỏ dở giữa chừng. Cảm xúc tiêu cực như lo lắng hay thiếu kiên nhẫn đôi khi cũng cản trở công việc của bạn.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc cho phép sự tự do nhưng hãy tự đặt ra kỷ luật cho bản thân để đi đến cùng.',
                    'Chia nhỏ mục tiêu để duy trì hứng thú là cách tốt nhất để bạn không bỏ cuộc.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là những người quản lý tiền bạc xuất sắc. Bạn có quan điểm khá rõ ràng: Tiền bạc là thước đo của sự an toàn và phẩm giá.',
                'Quản lý tài chính xuất sắc: Bạn linh hoạt và tháo vát trong việc xử lý dòng tiền. Ngay cả trong khủng hoảng tài chính, bạn vẫn biết cách xoay xở để sinh tồn và giữ vững tài sản. Khả năng ứng biến này là “tấm khiên” bảo vệ túi tiền của bạn.',
                'Kiếm tiền bằng đạo đức: Bạn chỉ hứng thú với những đồng tiền chính đáng. Bạn thà sống thanh cao còn hơn giàu có mà đánh mất phẩm giá. Sự trung thực này giúp bạn xây dựng được niềm tin lớn với đối tác, là nền tảng cho sự thịnh vượng bền vững.',
                'Tìm kiếm sự ổn định: Dù trong sự nghiệp bạn có thể mạo hiểm, nhưng trong tài chính bạn lại ưu tiên sự an toàn. Bạn có xu hướng tích lũy để tạo ra một nền tảng vững chắc.',
                'Rủi ro: Nguy cơ lớn nhất của bạn là sự mất tập trung. Khi quá tải cảm xúc hoặc chán nản, bạn có thể lơ là việc quản lý tài sản dài hạn. Đôi khi vì quá giữ gìn danh dự, bạn có thể từ chối những cơ hội kiếm tiền tốt chỉ vì cảm thấy nó chưa đủ “hoàn hảo” về mặt lý tưởng.',
                'dinh_huong' => [
                    'Hãy tiếp tục giữ vững nguyên tắc đạo đức, đó là thương hiệu của bạn.',
                    'Tận dụng trí tuệ sắc bén để đầu tư vào những lĩnh vực đòi hỏi sự phân tích sâu.',
                    'Tìm niềm vui trong nghệ thuật để giải tỏa cảm xúc, tránh để tâm trạng ảnh hưởng đến quyết định tài chính.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn không phải là người khéo miệng, nhưng lại là người có trái tim nhân hậu và sự tận tụy hiếm có.',
                'Hành động thay lời nói: Bạn không giỏi nói những lời đường mật sáo rỗng. Bạn thể hiện tình yêu bằng hành động thiết thực: chăm sóc, bảo vệ và lo lắng cho đối phương. Người bạn đời tinh tế sẽ trân trọng sự chân thành mộc mạc này của bạn.',
                'Sự cam kết tuyệt đối: Gia đình là ưu tiên số một. Bạn đáng tin cậy và chung thủy. Nhu cầu về sự an toàn khiến bạn gắn bó sâu sắc với người mình yêu. Hôn nhân của bạn thường bền vững và lâu dài.',
                'Xu hướng bảo vệ quá mức: Bạn muốn người yêu mình phải tài giỏi, độc lập, nhưng nghịch lý là bạn lại hay bảo bọc họ quá mức. Bạn sẵn sàng chiến đấu để giữ họ tránh xa rắc rối. Sự quan tâm sâu sắc của bạn đôi khi có thể khiến đối phương cảm thấy không gian riêng bị giới hạn.',
                'Thách thức: Khi gặp căng thẳng, bạn có xu hướng dựa dẫm cảm xúc vào người thân cận. Nếu không khéo léo, sự phụ thuộc này có thể tạo áp lực cho mối quan hệ.',
                'chien_luoc' => [
                    'Hãy học cách tin tưởng vào khả năng của đối phương.',
                    'Đừng biến tình yêu thành sự bao bọc thái quá.',
                    'Hãy tập nói ra những suy nghĩ của mình để đối phương hiểu bạn hơn, thay vì chỉ lẳng lặng làm mọi thứ.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn phụ thuộc nhiều vào sự cân bằng trong cơ thể.',
                'Sức sống dẻo dai: Nhìn chung bạn năng động, thích phiêu lưu và có khả năng phục hồi tốt.',
                'Quan tâm về gan và mật: Trong y học cổ truyền, Thiên Can Giáp của bạn đại diện cho gan và túi mật. Khi bạn tức giận, lo lắng hay thiếu kiên nhẫn, gan của bạn sẽ bị ảnh hưởng trực tiếp.',
                'Hệ tiêu hóa: Sự căng thẳng quá mức cũng có thể gây hại cho dạ dày. Bạn thường quên chăm sóc bản thân khi quá đam mê công việc.',
                'dinh_huong' => [
                    'Tránh xa rượu bia, vì nó là kẻ thù số một của gan.',
                    'Duy trì chế độ ăn uống cân bằng.'
                ],
                'Liệu pháp thiên nhiên: Bạn cần yếu tố thiên nhiên để dưỡng sinh. Hãy sống gần thiên nhiên xanh mát để tìm lại sự bình yên. Đảm bảo uống đủ nước và ngủ đủ giấc để “làm mới” cho cơ thể và tinh thần.'
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ sắc bén và một tâm hồn nghệ sĩ nhạy cảm.',
                'Trí tuệ và thích ứng: Bạn thông minh, hiểu rõ mình muốn gì và có khả năng xử lý mọi tình huống khó khăn. Bạn học hỏi khá nhanh và biết cách áp dụng kiến thức để sinh tồn.',
                'Tâm hồn nghệ sĩ: Bạn yêu cái đẹp, trân trọng nghệ thuật và sự sáng tạo. Những hoạt động nghệ thuật mang lại cho bạn niềm an ủi và hạnh phúc lớn lao.',
                'Thách thức nội tâm: Sâu thẳm bên trong, bạn luôn cảm thấy “bất mãn”. Bạn hiếm khi hài lòng hoàn toàn với hiện tại. Điều này vừa là động lực để bạn tiến lên, nhưng cũng là nguyên nhân khiến bạn hay bỏ dở công việc giữa chừng vì chán nản. Bạn cũng ghét các cấu trúc học tập gò bó.',
                'dinh_huong' => [
                    'Hãy tận dụng trí tuệ để theo đuổi những lĩnh vực tri thức sâu sắc.',
                    'Chuyển hóa năng lượng bất mãn thành sự sáng tạo nghệ thuật.',
                    'Học cách chia nhỏ mục tiêu và kiên nhẫn để chiến thắng sự chán nản nhất thời.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người ngoại giao khéo léo nhưng lại khá kín tiếng về đời tư.',
                'Ngoại giao thầm lặng: Bạn không phải là người hướng ngoại ồn ào. Bạn thích những nhóm bạn nhỏ, thân thiết hơn là đám đông xa lạ. Bạn nói năng nhẹ nhàng, lịch thiệp và biết cách cư xử.',
                'Sức hút bí ẩn: Bạn có vẻ đẹp, tính cách và sự hóm hỉnh khiến người khác khó cưỡng lại. Dù bạn chủ động bảo vệ đời sống riêng tư, nhưng chính sự kín đáo đó lại càng khiến bạn trở nên hấp dẫn.',
                'Đáng tin cậy: Bạn là chỗ dựa vững chắc cho bạn bè khi họ gặp khó khăn.',
                'Thách thức: Đôi khi sự bảo vệ đời tư quá mức khiến người khác cảm thấy bạn khó gần. Dù bên ngoài tự tin, nhưng bên trong bạn lại là người thiếu chắc chắn về bản thân nhất, chỉ những người khá thân mới nhận ra điều này.',
                'chien_luoc' => [
                    'Hãy tập trung vào chất lượng mối quan hệ thay vì số lượng.',
                    'Hãy mở lòng chia sẻ sự yếu đuối của mình với những người thực sự tin cậy.'
                ],
                'Quý nhân của bạn thường là những người bạn đời hoặc cộng sự độc lập, tài giỏi.'
            ]
        ],
        'giap_than' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp vừa hùng vĩ vừa đầy thử thách: Một cây lớn đứng giữa trời thu, khi lá bắt đầu chuyển vàng, hoặc một cây gỗ đang được rìu đẽo để trở thành rường cột. Lá số bạn biểu hiện một môi trường sống đầy áp lực và thử thách. Nhưng chính áp lực đó, tượng trưng cho sự cắt tỉa, rèn giũa sẽ biến bạn từ một cây gỗ thô sơ thành một tác phẩm giá trị, một người tài năng xuất chúng.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến áp lực thành kim cương.'
            ],
            'su_nghiep' => [
                'Bạn là mẫu người "lửa thử vàng, gian nan thử sức". Sự nghiệp của bạn thường không trải hoa hồng ngay từ đầu, nhưng kết quả đạt được lại vô cùng rực rỡ.',
                'Nhà lãnh đạo thực chiến: Bạn có khả năng giải quyết vấn đề nhanh nhạy và quyết đoán. Bạn không sợ khó khăn, ngược lại, bạn coi thách thức là cơ hội để chứng minh tài năng. Bạn có tư duy chiến lược sắc bén, biết cách biến những kế hoạch trên giấy thành hiện thực. Phong cách lãnh đạo của bạn là đi tiên phong và làm gương.',
                'Kỹ năng giao tiếp đỉnh cao: Bạn có tài ăn nói, hài hước và giàu biểu cảm. Khả năng thuyết phục của bạn là vũ khí lợi hại trong đàm phán và kinh doanh. Bạn biết cách xoay chuyển tình thế nhờ vào sự khéo léo của mình.',
                'Đa tài và thích ứng: Bạn phù hợp với những công việc đòi hỏi sự di chuyển và đổi mới liên tục. Kinh doanh, tiếp thị, truyền thông, luật sư hay thậm chí là âm nhạc đều là những lĩnh vực bạn có thể tỏa sáng.',
                'Thách thức: Bạn thường phải làm việc chăm chỉ gấp đôi người khác để được công nhận. Giai đoạn đầu đời có thể khá chật vật và thiếu sự hỗ trợ.',
                'chien_luoc' => [
                    'Hãy kiên nhẫn.',
                    'Thành công của bạn thường đến muộn.',
                    'Hãy tìm kiếm những môi trường cho phép bạn tư duy độc lập và có không gian yên tĩnh để suy ngẫm giải pháp.',
                    'Sự di chuyển như đi công tác, thay đổi môi trường thường mang lại may mắn cho bạn.'
                ]
            ],
            'tai_chinh' => [
                'Tài lộc của bạn thường đến từ sự nhạy bén và khả năng xoay sở tài tình.',
                'Năng khiếu kinh doanh: Bạn có trực giác tốt về tiền bạc và đầu tư. Bạn biết cách tận dụng các mối quan hệ xã hội để tạo ra cơ hội làm ăn. Tinh thần khởi nghiệp mạnh mẽ giúp bạn có thể làm chủ và xây dựng cơ nghiệp riêng.',
                'Hậu vận sung túc: Dù tuổi trẻ có thể bôn ba, vất vả, nhưng càng về già bạn càng tích lũy được nhiều tài sản và quyền lực. Bạn có thể trở thành một người giàu có và được kính trọng.',
                'Thói quen chi tiêu: Bạn là người hào phóng và thích trải nghiệm những điều mới lạ. Điều này khiến bạn khó giữ tiền mặt trong tay. Bạn có xu hướng tiêu xài cho những sở thích cá nhân hoặc để duy trì các mối quan hệ.',
                'Rủi ro: Bạn có thể đưa ra các quyết định đầu tư mạo hiểm. Nếu không quản lý chặt chẽ, khó tích lũy được tài sản.',
                'dinh_huong' => [
                    'Hãy học cách quản lý tài chính kỷ luật hơn.',
                    'Bạn cần yếu tố nước để nuôi dưỡng cây và làm dịu sự khắc nghiệt của môi trường.',
                    'Đầu tư vào tri thức hoặc các lĩnh vực liên quan đến nước, vận tải, giao thương sẽ mang lại lợi nhuận bền vững.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là một người quyến rũ, lãng mạn nhưng cũng khá khó tính và khó nắm bắt.',
                'Sức hút và sự lãng mạn: Bạn biết cách làm cho đối phương say đắm. Bạn lãng mạn, nhiệt tình và luôn tạo ra những bất ngờ thú vị. Bạn thường bắt đầu tình yêu từ tình bạn, nên người bạn đời của bạn thường cũng là người bạn thân nhất, người hiểu bạn sâu sắc nhất.',
                'Mong muốn sự cân bằng: Bạn muốn một đối tác thành công, thông minh và có thể cùng bạn chia sẻ những lý tưởng sống. Bạn cần sự kích thích về trí tuệ lẫn thể chất trong mối quan hệ.',
                'Thách thức: Bạn có xu hướng thích chinh phục và đôi khi hơi đào hoa, đặc biệt nếu là nam. Bạn thích trở thành trung tâm của sự chú ý và hay tán tỉnh. Điều này có thể gây bất an cho người bạn đời. Nếu đối phương không đủ thú vị, bạn có thể cảm thấy nhàm chán.',
                'chien_luoc' => [
                    'Hãy chuyển hóa năng lượng chinh phục vào việc cùng nhau khám phá thế giới thay vì tìm kiếm người mới.',
                    'Sự chung thủy và chân thành sẽ giúp bạn xây dựng một gia đình hạnh phúc, nơi bạn tìm thấy sự bình yên sau những sóng gió bên ngoài.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe là khía cạnh bạn cần đặc biệt lưu tâm, vì sự xung khắc trong chính cơ thể bạn.',
                'Rủi ro chấn thương: Bạn có thể bị tai nạn nhỏ, chấn thương tay chân hoặc các vấn đề liên quan đến xương khớp, gan mật.',
                'Vấn đề cân nặng: Bạn có xu hướng tăng cân hoặc gặp các vấn đề về sự phát triển bất thường trong cơ thể.',
                'Cách thức cân bằng: Hãy uống đủ nước, đi bơi hoặc sống gần nơi có nguồn nước.',
                'Liệu pháp: Tránh các hoạt động quá mạo hiểm. Chú ý chế độ ăn uống lành mạnh, bổ sung nhiều rau xanh để tăng cường sức đề kháng cho gan.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người học nhanh, nhớ lâu và sở hữu trí tuệ của một chiến lược gia.',
                'Học qua thử thách: Bạn không trưởng thành trong nhung lụa mà lớn lên qua gian khó. Mỗi thử thách là một bài học giúp bạn mài giũa bản lĩnh. Bạn có khả năng nắm bắt các khái niệm phức tạp khá nhanh.',
                'Nhu cầu tìm kiếm ý nghĩa: Dù thành công, đôi khi bạn vẫn cảm thấy khao khát một ý nghĩa sâu sắc hơn hoặc muốn lấp đầy đời sống tinh thần của mình. Điều này thúc đẩy bạn tìm kiếm những giá trị tinh thần, triết học hoặc tôn giáo để lấp đầy tâm hồn.',
                'Thời gian tĩnh lặng: Bạn cần những khoảng thời gian ở một mình để suy ngẫm và tái tạo năng lượng. Đây là lúc trí tuệ của bạn tỏa sáng nhất.',
                'dinh_huong' => [
                    'Hãy tìm một lý tưởng sống cao đẹp để cống hiến. Khi bạn làm việc vì một mục đích lớn lao hơn bản thân, bạn sẽ tìm thấy sự thỏa mãn thực sự và tránh được cảm giác trầm cảm hay vô định.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người vui vẻ, hòa đồng và có khả năng kết nối tuyệt vời.',
                'Ngôi sao xã hội: Bạn đi đến đâu cũng mang lại tiếng cười và sự thoải mái cho mọi người. Bạn có mạng lưới bạn bè rộng khắp và nhiều người ngưỡng mộ.',
                'Thiếu sự hỗ trợ ban đầu: Dù nhiều bạn, nhưng trong giai đoạn đầu đời, bạn thường phải tự lực cánh sinh, ít nhận được sự giúp đỡ thực sự từ quý nhân.',
                'Giá trị thu hút: Để thu hút quý nhân, bạn cần dùng trí tuệ và sự giúp đỡ của mình để hỗ trợ người khác trước. Khi bạn cho đi giá trị, bạn sẽ nhận lại sự ủng hộ.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những người bạn có cùng chí hướng, tham vọng và mục tiêu.',
                    'Kết nối với những người có tư duy sâu sắc hoặc mạnh mẽ kiên định sẽ giúp bạn cân bằng cuộc sống.'
                ]
            ]
        ],
        'ky_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp của sức sống mãnh liệt trong nghịch cảnh: Một hạt lúa mọc lên từ vùng đất khô cằn trên đỉnh núi, hay một vùng đất được nung nóng bởi lửa ngầm. Bạn có vẻ ngoài điềm đạm, thân thiện nhưng bên trong là một nội lực cuộn trào, một ý chí sắt đá và khả năng chịu đựng phi thường.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khai thác nguồn năng lượng nhiệt để biến vùng đất khô cằn thành cánh đồng trù phú.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tố chất lãnh đạo bẩm sinh pha trộn với sự thực dụng sắc bén.',
                'Đặc điểm cốt lõi: Bạn có tầm nhìn xa nhưng đôi chân luôn chạm đất. Bạn biết cách biến những ý tưởng lớn thành kế hoạch hành động cụ thể. Bạn có tư duy tổ chức tốt, quyết đoán và luôn khao khát không gian tự do để sáng tạo. Khả năng phân tích và trí thông minh sắc bén giúp bạn nhanh chóng trở thành chuyên gia.',
                'Tiềm năng phát triển: Kinh doanh và tài chính: Phù hợp để điều hành doanh nghiệp, làm việc trong bất động sản, ngân hàng hoặc thị trường chứng khoán. Lãnh đạo và giảng dạy: Tỏa sáng trong vai trò giảng viên, diễn giả hoặc quản lý cấp cao. Chữa lành và nhân đạo: Dòng máu nhân đạo thúc đẩy bạn đến với bác sĩ, chuyên gia tâm lý hoặc hoạt động xã hội.',
                'Thách thức và rủi ro: Đôi khi quá chú trọng vào bức tranh lớn mà bỏ qua các chi tiết nhỏ. Sự quyết liệt và cá tính mạnh đôi khi khiến bạn trở nên áp đặt, vô tình tạo khoảng cách với người khác. Nguy cơ trở thành người nghiện việc là khá cao, dẫn đến kiệt sức.',
                'dinh_huong' => [
                    'Rèn luyện sự tỉ mỉ: Rèn luyện tư duy logic, phương pháp làm việc có hệ thống. Hãy tìm cộng sự giỏi chi tiết để hỗ trợ.',
                    'Học cách khoan dung: Bớt đi sự áp đặt và học cách bao dung với lỗi lầm của người khác.',
                    'Cân bằng cuộc sống: Hãy đặt ra giới hạn cho công việc.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có nhu cầu khá lớn về sự an toàn và ổn định vật chất.',
                'Tư duy tài chính: Tiền bạc là thước đo của sự an tâm. Bạn có đầu óc kinh doanh mạnh mẽ và khả năng kiếm tiền xuất sắc. Bạn có tiềm năng đạt được địa vị cao và sự giàu có nếu biết cách duy trì sự nhiệt huyết, danh tiếng đúng cách.',
                'Tiềm năng thịnh vượng: Bạn có khả năng tích lũy tài sản tốt, đặc biệt là các tài sản liên quan đến đất đai, nhà cửa.',
                'Rủi ro cần lưu ý: Dù kiếm tiền giỏi, nhưng bạn có thể rơi vào tình trạng vung tay quá trán. Bạn hào phóng và biết hưởng thụ, nhưng cần cẩn trọng để không rơi vào trạng thái chi tiêu cảm xúc. Khi căng thẳng, bạn có xu hướng tiêu xài hoang phí. Nỗi sợ hãi về sự thiếu thốn đôi khi khiến bạn trở nên thực dụng không cần thiết.',
                'chien_luoc' => [
                    'Quản lý chi tiêu: Học cách lập ngân sách và tuân thủ nó. Tiết kiệm là chìa khóa.',
                    'Đầu tư vào bất động sản: Kênh đầu tư phù hợp nhất với năng lượng của bạn.',
                    'Chuyển hóa năng lượng: Tham gia các hoạt động từ thiện. Sự cho đi sẽ giúp bạn cân bằng lại tâm lý.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người ấm áp, chu đáo và bảo vệ.',
                'Đặc điểm tình cảm: Bạn coi trọng mối quan hệ, trung thành và có trách nhiệm cao. Bạn sẵn sàng làm chỗ dựa vững chắc cho người mình yêu. Tuy nhiên, bạn có một lớp vỏ bọc cảm xúc khá dày, khó bộc lộ sự yếu đuối hay những cảm xúc sướt mướt, bạn có xu hướng giấu kín tâm tư vào trong để giữ hình ảnh vững chãi cho người thân.',
                'Thách thức trong hôn nhân: Tính cách cứng đầu và muốn kiểm soát là rào cản lớn nhất. Khi đối phương không làm theo ý bạn, bạn có thể trở nên hống hách hoặc áp đặt.',
                'dinh_huong' => [
                    'Học cách biểu lộ: Hãy tập nói lời yêu thương và thể hiện sự quan tâm một cách rõ ràng hơn.',
                    'Giảm bớt sự kiểm soát: Tôn trọng sự tự do của đối phương. Mối quan hệ bền vững dựa trên sự bình đẳng.',
                    'Kiên nhẫn: Kiên nhẫn giải quyết mâu thuẫn thay vì nóng vội tranh cãi.'
                ]
            ],
            'suc_khoe' => [
                'Bạn có thể có sự mất cân bằng và có vần đề tiềm ẩn trong cơ thể.',
                'Căng thẳng và kiệt sức: Bạn là người hay lo âu và làm việc quá sức, có thể dẫn đến căng thẳng, mệt mỏi mãn tính.',
                'Tiêu hóa và tim mạch: Chú ý các nguy cơ ảnh hưởng đến dạ dày như viêm loét, nóng trong người và tim mạch, huyết áp.',
                'Tâm trí luôn bận rộn, bạn ít khi được nghỉ ngơi, luôn trong trạng thái suy nghĩ liên tục.',
                'Lieu_phap' => [
                    'Bổ sung năng lượng dòng chảy: Đây là yếu tố quan trọng nhất. Hãy uống nhiều nước, ăn đồ mát, đi bơi.',
                    'Tìm sự cân bằng: Học cách buông bỏ và thư giãn.',
                    'Phát triển tâm linh: Thiền định là cách tuyệt vời để bạn tìm thấy sự bình an, giảm bớt áp lực vật chất.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ sắc bén và khát khao học hỏi mãnh liệt.',
                'Tiềm năng trí tuệ: Bạn học khá nhanh, đặc biệt là thông qua kinh nghiệm thực tế và quan sát. Bạn có khả năng phân tích tâm lý con người cực tốt, có thể "đọc vị" người khác. Bạn có tư duy sáng tạo và độc đáo.',
                'Rào cản phát triển: Lòng tự tôn cao là ưu điểm nhưng đôi khi cũng là rào cản khiến bạn ngại chia sẻ khó khăn với người khác, đôi khi ngăn cản bạn thừa nhận điểm yếu hoặc hỏi xin sự giúp đỡ. Bạn thường che giấu sự thiếu quyết đoán bằng vẻ ngoài tự tin thái quá.',
                'dinh_huong' => [
                    'Phát huy sự độc đáo: Mạnh dạn theo đuổi những ý tưởng khác biệt của bạn.',
                    'Giáo dục là chìa khóa: Việc học tập liên tục sẽ giúp bạn mở rộng tầm nhìn và củng cố vị thế.',
                    'Tránh lãng phí năng lượng: Tập trung vào việc phát triển bản thân, tránh tham gia vào các cuộc đấu đá quyền lực vô bổ.',
                    'Tích cực chia sẻ và sáng tạo: Hãy viết lách, nói chuyện, hoặc tham gia nghệ thuật. Việc bộc lộ suy nghĩ ra bên ngoài chính là cách tốt nhất để giải phóng áp lực cho tâm trí bạn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hòa đồng, vui vẻ và có khả năng ngoại giao tự nhiên.',
                'Phong cách giao tiếp: Bạn dễ dàng hòa nhập với mọi người và thường là linh hồn của các buổi gặp gỡ. Bạn thường là người cầm trịch hoặc chủ động kết nối trong các cuộc gặp gỡ. Bạn thẳng thắn và trung thực.',
                'Quý nhân: Thành công của bạn chủ yếu đến từ nỗ lực cá nhân. Bạn thuộc tuýp người tự lập, tự cường. Thành công của bạn mang đậm dấu ấn của nỗ lực cá nhân hơn là sự thừa hưởng.',
                'Thử thách nhân tâm: Sự hào phóng của bạn đôi khi đặt chưa đúng chỗ. Hãy dùng trí tuệ để chọn lọc các mối quan hệ chất lượng.',
                'chien_luoc' => [
                    'Hãy giữ thái độ thân thiện nhưng tỉnh táo, chọn bạn mà chơi.',
                    'Sử dụng trực giác để nhận biết ai là người chân thành, ai là kẻ giả dối.'
                ]
            ]
        ],
        'ky_mao' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn tràn đầy sức sống và sự năng động: Một vùng đất vườn tược màu mỡ, nơi cây cỏ, hoa màu đang đâm chồi nảy lộc mạnh mẽ. Điều này tượng trưng cho bản mệnh sinh ra đã mang khát vọng vươn lên mạnh mẽ từ áp lực nội tại. Chính áp lực này tạo nên một con người đầy tham vọng, nhạy bén, kiên cường và luôn muốn khẳng định mình.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến áp lực thành động lực để gặt hái những mùa màng bội thu.'
            ],
            'su_nghiep' => [
                'Bạn mang phẩm chất của một chiến binh hành động, không chỉ biết ngồi vẽ vời kế hoạch. Bạn là người thực tế, ưu tiên hành động cụ thể thay vì chỉ dừng lại ở kế hoạch.',
                'Đặc điểm cốt lõi: Bạn có bản năng nhạy bén với thời cơ và luôn muốn bắt tay vào làm ngay. Bạn sở hữu tư duy sắc bén, đa tài và khả năng học hỏi cực nhanh. Bạn không thích đi theo lối mòn mà luôn muốn tìm ra con đường riêng, độc đáo. Khát vọng độc lập trong sự nghiệp khá lớn.',
                'Tiềm năng phát triển: Sáng tạo và Nghệ thuật: Thành công trong viết lách, thiết kế, thời trang, âm nhạc hay truyền thông. Xã hội và Luật pháp: Tỏa sáng trong ngành Luật, Ngoại giao, Chính trị hoặc hoạt động xã hội. Chuyên môn và Kỹ thuật: Làm tốt trong các ngành kỹ thuật, khoa học, phân tích dữ liệu.',
                'Thách thức và rủi ro: Điểm yếu lớn nhất là sự phân tán, bạn dễ mất tập trung. Vì sở hữu nhiều tài năng, bạn dễ bị thu hút bởi cái mới, dẫn đến việc khó duy trì sự tập trung lâu dài. Sự cầu toàn thái quá đôi khi biến bạn thành người hay phê phán, chỉ trích.',
                'dinh_huong' => [
                    'Rèn luyện sự Tập trung (Focus): Hãy chọn một mục tiêu quan trọng nhất và cam kết theo đuổi đến cùng.',
                    'Tìm kiếm tự do trong khuôn khổ: Tìm những vị trí cho phép bạn có quyền tự quyết cao.',
                    'Học cách chấp nhận: Bớt khắt khe với bản thân và người khác.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính đối với bạn gắn liền với nhu cầu về "sự an toàn" và "hình ảnh".',
                'Tư duy tài chính: Bạn có đầu óc kinh doanh nhạy bén và thường nhìn thấy cơ hội kiếm tiền ở khắp mọi nơi. Bạn chịu chi cho hình ảnh cá nhân (đồ sang trọng, xe đẹp) để khẳng định vị thế.',
                'Tiềm năng thịnh vượng: Vận may tài chính của bạn thường tốt hơn về hậu vận. Bạn có thể làm giàu từ nhiều nguồn, đặc biệt là những công việc đòi hỏi kỹ năng chuyên môn hoặc sự sáng tạo.',
                'Rủi ro cần lưu ý: Sự mâu thuẫn giữa nhu cầu vật chất và đời sống tinh thần đôi khi làm bạn bối rối. Bạn có thể bị cám dỗ bởi những con đường làm giàu nhanh nhưng rủi ro.',
                'chien_luoc' => [
                    'Quản lý chi tiêu: Lập ngân sách rõ ràng. Đừng để vẻ hào nhoáng bên ngoài ăn mòn tài sản bên trong.',
                    'Đầu tư vào kiến thức: Kiến thức và kỹ năng là tài sản sinh lời tốt nhất của bạn.',
                    'Tăng cường hoặc tham gia các lĩnh vực năng động để kích hoạt tài lộc.'
                ]
            ],
            'tinh_duyen' => [
                'Bạn sở hữu sức hút bí ẩn, nét duyên ngầm và vận đào hoa vượng.',
                'Đặc điểm tình cảm: Bạn dễ dàng thu hút người khác phái. Trong tình yêu, bạn ấm áp, hào phóng và biết cách quan tâm. Bạn thường bị thu hút bởi những người thành công, độc lập và mạnh mẽ.',
                'Thách thức trong hôn nhân: Bên trong vẻ ngoài tự tin, lạnh lùng là một trái tim khá nhạy cảm và đầy bất an. Do bản tính cẩn trọng và muốn nắm chắc mọi việc, đôi khi bạn dễ nảy sinh tâm lý bất an trong mối quan hệ. Bạn cũng có thể trải qua nhiều mối tình ngắn ngủi do tính cách hay thay đổi và tiêu chuẩn cao.',
                'chien_luoc' => [
                    'Chậm lại: Đừng vội vàng cam kết hay kết hôn khi chưa tìm hiểu kỹ.',
                    'Xây dựng niềm tin: Học cách tin tưởng. Sự tin tưởng và tôn trọng không gian riêng chính là dưỡng chất nuôi sống tình yêu của bạn.',
                    'Tôn trọng không gian riêng: Sự ràng buộc quá mức sẽ tạo ra phản ứng ngược.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe là yếu tố cần được ưu tiên hàng đầu do sự xung đột nội tại.',
                'Hệ tiêu hóa: Tỳ vị, dạ dày thường yếu. Có thể bị đau dạ dày, rối loạn tiêu hóa khi căng thẳng.',
                'Gan mật và thần kinh: Có thể gây ra các vấn đề về gan, mật hoặc căng thẳng thần kinh, mất ngủ.',
                'Chấn thương: Cần cẩn trọng hơn khi tham gia giao thông hoặc vận động mạnh để bảo vệ sự an toàn cho bản thân.',
                'Lieu_phap' => [
                    'Giải tỏa áp lực: Học cách xả stress thông qua chia sẻ hoặc các hoạt động giải trí. Đừng hy sinh sức khỏe để đổi lấy công việc.',
                    'Hãy ăn uống đồ ấm nóng, vận động ra mồ hôi, tiếp xúc với ánh nắng.',
                    'Thời gian tĩnh lặng: Dành thời gian nghỉ ngơi, thiền định để phục hồi năng lượng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là một nhà nghiên cứu bẩm sinh, luôn tò mò, muốn tìm hiểu đến cùng sự thật.',
                'Tiềm năng trí tuệ: Bạn có khả năng dự đoán tương lai (tầm nhìn xa) và lập kế hoạch thực tế. Trực giác của bạn khá mạnh, đôi khi là năng lực tâm linh. Bạn học hỏi liên tục để hoàn thiện bản thân.',
                'Rào cản phát triển: Sự phân tán là thách thức lớn nhất bạn cần chinh phục. Bạn thích quá nhiều thứ nên khó giỏi xuất sắc một thứ. Sự thiếu kiên nhẫn và bướng bỉnh cũng cản trở bạn.',
                'dinh_huong' => [
                    'Học cách tập trung: Rèn luyện sự tập trung như một kỹ năng sinh tồn.',
                    'Phát triển trực giác: Lắng nghe tiếng nói bên trong, nó sẽ dẫn dắt bạn đi đúng hướng.',
                    'Luôn luôn học hỏi: Tri thức là nguồn dinh dưỡng giúp cây cối trên mảnh đất của bạn luôn xanh tốt.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hấp dẫn, lôi cuốn, có tài ăn nói, và khá khéo miệng.',
                'Phong cách giao tiếp: Bạn dễ dàng kết bạn và là khách mời thú vị trong các bữa tiệc. Đời sống xã hội của bạn khá năng động.',
                'Quý nhân: Bạn thường kết giao với những người thành công, có động lực. Đó chính là nguồn quý nhân của bạn.',
                'Rủi ro: Sự thẳng thắn của bạn đôi khi thiếu đi sự mềm mỏng, có thể khiến người khác chưa kịp thích nghi, có thể làm người khác sốc. Bạn cũng có xu hướng bảo vệ người thân quá mức.',
                'chien_luoc' => [
                    'Học cách ngoại giao khéo léo hơn. Sự chân thành là tốt, nhưng sự tế nhị sẽ giúp bạn được lòng người hơn.',
                    'Mở rộng mạng lưới quan hệ nhưng hãy giữ những người bạn thực sự chất lượng bên mình.'
                ]
            ]
        ],
        'ky_dau' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật lấp lánh và giá trị: Một vùng đất màu mỡ chứa đựng bên dưới là mỏ vàng bạc, đá quý. Bạn sở hữu trí tuệ sắc bén, tài năng thiên bẩm và một gu thẩm mỹ tuyệt vời. Khác với vẻ mộc mạc của đất đồi, bạn là vùng đất đã được tinh lọc, mang vẻ đẹp của sự tinh tế và văn minh.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khai thác "mỏ vàng" trí tuệ của mình để tỏa sáng.'
            ],
            'su_nghiep' => [
                'Bạn là hiện thân của chủ nghĩa hoàn hảo.',
                'Đặc điểm cốt lõi: Bạn có con mắt tinh tường, nhìn thấy những chi tiết mà người khác bỏ qua. Bạn khá thông minh, học một biết mười và có khả năng giải quyết vấn đề xuất sắc. Bạn năng động, tham vọng và có tinh thần chiến đấu mạnh mẽ.',
                'Tiềm năng phát triển: Sáng tạo và nghệ thuật: Thế mạnh tuyệt đối: Viết lách, âm nhạc, thiết kế, giải trí. Quản lý và tổ chức: Thành công ở các vị trí điều hành, quản lý cấp cao hoặc tự kinh doanh. Chuyên môn: Chuyên gia tư vấn, luật sư, nhà khoa học hoặc giảng viên nhờ trí tuệ sắc bén.',
                'Thách thức: Thách thức lớn nhất là quản lý sự kỳ vọng. Vì cầu toàn, bạn dễ cảm thấy không hài lòng khi mọi việc chưa đạt độ hoàn hảo tuyệt đối.Vì quá thông minh và cầu toàn, bạn có thể khó chịu với sự chậm chạp của người khác. Bạn cũng có thể bị nhàm chán nếu công việc không đủ kích thích, dẫn đến sự thay đổi liên tục.',
                'dinh_huong' => [
                    'Tìm kiếm sự tự chủ: Bạn làm việc tốt nhất khi có quyền tự quyết. Hãy hướng tới việc tự làm chủ hoặc nắm giữ vị trí có thẩm quyền.',
                    'Rèn luyện sự bao dung: Bớt khắt khe với đồng nghiệp. Dùng tài năng của mình để hướng dẫn thay vì chỉ trích họ.',
                    'Duy trì sự ổn định: Bạn cần tăng danh tiếng, sự nhiệt huyết và sự vững chãi để giữ chân mình lại, tránh bay nhảy lung tung vì tâm lý cả thèm chóng chán.'
                ]
            ],
            'tai_chinh' => [
                'Bạn khá coi trọng sự an toàn về tài chính và muốn có một cuộc sống chất lượng cao.',
                'Tư duy tài chính: Bạn có đầu óc thực tế và khả năng kinh doanh bẩm sinh. Thành công tài chính của bạn chủ yếu đến từ trí thông minh và nỗ lực cá nhân. Bạn biết cách biến những kỹ năng, sở thích của mình thành công cụ kiếm tiền.',
                'Tiềm năng thịnh vượng: Bạn có khả năng nhìn xa trông rộng, lập kế hoạch tài chính dài hạn. Nếu bạn làm những công việc mình yêu thích, tiền bạc sẽ tự nhiên tìm đến.',
                'Rủi ro cần lưu ý: Mặc dù kiếm tiền giỏi, bạn lại gặp khó khăn trong việc giữ tiền. Bạn là người biết hưởng thụ cuộc sống. Đôi khi việc đầu tư mạnh tay cho các trải nghiệm chất lượng cao khiến quỹ tích lũy bị ảnh hưởng. Bạn phải tự lực cánh sinh.',
                'chien_luoc' => [
                    'Xây dựng nền tảng vững chắc: Hãy đầu tư vào những tài sản cố định như nhà cửa, đất đai để giữ tiền.',
                    'Kỷ luật tài chính là bắt buộc.',
                    'Kiểm soát cảm xúc nhất thời: Đừng mua sắm chỉ để giải tỏa cảm xúc. Hãy tìm niềm vui từ những giá trị tinh thần.',
                    'Đầu tư vào bản thân: Tiếp tục nâng cao kiến thức và kỹ năng, đó là cỗ máy in tiền vĩnh cửu của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người lãng mạn, lý tưởng và khá trung thành khi đã cam kết.',
                'Đặc điểm tình cảm: Bạn có sức hút tự nhiên, quyến rũ và biết cách làm hài lòng người khác. Bạn bị thu hút bởi những người thông minh, độc đáo, mạnh mẽ để cùng nhau phát triển trí tuệ.',
                'Thách thức trong hôn nhân: Sự ý nhị và e dè đôi khi tạo nên một lớp màn bí ẩn, khiến người khác cảm thấy bạn có chút lạnh lùng, khó nắm bắt. Thách thức lớn nhất là sự đòi hỏi tiêu chuẩn cao và cầu toàn. Với con mắt tinh tường, bạn dễ dàng nhìn thấy những tiểu tiết chưa hoàn thiện ở đối phương, muốn họ phải hoàn hảo. Đối với phụ nữ, cần lưu ý xung đột có thể gây ra khắc khẩu hoặc lo ngại về sức khỏe của người phối ngẫu.',
                'dinh_huong' => [
                    'Học cách chấp nhận: Không ai hoàn hảo cả. Hãy yêu cả những khiếm khuyết của người bạn đời.',
                    'Giao tiếp cởi mở: Đừng giữ những suy nghĩ chỉ trích trong đầu. Hãy chia sẻ mong muốn của mình một cách nhẹ nhàng.',
                    'Tự chủ cảm xúc: Đừng phụ thuộc quá nhiều vào đối phương. Hãy giữ cho mình một khoảng trời riêng để thỏa mãn nhu cầu sáng tạo.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn nhìn chung là tốt, dẻo dai. Cần chú ý một chút về dạ dày, phổi, ruột.',
                'Sức khỏe tinh thần: Tâm trí luôn kiếm tìm sự mới mẻ, không hài lòng với thực tại là nguyên nhân chính gây ra căng thẳng. Có thể bị thất vọng hoặc trầm cảm nếu không đạt được mục tiêu.',
                'Hành động bốc đồng: Khi căng thẳng, bạn có thể có những hành động hấp tấp gây ảnh hưởng đến sức khỏe hoặc tìm đến sự hưởng thụ quá mức như ăn uống, mua sắm để trốn tránh thực tế.',
                'Lieu_phap' => [
                    'Tìm sự bình yên nội tại: Thiền định, yoga là liều thuốc tốt nhất để làm dịu tâm trí hay xao động của bạn.',
                    'Cân bằng cuộc sống: Đừng để tham vọng công việc chiếm hết thời gian nghỉ ngơi.',
                    'Giải tỏa cảm xúc: Tìm một không gian để bạn được tự do biểu đạt cảm xúc như viết nhật ký, vẽ tranh thay vì kìm nén.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người tò mò, hiếu học và có tư duy khách quan.',
                'Tiềm năng trí tuệ: Bạn có khả năng học hỏi siêu phàm. Bạn có khả năng tổng hợp thông tin và đưa ra những ý tưởng độc đáo. Bạn hứng thú với văn học, triết học, tâm linh.',
                'Rào cản phát triển: Sự buồn chán là rào cản lớn nhất. Bạn cần sự kích thích liên tục. Nếu không, bạn sẽ trở nên lười biếng hoặc phân tán. Bạn cần học cách kỷ luật tâm trí.',
                'dinh_huong' => [
                    'Kỷ luật tinh thần: Hãy rèn luyện sự tập trung, chọn một lĩnh vực chuyên sâu để phát triển thành chuyên gia.',
                    'Nuôi dưỡng trí tuệ và sự phát triển: Tiếp tục học hỏi, khám phá để đạt được sự trưởng thành thực sự.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn khá hòa đồng, thân thiện và là một chủ nhà tuyệt vời.',
                'Phong cách giao tiếp: Bạn thông minh, hóm hỉnh và biết cách khuấy động không khí. Bạn thích kết giao với những người tử tế, tích cực và có trí tuệ.',
                'Quý nhân: Thành công của bạn phần lớn đến từ nỗ lực cá nhân, tự lực cánh sinh. Những mối quan hệ tích cực sẽ mang lại sự hỗ trợ về tinh thần và cơ hội.',
                'Rủi ro: Bạn có xu hướng che giấu cảm xúc thật, khiến người khác khó hiểu. Đôi khi bạn bị cuốn vào vấn đề của người khác quá sâu. Sự tự tin vào năng lực bản thân đôi khi bị hiểu lầm là khoảng cách hoặc sự xa cách, tính hay phán xét cũng có thể làm mất lòng bạn bè.',
                'chien_luoc' => [
                    'Hãy chân thành và bớt phán xét.',
                    'Giữ gìn các mối quan hệ bằng sự quan tâm thực sự.',
                    'Hãy nhớ rằng, sự hỗ trợ từ người khác là quý giá, nhưng đừng phụ thuộc vào nó.'
                ]
            ]
        ],
        'ky_suu' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp của sự kiên cường, tĩnh lặng và đầy tiềm năng: Một vùng đất phù sa ẩm ướt, giàu dinh dưỡng đang trải qua mùa đông lạnh giá, hay một đóa Hoa Thủy Tiên vươn lên mạnh mẽ từ trong tuyết trắng. Bạn có vẻ ngoài điềm đạm, không ồn ào. Nhưng ẩn sâu bên trong là một trí tuệ sắc sảo, một khả năng chịu đựng phi thường và một vận may tài lộc tự nhiên mà nhiều người mơ ước.',
                'La Bàn Thịnh Vượng sẽ giúp bạn "sưởi ấm" vùng đất của mình để tài năng được nảy mầm rực rỡ nhất.'
            ],
            'su_nghiep' => [
                'Bạn chinh phục người khác bằng thực lực và kết quả công việc.',
                'Đặc điểm cốt lõi và phong cách làm việc: Bạn sở hữu trí tuệ sắc bén và khả năng học hỏi thần tốc. Tư duy sáng tạo của bạn luôn gắn liền với tính thực tế và ứng dụng. Bạn có tài năng thiên bẩm trong việc tổ chức, sắp xếp và giải quyết vấn đề một cách logic. Khi cần, bạn thể hiện khả năng hùng biện và sự lôi cuốn bất ngờ.',
                'Tiềm năng phát triển: Chuyên môn sâu: Phù hợp với nghiên cứu, kỹ thuật, phân tích dữ liệu hoặc y học. Giáo dục và văn hóa: Tỏa sáng trong vai trò nhà giáo, nhà văn, biên tập viên hoặc lĩnh vực xuất bản. Ngoại giao và kết nối: Thành công trong du lịch, ngoại giao, quan hệ công chúng hoặc giải trí.',
                'Thách thức và rủi ro: Rào cản tâm lý lớn nhất là sự mau chán. Nếu công việc quá tẻ nhạt, bạn có thể cảm thấy chán nản và bỏ cuộc. Sự cố chấp và khó thay đổi tư duy khiến bạn khó thích nghi khi kế hoạch thay đổi đột ngột.',
                'dinh_huong' => [
                    'Tìm kiếm nhiệt lượng và tăng sự tự tin: Chủ động nhận dự án mới, học thêm kỹ năng mới hoặc tìm cộng sự nhiệt huyết để truyền lửa.',
                    'Lập kế hoạch chi tiết: Chia nhỏ mục tiêu lớn thành những bước đi cụ thể để duy trì động lực.',
                    'Độc lập và tự chủ: Hãy hướng tới việc trở thành chuyên gia độc lập hoặc làm chủ công việc kinh doanh.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được trời phú cho vận may về tài lộc.',
                'Tư duy tài chính: Tài lộc đến với bạn một cách tự nhiên nhờ sự chăm chỉ và uy tín. Bạn có tư duy tài chính thực tế, tin vào sự tích lũy bền vững, không thích mạo hiểm.',
                'Tiềm năng thịnh vượng: Sự trung thực và trách nhiệm khiến mọi người tin tưởng giao phó cơ hội cho bạn. Khả năng quản lý tài chính đáng nể, bạn biết cách vun vén để tiền đẻ ra tiền một cách an toàn. Bạn có duyên với các tài sản liên quan đến đất đai, bất động sản.',
                'Rủi ro cần lưu ý: Cẩn thận bị người thân, bạn bè lợi dụng tiền bạc hoặc vướng vào tranh chấp tài chính.',
                'chien_luoc' => [
                    'Đầu tư vào bất động sản: Đây là kênh giữ tiền an toàn và phù hợp nhất.',
                    'Hợp tác, tìm đối tác năng lượng mạnh mẽ, quyết đoán.',
                    'Tránh rủi ro cao: Không nên tham gia các hình thức đầu cơ lướt sóng mạo hiểm.',
                    'Hãy làm giàu chậm mà chắc.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình yêu, bạn là người nhiệt huyết, lãng mạn nhưng cũng chứa đầy mâu thuẫn nội tâm.',
                'Đặc điểm tình cảm: Bạn yêu hết mình, sẵn sàng hy sinh và chăm sóc cho đối phương. Bạn coi trọng sự chung thủy và bền vững. Tuy nhiên, bạn lại bị thu hút bởi những người có tính cách độc lập, mạnh mẽ, người thích tự do.',
                'Thách thức trong hôn nhân: Tính cách "nóng lạnh thất thường" cảm xúc thay đổi đa chiều, nội tâm phong phú, khó đoán. Có lúc nồng nhiệt, có lúc lạnh lùng, xa cách và giữ bí mật. Bạn hay đa nghi và tâm lý muốn giữ chặt đối phương. Xu hướng muốn kiểm soát cuộc sống của đối phương cũng có thể gây xung đột.',
                'dinh_huong' => [
                    'Học cách minh bạch: Hãy chia sẻ suy nghĩ và cảm xúc của mình một cách cởi mở hơn.',
                    'Tôn trọng sự khác biệt: Chấp nhận sự độc lập của người bạn đời như một phần tất yếu.',
                    'Hâm nóng tình cảm: Sự lãng mạn và những cử chỉ quan tâm nhỏ nhặt sẽ giúp xua tan sự lạnh lẽo.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn mang tính chất hàn lạnh và ẩm thấp, có thể có các vấn đề tiềm ẩn.',
                'Hệ tiêu hóa: Có thể mắc các bệnh về dạ dày, lạnh bụng, khó tiêu do ảnh hưởng đến tỳ vị.',
                'Những trăn trở nội tâm: Lo âu, hay suy nghĩ và bồn chồn là đặc điểm thường thấy. Có thể rơi vào trạng thái u sầu, trầm cảm nếu cảm xúc mất kiểm soát.',
                'Cơ bắp và xương khớp: Sự ẩm ướt có thể gây ra đau nhức, mỏi mệt.',
                'Lieu_phap' => [
                    'Giữ ấm: Thường xuyên vận động, tập thể dục để ra mồ hôi. Tắm nắng là cách thức tuyệt vời.',
                    'Chế độ ăn: Ưu tiên thực phẩm ấm nóng như gừng, tiêu, nghệ. Hạn chế đồ sống, lạnh.',
                    'Chăm sóc tinh thần: Tìm niềm vui, gặp gỡ bạn bè, đi du lịch đến những vùng đất nắng ấm để giải tỏa căng thẳng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn có một khao khát tìm hiểu sự thật mãnh liệt và là người học tập suốt đời.',
                'Tiềm năng trí tuệ: Bạn muốn đào sâu vào bản chất của vấn đề, không chấp nhận câu trả lời hời hợt. Bạn có trực giác tốt và khả năng cảm nhận tinh tế về nghệ thuật, cái đẹp.',
                'Rào cản phát triển: Sự mâu thuẫn giữa mong muốn ổn định an toàn và khát khao phiêu lưu khiến bạn do dự, không dám bứt phá. Đôi khi hoài nghi quá mức, bỏ lỡ cơ hội học hỏi.',
                'dinh_huong' => [
                    'Kỷ luật là chìa khóa, thiết lập và tuân thủ những thói quen tốt để vượt qua trạng thái trì hoãn.',
                    'Tham gia các hoạt động nghệ thuật, sáng tạo như viết lách, vẽ tranh, chơi nhạc để giải phóng năng lượng bồn chồn bên trong.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là một chủ nhà hiếu khách và có khiếu hài hước ngầm.',
                'Phong cách giao tiếp: Bạn thân thiện, dễ mến và biết cách tạo ra không khí ấm cúng. Tuy nhiên, bạn khá kén chọn bạn bè, thích những mối quan hệ chất lượng, sâu sắc.',
                'Tự lực: Thành công của bạn chủ yếu đến từ nỗ lực tự thân. Bạn chính là quý nhân của chính mình. Sự nỗ lực tự thân là gốc rễ, nhưng sự hỗ trợ từ cộng sự cũng là đòn bẩy quan trọng.',
                'Nguyên tắc ứng xử: Bạn đề cao sự trung thực và chân thành tuyệt đối. Chính vì đặt trọn niềm tin nên khi bị phản bội, vết thương lòng trong bạn thường rất sâu và khó phai mờ theo thời gian. Thay vì ôm giữ những ký ức buồn như đất giữ nước, hãy học cách buông xả để tâm hồn được hong khô và nhẹ nhõm hơn.',
                'chien_luoc' => [
                    'Hãy giữ thái độ hòa nhã nhưng cũng cần biết bảo vệ bản thân.',
                    'Đừng đặt quá nhiều kỳ vọng vào sự giúp đỡ của người khác để tránv thất vọng.',
                    'Xây dựng uy tín cá nhân, đó là nam châm hút những điều tốt đẹp đến với bạn.'
                ]
            ]
        ],
        'ky_mui' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là biểu tượng của sự sinh tồn phi thường và bền bỉ: Một cây xương rồng gai góc nhưng nở hoa rực rỡ giữa sa mạc khô cằn. Bạn là người thực tế, kiên cường, có trực giác mạnh mẽ và một khả năng tự lực cánh sinh đáng nể. Dù bề ngoài có vẻ thô mộc, bên trong bạn là một chiến binh không bao giờ gục ngã.',
                'La Bàn Thịnh Vượng sẽ giúp bạn biến sự khô cằn thành sức mạnh để nở hoa rực rỡ và thu hút tài lộc.'
            ],
            'su_nghiep' => [
                'Bạn mang trong mình tinh thần của một người làm kinh doanh bẩm sinh. Bạn thích tự mình làm chủ vận mệnh và kiến tạo giá trị.',
                'Tinh thần doanh chủ: Bạn chăm chỉ, đam mê và nuôi dưỡng những tham vọng lớn lao trong thầm lặng. Bạn tiếp cận công việc với tư duy thực tế và lý trí sắc bén. Bạn có khả năng lập kế hoạch tỉ mỉ và nhất quán. Một khi đã đặt mục tiêu, bạn sẽ làm mọi cách để đạt được nó.',
                'Lãnh đạo và tiên phong: Bạn có năng lực lãnh đạo đáng kinh ngạc, luôn sẵn sàng gánh vác những trách nhiệm nặng nề. Bạn có tinh thần tiên phong, dám dấn thân vào những con đường mới. Những ý tưởng độc đáo của bạn thường dẫn dắt người khác đi theo.',
                'Giải quyết vấn đề: Trực giác nhạy bén là vũ khí bí mật, giúp bạn đoán biết suy nghĩ người khác, một lợi thế lớn trong đàm phán và quản lý. Khi gặp khó khăn, bạn luôn tìm ra giải pháp thay vì than vãn.',
                'Thách thức: Bạn ghét sự đơn điệu và lặp lại. Nếu công việc thiếu tính thử thách, bạn có thể bị phân tán năng lượng. Đôi khi sự thiếu quyết đoán hoặc tự nghi ngờ bản thân khiến bạn chùn bước Vì tin tưởng tuyệt đối vào phán đoán của mình, đôi khi bạn trở nên cứng rắn và áp đặt mong muốn lên người khác.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc cho phép bạn tự do thể hiện và sáng tạo như viết lách, nghệ thuật, kinh doanh tự chủ.',
                    'Học cách ủy quyền và tin tưởng người khác, kỷ luật bản thân trong việc duy trì sự tập trung là chìa khóa.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được mệnh danh là người có may mắn về tài lộc. Bạn có duyên lớn với tiền bạc và khả năng tự chủ về tài sản.',
                'Tự tạo ra sự thịnh vượng: Bạn có khả năng tự mình làm giàu nhờ sự siêng năng và nhất quán hiếm có. Bạn có thể xây dựng cơ đồ từ bàn tay trắng một cách bền vững.',
                'Tư duy kinh doanh: Bạn có đầu óc kinh doanh khôn ngoan và nhìn thấy cơ hội sinh lời ở khắp nơi. Khát vọng làm giàu của bạn mãnh liệt như đất khô mong chờ cơn mưa rào, luôn muốn tích lũy để tìm cảm giác an toàn, điều này trở thành động lực mạnh mẽ thúc đẩy bạn kiếm tiền không ngừng nghỉ.',
                'Lối sống: Bạn thích sự sang trọng và tiện nghi. Đôi khi bạn có xu hướng tiêu xài hoang phí cho lối sống xa hoa, nhưng năng lực kiếm tiền của bạn thường đủ để chi trả cho những nhu cầu này.',
                'Rủi ro: Bạn hay lo lắng không cần thiết về tiền bạc. Đôi khi bạn thiếu sự phân tích chi tiết trong quản lý dòng tiền lớn. Cần đặc biệt cẩn trọng với những kế hoạch làm giàu nhanh chóng đầy rủi ro.',
                'dinh_huong' => [
                    'Hãy giữ sự thận trọng và tập trung vào giá trị cốt lõi.',
                    'Tránh xa các trò đỏ đen hoặc đầu cơ.',
                    'Phát triển kỹ năng quản lý tài chính chi tiết.',
                    'Tăng cường kỷ luật quản lý và thúc đẩy dòng tiền lưu thông liên tục để gia tăng thu nhập và bảo vệ tài sản.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người lãng mạn, nhạy cảm nhưng cũng khá kiểm soát và cực kỳ trung thành.',
                'Yêu thương sâu sắc: Bạn là người bạn đời tận tâm, ổn định và luôn muốn vun đắp. Bạn thích sự gần gũi và khao khát một mái ấm gia đình hạnh phúc, bình yên.',
                'Tiêu chuẩn chọn bạn đời: Bạn bị thu hút bởi những người tháo vát, hào phóng và đặc biệt là người có thể kích thích trí tuệ của bạn. Bạn cần sự tôn trọng và nể phục từ đối phương.',
                'Thách thức: Kiểm soát: Bạn có xu hướng muốn thống trị và kiểm soát đối phương, biến sự chân thành quá mức thành sự lo âu ngầm, đôi khi khiến bạn nảy sinh tâm lý phòng vệ quá mức trong tình yêu.. Khó bày tỏ: Bạn gặp khó khăn trong việc nói ra cảm xúc thật của mình, khiến bạn trông có vẻ lạnh lùng hoặc xa cách, có thể tạo ra hiểu lầm. Đối với phụ nữ, cần chú ý đến vấn đề sức khỏe sinh sản.',
                'chien_luoc' => [
                    'Bên cạnh những hành động chăm sóc ân cần, hãy học cách thể hiện tình yêu bằng lời nói ngọt ngào.',
                    'Giảm bớt sự kiểm soát và tôn trọng không gian riêng của nhau.',
                    'Tin tưởng vào trực giác nhưng đừng để sự tiêu cực lấn át phán đoán.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn tượng trưng như sự khô hạn của đất đai.',
                'Sức khỏe sinh sản: Đối với phụ nữ, cần đặc biệt nâng niu và chăm sóc hệ nội tiết cũng như sức khỏe sinh sản. Hãy duy trì thói quen kiểm tra định kỳ để luôn an tâm và khỏe mạnh.',
                'Nội tâm dậy sóng: Bạn có thể bị căng thẳng, lo âu và tâm trí xáo động, trạng thái bất an vô cớ.',
                'Thể chất: Bạn có sức chịu đựng tốt, nhưng vì làm việc quá sức và ít nghỉ ngơi nên có thể dẫn đến kiệt quệ mãn tính và các vấn đề về tiêu hóa.',
                'Lieu_phap' => [
                    'Thiền định là phương pháp bắt buộc để bạn tìm lại sự bình yên, giảm bớt sự xáo động nội tâm.',
                    'Nghệ thuật như âm nhạc, vũ đạo cũng là cách tuyệt vời để giải tỏa căng thẳng và kênh hóa năng lượng sáng tạo.',
                    'Uống đủ nước và ăn các thực phẩm thanh mát.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người học qua thực hành và quan sát. Bạn có một kho tàng ý tưởng độc đáo.',
                'Trí tuệ thực tế: Bạn không thích lý thuyết suông. Bạn muốn tự tay làm, tự trải nghiệm và học hỏi nhanh. Bạn biết cách ứng dụng kiến thức vào thực tế để tạo ra giá trị.',
                'Tư duy độc đáo: Bạn có cách tiếp cận cuộc sống khá riêng, đôi khi là kỳ quặc hoặc nổi loạn. Tư duy tiến bộ giúp bạn đi trước thời đại và có khả năng đổi mới.',
                'Thách thức: Sự phân tán năng lượng vào quá nhiều sở thích khiến bạn thiếu chuyên sâu. Sự thiếu tự tin ngầm đôi khi khiến bạn cố gắng kiểm soát mọi thứ để che giấu sự bất an.',
                'dinh_huong' => [
                    'Kỷ luật là chìa khóa vạn năng.',
                    'Hãy tìm một mục tiêu thực sự truyền cảm hứng và cống hiến cho nó.',
                    'Phát triển thói quen đọc sách triết lý.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hấp dẫn, lôi cuốn và có từ trường xã hội mạnh mẽ.',
                'Trung tâm của sự chú ý: Bạn hòa đồng, thân thiện và thích giao lưu. Bạn dễ dàng kết nối với mọi tầng lớp xã hội nhờ sự chân thành và hào phóng.',
                'Quý nhân: Bạn thường thu hút được sự hỗ trợ từ những người tốt xung quanh, đặc biệt là trong công việc. Mạng lưới quan hệ của bạn là một tài sản vô hình.',
                'Rủi ro: Bạn có thể bị ảnh hưởng bởi áp lực bạn bè. Đôi khi bạn trở nên độc tài trong nhóm để khẳng định vị thế. Hãy dùng sự tỉnh táo để nhận diện những người đến với bạn vì lợi ích, tránh đặt lòng tốt sai chỗ, phòng khi có người muốn lợi dụng sự hào phóng của bạn.',
                'chien_luoc' => [
                    'Hãy giữ vững sự tự chủ và chính kiến.',
                    'Đừng để người khác chi phối quyết định của bạn.',
                    'Sử dụng sự ngoại giao và lòng trắc ẩn để duy trì các mối quan hệ hài hòa, và tránh xa những tranh chấp quyền lực vô bổ.'
                ]
            ]
        ],
        'ky_hoi' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật thơ mộng nhưng cũng khá thực tế: Một vùng đất màu mỡ nằm ven biển, đang ngắm nhìn ánh hoàng hôn rực rỡ phản chiếu trên mặt nước. Bạn sở hữu vẻ ngoài ưa nhìn, sức hút tự nhiên, sự khôn khéo trong giao tiếp và một tham vọng thực tế mạnh mẽ.',
                'La Bàn Thịnh Vượng sẽ giúp bạn cân bằng giữa dòng nước cảm xúc và mảnh đất lý trí để đạt được cuộc sống viên mãn và thịnh vượng.'
            ],
            'su_nghiep' => [
                'Bạn là những nhà ngoại giao và quản lý bẩm sinh.',
                'Kỹ năng xã hội và lãnh đạo: Bạn có khả năng kết nối con người tuyệt vời và xây dựng mạng lưới quan hệ hiệu quả. Bạn nhanh trí, tư duy sắc bén và luôn là người đưa ra giải pháp sáng tạo khi tập thể bế tắc. Bạn có tố chất lãnh đạo, biết cách gây ảnh hưởng và thuyết phục người khác một cách nhẹ nhàng nhưng hiệu quả.',
                'Đa tài và linh hoạt: Bạn có thể thành công trong nhiều lĩnh vực từ kinh doanh, thương mại nhờ óc thực tế, khả năng đàm phán đến nghệ thuật, viết lách, truyền thông nhờ sự sáng tạo, khả năng biểu đạt. Lĩnh vực dịch vụ công, giáo dục hay công tác xã hội cũng khá phù hợp.',
                'Phong cách làm việc: Bạn làm việc chăm chỉ, có tổ chức và quyết tâm cao. Bạn muốn là người thiết lập trật tự và quy trình, không thích bị kiểm soát vi mô. Bạn có tiêu chuẩn đạo đức cao và thường đặt lợi ích chung lên trên mục tiêu cá nhân.',
                'Thách thức: Bạn có quá nhiều ý tưởng nhưng đôi khi thiếu kỷ luật để hoàn thành trọn vẹn. Tâm lý hứng khởi nhất thời nhưng thiếu sự bền bỉ là rào cản lớn. Bạn cũng có thể bị dao động cảm xúc, ảnh hưởng đến phán đoán và sự kiên định.',
                'chien_luoc' => [
                    'Hãy tập trung năng lượng vào những mục tiêu cụ thể và có tính khả thi cao.',
                    'Rèn luyện tính kỷ luật thép để biến ý tưởng thành hiện thực.',
                    'Sử dụng sự quyến rũ và trí tuệ của mình để lãnh đạo thay vì áp đặt quyền lực.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là điểm sáng rực rỡ của bạn. Bạn là người có số hưởng về tiền bạc và tài lộc.',
                'Vận may tài lộc: Bạn nắm giữ chiếc chìa khóa vàng mở cửa kho báu. Điều này mang lại cho bạn khả năng kiếm tiền xuất sắc và duy trì cuộc sống sung túc, thoải mái. Bạn có tư duy thực dụng, coi trọng sự an toàn vật chất tuyệt đối.',
                'Nguồn thu nhập: Bạn có thể kiếm tiền từ công việc ổn định, kinh doanh hoặc đầu tư đa dạng. Đối với phụ nữ, thường có số vượng phu, hoặc lấy được chồng giàu có, có năng lực tài chính tốt.',
                'Lối sống: Bạn có xu hướng thích hưởng thụ cuộc sống chất lượng cao và không ngại chi tiêu cho những trải nghiệm tốt.',
                'Rủi ro: Nếu thiếu kế hoạch quản lý vững chắc, dòng tiền đến với bạn tuy dồi dào nhưng cũng dễ dàng phân tán như nước chảy qua kẽ tay, khó tích lũy lâu dài. Xu hướng tận hưởng cuộc sống đôi khi khiến bạn lơ là việc kiểm soát ngân sách và chi tiêu bốc đồng cũng là một lỗ hổng tài chính lớn cần kiểm soát.',
                'dinh_huong' => [
                    'Hãy ưu tiên sự ổn định và tích lũy bền vững.',
                    'Đầu tư vào các kênh an toàn, có tính thanh khoản cao.',
                    'Tránh xa các kế hoạch làm giàu nhanh chóng đầy rủi ro và thiếu minh bạch.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người tình lý tưởng: Chung thủy, lãng mạn và đầy đam mê sâu sắc.',
                'Sức hút: Bạn có vẻ ngoài thu hút và tính cách dễ mến. Bạn nhiệt tình, thân thiện và khá biết cách quan tâm người khác một cách tinh tế.',
                'Nghiêm túc trong tình cảm: Bạn coi trọng hôn nhân và sự ổn định gia đình là bến đỗ cuối cùng. Bạn sẵn sàng hy sinh và hỗ trợ đối phương hết mình. Bạn tìm kiếm một người bạn đời thông minh, sáng tạo để có sự kết nối về tinh thần và trí tuệ.',
                'May mắn: Đối với phụ nữ, thường có hôn nhân tốt đẹp, cuộc sống an nhàn. Chồng thường là người có năng lực, địa vị.',
                'Thách thức: Sự cẩn trọng và cân nhắc quá kỹ đôi khi khiến bạn bỏ lỡ thời điểm vàng để đưa ra quyết định dứt khoát và có thể bị tổn thương cảm xúc. Đôi khi bạn muốn kiểm soát đối phương hoặc trở nên quá phụ thuộc vào sự an toàn mà họ mang lại. Sự nghi ngờ và tâm lý muốn sở hữu và bảo vệ người thương quá mức đôi khi tạo ra những cơn sóng ngầm trong mối quan hệ, có thể làm rạn nứt tình cảm.',
                'chien_luoc' => [
                    'Đừng vội vàng cam kết.',
                    'Học cách tin tưởng và trao quyền tự do cho nhau.',
                    'Giao tiếp thẳng thắn và trung thực là cách tốt nhất để giải tỏa những nghi ngờ âm ỉ trong lòng.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng lớn từ cảm xúc và trạng thái tinh thần.',
                'Tinh thần: Bạn có thể bị lo lắng, bồn chồn và căng thẳng thần kinh do suy nghĩ quá nhiều. Nếu để cảm xúc lấn át, bạn dễ rơi vào trạng thái trầm lắng và khép kín sâu sắc hoặc u sầu kéo dài. Sự kìm nén cảm xúc cũng gây hại cho sức khỏe lâu dài.',
                'Thói quen sinh hoạt: Bạn có xu hướng thích hưởng thụ nên có thể sa đà vào ăn uống, vui chơi quá độ, dẫn đến các vấn đề về tiêu hóa và cân nặng.',
                'Vấn đề thể chất: Chú ý các bệnh liên quan đến thận, bàng quang và hệ tiêu hóa.',
                'Lieu_phap' => [
                    'Tìm kiếm sự bình yên nội tại thông qua thiền định, yoga hoặc các hoạt động tâm linh.',
                    'Tăng cường vận động như bơi lội, chạy bộ để tiêu hao năng lượng dư thừa và tránh sự trì trệ của bản thân.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người thông minh, ham học và có trực giác mạnh mẽ hiếm có.',
                'Học hỏi đa dạng: Bạn thích tìm hiểu nhiều lĩnh vực khác nhau và có khả năng tiếp thu kiến thức nhanh chóng, biết cách ứng dụng vào thực tế. Khả năng tự học của bạn khá đáng nể.',
                'Trực giác: Bạn có khả năng tâm linh tiềm ẩn và trực giác khá mạnh mẽ. Càng lớn tuổi, trí tuệ và trực giác của bạn càng phát triển và dẫn dắt bạn đi đúng hướng.',
                'Thách thức: Bạn có xu hướng khá mất tập trung. Bạn biết nhiều nhưng có thể không sâu. Sự do dự, lo lắng và tính trì hoãn cũng cản trở bạn tiến lên.',
                'dinh_huong' => [
                    'Hãy tìm một mục đích sống cao cả hoặc lý tưởng lớn để neo giữ tâm trí.',
                    'Chuyển hóa sự sáng tạo vào các hoạt động thực tế có tính kỷ luật.',
                    'Học cách tin tưởng vào trực giác của mình, đồng thời cân bằng nó bằng tư duy logic.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người của công chúng, quyến rũ và được nhiều người yêu mến.',
                'Mạng lưới rộng: Bạn dễ dàng kết bạn với mọi tầng lớp xã hội nhờ sự duyên dáng và tính cách dễ chịu. Bạn là người hòa giải tuyệt vời trong các mối quan hệ.',
                'Quý nhân: Bạn thường thu hút được sự giúp đỡ từ những người có quyền lực hoặc địa vị. Sự nhiệt tình của bạn là nam châm hút quý nhân.',
                'Rủi ro: Bạn có thể bị ảnh hưởng bởi áp lực bạn bè và đôi khi bị cuốn vào vấn đề của người khác. Đôi khi bạn cảm thấy thiếu sự hỗ trợ từ gia đình nền tảng.',
                'chien_luoc' => [
                    'Hãy chọn lọc bạn bè cẩn thận.',
                    'Đừng để bị cuốn theo đám đông một cách vô thức.',
                    'Sử dụng sự duyên dáng và lòng trắc ẩn để xây dựng những mối quan hệ chất lượng và bền vững.'
                ]
            ]
        ],
        'mau_ngo' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một ngọn núi lửa đang hoạt động, hay một vùng sa mạc nóng bỏng dưới ánh mặt trời gay gắt. Bạn là hiện thân của sức mạnh, ý chí sắt đá, tham vọng cháy bỏng và sự cuồng nhiệt. Bề ngoài uy nghiêm, khó gần, nhưng bên trong là một lò lửa của khát khao chinh phục.',
                'La Bàn Thịnh Vượng sẽ giúp bạn kiểm soát "ngọn núi lửa" ấy, biến sức tàn phá thành năng lượng kiến tạo những kỳ tích vĩ đại.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra với khí chất của một vị tướng quân, bạn không phải người sinh ra để phục tùng.',
                'Tố chất lãnh đạo và tiên phong: Bạn quyết đoán, mạnh mẽ, kỷ luật và đầy uy quyền. Bạn muốn là người ra lệnh và người chịu trách nhiệm sau cùng. Bạn là người tiên phong, dám chấp nhận rủi ro để khai phá những vùng đất mới.',
                'Năng lượng làm việc: Khả năng làm việc của bạn đạt cường độ cao khủng khiếp. Khi đã xác định mục tiêu, bạn lao vào như một con chiến mã, không gì có thể ngăn cản. Bạn thường duy trì cường độ làm việc cao độ.',
                'Lĩnh vực phù hợp: Bạn tỏa sáng trong kinh doanh như khởi nghiệp, xây dựng đế chế, và truyền thông, diễn thuyết thu hút đám đông.',
                'Thách thức: Điểm yếu lớn nhất là sự nóng nảy và độc đoán. Bạn thiếu kiên nhẫn với người chậm chạp và khó chấp nhận sự phản biện. Điều này khiến bạn có thể bị cô lập hoặc tạo ra kẻ thù.',
                'chien_luoc' => [
                    'Học cách lắng nghe, hãy tìm những cộng sự điềm tĩnh, chi tiết để bổ khuyết cho bạn.',
                    'Kiểm soát cơn giận, đừng để sự nóng giận thiêu rụi thành quả bao năm gây dựng.',
                    'Rèn luyện sự tổ chức, hãy sử dụng công cụ hoặc nhân sự để sắp xếp công việc khoa học hơn.'
                ]
            ],
            'tai_chinh' => [
                'Đối với bạn, tiền bạc là phương tiện để thể hiện quyền lực và khẳng định vị thế tối thượng.',
                'Tham vọng làm giàu: Bạn có tham vọng tài chính cực lớn. Bạn nhạy bén với thị trường và dám chơi những ván bài lớn. Bạn không thích kiếm tiền lẻ, bạn muốn những thương vụ lớn. Bạn mang tư duy của một nhà đầu tư mạo hiểm.',
                'Tiềm năng thịnh vượng: Bạn có khả năng tự lực cánh sinh và đạt được thành công rực rỡ. Nhu cầu về tiền bạc của bạn là vô tận, tạo ra động lực kiếm tiền khủng khiếp.',
                'Rủi ro: Thăng hoa nhanh chóng nhưng cũng dễ biến động bất ngờ. Sự nóng vội và liều lĩnh là yếu tố gây hao hụt tài chính lớn nhất của túi tiền bạn. Bạn có thể bị cuốn vào các hoạt động đầu cơ rủi ro cao. Thói quen chi tiêu hoang phí để duy trì hình ảnh hào nhoáng cũng là một lỗ hổng lớn.',
                'dinh_huong' => [
                    'Đầu tư có kỷ luật: Tránh xa cờ bạc và đầu cơ ngắn hạn, hãy học cách quản lý rủi ro.',
                    'Kiếm tiền chính đạo: Sự giàu có bền vững phải đến từ giá trị thực.'
                ]
            ],
            'tinh_duyen' => [
                'Tình cảm của bạn giống như một ngọn lửa bùng cháy giữa sa mạc: nồng nhiệt, đam mê, chiếm hữu và đầy kịch tính.',
                'Yêu ghét rõ ràng: Bạn không có khái niệm lấp lửng. Khi yêu, bạn dành trọn trái tim, chăm sóc và bảo vệ đối phương hết mình. Bạn muốn là người hùng che chở hoặc hậu phương vững chắc.',
                'Tâm lý bảo vệ thái quá: Sự bất an sâu thẳm khiến bạn luôn muốn kiểm soát người yêu. Bạn có tâm lý mong muốn được sở hữu trọn vẹn, muốn đối phương phải hoàn toàn thuộc về mình.',
                'Thách thức: Những cơn giận dữ bùng phát có thể làm tổn thương người bạn đời. Hôn nhân có xu hướng xảy ra tranh cãi vì sự bướng bỉnh, không ai chịu ai. Tiêu chuẩn chọn bạn đời của bạn khá khó tìm.',
                'chien_luoc' => [
                    'Học cách thỏa hiệp, hôn nhân không phải chiến trường.',
                    'Tin tưởng, buông bỏ sự kiểm soát, hãy để cho đối phương có không gian riêng.',
                    'Kết hôn muộn, thời gian sẽ làm nguội bớt cái đầu nóng, giúp bạn có những lựa chọn chín chắn hơn.'
                ]
            ],
            'suc_khoe' => [
                'Cơ thể bạn sở hữu nguồn năng lượng giống như động cơ luôn chạy tối đa công suất, nếu không có cơ chế làm mát, động cơ rất dễ bị quá nhiệt.',
                'Cần đặc biệt quan tâm đến huyết áp và tim mạch, tránh để cảm xúc kích động mạnh gây áp lực lên thành mạch.',
                'Viêm nhiễm và nóng trong người: Cơ thể bạn có thể bị viêm nhiễm, mụn nhọt, táo bón, các bệnh về mắt.',
                'Tai nạn thương tích: Cẩn thận nguy cơ về tai nạn xe cộ, chấn thương hoặc phẫu thuật. Tính cách vội vàng càng làm tăng rủi ro này.',
                'Cách thức cân bằng: Làm mát cơ thể là ưu tiên hàng đầu. Uống nhiều nước, ăn đồ mát. Tránh xa rượu bia, đồ cay nóng.',
                'Lieu_phap' => [
                    'Sống chậm lại, học thiền, hít thở sâu để làm dịu hệ thần kinh đang căng như dây đàn.',
                    'Thận trọng tối đa khi lái xe hoặc làm việc với máy móc, dao kéo.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, học nhanh và có tư duy logic sắc bén, nhưng cần sự tu dưỡng để thuần hóa bản năng.',
                'Trí tuệ thực chiến: Bạn thích những kiến thức thực tế, áp dụng được ngay để ra tiền hoặc ra quyền. Bạn không thích lý thuyết suông. Trí nhớ của bạn khá tốt.',
                'Rào cản phát triển: Sự thiếu kiên nhẫn và có xu hướng muốn đốt cháy giai đoạn. Bạn cũng có xu hướng tự mãn, cho rằng mình đã biết đủ và không chịu nghe lời khuyên.',
                'Nguy cơ tâm linh: Với trực giác mạnh và nội lực thâm sâu, bạn dễ bị thu hút bởi các bộ môn huyền học hoặc tâm linh. Tuy nhiên, cần giữ cái đầu lạnh và trí tuệ sáng suốt, có chính kiến để không sa đà vào những niềm tin mê tín, thiếu cơ sở khoa học, dẫn đến lệch lạc trong tư duy.',
                'dinh_huong' => [
                    'Học kỹ năng quản trị cảm xúc và lãnh đạo: Đây là chìa khóa để bạn trở thành một người dẫn đầu vĩ đại thay vì một người lãnh đạo độc lập.',
                    'Đọc sách: Giúp bạn tĩnh tâm và mở rộng chiều sâu tư duy.',
                    'Tìm người thầy: Một người thầy nghiêm khắc và uyên bác sẽ giúp bạn đi đúng hướng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao của các buổi tiệc, quyến rũ, hài hước và hào phóng.',
                'Tâm điểm chú ý: Bạn luôn nổi bật trong đám đông. Bạn thích kết giao với những người tài giỏi, mạnh mẽ, có địa vị. Bạn xem các mối quan hệ là đòn bẩy quan trọng để khẳng định bản thân.',
                'Quý nhân: Bạn thường thu hút được những cộng sự đắc lực hoặc người có quyền lực giúp đỡ. Sự nhiệt huyết của bạn lan tỏa năng lượng tích cực.',
                'Rủi ro: Sự đa nghi khiến bạn khó có bạn thân thực sự. Tính cách độc đoán và lời nói thẳng thừng làm mất lòng bạn bè, tạo ra những sự đối lập ngầm, sự ghen ghét từ người xung quanh.',
                'chien_luoc' => [
                    'Hãy chân thành và bớt tính toán.',
                    'Dùng sự hào phóng để giúp đỡ mọi người không vụ lợi.',
                    'Xây dựng quan hệ dựa trên sự tin tưởng và tôn trọng.',
                    'Hãy nhớ: "Muốn đi nhanh hãy đi một mình, muốn đi xa hãy đi cùng nhau".'
                ]
            ]
        ],
        'mau_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp thâm trầm và đầy nội lực: Một ngọn núi đá vững chãi đứng lặng lẽ giữa màn sương, hay một ngọn đồi xanh mướt phủ lên dòng suối ngầm cuộn chảy. Bề ngoài, bạn nhu mì, trầm ổn và đáng tin cậy, nhưng bên trong lại ẩn chứa một thế giới nội tâm dậy sóng và khao khát mãnh liệt về sự an toàn. Bạn được ví là người "ngồi trên đống vàng" nhưng lại luôn mang tâm thế lo âu của người canh giữ kho báu.',
                'La Bàn Thịnh Vượng sẽ giúp bạn vén màn sương mù để lộ diện ngọn núi tài năng sừng sững.'
            ],
            'su_nghiep' => [
                'Bên dưới vẻ ngoài điềm đạm, bạn là một nhà chiến lược đại tài và một bậc thầy chuyên xử lý vấn đề.',
                'Tư duy kiến tạo: Bạn không thụ động chờ thời. Bạn sở hữu tư duy của một nhà quản trị, luôn muốn thiết lập trật tự cho sự hỗn loạn. Khi người khác hoảng loạn, bạn giữ được cái đầu lạnh đáng kinh ngạc để đưa ra giải pháp gãy gọn, hiệu quả nhất.',
                'Lãnh đạo ngầm: Bạn không ồn ào, phô trương nhưng lời nói tựa núi non. Bạn thích hợp với vai trò quản lý cấp cao, cố vấn chiến lược hoặc tự khởi nghiệp. Bạn muốn nắm quyền kiểm soát vận mệnh thay vì làm nhân viên thừa hành.',
                'Thách thức: Đôi khi, niềm tin sắt đá vào bản thân khiến bạn vô tình bỏ qua những ý kiến quý báu xung quanh. Hãy nhớ rằng, ngọn núi cao nhất cũng cần những sườn dốc thoai thoải để cỏ cây sinh sôi. Sự mềm mỏng không làm giảm uy quyền của bạn, mà chính là lạt mềm buộc chặt. Tham vọng quá lớn đôi khi khiến bạn ôm đồm, không dám tin tưởng giao việc cho người khác.',
                'chien_luoc' => [
                    'Hãy học cách ủy thác và phân quyền vì "một cây làm chẳng nên non", bạn cần những người đồng hành.',
                    'Tìm kiếm cộng sự giàu lòng nhiệt huyết để sưởi ấm sự lạnh lùng và đa nghi của bạn.',
                    'Rèn luyện sự lắng nghe để tránh trở thành kẻ độc tài cô độc.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là "sân nhà" của bạn, bạn hoàn toàn có khả năng kiểm soát tốt tình hình tài chính của bản thân.',
                'Bậc thầy tích lũy: Bạn có trực giác bẩm sinh về tiền bạc và tính toán cực kỳ thực tế. Bạn không mơ mộng hão huyền mà biết cách làm cho tiền đẻ ra tiền. Cơ hội kiếm tiền dường như luôn nằm ngay dưới chân bạn.',
                'May mắn từ đối tác: Bạn có thể gặp vận may tài chính thông qua hôn nhân hoặc những mối quan hệ thân thiết. Người bạn đời có thể chính là thần tài giúp bạn quản lý và nhân rộng tài sản.',
                'Rủi ro: Thử thách lớn nhất trong quản lý tài chính là cảm xúc. Khi bất an hoặc buồn chán, bạn có xu hướng chi tiêu vô độ để lấp đầy khoảng trống nội tâm, hoặc ngược lại, trở nên keo kiệt quá mức cần thiết.',
                'dinh_huong' => [
                    'Đầu tư vào bất động sản là chiến lược phù hợp nhất để củng cố sự vững chãi cho ngọn núi của bạn.',
                    'Tách biệt tuyệt đối cảm xúc khỏi các quyết định đầu tư.',
                    'Lắng nghe lời khuyên tài chính từ bạn đời, họ là chìa khóa kho báu của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn giống như ngọn núi lửa ngủ yên dưới lớp băng, bên ngoài lạnh lùng, bên trong rực cháy.',
                'Sức hút bí ẩn: Bạn quyến rũ bởi sự thâm trầm, khó đoán. Bạn không vồn vã nhưng sự hiện diện của bạn tạo cảm giác an tâm, vững chãi cho người đối diện.',
                'Bảo bọc thầm lặng: Khi yêu, bạn chân thành và có xu hướng che chở tuyệt đối cho đối phương. Bạn ít nói lời hoa mỹ lãng mạn, mà thể hiện bằng hành động: chu cấp, gánh vác và bảo vệ.',
                'Thách thức: Sự bất an là điểm yếu tâm lý cần khắc phục. Bạn có thể có tâm lý muốn bảo vệ thái quá hoặc đa nghi vô cớ khiến đối phương ngột ngạt. Đôi khi, bạn im lặng quá lâu khiến người yêu cảm thấy bạn vô tâm, xa cách.',
                'chien_luoc' => [
                    'Kết hôn muộn là lời khuyên vàng để bạn đủ trưởng thành về cảm xúc.',
                    'Hãy chọn người bạn đời vui vẻ, hoạt bát như ánh nắng để xua tan màn sương của sự lo âu quanh bạn.',
                    'Học cách giao tiếp bằng lời nói, đừng bắt đối phương phải tự đoán ý mình.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn là tấm gương phản chiếu trạng thái tinh thần, tinh thần tác động trực tiếp đến thể chất.',
                'Vấn đề tiềm ẩn: Lớp sương mù bao quanh núi chính là sự lo âu, suy nghĩ quá nhiều. Việc dồn nén cảm xúc lâu ngày có thể dẫn đến các bệnh về dạ dày hoặc vấn đề về thận, bài tiết',
                'Cách thức cân bằng: Bạn cần sự tĩnh lặng thực sự chứ không phải sự kìm nén. Hãy tìm đến thiên nhiên, đất đai để nạp năng lượng.',
                'Lieu_phap' => [
                    'Thiền định là liều thuốc tiên dược giúp xua tan mây mù trí tuệ.',
                    'Duy trì chế độ ăn uống ấm nóng, đúng giờ để bảo vệ dạ dày.',
                    'Khi bế tắc, hãy đi du lịch để khơi thông dòng chảy năng lượng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ thực dụng và khả năng tự học đáng nể.',
                'Học từ trường đời: Bạn không thích lý thuyết suông. Mọi kiến thức bạn nạp vào đều phải phục vụ mục đích cụ thể: tiền bạc hoặc danh tiếng. Bạn có khả năng tập trung cao độ vào mục tiêu.',
                'Thách thức: Bạn có thể rơi vào sự tự mãn, đóng cửa tư duy vì nghĩ mình đã biết đủ. Khi thất bại, bạn có xu hướng tự trách mình và thu mình lại để chiêm nghiệm quá lâu.',
                'dinh_huong' => [
                    'Tìm một mục tiêu mang tính trách nhiệm cao cả để làm động lực sống.',
                    'Liên tục tìm kiếm thử thách trí tuệ mới để mài giũa sự sắc bén.',
                    'Học cách buông bỏ quá khứ, như dòng nước luôn chảy về trước.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người bạn trung thành nhưng cô đơn trong chính đám đông của mình.',
                'Ngoại giao văn minh: Bạn cư xử chừng mực, khéo léo và luôn giữ hình ảnh lịch sự. Mọi người tôn trọng bạn vì sự uy tín và cảm giác an toàn.',
                'Rủi ro: Bạn ít khi chia sẻ tâm tư thật, khiến người khác thấy bạn bí hiểm, khó gần. Sự đa nghi khiến bạn đẩy những người chân thành ra xa.',
                'chien_luoc' => [
                    'Chủ động kết giao với những người tích cực để sưởi ấm trái tim.',
                    'Đừng ngại chia sẻ sự yếu đuối, đó là cầu nối giữa người với người.',
                    'Hãy nhớ "muốn đi xa hãy đi cùng nhau", đừng để ngọn núi của bạn trở thành đảo hoang.'
                ]
            ]
        ],
        'mau_dan' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp của sự uy nghi, tráng lệ và sức sống hoang dã: Một ngọn núi cao sừng sững được bao phủ bởi những cánh rừng nguyên sinh, hay hình tượng một con hổ chúa sơn lâm đang ngạo nghễ tuần tra lãnh địa. Bề ngoài bạn có vẻ điềm tĩnh, trầm ổn, nhưng bên trong là một nội lực cuộn trào, một tham vọng ngút trời và khả năng biến những áp lực khắc nghiệt nhất thành bàn đạp để vươn lên.',
                'La Bàn Thịnh Vượng sẽ giúp bạn học cách "cưỡi hổ", biến nguồn năng lượng hoang dã ấy thành quyền lực thực sự.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra không phải để làm người thừa hành hay đứng trong bóng tối. Bạn mang trong mình cốt cách của một vị tướng.',
                'Tố chất lãnh đạo bẩm sinh: Bạn sở hữu khí chất của người đứng đầu. Ngay cả khi không cố tình thể hiện, người khác vẫn cảm nhận được sự uy nghiêm và nể trọng bạn. Bạn có tầm nhìn xa, không sa đà vào chi tiết vụn vặt mà luôn hướng về mục tiêu chiến lược. Bạn thích hợp với vai trò người cầm trịch, người ra quyết định.',
                'Khát vọng tiên phong: Bạn ghét sự gò bó và những quy tắc cứng nhắc. Tinh thần độc lập thúc đẩy bạn tìm lối đi riêng. Bạn làm việc tốt nhất khi được tự chủ: Khởi nghiệp, làm freelancer cấp cao, hoặc nắm giữ các vị trí giám đốc, quản lý. Áp lực là nhiên liệu để ngọn lửa tham vọng trong bạn bùng cháy.',
                'Đa tài và thuyết phục: Ẩn sau vẻ ngoài lầm lì là khả năng giao tiếp và thuyết phục đáng nể. Bạn biết cách sử dụng uy quyền mềm để gây ảnh hưởng. Bạn có thể thành công rực rỡ trong chính trị, luật pháp, giáo dục hoặc các lĩnh vực đòi hỏi thần kinh thép.',
                'Thách thức: Sự quyết đoán quá mức đôi khi trở thành áp đặt. Bạn có xu hướng tin rằng mình luôn đúng và có xu hướng áp đặt ý chí lên cộng sự. Sự thiếu kiên nhẫn, muốn đốt cháy giai đoạn đôi khi khiến bạn vấp ngã.',
                'chien_luoc' => [
                    'Hãy học cách cúi đầu, ngọn núi cao đến đâu cũng cần chân đế vững, sự khiêm tốn sẽ giúp bạn thu phục nhân tâm.',
                    'Đừng ôm đồm mọi thứ vì sợ người khác làm không tốt bằng mình.',
                    'Rèn luyện sự kiên định là chìa khóa để biến tham vọng thành di sản.'
                ]
            ],
            'tai_chinh' => [
                'Đối với bạn, tiền bạc không chỉ là phương tiện sinh tồn, mà là thước đo của danh dự và địa vị.',
                'Tư duy thịnh vượng: Bạn có gu hưởng thụ cao cấp. Bạn thích những thứ sang trọng, đắt tiền. Chính nhu cầu về lối sống đẳng cấp là động lực thúc đẩy bạn kiếm tiền không ngừng nghỉ. Bạn không chấp nhận sự tầm thường.',
                'Khả năng kiến tạo tài sản: Tài lộc thường đến từ danh tiếng, chức vụ và các mối quan hệ chất lượng. Bạn có khả năng đàm phán những thương vụ lớn, nhìn thấy cơ hội trong rủi ro.',
                'Rủi ro: Sự hào sảng là thương hiệu của bạn, nhưng đó cũng là con dao hai lưỡi. Đôi khi, vì quá trọng danh dự và nể nang bạn bè, bạn dễ đưa ra các quyết định chi tiêu hào phóng vượt quá ngân sách. Hãy nhớ rằng, sự tôn trọng thực sự đến từ nội lực, không phải từ những bữa tiệc xa hoa. Cảm xúc hưng phấn nhất thời chính là cái bẫy trong đầu tư mà bạn cần tỉnh táo để vượt qua. Nỗi sợ bị thua kém bạn bè cũng tạo áp lực tài chính vô hình.',
                'dinh_huong' => [
                    'Đầu tư vào tài sản thực như bất động sản là kênh an toàn nhất để giữ gìn thành quả.',
                    'Hãy phân biệt giữa cái mình cần và cái mình muốn muốn, đừng để sĩ diện làm rỗng túi tiền.',
                    'Đầu tư cho tri thức và bằng cấp chuyên môn là khoản sinh lời vô hạn.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người tình nồng nhiệt, quyến rũ nhưng cũng khó nắm bắt và kịch tính.',
                'Sức hút và chinh phục: Bạn tỏa ra từ trường hấp dẫn giới tính mạnh mẽ. Bạn thích cảm giác chinh phục. Bạn bị thu hút bởi người thông minh, cá tính, độc lập để kích thích bản năng săn mồi.',
                'Phong cách yêu: Khi yêu, bạn cực kỳ trung thành, bảo bọc và hào phóng. Bạn muốn là bầu trời che chở cho đối phương. Tuy nhiên, bạn có tính sở hữu khá cao. Bạn là trụ cột vững chắc cho gia đình.',
                'Thách thức tâm lý muốn bảo vệ thái quá và tính khí nóng nảy là liều thuốc độc giết chết sự lãng mạn. Bạn có xu hướng lý tưởng hóa người yêu rồi vỡ mộng. Đối với nam giới, có thể gia trưởng. Đối với phụ nữ, có thể lấn lướt bạn đời.',
                'chien_luoc' => [
                    'Hôn nhân muộn là chìa khóa hạnh phúc, khi cái tôi ngông cuồng đã được mài dũa.',
                    'Tôn trọng không gian riêng. Ngay cả Hổ cũng cần không gian để thở, đừng siết chặt đối phương.',
                    'Học cách thỏa hiệp. Gia đình không phải công ty, hãy dùng tình thương thay vì mệnh lệnh.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn giống như ngọn núi lửa, bên ngoài xanh tốt, bên trong tích tụ áp lực chờ phun trào.',
                'Năng lượng dồi dào: Bạn có sức sống mãnh liệt và khả năng phục hồi cực tốt. Bạn ít khi ốm vặt, nhưng một khi bệnh thường do tích tụ lâu ngày.',
                'Rủi ro vật lý: Các vấn đề về tiêu hóa do căng thẳng thần kinh gây ra như đau dạ dày, trào ngược. Bạn cũng có nguy cơ chấn thương tay chân do tính cách vội vàng.',
                'Vấn đề tinh thần: Áp lực thành công và sự kìm nén cảm xúc để giữ hình tượng mạnh mẽ khiến bạn có thể bị căng thẳng nặng, mất ngủ.',
                'Cách thức cân bằng: Bạn cần giải phóng năng lượng dư thừa. Sự tĩnh lặng tuyệt đối đôi khi không phù hợp bằng những hoạt động thể chất mạnh.',
                'Lieu_phap' => [
                    'Vận động cường độ cao như gym, leo núi là cách tuyệt vời để "xả" bớt bực bội. Đồng thời, thường xuyên về rừng, lên núi để nạp lại năng lượng bình an.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là viên ngọc thô cần được mài giũa bởi áp lực và kỷ luật để thành kim cương.',
                'Trí tuệ và trực giác: Bạn thông minh, học nhanh và có trực giác nhạy bén. Bạn có khả năng nhìn thấu tâm can người khác. Tiềm năng phát triển là vô hạn nếu kết hợp giữa tri thức và trải nghiệm thực tế.',
                'Khả năng ngôn ngữ: Bạn có khiếu viết lách hoặc hùng biện. Ngôn từ của bạn có lửa, có khả năng truyền cảm hứng và lay động đám đông.',
                'Thách thức: Sự tự mãn: hài lòng quá sớm với thực tại. Khi đạt thành tựu, bạn có thể ngủ quên trên chiến thắng, trở nên kiêu ngạo. Sự thiếu kỷ luật trong việc nhỏ cũng cản trở bạn vươn tới sự vĩ đại.',
                'dinh_huong' => [
                    'Tìm một lý tưởng sống cao cả như hoạt động xã hội để neo giữ tâm hồn.',
                    'Rèn luyện sự khiêm tốn. Hãy nhớ "cao nhân tắc hữu cao nhân trị".',
                    'Kỷ luật tự giác là bài học cốt lõi để làm được điều phi thường.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao trong các vòng tròn xã hội, luôn nổi bật và thu hút.',
                'Sức hút thủ lĩnh: Bạn bè vây quanh bạn vì nguồn năng lượng tích cực, sự hào sảng và cảm giác an toàn. Bạn trung thành, trọng nghĩa khí, sẵn sàng phản ứng lại bất chấp để bảo vệ người thân.',
                'Quý nhân: Bạn thu hút những người giỏi, có địa vị hoặc cá tính mạnh. Họ vừa là hỗ trợ, vừa là sự cạnh tranh thúc đẩy bạn.',
                'Rủi ro: Bạn có thể bị cô lập ở vị trí cao nhất, cô độc trên đỉnh vinh quang. Sự áp đặt có thể làm mất đi bạn bè chân thành. Cần đề phòng những mối quan hệ thiếu chân thành, lợi dụng sự hào phóng của bạn.',
                'chien_luoc' => [
                    'Hãy hạ cái tôi xuống và hòa đồng thực sự.',
                    'Tránh xung đột quyền lực không cần thiết.',
                    'Chọn bạn mà chơi: Kết giao với người điềm đạm để cân bằng lại sự bốc đồng.'
                ]
            ]
        ],
        'mau_thin' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp của sự trù phú, vững chãi và đầy tiềm năng: Một ngọn núi lớn vào giữa mùa xuân, cây cối xanh tươi bao phủ, nhưng bên trong lòng đất lại ẩn chứa những mạch nước ngầm dồi dào và các mỏ khoáng sản quý giá. Bạn là điềm tĩnh, bao dung nhưng đầy uy quyền. Bề ngoài bạn có thể chậm rãi, ôn hòa, nhưng bên trong là một nội lực thâm hậu, một trí tuệ sắc bén và khả năng tích lũy tài sản phi thường.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khai mở "kho báu" trong lòng núi để kiến tạo một cuộc đời rực rỡ.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tư duy của một kiến trúc sư đại tài, bạn nhìn thấy cả tòa lâu đài trước khi nó được xây dựng',
                'Tầm nhìn thực tế: Bạn có khả năng kết hợp giữa tầm nhìn vĩ mô và sự tỉ mỉ vi mô. Khi đặt ra mục tiêu, bạn đã có sẵn lộ trình trong đầu. Bạn là người xây dựng nền móng, tạo ra quy trình và biến ý tưởng trừu tượng thành kết quả hữu hình.',
                'Lãnh đạo bằng uy quyền: Tố chất lãnh đạo của bạn là bẩm sinh. Sự uy nghiêm, trầm ổn và năng lực thực tế của bạn tự động khiến người khác nể phục và đi theo. Bạn hợp với vai trò giám đốc điều hành, doanh nhân hoặc quản lý cấp cao.',
                'Đa dạng lĩnh vực: Với trí tuệ sâu sắc và sự ham học hỏi, bạn có thể tiến xa trong các lĩnh vực đòi hỏi chuyên môn cao như y học, luật pháp, kiến trúc hoặc nghiên cứu.',
                'Thách thức: Xu hướng muốn tự mình kiểm soát mọi việc là rào cản lớn nhất. Vì thường nhìn thấy giải pháp trước người khác, bạn có thể trở nên thiếu kiên nhẫn và muốn áp đặt ý kiến. Đôi khi, bạn cảm thấy lạc lõng nếu không có một mục tiêu đủ cao để chinh phục.',
                'chien_luoc' => [
                    'Hãy học cách trao quyền, một nhà lãnh đạo vĩ đại là người tạo ra nhiều nhà lãnh đạo khác.',
                    'Sử dụng sức mạnh của mình để nâng đỡ nhân viên như đất nuôi cây, thay vì chèn ép họ.',
                    'Khi mất phương hướng, hãy tìm về thiên nhiên để tái tạo năng lượng.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là sân nhà của bạn, bạn được mệnh danh là người ngồi trên kho tiền.',
                'Tư duy tích lũy: Bạn có khả năng đánh hơi thấy tiền và giữ tiền cực tốt. Bạn tin vào sự tích lũy bền vững, vào lãi suất kép và giá trị gia tăng theo thời gian. Dòng tiền của bạn giống như mạch nước ngầm: chảy êm đềm, liên tục.',
                'Cơ duyên với đất đai: Bạn có duyên lớn với bất động sản, tài nguyên, khoáng sản hoặc các ngành liên quan đến lưu trữ, ngân hàng. Khả năng trở nên giàu có và quyền quý là khá cao.',
                'Rủi ro: Kho tiền cần phải được quản lý đúng cách. Nếu gặp vận hạn xung khắc, kho tài lộc có thể bị biến động, dẫn đến hao tài tốn của hoặc kiện tụng. Ngoài ra, tính hào phóng và thích thể hiện đôi khi khiến bạn chi tiêu quá tay cho những thứ xa xỉ.',
                'dinh_huong' => [
                    'Hãy tập trung vào các kênh đầu tư giữ tiền an toàn như đất đai, nhà xưởng, kho bãi.',
                    'Hạn chế đầu tư mạo hiểm vào những năm xung khắc.',
                    'Học cách quản lý dòng tiền chặt chẽ, đừng để sự hào phóng biến thành sự phung phí vô nghĩa.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người tình nồng nhiệt, quyến rũ nhưng cũng đầy lý trí và kén chọn.',
                'Tiêu chuẩn cao: Bạn không dễ dãi trao gửi tình cảm. Bạn luôn tìm kiếm một người bạn đời xứng tầm cả về trí tuệ, địa vị lẫn tâm hồn. Bạn muốn xây dựng một gia đình kiểu mẫu.',
                'Sự cam kết: Khi yêu, bạn lãng mạn, gợi cảm và biết cách chăm sóc, che chở cho đối phương. Bạn là trụ cột vững chắc, là chỗ dựa an toàn tuyệt đối cho gia đình.',
                'Thách thức: Vì sở hữu tiêu chuẩn cao về sự hoàn mỹ, bạn thường mất nhiều thời gian để tìm được người tri kỷ xứng tầm, dẫn đến tình trạng kết hôn muộn. Trong đời sống hôn nhân, sự vững chãi của bạn đôi khi lại trở thành sự cứng nhắc. Những khoảng lặng kéo dài hay sự im lặng cố chấp chính là rào cản lớn nhất ngăn cách hai trái tim. Hãy nhớ rằng: Hạnh phúc là sự hòa hợp, không phải là cuộc thi xem ai đúng ai sai.',
                'chien_luoc' => [
                    'Hãy hạ bớt tiêu chuẩn hoàn hảo, tình yêu là sự chấp nhận và cùng nhau hoàn thiện.',
                    'Học cách thỏa hiệp. Trong gia đình, thắng thua không quan trọng bằng hòa khí.',
                    'Hãy tạo không gian cho đối phương thể hiện vai trò của họ, đừng cố gắng gánh vác hay kiểm soát tất cả.'
                ]
            ],
            'suc_khoe' => [
                'Bạn có nền tảng thể chất tốt, dẻo dai nhưng sự lười vận động cũng là vấn đề cần lưu tâm.',
                'Hệ tiêu hóa và chuyển hóa: Cẩn thận các vấn đề về dạ dày, tỳ vị. Bạn có thể bị đầy hơi, khó tiêu hoặc gặp vấn đề về cân nặng vì dư thừa năng lượng.',
                'Những trở ngại về mặt tâm thức: Sự lo âu, bồn chồn và áp lực phải thành công luôn đè nặng lên tâm trí như một tảng đá, dẫn đến căng thẳng thần kinh ngầm.',
                'Cách thức cân bằng: Bạn cần vận động liên tục để khơi thông dòng chảy năng lượng, tránh để bản thân bị trì trệ dẫn đến cảm giác lười.',
                'Lieu_phap' => [
                    'Các môn thể thao vận động như đi bộ, leo núi khá tốt để giúp khí huyết lưu thông.',
                    'Chế độ ăn nhiều rau xanh, hạn chế đồ ngọt và tinh bột.',
                    'Hãy chia sẻ hoặc viết ra những lo âu, đừng giữ trong lòng.',
                    'Tiếng cười là liều thuốc giải độc tốt nhất cho bạn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người có tư duy triết học và chiều sâu tâm linh, không chỉ dừng lại ở bề nổi.',
                'Trí tuệ sâu sắc: Bạn học để hiểu quy luật vận hành của thế giới. Bạn thích nghiên cứu về tôn giáo, huyền học, tâm lý hoặc khoa học xã hội. Bạn luôn đặt câu hỏi "tại sao?".',
                'Khả năng tự hoàn thiện: Bạn có ý thức khá cao về giá trị bản thân. Bạn không ngừng trau dồi để trở thành phiên bản tốt hơn, hoàn hảo hơn.',
                'Thách thức: Bạn có xu hướng bảo thủ, khó lay chuyển trước cái mới.. Khi đã tin vào điều gì, bạn khá khó thay đổi quan điểm. Điều này có thể khiến bạn bỏ lỡ những góc nhìn mới mẻ.',
                'dinh_huong' => [
                    'Kết hợp lý thuyết và thực hành, hãy áp dụng trí tuệ uyên bác để giải quyết vấn đề thực tế.',
                    'Mở rộng thế giới quan, đi du lịch và gặp gỡ nhiều người sẽ giúp bạn bớt đi sự định kiến.',
                    'Rèn luyện trực giác, hãy tin tưởng vào higher-self thông thái bên trong bạn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người ngoại giao tốt, hào sảng và có sức ảnh hưởng tự nhiên trong tập thể.',
                'Người kết nối: Bạn biết cách tạo ra sự hòa hợp và kết nối mọi người. Bạn được bạn bè yêu mến vì sự hào phóng, tốt bụng và đáng tin cậy. Lời nói của bạn thường có trọng lượng.',
                'Rủi ro: Bạn có thể vướng vào tranh cãi vì tính bảo thủ, muốn bảo vệ quan điểm đến cùng. Đôi khi lòng tốt đặt sai chỗ khiến bạn bị lợi dụng về tiền bạc.',
                'chien_luoc' => [
                    'Hãy giữ thái độ khiêm tốn và cầu thị. Sông sâu tĩnh lặng, lúa chín cúi đầu.',
                    'Đừng tham gia vào những cuộc tranh luận vô bổ để thỏa mãn cái tôi.',
                    'Dùng uy tín để lan tỏa giá trị nhân văn, bạn sẽ thu hút những người bạn tri kỷ thực sự.'
                ]
            ]
        ],
        'mau_than' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn vô cùng đặc biệt và giàu giá trị: Một ngọn núi đá thanh lịch, bên dưới lòng đất chứa đựng những mỏ khoáng sản kim loại quý giá chưa được khai phá. Bạn là người có khả năng "tự sinh", tự tạo ra giá trị và tài lộc cho chính mình. Bạn không thô cứng như đá tảng, mà tinh tế, sắc sảo và đầy ắp những ý tưởng mới mẻ. Bạn sở hữu một nội lực phong phú, tài năng đa diện và một sự quyến rũ tự nhiên.',
                'La Bàn Thịnh Vượng sẽ giúp bạn trở thành một "kỹ sư khai khoáng" tài ba để khai thác hết kho báu tiềm năng bên trong mình.'
            ],
            'su_nghiep' => [
                'Bạn là những nhà đổi mới bẩm sinh. Bạn không thích đi theo lối mòn đã cũ.',
                'Tư duy đột phá: Tư duy của bạn độc đáo, phi truyền thống và luôn tìm kiếm những cách thức mới lạ. Bạn sở hữu trí thông minh vượt trội và khả năng phân tích sắc bén. Bạn là người tháo vát, đa tài và có khả năng thích ứng tuyệt vời.',
                'Chuyên gia gỡ rối: Kỹ năng phân tích xuất sắc biến bạn thành một chuyên gia gỡ rối tài ba. Bạn nhìn thấy những chi tiết mà người khác bỏ qua. Bạn phù hợp với các vị trí quản lý, điều hành hoặc cố vấn chiến lược.',
                'Lãnh đạo kết nối: Bạn có tố chất lãnh đạo theo phong cách kết nối và truyền cảm hứng thay vì áp đặt. Bạn khéo léo trong đối nhân xử thế và làm việc nhóm.',
                'Lĩnh vực phù hợp: Bạn tỏa sáng trong PR, giải trí, chính trị, tâm lý học do sự duyên dáng hoặc sân khấu, âm nhạc, nghệ thuật do khả năng biểu đạt.',
                'Thách thức: Sự đa tài khiến bạn bị phân tán năng lượng. Bạn thiếu sự tập trung sâu và có thể gặp xung đột nội tâm.',
                'chien_luoc' => [
                    'Hợp tác là sức mạnh, hãy tận dụng khả năng làm việc nhóm.',
                    'Bạn sẽ thành công lớn hơn khi có những đối tác tin cậy.',
                    'Sự nghiệp của bạn sẽ thăng hoa nếu gắn liền với công tác, di chuyển hoặc môi trường quốc tế.',
                    'Tìm sự công nhận, chọn môi trường nơi tài năng độc đáo của bạn được trân trọng.'
                ]
            ],
            'tai_chinh' => [
                'Bạn mang trong mình tiềm năng tài lộc khổng lồ và khả năng tự kiến tạo sự giàu có.',
                'Mỏ khoáng sản vô tận: Tài lộc của bạn có tính chất sinh sôi nảy nở liên tục, không bao giờ cạn kiệt nếu bạn biết cách vận hành.',
                'Kỹ năng kiếm tiền: Bạn có khả năng quản lý tài chính tốt, đặc biệt là xử lý tiền bạc cho tổ chức. Bạn có sức hút giúp đàm phán các hợp đồng béo bở dễ dàng.',
                'Rủi ro: Bạn có xu hướng hào phóng và thích trải nghiệm cuộc sống xa xỉ, điều này đôi khi dẫn đến chi tiêu không kiểm soát.',
                'dinh_huong' => [
                    'Khai thác sự sáng tạo: Hãy biến những ý tưởng độc đáo, khả năng biểu đạt nghệ thuật thành công cụ in tiền.',
                    'Đầu tư vào quan hệ: Mạng lưới xã hội chính là tài sản vô hình lớn nhất của bạn.',
                    'Tự lực cánh sinh: Bạn không cần dựa dẫm vào ai để trở nên giàu có, chính bạn là người tạo ra của cải.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người lãng mạn, ấm áp, chu đáo nhưng cũng đầy lý tưởng hóa.',
                'Người vun đắp: Bạn đặt gia đình lên hàng đầu. Bạn sẵn sàng hy sinh để bảo vệ và chăm sóc người mình yêu. Bạn tìm kiếm sự ổn định lâu dài.',
                'Sự kết nối trí tuệ: Bạn bị thu hút bởi những người thông minh, có trí tuệ sắc sảo để có thể cùng bạn chia sẻ ý tưởng và tranh luận.',
                'Thách thức: Bạn lý tưởng hóa tình yêu, đặt kỳ vọng quá cao dẫn đến có thể bị thất vọng. Cảm xúc của bạn dao động mạnh do tính nhạy cảm. Bạn cũng có xu hướng giữ kín tâm tư, nội tâm khép kín.',
                'chien_luoc' => [
                    'Phát triển sự linh hoạt, đừng đánh giá đối phương dựa trên tiêu chuẩn vật chất hay sự hoàn hảo. Hãy chấp nhận sự khác biệt.',
                    'Kiểm soát sự áp đảo, hãy cẩn thận để không trở nên độc đoán hay áp đặt người bạn đời, vô tình lấn lướt người khác bằng lý lẽ sắc bén.',
                    'Tìm kiếm sự kích thích trí tuệ, một người bạn đời có thể cùng bạn khám phá tri thức sẽ giúp mối quan hệ bền vững.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn gắn liền mật thiết với tinh thần. Khi tâm trí thoải mái, cơ thể bạn tự nhiên sẽ khỏe mạnh và dẻo dai.',
                'Chăm sóc trí não: Bạn suy nghĩ rất nhanh và liên tục nên não bộ dễ bị mệt mỏi, căng thẳng. Hãy dành thời gian nghỉ ngơi hoàn toàn, tạm gác lại công việc để đầu óc được sạc lại năng lượng. Đây là cách tốt nhất để bạn duy trì sự sáng suốt lâu dài.',
                'Tìm về sự tĩnh lặng: Những hoạt động nhẹ nhàng như đi dạo, ngắm cảnh hay ngồi thiền rất tốt cho bạn. Chúng giúp bạn lắng lại, bớt nóng vội và củng cố sự vững vàng từ bên trong.',
                'Giải tỏa năng lượng: Khi cảm thấy bứt rứt hay khó chịu, đó thường là do bạn đang dư thừa năng lượng mà chưa dùng đến. Thay vì ngồi lo âu, hãy bắt tay vào một việc mới hoặc nói ra suy nghĩ của mình. Hành động cụ thể sẽ giúp bạn thấy nhẹ nhõm và phấn chấn hơn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người đa tài, đa diện với kho tàng tài năng ẩn giấu chờ được khai phá.',
                'Trí tuệ ưu việt: Bạn có khát khao học hỏi mãnh liệt. Bạn thông minh, kiến thức rộng và có khả năng nắm bắt ý tưởng cực nhanh. Sự kết hợp giữa tư duy lý trí và tài năng sáng tạo là điểm mạnh hiếm có.',
                'Trực giác sắc bén giúp bạn giải quyết các vấn đề phức tạp một cách đơn giản hóa.',
                'Thách thức: Sự trì trệ khi mất cảm hứng. Bạn cần liên tục được "kích thích" bởi những điều mới lạ. Sự bồn chồn khiến bạn có thể nhảy việc mà không hoàn thành việc gì.',
                'dinh_huong' => [
                    'Khai thác trực giác: Hãy tin tưởng vào trực giác, nó sẽ dẫn bạn đến những chân trời tri thức mới.',
                    'Thể hiện bản thân: Hãy theo đuổi các môn nghệ thuật hoặc sở thích sáng tạo để giải phóng năng lượng.',
                    'Học qua tương tác: Bạn học nhanh nhất thông qua việc giao tiếp, tranh luận và quan sát người khác.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hướng ngoại, hài hước và cực kỳ nổi tiếng trong các nhóm xã hội.',
                'Ngôi sao xã giao: Sự quyến rũ và hoạt bát giúp bạn dễ dàng kết bạn với mọi tầng lớp. Bạn là người bạn trung thành, hào phóng và luôn mang lại tiếng cười.',
                'Quý nhân: Bạn đạt được thành công lớn nhất thông qua sự hợp tác. Đối tác, đồng nghiệp chính là quý nhân của bạn.',
                'Rủi ro: Bạn có thể bị tổn thương bởi sự phán xét của người khác. Đôi khi bạn trở nên quá nhạy cảm hoặc áp đặt ý kiến trong nhóm bạn bè. Cẩn thận để không bị ảnh hưởng quá nhiều bởi cảm xúc những người khác có cá tính mạnh hơn.',
                'chien_luoc' => [
                    'Hãy giữ lòng trung thành nhưng đừng mù quáng.',
                    'Tránh đánh giá người khác qua vẻ bề ngoài hay vật chất.',
                    'Sử dụng sự duyên dáng và trực giác trời phú để duy trì sự hài hòa, biến các mối quan hệ xã giao thành liên minh vững chắc.'
                ]
            ]
        ],
        'mau_tuat' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là một ngọn núi đá chứa đầy khoáng sản quý giá, kiên cố và sừng sững, sự vững chãi tuyệt đối, lòng trung thành và một ý chí kiên định không gì lay chuyển được. Bề ngoài bạn có thể trầm mặc, khô khan như đá tảng, nhưng bên trong lại ẩn chứa một trái tim nóng bỏng, lòng trắc ẩn sâu sắc và những tiềm năng to lớn.',
                'La Bàn Thịnh Vượng sẽ giúp bạn mở cánh cửa vào mỏ khoáng sản nội tâm để trở nên giàu có và hạnh phúc viên mãn.'
            ],
            'su_nghiep' => [
                'Bạn là những chiến binh thực tế, chăm chỉ và đầy tham vọng. Bạn tin vào mồ hôi và nước mắt.',
                'Lãnh đạo kiên định: Bạn có tố chất lãnh đạo tự nhiên và phong cách làm việc kỷ luật thép. Bạn thực tế, tổ chức tốt và có khả năng ra quyết định nhanh chóng trong khủng hoảng. Bạn làm việc cật lực để đạt mục tiêu. Sự hoàn hảo là tiêu chuẩn tối thiểu của bạn.',
                'Đa tài và thích nghi: Bạn có thể thành công trong hầu hết mọi lĩnh vực nhờ sự thông minh và khả năng chịu áp lực. Bạn hợp với kinh doanh, tài chính ngân hàng, chứng khoán, quân đội, cố vấn nhờ sự đáng tin cậy hoặc xã hội, y tế nhờ lòng trắc ẩn.',
                'Thách thức: Sự thẳng thắn là phẩm chất quý giá của bạn, nhưng đôi khi nó lại sắc bén như lưỡi dao vô tình làm tổn thương người đối diện. Sự vững chãi của ngọn núi nếu thiếu đi cây cỏ và dòng suối sẽ trở nên khô cằn, cô độc. Hãy nhớ rằng, cương quyết giúp bạn đi nhanh, nhưng sự linh hoạt và lắng nghe mới giúp bạn đi xa và thu phục được nhân tâm',
                'chien_luoc' => [
                    'Học cách mềm mỏng hơn trong giao tiếp. Lời nói ngọt ngào mang lại lợi ích lớn.',
                    'Phát triển tư duy cởi mở để đón nhận lời phê bình thay vì tự ái.',
                    'Sử dụng kỹ năng ngoại giao để tạo ra môi trường làm việc hài hòa thay vì áp đặt kỷ luật quân đội.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính là mối quan tâm lớn và là thế mạnh tiềm ẩn của bạn. Bạn sở hữu kho tiền, hứa hẹn sự giàu có bền vững.',
                'Tiềm năng tích lũy: Bạn có khả năng tiết kiệm và tích lũy tài sản xuất sắc. Bạn có ý thức kinh doanh mạnh mẽ và con mắt nhìn ra những món hời. Bạn có tiềm năng trở nên khá giàu có và quyền quý, đặc biệt là vào giai đoạn hậu vận.',
                'Hành trình tài chính: Tuổi trẻ có thể gặp nhiều khó khăn do sự tranh đoạt hoặc gánh vác trách nhiệm. Nhưng càng về già, kho tài lộc càng mở rộng và vững chắc.',
                'Rủi ro: Bạn có xu hướng lo lắng thái quá về tiền bạc, dẫn đến quá thận trọng trong chi tiêu, thắt chặt tài chính thái quá hoặc bỏ lỡ cơ hội. Đôi khi vì muốn đi đường tắt, bạn dễ tin lầm người vì mong muốn đi nhanh.',
                'dinh_huong' => [
                    'Chọn con đường chậm mà chắc, sự kiên trì là vũ khí mạnh nhất của bạn.',
                    'Duy trì sự thận trọng trong đầu tư, tránh xa những rủi ro không cần thiết'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là người chân thành, trực tiếp nhưng cũng đầy mâu thuẫn giữa nhu cầu gắn kết và khao khát tự do.',
                'Yêu thương và tận tụy: Bạn ấm áp, chu đáo và cực kỳ trung thành. Bạn coi trọng giá trị gia đình và sẵn sàng hy sinh để bảo vệ người thân. Bạn là bờ vai vững chắc nhất.',
                'Nhu cầu tự do: Dù yêu sâu đậm, bạn không thích bị kiểm soát hay ràng buộc quá chặt. Bạn cần không gian riêng tư và sự độc lập.',
                'Thách thức: Bạn có xu hướng do dự khi cam kết vì luôn tìm kiếm sự hoàn hảo không có thật. Tính cách độc đoán, có xu hướng tranh cãi đúng sai là nguyên nhân chính gây ra xích mích gia đình.',
                'chien_luoc' => [
                    'Hãy tìm một người bạn đời thực tế nhưng cũng giàu trí tưởng tượng để làm mềm tâm hồn bạn.',
                    'Cân bằng giữa cái tôi và sự hòa hợp chung, thắng lý lẽ mà thua tình cảm thì cũng vô nghĩa.',
                    'Đừng vội vàng kết hôn, sự chín chắn của thời gian sẽ giúp bạn chọn đúng người.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn liên quan nhiều đến sự dồn nén và áp lực tinh thần.',
                'Nhạy cảm cao độ: Ẩn sau vẻ ngoài cứng rắn là tâm hồn cực kỳ nhạy cảm. Bạn dễ bị ảnh hưởng bởi năng lượng tiêu cực. Cảm xúc cực đoan bị kìm nén khiến bạn luôn trong trạng thái căng thẳng, lo âu ngầm.',
                'Vấn đề thể chất: Chú ý các bệnh về dạ dày do hay lo nghĩ, hệ tiêu hóa và các vấn đề về da.',
                'Cách thức cân bằng: Bạn cần tìm kiếm sự an định tinh thần hơn là sự thành công bề nổi.',
                'Lieu_phap' => [
                    'Tâm linh và tôn giáo là con đường tuyệt vời để bạn tìm thấy sự cân bằng.',
                    'Học cách chấp nhận bản thân, hiểu rằng cuộc đời không bao giờ hoàn hảo.',
                    'Biến cảm giác bứt rứt, khó chịu trong lòng thành hành động tích cực: một là chơi các môn đối kháng, hai là đi giúp đỡ người khác để chữa lành tâm hồn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người thông minh, uyên bác và có tư duy triết học sâu sắc. Bạn là nhà tư tưởng trong hình hài của một chiến binh.',
                'Học từ trải nghiệm: Bạn không tin vào lý thuyết suông. Bạn học qua thực tế, qua sai lầm, qua những vết sẹo và trải nghiệm xương máu.',
                'Tư duy sắc bén: Bạn có khả năng nắm bắt chi tiết nhanh chóng, tư duy logic và khách quan. Trí tưởng tượng phong phú giúp bạn có nhiều ý tưởng sáng tạo độc đáo.',
                'Khát vọng hoàn thiện: Bạn không bao giờ hài lòng với hiện tại, luôn muốn cải thiện bản thân tốt hơn nữa. Đây là động lực tiến bộ nhưng cũng là nguồn gốc của nỗi trăn trở thường trực.',
                'dinh_huong' => [
                    'Tìm một triết lý sống hoặc đức tin để làm kim chỉ nam vững chắc.',
                    'Du lịch và gặp gỡ nhiều người để phá bỏ sự bảo thủ, định kiến.',
                    'Học cách buông bỏ cái tôi và sự bướng bỉnh để lắng nghe lời khuyên của người khác.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người bạn trung thành, hào sảng và có sức hút xã hội lớn nhờ sự đáng tin cậy.',
                'Người kiến tạo hòa bình: Bạn thân thiện, thẳng thắn và biết cách làm hài lòng mọi người bằng sự chân thành. Bạn thích đóng vai trò người hòa giải, người phân xử công bằng trong nhóm.',
                'Mạng lưới cơ hội: Phần lớn cơ hội làm giàu của bạn đến từ các mối quan hệ xã hội. Bạn thích kết giao với những người quyền lực, có ý chí quyết tâm.',
                'Rủi ro: Bạn dễ vướng vào tranh cãi không đáng có vì tính bướng bỉnh. Sự nhạy cảm quá mức khiến bạn dễ bị tổn thương và phản ứng tự vệ mạnh mẽ trước những lời chỉ trích nhẹ nhàng.',
                'chien_luoc' => [
                    'Hãy giữ tính khách quan trong tranh luận.',
                    'Sử dụng lòng trắc ẩn để kết nối mọi người thay vì dùng lý lẽ để áp đặt.',
                    'Tăng cường giao tiếp khéo léo và mở rộng mạng lưới để thu hút quý nhân hỗ trợ.'
                ]
            ]
        ],
        'nham_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn đầy hùng vĩ và sâu sắc: “Một tảng băng trôi khổng lồ giữa đại dương mênh mông” hay “Một con sóng lớn cuộn trào không bao giờ nghỉ ngơi”. Phần mọi người nhìn thấy ở bạn chỉ là bề nổi: điềm đạm, hòa nhã và dễ chịu. Nhưng phần chìm bên dưới mới thực sự vĩ đại – đó là một thế giới nội tâm phức tạp, trí tuệ uyên bác và nguồn năng lượng vô tận.',
                'La Bàn Thịnh Vượng sẽ giúp bạn định hướng dòng chảy mạnh mẽ này để kiến tạo đại nghiệp.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu trí thông minh vượt trội và khả năng tư duy sắc bén như một kiến trúc sư đại tài cho sự nghiệp của mình.',
                'Phong cách làm việc: Bạn là người có tư duy độc lập. Bạn khao khát tự do và cảm thấy không thoải mái trong môi trường quá gò bó. Bạn không thích bị quản lý chi li hay phải làm những công việc lặp lại nhàm chán. Bạn làm việc hiệu quả nhất khi được trao quyền tự quyết, được tự do sáng tạo và vùng vẫy trong không gian của riêng mình.',
                'Động lực và tham vọng: Bên trong vẻ ngoài điềm tĩnh của bạn là một ngọn lửa tham vọng không bao giờ tắt. Bạn luôn muốn chinh phục những đỉnh cao mới và không ngại đối mặt với thử thách. Khó khăn với người khác là rào cản, nhưng với bạn là cơ hội để chứng tỏ bản thân.',
                'Lĩnh vực phù hợp: Lãnh đạo và quản lý: Phù hợp với vai trò CEO, Quản lý cấp cao nhờ khả năng ra quyết định lý trí và tách biệt cảm xúc. Khởi nghiệp và kinh doanh: Tư duy sắc bén giúp bạn nhìn thấy cơ hội và dám dấn thân vào con đường tự làm chủ.',
                'Thách thức: Sự tập trung cao độ vào mục tiêu đôi khi khiến bạn vô tình tạo khoảng cách với đồng nghiệp. Năng lượng quá mạnh nếu không có định hướng sẽ giống như dòng thác mạnh cần được dẫn dòng để tạo ra thủy điện thay vì chảy tràn tự do.',
                'chien_luoc' => [
                    'Cần dùng sự kỷ luật như đê điều để định hướng, dẫn dắt dòng chảy mạnh mẽ đi đúng hướng, tạo ra dòng điện năng khổng lồ.',
                    'Xây dựng mối quan hệ tốt đẹp với đồng nghiệp và cấp trên để tránh bị cô lập.',
                    'Kiên định với những mục tiêu cụ thể và dài hạn thay vì phân tán năng lượng.'
                ]
            ],
            'tai_chinh' => [
                'Tiền bạc với bạn không chỉ là phương tiện sinh tồn mà là công cụ để tận hưởng cuộc sống sung túc, tiện nghi.',
                'Trực giác tài chính: Bạn có máu kinh doanh bẩm sinh và trực giác nhạy bén với tiền bạc. Nếu bạn sinh vào mùa Xuân, mùa Hè hoặc sinh vào ban ngày, khả năng tích lũy tài sản của bạn là rất lớn. Bạn biết nắm bắt cơ hội và biến ý tưởng thành lợi nhuận nhanh chóng.',
                'Dòng tiền biến động: Năng lượng của bạn như dòng nước lũ, nên dòng tiền cũng thường biến động, đến nhanh mà đi cũng nhanh. Bạn có thể kiếm được rất nhiều tiền nhưng cũng dễ bị thất thoát do chi tiêu ngẫu hứng hoặc đầu tư mạo hiểm.',
                'Rủi ro: Cẩn trọng khi hợp tác làm ăn hoặc cho vay mượn, tránh tin người thái quá dẫn đến mất mát.',
                'dinh_huong' => [
                    'Chiến lược tốt nhất là giữ tiền bằng đất, hãy ưu tiên đầu tư vào bất động sản để giữ lại dòng nước tài lộc, ngăn nó trôi đi mất.',
                    'Xây dựng quỹ dự phòng và quản lý tài chính chặt chẽ để luôn cảm thấy an tâm.'
                ]
            ],
            'tinh_duyen' => [
                'Bạn là một ẩn số đầy quyến rũ, có sức hút tự nhiên nhưng đường tình cảm lại nhiều thăng trầm do nội tâm phức tạp.',
                'Mẫu người lý tưởng: Bạn bị thu hút bởi những người thông minh, độc lập, có cá tính mạnh và chiều sâu tâm hồn. Bạn cần một người bạn đời không chỉ để yêu thương mà còn để đối thoại, một người đủ bản lĩnh để làm bến đỗ vững chãi cho tâm hồn hay dao động của bạn.',
                'Thách thức hôn nhân: Đối với nam giới: hào phóng, chu đáo nhưng yêu thích tự do nên thường kết hôn muộn. Bạn cần người vợ hiểu chuyện và biết tôn trọng không gian riêng tư. Đối với phụ nữ: thông minh, sắc sảo và đôi khi có phần lấn lướt trong mối quan hệ. Thách thức lớn nhất là việc xây dựng niềm tin trọn vẹn do nội tâm nhạy cảm và sự cẩn trọng cao.',
                'dinh_huong' => [
                    'Đối với nam giới: Bạn cần người vợ hiểu chuyện và biết tôn trọng không gian riêng tư của bạn.',
                    'Đối với phụ nữ: Bạn nên ưu tiên phát triển sự nghiệp rực rỡ trước khi lập gia đình, hoặc chọn người bạn đời lớn tuổi hơn, chín chắn hơn để bao dung được cá tính mạnh mẽ của bạn.',
                    'Chìa khóa hạnh phúc là sự chia sẻ, hãy mở lòng tâm sự với người bạn đời. Đừng giữ lo lắng, nghi ngờ trong lòng rồi tự mình suy diễn.',
                    'Học cách buông bỏ kiểm soát, tin tưởng là sự lựa chọn dũng cảm mang lại bình yên.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe gắn liền mật thiết với trạng thái tinh thần. Bộ não hoạt động không ngừng nghỉ là con dao hai lưỡi.',
                'Vấn đề thần kinh: Bạn suy nghĩ liên tục như dòng nước chảy, có thể dẫn đến căng thẳng, mất ngủ, lo âu.',
                'Hệ cơ quan: Sự bất ổn cảm xúc kéo dài ảnh hưởng trực tiếp đến thận, bàng quang và hệ tiêu hóa.',
                'Lieu_phap' => [
                    'Bổ sung nhiệt lượng bằng các hoạt động thể thao ngoài trời, tiếp xúc ánh nắng.',
                    'Thiền định, yoga là liều thuốc tuyệt vời giúp làm dịu dòng suy nghĩ, đưa tâm trí về trạng thái tĩnh lặng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là mẫu người học một hiểu mười, sở hữu khả năng tiếp thu kiến thức siêu tốc.',
                'Trí tuệ thực chứng: Bạn không học vẹt. Bạn luôn kết nối lý thuyết với thực tế và tò mò về mọi thứ. Kiến thức là sức mạnh để bạn đạt được tự do.',
                'Thách thức: Sự thông minh có thể khiến bạn chủ quan hoặc thiếu kiên nhẫn với người chậm hơn. Bạn cũng có thể rơi vào bẫy phân tích quá mức, dẫn đến do dự không dám hành động.',
                'dinh_huong' => [
                    'Rèn luyện tính kiên trì và kỷ luật.',
                    'Đặt ra mục tiêu nhỏ và hoàn thành chúng để xây dựng sự tự tin.',
                    'Hãy nhớ: Trí thông minh chỉ có giá trị khi được chuyển hóa thành hành động cụ thể.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là bậc thầy ngoại giao, có khả năng kết nối với mọi tầng lớp xã hội.',
                'Sức hút xã hội: Bạn khéo léo, linh hoạt và biết cách nói chuyện đi vào lòng người. Sự hoạt ngôn và dí dỏm giúp bạn luôn là tâm điểm.',
                'Rủi ro: Bạn có thể bị ảnh hưởng bởi ý kiến người khác và hay nghi ngờ đồng nghiệp có ý đồ xấu. Nỗi sợ này thường đến từ sự bất an bên trong hơn là thực tế.',
                'chien_luoc' => [
                    'Tìm kiếm quý nhân mang năng lượng ấm áp, vững chãi, họ sẽ giúp bạn cảm thấy an toàn và đưa ra lời khuyên thực tế.',
                    'Xây dựng lòng tin và bớt đa nghi để các mối quan hệ trở nên bền chặt hơn.'
                ]
            ]
        ],
        'nham_tuat' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang một vẻ đẹp hùng vĩ, thanh cao và đầy tính kỷ luật: “Một hồ nước lớn tĩnh lặng, trong xanh nằm yên bình trên đỉnh núi cao”. Bạn sở hữu một tầm nhìn bao quát, rộng lớn của nước, nhưng lại được bao bọc bởi những bờ đê vững chãi của núi. Điều này tạo nên một con người có tư duy sâu sắc, nguyên tắc, trách nhiệm và khả năng nhìn xa trông rộng hiếm ai sánh bằng.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khơi thông dòng chảy trí tuệ này để kiến tạo những thành tựu để đời.'
            ],
            'su_nghiep' => [
                'Bạn không làm việc theo kiểu tự phát hay ngẫu hứng. Bạn là hiện thân của tư duy chiến lược, kế hoạch và những mục tiêu vĩ mô.',
                'Nhà chiến lược đại tài: Bạn có khả năng bao quát vấn đề và tư duy logic tuyệt vời. Giống như hồ nước trên cao nhìn xuống vạn vật, bạn biết cách sắp xếp, tổ chức và điều phối nguồn lực để đạt được mục tiêu lớn. Bạn sinh ra để làm lãnh đạo, quản lý, hoạch định chính sách hoặc cố vấn chiến lược.',
                'Làm việc vì lý tưởng: Bạn không chỉ làm việc vì tiền lương cuối tháng. Bạn muốn công việc của mình phải có ý nghĩa, phải đóng góp giá trị thực tế cho cộng đồng và xã hội. Bạn thường tỏa sáng trong các lĩnh vực như giáo dục, chính trị, quản lý công hoặc các hoạt động mang tính nhân đạo.',
                'Nguyên tắc và Kỷ luật: Bạn làm việc cực kỳ nghiêm túc và trách nhiệm. Bạn đặt ra những tiêu chuẩn khắt khe cho bản thân và cả đội ngũ. Chính sự uy nghiêm này giúp bạn xây dựng được uy tín vững chắc trong nghề nghiệp.',
                'Thách thức: Đôi khi bạn quá lý tưởng hóa mọi việc dẫn đến xa rời thực tế. Sự kiên định và giữ vững nguyên tắc trong quan điểm đôi khi khiến bạn chậm thích nghi với những thay đổi đột ngột. Bạn cũng dễ cảm thấy cô độc vì dòng nước trên đỉnh núi càng cao thì càng lạnh, ít ai hiểu được tầm nhìn của bạn.',
                'chien_luoc' => [
                    'Hãy học cách linh hoạt hơn, đôi khi lạt mềm buộc chặt.',
                    'Lắng nghe ý kiến của cấp dưới và đồng nghiệp để có cái nhìn đa chiều.',
                    'Sự kiên nhẫn và khả năng thích ứng mềm dẻo là chìa khóa giúp bạn đi đường dài.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được xem là người có năng lực giữ tiền xuất sắc. Tài chính của bạn thường đến chậm nhưng cực kỳ vững chắc.',
                'Hậu vận thịnh vượng: Tuổi trẻ có thể bạn phải bôn ba, vất vả để xây nền móng, nhưng càng về sau cuộc sống càng sung túc. Tài sản của bạn giống như nước trong hồ, tích tụ dần dần qua năm tháng rồi trở nên mênh mông.',
                'Quản lý tài sản chặt chẽ: Bạn rất hiếm khi tiêu xài hoang phí. Bạn trân trọng giá trị sức lao động và luôn có kế hoạch chi tiêu hợp lý. Bạn ưu tiên tích lũy những tài sản bền vững, có giá trị lâu dài như bất động sản hơn là những thú vui phù phiếm nhất thời.',
                'Rủi ro: Bạn hay lo xa thái quá về tiền bạc. Dù trong túi có tiền nhưng bạn vẫn cảm thấy chưa đủ an toàn, luôn lo sợ rủi ro trong tương lai. Điều này khiến bạn đôi khi trở nên khắc khổ, quá tiết kiệm với chính bản thân mình.',
                'dinh_huong' => [
                    'Hãy hào phóng hơn với bản thân và mọi người.',
                    'Quy luật của thịnh vượng là dòng chảy, nước cần chảy ra thì mới có nước mới chảy vào.',
                    'Việc chia sẻ hoặc giúp đỡ cộng đồng không chỉ giúp tâm hồn bạn thanh thản mà còn kích hoạt thêm nhiều nguồn tài lộc mới.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là mẫu người nghiêm túc, chân thành và đề cao sự cam kết trọn đời.',
                'Trách nhiệm và Cam kết: Bạn coi trọng hôn nhân và xem đó là một cam kết thiêng liêng. Bạn là người bạn đời mẫu mực, luôn lo lắng, vun vén cho gia đình. Bạn sẵn sàng hy sinh sở thích cá nhân để bảo vệ sự bình yên cho tổ ấm.',
                'Sự che chở: Bạn thích đóng vai trò người bảo vệ, định hướng cho người bạn đời. Bạn muốn mình là trụ cột vững chắc, là điểm tựa để người kia dựa vào khi mỏi mệt.',
                'Mặt trái: Đôi khi sự quan tâm của bạn biến thành sự kiểm soát hoặc gia trưởng. Bạn có xu hướng hay áp đặt suy nghĩ của mình lên đối phương vì cho rằng mình làm vậy là tốt cho họ. Sự nghiêm túc, ít thể hiện cảm xúc lãng mạn cũng khiến mối quan hệ đôi lúc trở nên nặng nề.',
                'chien_luoc' => [
                    'Hãy thả lỏng và mềm mỏng hơn.',
                    'Tình yêu cần sự chia sẻ và thấu cảm chứ không chỉ là trách nhiệm khô cứng.',
                    'Hãy tôn trọng không gian riêng của đối phương và học cách thể hiện tình cảm bằng những cử chỉ lãng mạn bất ngờ.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn nhìn chung rất bền bỉ, nhưng tâm bệnh là điều đáng lo ngại nhất.',
                'Căng thẳng nội tâm: Do hay lo xa, suy nghĩ nhiều và gánh vác trọng trách, đầu óc bạn lúc nào cũng căng như dây đàn. Điều này có thể dẫn đến chứng đau đầu, mất ngủ hoặc các vấn đề về dạ dày, tiêu hóa.',
                'Vấn đề xương khớp: Áp lực từ công việc và sự gồng mình gánh vác quá lâu có thể gây ra các vấn đề về lưng, cột sống hoặc thắt lưng.',
                'Giải pháp cân bằng: Bạn cần học nghệ thuật buông bỏ. Hãy dành thời gian để nghỉ ngơi thực sự, ngắt kết nối với công việc để não bộ được thư giãn.',
                'Lieu_phap' => [
                    'Leo núi hoặc đi dạo ở những vùng cao nguyên thoáng đãng là cách nạp năng lượng tuyệt vời nhất cho bạn. Sự tĩnh lặng và bao la của núi rừng sẽ giúp tâm trí bạn bình yên trở lại.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người có chiều sâu nội tâm và tư duy triết học bẩm sinh.',
                'Học qua trải nghiệm: Bạn trưởng thành qua những va vấp và thử thách thực tế hơn là sách vở. Mỗi khó khăn đi qua đều để lại cho bạn một bài học quý giá về nhân sinh quan.',
                'Điểm tựa tinh thần: Bạn thường có xu hướng tìm kiếm những giá trị tinh thần sâu sắc, đức tin hoặc tôn giáo để làm điểm tựa. Điều này giúp bạn giữ vững lập trường giữa cuộc đời đầy biến động.',
                'Thách thức: Sự bảo thủ là rào cản lớn nhất. Đôi khi bạn quá tin vào kinh nghiệm cá nhân mà bác bỏ những cái mới, khiến bạn chậm tiến hơn thời đại.',
                'dinh_huong' => [
                    'Hãy giữ cho tâm trí luôn cởi mở.',
                    'Tu dưỡng đạo đức và rèn luyện nội tâm là con đường dẫn bạn đến sự thông tuệ đích thực.',
                    'Học cách chấp nhận sự khác biệt của người khác.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn có mối quan hệ xã hội rộng rãi nhưng có sự phân tầng và chọn lọc rất kỹ lưỡng.',
                'Quảng giao có chiều sâu: Bạn chơi được với nhiều tầng lớp, nhưng bạn chỉ thực sự kết giao thân thiết với những người thành đạt, có địa vị hoặc có trí tuệ để học hỏi lẫn nhau.',
                'Được tin tưởng: Nhờ sự chín chắn, kín đáo và đáng tin cậy, bạn thường được cấp trên, người lớn tuổi tin tưởng và trao cho những trọng trách lớn. Họ chính là quý nhân giúp bạn thăng tiến.',
                'Rủi ro: Sự thẳng thắn và nguyên tắc quá mức của bạn có thể vô tình làm mất lòng người khác. Đôi khi sự thành công của bạn cũng khơi dậy lòng đố kỵ hoặc sự cạnh tranh không lành mạnh.',
                'chien_luoc' => [
                    'Hãy luôn giữ thái độ khiêm tốn. Lúa chín cúi đầu, càng thành công bạn càng nên nhún nhường.',
                    'Đừng để sự tự tin biến thành sự kiêu ngạo xa cách.',
                    'Đối xử chân thành với tất cả mọi người, bạn sẽ nhận được sự kính trọng thực sự từ cộng đồng.'
                ]
            ]
        ],
        'nham_ngo' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn vừa mãnh liệt vừa thư thái: “Một dòng suối khoáng nóng tuôn trào mạnh mẽ từ lòng đất” hay “Mặt hồ lung linh phản chiếu ánh nắng mặt trời rực rỡ”. Bạn là sự hòa quyện tuyệt vời giữa sự linh hoạt, sâu sắc của dòng nước và sức nóng, nhiệt huyết của ngọn lửa. Bạn sống bằng cảm xúc, hành động bằng trực giác và thành công nhờ sự nhạy bén thiên bẩm.',
                'La Bàn Thịnh Vượng sẽ giúp bạn điều hòa hai nguồn năng lượng đối lập này để kiến tạo một cuộc đời rực rỡ và cân bằng.'
            ],
            'su_nghiep' => [
                'Sự nghiệp của bạn được thúc đẩy bởi nguồn cảm hứng bất tận. Bạn không sinh ra để làm việc như một cỗ máy, bạn cần ngọn lửa đam mê để vận hành.',
                'Làm việc vì cảm hứng: Khi tìm thấy công việc yêu thích, năng lượng của bạn là vô tận. Bạn sáng tạo, nhiệt huyết và có thể cống hiến quên mình. Ngược lại, sự nhàm chán và lặp lại đơn điệu có thể làm giảm nguồn cảm hứng sáng tạo của bạn.',
                'Tư duy linh hoạt và ứng biến: Bạn sở hữu trí thông minh thực tế và khả năng ứng biến cực nhanh. Bạn khéo léo trong giao tiếp, biết cách thuyết phục và thu phục nhân tâm. Những lĩnh vực như kinh doanh, truyền thông, nghệ thuật, tiếp thị hay quan hệ công chúng là mảnh đất màu mỡ để bạn tỏa sáng.',
                'Tinh thần khởi nghiệp: Bạn có tố chất làm chủ và quản lý rất lớn. Bạn mang trong mình dòng máu kinh doanh, dám chấp nhận rủi ro và luôn nhìn thấy cơ hội trong khi người khác thấy khó khăn. Bạn thích hợp với vai trò người đứng đầu, người tiên phong hơn là nhân viên thừa hành.',
                'Thách thức: Cảm xúc của bạn hay lên xuống thất thường. Duy trì ngọn lửa nhiệt huyết là thách thức lớn nhất ngăn cản bạn đi đến đỉnh vinh quang. Bạn thường khởi đầu rất hoành tráng nhưng lại dễ bỏ cuộc khi gặp khó khăn hoặc khi hết hứng thú.',
                'chien_luoc' => [
                    'Kỷ luật là chìa khóa vàng cho sự thành công của bạn.',
                    'Hãy học cách duy trì sự kiên định ngay cả khi cảm hứng đã vơi đi.',
                    'Tìm kiếm những cộng sự có tính cách trầm ổn, kiên trì để bù đắp cho sự bay bổng và giữ bạn đi đúng lộ trình.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là người có duyên đặc biệt với tiền bạc. Sự nhạy bén giúp bạn hiếm khi lâm vào cảnh túng thiếu.',
                'Trực giác kinh doanh sắc bén: Bạn có khả năng đánh hơi thấy cơ hội kiếm tiền nhanh hơn người khác. Bạn nắm bắt xu hướng thị trường tốt và không ngại thử nghiệm những cách làm giàu mới mẻ. Dù khởi đầu tay trắng, sự lanh lợi sẽ giúp bạn nhanh chóng gây dựng cơ đồ.',
                'Nguồn thu đa dạng: Bạn có khả năng tạo ra dòng tiền từ nhiều nguồn khác nhau. Tài chính thường tìm đến bạn thông qua các hoạt động đầu tư, kinh doanh hoặc buôn bán năng động.',
                'Thói quen chi tiêu: Kiếm được nhiều nhưng bạn tiêu xài cũng rất thoáng. Bạn thích hưởng thụ cuộc sống, yêu cái đẹp và sự sang trọng. Đôi khi, những phút bốc đồng khiến bạn chi tiêu vượt kế hoạch hoặc đầu tư vào những dự án mạo hiểm rủi ro cao.',
                'Rủi ro: Quyết định tài chính dựa trên cảm xúc là rủi ro lớn nhất cần lưu ý. Tránh những quyết định tài chính khi tâm trạng đang quá hưng phấn hoặc quá chán nản, bạn có thể đưa ra những lựa chọn sai lầm gây hao hụt tài sản.',
                'dinh_huong' => [
                    'Bạn cần một thủ quỹ đáng tin cậy hoặc một cơ chế quản lý tài chính tự động để giữ tiền.',
                    'Hãy tập trung vào việc tích lũy tài sản bền vững, dài hạn thay vì chỉ chạy theo những khoản lợi nhuận ngắn hạn đầy rủi ro.',
                    'Luôn để lý trí làm mát cái đầu nóng trước khi đưa ra bất kỳ quyết định tài chính nào.'
                ]
            ],
            'tinh_duyen' => [
                'Tình yêu được xem là lẽ sống của bạn. Bạn lãng mạn, giàu cảm xúc và luôn khao khát một tình yêu nồng cháy, lý tưởng.',
                'Sức hút khó cưỡng: Bạn có sự duyên dáng ngầm, ngọt ngào và biết cách chiều chuộng. Dù là nam hay nữ, bạn đều tỏa ra sức hấp dẫn tự nhiên đối với người khác giới. Bạn thích cảm giác được yêu thương, được quan tâm và luôn muốn làm mới cảm xúc trong mối quan hệ.',
                'Bức tranh hôn nhân: Đối với nam giới, thường may mắn lấy được người vợ đảm đang, tháo vát hoặc có nền tảng kinh tế tốt, là hậu phương vững chắc cho sự nghiệp của bạn. Đối với phụ nữ, thông minh, sắc sảo và đôi khi có phần lấn lướt chồng. Bạn cần một người bạn đời bao dung, vững chãi và có sự nghiệp ổn định để làm chỗ dựa.',
                'Mặt trái của cảm xúc: Vì quá nhạy cảm và coi trọng tình yêu, bạn dễ bị tổn thương và có xu hướng phức tạp hoá vấn đề. Sự ghen tuông, suy diễn hoặc lo lắng thái quá của bạn đôi khi khiến đối phương cảm thấy mệt mỏi và ngột ngạt.',
                'chien_luoc' => [
                    'Hãy học cách yêu một cách bình yên hơn.',
                    'Hạnh phúc không phải lúc nào cũng cần sự kích thích hay cao trào. Hãy trân trọng những khoảnh khắc giản dị và tin tưởng vào người bạn đời của mình.',
                    'Trân trọng sự cam kết và làm chủ những rung động nhất thời của bản thân là cách tốt nhất để bảo vệ hạnh phúc gia đình.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn phụ thuộc vào sự cân bằng nhiệt độ và cảm xúc bên trong cơ thể.',
                'Xung đột nội tại: Sự đối lập giữa hai luồng năng lượng nóng và lạnh trong cơ thể bạn khá mạnh. Khi mất cân bằng, bạn có thể gặp các vấn đề về tim mạch, huyết áp hoặc thị lực.',
                'Hệ thần kinh nhạy cảm: Sự thay đổi tâm trạng liên tục có thể dẫn đến căng thẳng và rối loạn lo âu. Bạn có thể bị kích động, nóng nảy hoặc rơi vào trạng thái buồn bã vô cớ.',
                'Lối sống: Sở thích tiệc tùng, giao lưu có thể dẫn đến việc sinh hoạt thiếu điều độ, thức khuya, ảnh hưởng xấu đến sức khỏe lâu dài.',
                'Lieu_phap' => [
                    'Tìm về sự tĩnh lặng là liều thuốc tốt nhất. Thiền định, yoga hoặc âm nhạc nhẹ nhàng giúp làm dịu tâm trí bạn.',
                    'Duy trì thói quen uống đủ nước và tiếp xúc với thiên nhiên, sông hồ để cân bằng lại năng lượng nhiệt dư thừa trong người.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là mẫu người học nhanh, hiểu rộng nhưng cần rèn luyện chiều sâu.',
                'Trí tuệ thực chiến: Bạn học hỏi thông qua quan sát và trực giác nhiều hơn là lý thuyết sách vở. Bạn nắm bắt ý chính rất nhanh và biết cách ứng dụng linh hoạt vào thực tế.',
                'Tư duy đổi mới: Bạn ghét những lối mòn cũ kỹ. Sự sáng tạo và khao khát tìm kiếm những giải pháp độc đáo là động lực phát triển lớn nhất của bạn.',
                'Thách thức: Bạn biết rất nhiều thứ nhưng thường thiếu sự chuyên sâu. Sự thiếu kiên nhẫn khiến bạn khó trở thành bậc thầy trong một lĩnh vực hẹp nếu không nỗ lực rèn luyện.',
                'dinh_huong' => [
                    'Hãy rèn luyện sự tập trung.',
                    'Chọn ra lĩnh vực bạn đam mê nhất và cam kết đi đến cùng.',
                    'Đừng để những thú vui hay cơ hội mới mẻ bên ngoài làm xao nhãng mục tiêu lớn của cuộc đời.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là linh hồn của những cuộc vui, là thỏi nam châm thu hút mọi người xung quanh.',
                'Quảng giao và nổi bật: Bạn có mạng lưới quan hệ rộng khắp. Bạn vui vẻ, hào phóng và nhiệt tình. Mọi người thích ở bên cạnh bạn vì năng lượng tích cực và sự sôi nổi mà bạn lan tỏa.',
                'Xây dựng hình ảnh: Bạn rất biết cách chăm chút cho thương hiệu cá nhân. Bạn muốn xuất hiện hoàn hảo và được ngưỡng mộ. Điều này tốt cho công việc nhưng đôi khi khiến bạn mệt mỏi vì phải đeo mặt nạ vui vẻ ngay cả khi trong lòng đang buồn.',
                'Quý nhân: Những người mang lại may mắn cho bạn thường là những người có địa vị, năng lực tài chính hoặc những người có tính cách nhẹ nhàng, biết lắng nghe để dung hòa sự nóng nảy của bạn.',
                'chien_luoc' => [
                    'Hãy chân thành trong các mối quan hệ thay vì xã giao hời hợt.',
                    'Uy tín là thứ giữ chân những người bạn thực sự, đừng hứa suông chỉ để làm vui lòng người khác nhất thời.',
                    'Hãy cho phép bản thân được sống thật với cảm xúc của mình trước những người tri kỷ.'
                ]
            ]
        ],
        'nham_than' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp của chiều sâu và tri thức: “Một dòng suối mùa thu trong vắt, mát lạnh chảy qua những mỏ khoáng sản quý giá” hay “Một con tàu chứa đầy kho báu đang nằm yên tĩnh dưới đáy đại dương”. Bạn được sinh ra trên cái nôi của nguồn cội vững chắc và sự hỗ trợ vô tận. Bạn thông thái, nội tâm, dòng chảy cuộc đời bạn mang theo những giá trị ngầm to lớn nhưng lại ẩn chứa một nét trầm tư sâu lắng của sự hoài niệm.',
                'La Bàn Thịnh Vượng sẽ giúp bạn trục vớt con tàu kho báu ấy lên mặt nước để tỏa sáng rực rỡ.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu một trí tuệ sắc bén và khả năng học hỏi phi thường. Bạn không chỉ thông minh theo kiểu sách vở. Bạn còn sở hữu sự khôn ngoan của người từng trải.',
                'Nhà tư tưởng và phân tích: Bạn có khả năng nhìn thấu bản chất của vấn đề. Bạn thích nghiên cứu, tìm tòi và đào sâu vào những lĩnh vực đòi hỏi chuyên môn cao. Bạn không thích những gì hời hợt. Sự nghiệp của bạn thường gắn liền với trí tuệ, tư vấn hoặc giải quyết những vấn đề phức tạp mà người khác bất lực đầu hàng.',
                'Sự sáng tạo từ chiều sâu: Bạn có tâm hồn nghệ sĩ ẩn giấu. Sự nhạy cảm và trí tưởng tượng phong phú giúp bạn thành công trong các lĩnh vực như viết lách, nghệ thuật, thiết kế hoặc những công việc đòi hỏi sự tinh tế và tư duy trừu tượng.',
                'Phong cách làm việc: Bạn là người có trách nhiệm và làm việc có kế hoạch. Tuy nhiên, bạn như con sói đơn độc, thích làm việc độc lập hoặc trong một nhóm nhỏ tin cậy hơn là những đám đông ồn ào, hỗn loạn. Bạn cần không gian yên tĩnh để tư duy hiệu quả nhất.',
                'Thách thức: Rào cản lớn nhất cản bước bạn là thói quen nhìn lại quá khứ. Bạn hay nuối tiếc những gì đã qua hoặc để những kinh nghiệm chưa thành công trong quá khứ ảnh hưởng, khiến bạn thiếu quyết đoán khi cần tiến lên phía trước. Đôi khi, bạn thiếu tham vọng tranh đấu, thích sự ổn định, bình yên hơn là dấn thân vào chốn thương trường khốc liệt.',
                'chien_luoc' => [
                    'Hãy hướng nhìn về tương lai thay vì ngoái lại phía sau.',
                    'Dùng trí tuệ để kiến tạo giá trị mới thay vì chỉ ngồi phân tích cái cũ.',
                    'Những vai trò như cố vấn, chuyên gia kỹ thuật, nhà nghiên cứu hoặc nhà văn sẽ là nơi bạn phát huy tối đa sở trường.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được ví là người giàu ngầm. Bạn có khả năng tích lũy tài sản một cách bền vững và chắc chắn.',
                'Nguồn lực dồi dào: Bạn giống như dòng suối không bao giờ cạn nguồn. Cuộc đời bạn thường nhận được sự hỗ trợ hoặc nguồn lực tài chính từ gia đình, người thân vào những lúc cần thiết. Bạn cũng có duyên với việc thừa kế hoặc tiếp quản di sản.',
                'Kiếm tiền bằng trí tuệ: Bạn không phải mẫu người lao động chân tay vất vả để mưu sinh. Tài sản của bạn đến từ kiến thức, kỹ năng chuyên môn và sự uy tín cá nhân. Càng giỏi chuyên môn, bạn càng giàu có.',
                'Thói quen chi tiêu: Bạn không quá phung phí nhưng đôi khi lại hào phóng quá mức với bạn bè vì nể nang tình cảm. Bạn cũng sẵn sàng chi tiêu mạnh tay cho các sở thích sưu tầm, văn hóa hoặc những thú vui tinh thần tao nhã.',
                'Rủi ro: Bạn có thể bị thất thoát tiền bạc do tin người hoặc đưa ra quyết định dựa trên cảm tính nể nang. Sự do dự thái quá cũng có thể khiến bạn tuột mất những cơ hội đầu tư chớp nhoáng.',
                'dinh_huong' => [
                    'Hãy đầu tư vào chất xám, học thêm kỹ năng mới, lấy thêm bằng cấp là khoản đầu tư sinh lời nhất của bạn.',
                    'Học cách nói không dứt khoát khi bị hỏi vay mượn nếu cảm thấy không an toàn.',
                    'Tích lũy tài sản dưới dạng những giá trị bền vững, ít biến động.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là một đại dương cảm xúc: bề mặt có thể phẳng lặng, lạnh lùng nhưng bên dưới là những dòng hải lưu ấm áp và mãnh liệt.',
                'Sự chung thủy và hy sinh: Bạn yêu rất sâu sắc và nặng tình. Khi đã chọn ai, bạn dành trọn tâm trí để vun đắp và bảo vệ người đó. Bạn có xu hướng muốn che chở, bao bọc cho đối phương. Với bạn, nghĩa tình và sự gắn kết lâu dài quan trọng hơn những rung động nhất thời.',
                'Vẻ ngoài lạnh lùng: Vì sợ bị tổn thương, bạn thường tạo cho mình một lớp vỏ bọc xa cách, khó gần. Điều này khiến người khác giới đôi khi e ngại khi tiếp cận, hoặc người yêu cảm thấy bạn chưa đủ nhiệt tình, dù trong lòng bạn rất yêu họ.',
                'Thách thức: Bạn có xu hướng lo xa và nhạy cảm với những rủi ro trong tình cảm. Chỉ một hành động nhỏ vô tâm của đối phương cũng có thể khiến bạn suy diễn và buồn phiền. Sự hoài niệm người cũ cũng là bóng ma tâm lý khiến bạn khó mở lòng trọn vẹn với người mới.',
                'chien_luoc' => [
                    'Hãy học cách thể hiện tình cảm, ngôn ngữ yêu thương ra bên ngoài. Một cái ôm, một lời nói ngọt ngào sẽ phá tan lớp băng khoảng cách.',
                    'Hãy tin tưởng vào đối phương và sống cho hiện tại.',
                    'Hạnh phúc đang ở ngay trước mắt, đừng tìm kiếm nó trong những trang nhật ký cũ.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng lớn từ hàn khí lạnh bên trong cơ thể và tâm trạng trầm lắng, suy tư.',
                'Hệ hô hấp và tiêu hóa: Cơ thể bạn có xu hướng có thể bị lạnh hơn người khác. Bạn cần đặc biệt chú ý bảo vệ phổi, cổ họng và hệ tiêu hóa. Bạn có thể mắc các bệnh vặt khi thời tiết chuyển mùa.',
                'Sức khỏe tinh thần: Bạn có thể rơi vào trạng thái buồn vu vơ, cảm giác cô đơn xâm chiếm ngay cả khi đang ở giữa đám đông. Sự nhạy cảm quá mức khiến hệ thần kinh của bạn có thể bị quá tải và mệt mỏi.',
                'Cách thức cân bằng: Bạn cực kỳ cần yếu tố nhiệt độ và vận động thể chất. Hãy thường xuyên sưởi ấm, tắm nắng, tập thể dục cho toát mồ hôi để đẩy khí lạnh ra ngoài và kích hoạt năng lượng sống.',
                'Lieu_phap' => [
                    'Hãy cười nhiều hơn. Tìm kiếm những niềm vui giản dị, kết giao với những người bạn lạc quan, tích cực. Tránh nghe nhạc buồn hay xem phim bi kịch khi tâm trạng đang đi xuống.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là kiểu người học giả điển hình, sự học đối với bạn là hành trình cả đời.',
                'Trí tuệ uyên bác: Bạn có khả năng tiếp thu những kiến thức trừu tượng, triết học, hay tâm linh rất tốt. Bạn thích tìm hiểu về cội nguồn, lịch sử và những giá trị cốt lõi của cuộc sống.',
                'Góc nhìn độc đáo: Tư duy của bạn không đi theo lối mòn số đông. Bạn thường có những góc nhìn rất lạ, sâu sắc và mang tính chiêm nghiệm cao về cuộc sống.',
                'Thách thức: Bạn hay nghi ngờ năng lực của chính mình, như hội chứng kẻ mạo danh. Dù thực tế bạn rất giỏi, bạn thường khiêm tốn quá mức hoặc thiếu tự tin, không dám bước ra ánh sáng để thể hiện hết mình.',
                'dinh_huong' => [
                    'Hãy biến kiến thức thành hành động cụ thể.',
                    'Đừng để những ý tưởng tuyệt vời chỉ nằm trong đầu hoặc trên trang giấy.',
                    'Hãy mạnh dạn chia sẻ kiến thức của bạn với cộng đồng, bạn sẽ thấy mình có giá trị và ảnh hưởng hơn bạn nghĩ rất nhiều.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn không phải là người quảng giao, bạn có xu hướng chọn lọc và cẩn trọng trong các mối quan hệ, nhưng đó là sự lựa chọn của trí tuệ.',
                'Chất lượng hơn số lượng: Bạn không có quá nhiều bạn bè, nhưng những người bạn đã chơi là chơi rất bền, rất sâu. Bạn bè của bạn thường là những người có tri thức, hiểu biết và tôn trọng lẫn nhau.',
                'Người lắng nghe tuyệt vời: Bạn biết lắng nghe, thấu cảm và giữ bí mật tuyệt đối. Mọi người thường tìm đến bạn để xin lời khuyên hoặc trút bầu tâm sự vì họ cảm thấy an toàn.',
                'Quý nhân: Những người mang lại may mắn và cơ hội cho bạn thường là những người Thầy, người lớn tuổi, cấp trên hoặc những bậc tiền bối có kiến thức uyên thâm. Họ là người nhận ra viên ngọc trong đá là bạn và sẵn lòng nâng đỡ.',
                'chien_luoc' => [
                    'Đừng quá khép kín trong thế giới riêng.',
                    'Hãy mở rộng lòng mình để đón nhận những cơ hội mới.',
                    'Đôi khi, chủ động kết nối với một người bạn mới năng động sẽ mang đến làn gió tươi mới, giúp thổi bay sự u sầu cố hữu trong bạn.'
                ]
            ]
        ],
        'nham_thin' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn đầy uy quyền và bí ẩn: “Một hồ nước khổng lồ tĩnh lặng trên đỉnh núi” hay “Con Rồng nước cuộn mình trong đầm sâu”. Bề mặt bạn có thể tĩnh lặng, nhưng bên dưới là một nội lực vô cùng mạnh mẽ đang kìm nén, một kho tàng năng lượng chờ ngày khai phá. Bạn không trôi nổi vô định, bạn là dòng nước có chiều sâu, có mục đích và có nơi chốn.',
                'La Bàn Thịnh Vượng sẽ giúp bạn đánh thức con rồng đang ngủ đông để kiến tạo những thành tựu phi thường.'
            ],
            'su_nghiep' => [
                'Bạn được trời phú cho sự kết hợp hiếm có giữa trí tuệ sắc sảo và ý chí sắt đá. Bạn không chỉ là người mơ mộng với những ý tưởng lớn. Bạn còn là người hành động quyết liệt để biến những giấc mơ đó thành hiện thực',
                'Tố chất lãnh đạo bẩm sinh: Bạn sinh ra với tinh thần thép. Bạn không phải là người thừa hành tuân theo mệnh lệnh một cách thụ động. Bạn có tư duy độc lập, cái nhìn chiến lược và khả năng thuyết phục người khác một cách tự nhiên. Trong công việc, bạn thường là người đứng mũi chịu sào, dám nhận trách nhiệm và giải quyết những vấn đề hóc búa mà người khác e ngại.',
                'Khả năng chịu đựng áp lực: Sức mạnh lớn nhất của bạn nằm ở sự kiên cường. Càng gặp khó khăn, áp lực, bạn càng trở nên mạnh mẽ và sáng suốt. Bạn giống như lò xo, càng nén chặt thì sức bật càng cao, điều này giúp bạn rất phù hợp với các vị trí quản lý cấp cao, khởi nghiệp hoặc các lĩnh vực đòi hỏi thần kinh thép như tài chính, chính trị, kỹ thuật công nghệ.',
                'Tư duy chiến lược: Bạn không làm việc dựa trên cảm tính. Bạn có khả năng phân tích rủi ro và cơ hội cực kỳ nhạy bén. Bạn nhìn thấy tiềm năng sinh lời ở những nơi người khác bỏ qua.',
                'Thách thức: Vì cái tôi và lòng tự tôn của bạn khá cao. Bạn không thoải mái khi bị kiểm soát hay chỉ trích công khai. Đôi khi, sự bướng bỉnh khiến bạn bỏ ngoài tai những lời khuyên hữu ích. Ngoài ra, nếu không tìm thấy động lực đủ lớn, bạn có thể rơi vào trạng thái Rồng Ngủ Đông khiến bạn thiếu động lực hành động và rơi vào trạng thái trì trệ.',
                'chien_luoc' => [
                    'Hãy tìm kiếm môi trường cho phép bạn tự chủ hoàn toàn.',
                    'Đặt ra những mục tiêu lớn lao và đầy thách thức để kích hoạt năng lượng tiềm ẩn của bạn.',
                    'Đừng ngại dấn thân vào con đường kinh doanh hoặc tự làm chủ, đó là sân khấu nơi bạn tỏa sáng nhất.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được ví như là Kho Chứa Nước. Nước quản về tài lộc, vì vậy bạn bẩm sinh đã sở hữu một kho báu tiềm ẩn. Bạn có duyên với tiền bạc và khả năng tích luỹ tài sản cực tốt.',
                'Tiềm năng làm giàu: Bạn không mơ mộng hão huyền về việc giàu xổi. Bạn hiểu giá trị của lao động và sự kiên trì. Tiền bạc đối với bạn đến từ những kế hoạch bài bản, chiến lược đầu tư thông minh và sự nỗ lực không ngừng nghỉ. Bạn có khả năng biến những nguồn lực nhỏ thành khối tài sản lớn theo thời gian.',
                'Trực giác đầu tư: Bạn có duyên với tài sản cố định. Những lĩnh vực liên quan đến lưu trữ như kho bãi, bất động sản hoặc quản lý quỹ thường mang lại may mắn lớn cho bạn.',
                'Quản lý dòng tiền: Mặc dù có khả năng kiếm tiền tốt, nhưng giai đoạn tuổi trẻ bạn có thể gặp khó khăn trong việc giữ tiền. Bạn có xu hướng hào phóng, thích chi tiêu cho trải nghiệm hoặc sở thích cá nhân, đôi khi là phung phí. Tuy nhiên, càng về hậu vận, khả năng quản lý tài chính của bạn càng sắc bén, giúp bạn có một cuộc sống sung túc và an nhàn.',
                'Rủi ro: Hãy cẩn trọng vào những năm gặp xung khắc, vì kho tài lộc có thể bị xung phá, dẫn đến hao hụt nếu đầu tư mạo hiểm.',
                'dinh_huong' => [
                    'Hãy học cách tiết kiệm và đầu tư từ sớm.',
                    'Bạn là kho chứa, nhiệm vụ của bạn là tích lũy để tạo ra sự thịnh vượng bền vững chứ không phải để tiêu xài hoang phí.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn thâm trầm, kín đáo nhưng vô cùng sâu sắc và trung thành.',
                'Sự tận tụy ngầm: Bạn không giỏi nói lời đường mật. Bạn thể hiện tình yêu bằng sự bảo vệ và che chở. Bạn mang lại cảm giác an toàn vững chãi như núi thái sơn cho người bạn đời.',
                'Tiêu chuẩn cao: Bạn không tìm kiếm sự lãng mạn hời hợt. Bạn cần một đối tác có trí tuệ tương xứng, có chiều sâu tâm hồn và sự độc lập để cùng bạn chia sẻ quan điểm sống.',
                'Thách thức: Vẻ ngoài lạnh lùng, điềm tĩnh đôi khi khiến đối phương cảm thấy bạn xa cách. Bên trong bạn có tính chiếm hữu cao, bạn mong muốn sự cam kết chặt chẽ và có thể có những nỗi bất an ngầm về mối quan hệ. Bạn thường gặp khó khăn trong việc bộc lộ cảm xúc thật.',
                'chien_luoc' => [
                    'Hãy học cách dịu dàng, ôn nhu hơn.',
                    'Đừng chỉ hành động, hãy học cách nói ra những lời yêu thương.',
                    'Kết hôn muộn thường mang lại hạnh phúc bền vững hơn, khi bạn đã đủ trưởng thành để bao dung.'
                ]
            ],
            'suc_khoe' => [
                'Vấn đề lớn nhất của bạn nằm ở sự lưu thông năng lượng.',
                'Sự ứ trệ: Nếu bạn lười vận động hoặc kìm nén cảm xúc quá lâu, bạn có thể mắc các chứng bệnh liên quan đến khí huyết, tiêu hóa, thận hoặc dạ dày.',
                'Căng thẳng nội tâm: Bạn hay lo xa và suy nghĩ nhiều. Những áp lực vô hình tự đặt ra có thể gây căng thẳng thần kinh.',
                'Cách thức cân bằng: Di chuyển là liều thuốc tiên. Hãy đi du lịch, thay đổi môi trường hoặc tập thể dục để kích hoạt dòng chảy.',
                'Lieu_phap' => [
                    'Thiền định cũng giúp bạn xả bớt những toan tính trong đầu.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn có trí tuệ sâu sắc và không thích những thứ hời hợt bề nổi.',
                'Tư duy chiều sâu: Bạn luôn muốn đào sâu vào bản chất vấn đề. Bạn có khả năng kết hợp nhuần nhuyễn giữa lý thuyết và thực tiễn.',
                'Trực giác nhạy bén: Bạn có giác quan thứ sáu rất tốt, đôi khi cảm được kết quả trước khi nó xảy ra.',
                'Thách thức: Việc vượt qua sự trì hoãn và duy trì động lực là thách thức lớn nhất trên con đường thành công. Đôi khi sự thông minh khiến bạn chủ quan.',
                'dinh_huong' => [
                    'Bài học lớn nhất của bạn là kỷ luật.',
                    'Hãy rèn luyện thói quen kết thúc những gì mình bắt đầu.',
                    'Tìm cho mình một đức tin hoặc triết lý sống để làm kim chỉ nam vững vàng trước sóng gió.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người uy tín, nói được làm được và rất hào hiệp trong mắt bạn bè.',
                'Sự chọn lọc: Bạn không quảng giao bừa bãi. Bạn chọn bạn rất kỹ. Bạn bè của bạn tuy ít nhưng chất lượng, trung thành và có thể hỗ trợ lẫn nhau.',
                'Đa nghi: Bạn có xu hướng hoài nghi động cơ của người khác. Điều này đôi khi khiến bạn tự cô lập mình trong tập thể.',
                'Quý nhân: Nhờ năng lực thực sự, bạn thường thu hút sự chú ý của những người lãnh đạo, người có quyền lực.',
                'chien_luoc' => [
                    'Hãy bớt toan tính và mở lòng hơn.',
                    'Tin rằng khi bạn chân thành, bạn sẽ nhận lại sự chân thành.',
                    'Đừng cố gánh vác cả thế giới một mình, hãy cho phép người khác được giúp đỡ bạn.'
                ]
            ]
        ],
        'nham_dan' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật hùng vĩ và tràn đầy sinh lực: “Một dòng sông lớn mang nặng phù sa chảy xuyên qua khu rừng già rậm rạp”. Bạn mang trong mình khả năng sinh tồn đáng kinh ngạc và một tư duy thực tế sắc bén. Bạn là biểu tượng của sự may mắn, uy quyền và khả năng làm chủ vận mệnh.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khơi thông dòng chảy trí tuệ này để kiến tạo đại nghiệp.'
            ],
            'su_nghiep' => [
                'Sự nghiệp của bạn thường rực rỡ và thú vị, bởi bạn sở hữu vũ khí bí mật, trí tuệ thông minh kết hợp với hành động quyết liệt.',
                'Trí tuệ thực chiến: Bạn là người giải quyết vấn đề xuất sắc. Bạn có khả năng nhìn thấu bản chất sự việc và tìm ra giải pháp nhanh nhất. Bạn làm việc bằng cái đầu chứ không chỉ dùng sức. Trong mắt đồng nghiệp, bạn tháo vát, đa tài và luôn có những ý tưởng đột phá.',
                'Tố chất tiên phong: Bạn có tầm nhìn xa và tinh thần dám nghĩ dám làm. Bạn không thích ngồi yên chờ việc. Bạn luôn muốn là người mở đường. Phong thái tự tin và lạc quan của bạn truyền cảm hứng mạnh mẽ cho đội ngũ.',
                'Thích ứng linh hoạt: Giống như dòng nước len lỏi qua mọi địa hình, bạn có thể tỏa sáng ở bất cứ đâu. Kinh doanh, nghệ thuật, giáo dục hay quản lý đều là sân chơi của bạn. Đặc biệt, bạn phù hợp với những công việc đòi hỏi sự di chuyển, đổi mới và sáng tạo liên tục.',
                'Thách thức: Cái tôi của bạn khá lớn. Đôi khi bạn muốn mọi thứ theo ý mình và không thích bị kiểm soát hoặc chỉ đạo quá chi tiết. Bạn cũng có thể cảm thấy nhàm chán nếu công việc lặp đi lặp lại thiếu tính thử thách.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những môi trường cho phép bạn tự chủ và độc lập tác chiến.',
                    'Khởi nghiệp hoặc quản lý cấp cao là vị trí lý tưởng.',
                    'Học cách kiên nhẫn và lắng nghe để trở thành một nhà lãnh đạo toàn diện thay vì một người chỉ huy thiếu sự lắng nghe.'
                ]
            ],
            'tai_chinh' => [
                'Bạn được xem là một trong những trụ ngày may mắn nhất về tài lộc. Dòng sông của bạn luôn chảy về nơi trù phú, mang theo phù sa màu mỡ.',
                'Trực giác tài chính: Bạn sở hữu sự nhạy bén đặc biệt với các cơ hội tài chính. Bạn nhìn ra cơ hội kinh doanh ở những nơi người khác bỏ qua. Đặc biệt, bạn giỏi kết hợp giữa làm ăn và hưởng thụ. Những hợp đồng lớn thường được bạn ký kết trên bàn tiệc hoặc trong những chuyến du lịch.',
                'Dòng tiền đa dạng: Thu nhập của bạn thường không đến từ một nguồn lương cố định mà từ sự đầu tư, kinh doanh hoặc các ý tưởng sáng tạo. Bạn kiếm tiền nhờ vào trí tuệ và giá trị độc đáo bạn tạo ra.',
                'Đầu tư cho trải nghiệm: Bạn thực tế nhưng không keo kiệt. Bạn sẵn sàng chi tiền cho kiến thức, trải nghiệm và các mối quan hệ chất lượng, vì bạn hiểu đó là những khoản đầu tư sinh lời cao nhất.',
                'Rủi ro: Những năm đầu đời có thể bạn sẽ chật vật để tìm hướng đi. Nếu sinh vào mùa Thu hoặc Đông, bạn cần nỗ lực gấp đôi để giữ tiền, tránh thất thoát do tính hào phóng quá đà.',
                'dinh_huong' => [
                    'Hãy tận dụng khả năng giao tiếp để mở rộng mạng lưới làm ăn.',
                    'Mạnh dạn đầu tư vào những lĩnh vực cần sự đổi mới.',
                    'Sự giàu có của bạn đến từ việc tạo ra giá trị mới lạ cho cộng đồng.'
                ]
            ],
            'tinh_duyen' => [
                'Đối với bạn, tình cảm là gia vị không thể thiếu. Bạn lãng mạn, nồng nhiệt nhưng cũng khó chiều.',
                'Sự lãng mạn đầy khí chất: Bạn ấm áp, biết quan tâm và rất hào phóng với người yêu. Bạn có khiếu hài hước và luôn biết cách làm mới mối quan hệ, khiến tình yêu không bao giờ tẻ nhạt.',
                'Kết nối trí tuệ: Bạn không yêu một cách mù quáng. Bạn bị hấp dẫn bởi những người thông minh, có chiều sâu để cùng đối thoại. Sự kết nối về tinh thần quan trọng hơn vẻ bề ngoài',
                'Thách thức: Cảm xúc của bạn khá phong phú và biến đổi nhanh chóng. Bạn có xu hướng tìm kiếm sự mới mẻ liên tục. Nếu đối phương không đủ thú vị hoặc không theo kịp tư duy của bạn, bạn có thể xao lòng bởi những điều mới mẻ bên ngoài.',
                'chien_luoc' => [
                    'Nên kết hôn muộn để tâm tính ổn định và chín chắn hơn.',
                    'Hãy tìm một người bạn đời vừa là người yêu, vừa là tri kỷ, đủ bao dung cho tính cách nghệ sĩ và đôi chút bốc đồng của bạn.',
                    'Nếu là nữ, hãy bớt kỳ vọng vào sự hoàn hảo để tránh thất vọng trong hôn nhân.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn phụ thuộc vào sự thông suốt của dòng chảy năng lượng bên trong.',
                'Hệ tiêu hóa và dạ dày: Do hay suy nghĩ, làm việc cường độ cao và cầu toàn, bạn có thể gặp vấn đề về dạ dày và tiêu hóa.',
                'Cảm xúc dồn nén: Bạn thường giấu lo lắng vào bên trong vẻ ngoài lạc quan. Lâu ngày, sự tắc nghẽn cảm xúc này gây ra mệt mỏi vô cớ.',
                'Lieu_phap' => [
                    'Hãy dành thời gian đi dạo trong rừng, tắm nắng.',
                    'Tham gia các hoạt động sáng tạo nghệ thuật để giải tỏa năng lượng dư thừa.',
                    'Sự vận động, di chuyển là liều thuốc bổ tốt nhất cho bạn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là hiện thân của mẫu người học đi đôi với hành.',
                'Học từ trải nghiệm: Bạn không thích lý thuyết suông. Mọi kiến thức bạn nạp vào đều phải áp dụng được ngay. Bạn học tốt nhất qua quan sát và trải nghiệm thực tế.',
                'Đam mê khám phá: Bạn tò mò về thế giới. Những chuyến đi, những nền vă hóa mới chính là trường học vĩ đại nhất của bạn.',
                'Thách thức nội tâm: Bạn có xu hướng tự phê bình quá mức. Dù làm tốt, bạn vẫn cảm thấy chưa đủ, tạo ra áp lực vô hình.',
                'dinh_huong' => [
                    'Hãy học cách bao dung với chính mình.',
                    'Chấp nhận sai lầm là một phần của trưởng thành.',
                    'Tìm một mục tiêu cụ thể để dồn sự tập trung cao độ, tránh lan man, phân tán năng lượng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao sáng trong các mối quan hệ, là người kết nối đại tài.',
                'Sức hút tự nhiên: Bạn vui vẻ, phóng khoáng và chân thành. Mọi người thích ở bên bạn vì nguồn năng lượng tích cực mà bạn tỏa ra.',
                'Mạng lưới rộng lớn: Bạn có bạn bè ở khắp nơi, đủ mọi tầng lớp. Bạn biết dùng sự khéo léo kết hợp với thẳng thắn để xây dựng lòng tin.',
                'Quý nhân: Quý nhân thường xuất hiện trong những buổi giao lưu, tiệc tùng hoặc những chuyến đi xa.',
                'chien_luoc' => [
                    'Đôi khi vì quá coi trọng hình ảnh, bạn có thể bị áp lực từ bạn bè chi phối.',
                    'Hãy học cách nói không khi cần thiết.',
                    'Tập trung vào chất lượng mối quan hệ thay vì cố gắng làm hài lòng tất cả mọi người.'
                ]
            ]
        ],
        'quy_mui' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là “Hơi nước bốc lên từ mặt đất” hay “Những đám mây đang hình thành”. Bạn mang trong mình trạng thái khí – nhẹ nhàng, len lỏi, khó nắm bắt và luôn có xu hướng bay lên cao. Bạn sở hữu trực giác tâm linh mạnh mẽ, sự sáng tạo nghệ thuật và một tâm hồn luôn khao khát tự do, đổi mới. Bạn có xu hướng không thích sự cố định quá lâu, cả về thể xác lẫn tinh thần.',
                'La Bàn Thịnh Vượng sẽ giúp bạn định hình đám mây của mình để bay cao và bay xa hơn.'
            ],
            'su_nghiep' => [
                'Sự nghiệp của bạn gắn liền với sự di chuyển và thay đổi. Bạn không phù hợp với những công việc bàn giấy khô khan.',
                'Cơ hội luôn gõ cửa: Bạn may mắn khi luôn có nhiều cơ hội việc làm tìm đến. Đặc biệt, những cơ hội này thường đi kèm với sự thay đổi nơi ở, đi công tác hoặc du lịch. Bạn giống như hơi nước, phải di chuyển mới tạo ra năng lượng.',
                'Thực tế và tưởng tượng: Bạn có sự kết hợp hiếm có giữa tính thực tế và trí tưởng tượng bay bổng. Bạn biết cách lên kế hoạch chi tiết để biến những giấc mơ nghệ thuật thành hiện thực. Bạn là người cầu toàn và đa năng.',
                'Lĩnh vực phù hợp: Bạn có đôi bàn tay khéo léo và tư duy thẩm mỹ tốt. Những nghề nghiệp như thiết kế, thủ công mỹ nghệ, ẩm thực, PR, bán hàng hay tư vấn tâm lý là đất diễn tuyệt vời cho bạn.',
                'Thách thức: Bạn có xu hướng tìm kiếm sự mới mẻ, đôi khi dẫn đến việc khó duy trì sự kiên nhẫn hoặc tập trung dài hạn. Nếu không tìm được công việc mình thực sự đam mê, năng lượng của bạn sẽ bị phân tán, làm nhiều việc nhưng hiệu quả cuối cùng chưa cao như mong đợi hoặc khó hoàn thành mục tiêu một cách trọn vẹn.',
                'chien_luoc' => [
                    'Hãy tìm một mục tiêu khiến bạn bùng cháy thực sự.',
                    'Khi có đam mê, bạn sẽ làm việc với sự tập trung cao độ đáng kinh ngạc.',
                    'Hãy tận dụng trực giác sắc bén để đưa ra những quyết định nghề nghiệp đúng đắn.'
                ]
            ],
            'tai_chinh' => [
                'Tài chính của bạn có thể trải qua nhiều biến động thăng trầm lớn trong giai đoạn đầu nhưng sẽ khá rực rỡ về sau.',
                'Hậu vận sung túc: Tuổi trẻ có thể là giai đoạn bạn phải nỗ lực kiến tạo và vun đắp nền tảng. Quá trình này dẫu có vất vả, nhưng tin vui là khi bước vào tuổi trung niên, vận may tài chính sẽ mỉm cười với bạn. Bạn không chỉ giàu có mà còn đạt được sự an tâm, thanh thản.',
                'Thói quen chi tiêu: Bạn kiếm tiền giỏi nhưng cũng có xu hướng chi tiêu khá phóng khoáng và nhanh chóng. Bạn khó giữ được tiền mặt trong tay vì luôn có nhu cầu chi tiêu hoặc đầu tư.',
                'Tự lực cánh sinh: Bạn ít khi nhận được sự hỗ trợ tài chính từ gia đình. Mọi thứ bạn có đều do đôi bàn tay và khối óc của bạn tạo ra. Điều này khiến thành quả của bạn càng thêm đáng tự hào.',
                'Rủi ro: Bạn có xu hướng lo lắng về tiền bạc và có thể bị mất ổn định nếu tình hình tài chính không như ý.',
                'dinh_huong' => [
                    'Hãy đầu tư vào các tài sản có tính thanh khoản thấp như nhà đất, sổ tiết kiệm dài hạn để giữ cho tiền đừng thất thoát.',
                    'Hãy học cách tạo ra các dòng thu nhập thụ động để bù đắp cho thói quen chi tiêu của mình.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình yêu, bạn là một nghệ sĩ lãng mạn, nhạy cảm và đầy lý tưởng.',
                'Sự tận tụy: Khi yêu, bạn dốc hết tâm can cho đối phương. Bạn chăm sóc, lo lắng và vun vén cho mối quan hệ hết mình. Bạn là mẫu người của gia đình, thích cảm giác ấm cúng, sum vầy.',
                'Lý tưởng hóa: Bạn hay vẽ ra một viễn cảnh tình yêu hoàn hảo. Điều này khiến bạn đặt kỳ vọng khá cao vào người bạn đời. Khi thực tế không như mơ, bạn có thể thất vọng, buồn bã và trở nên thất thường, thay đổi tâm trạng.',
                'Đối với phụ nữ, đảm đang, đức hạnh. Sự may mắn sẽ đến nếu bạn kết hôn với người có tính cách điềm tĩnh, vững chãi, điều này giúp ổn định cuộc sống.',
                'Thách thức: Bạn cần học cách buông bỏ sự kiểm soát và bớt lo lắng quá mức. Bạn cũng có thể bị người khác lợi dụng lòng tốt của mình.',
                'chien_luoc' => [
                    'Hãy học cách chấp nhận sự không hoàn hảo của đối phương. Tình yêu cần sự thấu hiểu và lòng bao dung, đừng chỉ dựa vào sự lý tưởng hóa.'
                ]
            ],
            'suc_khoe' => [
                'Khí huyết trong cơ thể bạn có thể bị ngưng trệ, kém lưu thông, là nguyên nhân gây ra những triệu chứng mệt mỏi mãn tính.',
                'Nguy cơ bệnh mãn tính: Bạn có thể mắc các bệnh dai dẳng liên quan đến dạ dày, tiêu hóa hoặc các vấn đề về da, tế bào. Những bệnh này thường xuất hiện rõ hơn khi bạn lớn tuổi.',
                'Trốn tránh thực tại: Khi gặp áp lực hoặc bất mãn, bạn có xu hướng muốn tạm thoát ly thực tế, có thể sa đà vào những thói quen không tốt hoặc rơi vào trầm cảm.',
                'dinh_huong' => [
                    'Bạn cần bộ lọc để làm sạch dòng chảy của mình.'
                ],
                'Lieu_phap' => [
                    'Hãy giữ cho tâm trí luôn trong sáng, lạc quan.',
                    'Tránh xa những suy nghĩ tiêu cực, u ám. Sự vui vẻ chính là liều thuốc kháng sinh mạnh nhất của bạn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu trí tuệ tâm linh và khả năng trực giác thiên bẩm.',
                'Giác quan thứ 6: Bạn có những linh cảm cực kỳ chính xác. Bạn thường biết trước điều gì là đúng đắn cho mình mà không cần phân tích logic. Hãy tin vào trực giác đó.',
                'Tâm hồn nghệ sĩ: Bạn yêu cái đẹp, thích nghệ thuật và những gì tinh tế. Việc học các bộ môn nghệ thuật hoặc tâm linh, triết học sẽ giúp bạn khai phá tiềm năng to lớn bên trong.',
                'Bài học cuộc đời: Bạn cần học cách kiên nhẫn. Sự bồn chồn, muốn đốt cháy giai đoạn thường khiến bạn vấp ngã. Hãy học cách đi chậm lại để nhìn rõ con đường.',
                'dinh_huong' => [
                    'Tìm kiếm bản thể bên trong – higher-self.',
                    'Thiền định, yoga hoặc các hoạt động tu dưỡng nội tâm sẽ giúp bạn tìm thấy sự an yên và trí tuệ đích thực.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người bạn tốt bụng, hay giúp đỡ nhưng cũng có thể chịu thiệt thòi.',
                'Lòng tốt cần đặt đúng chỗ: Bạn nhạy cảm, thương người và sẵn sàng giúp đỡ bạn bè khi họ gặp khó khăn. Tuy nhiên, lòng tốt của bạn đôi khi đặt không đúng chỗ, khiến bạn có thể gặp thiệt thòi trong một số mối quan hệ nếu không cẩn trọng.',
                'Tự lực: Bạn bè có thể đến rồi đi, nhưng chính điều đó tôi luyện nên sự độc lập, kiên cường của bạn.',
                'Quý nhân: Quý nhân của bạn thường là những người có kinh nghiệm, trí tuệ, họ sẽ mang lại sự ấm áp và sự hỗ trợ tài chính cho bạn.',
                'chien_luoc' => [
                    'Hãy biết bảo vệ bản thân. Giúp đỡ người khác là tốt, nhưng đừng để mình trở thành nơi trút bỏ gánh nặng.',
                    'Hãy tin vào trực giác của mình khi chọn bạn để tránh bị thất vọng.'
                ]
            ]
        ],
        'quy_ty' => [
            'tong_quan' => [
                'Trong Bát Tự, bạn sở hữu một trong những khí chất quý phái nhất, được ví như hình ảnh “Nhật Nguyệt Trùng Quan”, Mặt Trời và Mặt Trăng cùng toả sáng. Bạn có một khí chất thanh cao, phẩm giá hơn người. Bạn thông minh, lanh lợi và mang trong mình sự kết hợp hoàn hảo giữa trí tuệ và nhiệt huyết.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khai thác kho báu trời ban này.'
            ],
            'su_nghiep' => [
                'Bạn có tiềm năng lớn để phát triển tố chất của một nhà quản lý và lãnh đạo tài ba. Bạn không chỉ thông minh mà còn có khả năng thấu hiểu sâu sắc mọi việc mình làm.',
                'Tư duy chiến lược: Bạn làm việc bằng trí óc, không phải bằng sức lực. Bạn có trực giác nhạy bén và tầm nhìn xa, giúp bạn nhận ra những cơ hội lớn mà người khác bỏ lỡ. Bạn khá phù hợp với các vị trí quản lý, hành chính, chiến lược gia hoặc những công việc đòi hỏi sự tính toán khôn ngoan.',
                'Động lực đặc biệt: Điều thú vị là bạn không chỉ làm việc vì tiền. Động lực lớn nhất thúc đẩy bạn chính là sự công nhận, sự tán thưởng từ xã hội. Bạn khao khát được khan ngợi, được mọi người thừa nhận tài năng và thành tựu của mình. Danh tiếng đối với bạn quan trọng ngang hàng với tiền tài.',
                'Phong cách lãnh đạo: Bạn biết cách khởi xướng dự án và giao việc cho người khác. Bạn hiểu rằng sức mình có hạn nhưng sức người là vô hạn. Bạn có tài năng hợp tác và sử dụng người khá hiệu quả.',
                'Thách thức: Thách thức lớn nhất của bạn là vượt qua sức ì nội tại hoặc xu hướng trì hoãn. Bạn thông minh nên thường muốn đạt kết quả nhanh nhất với ít nỗ lực nhất. Đôi khi bạn trở nên thụ động, chờ đợi cơ hội đến tay thay vì lăn xả đi tìm.',
                'chien_luoc' => [
                    'Hãy chiến thắng sức ì của bản thân. Hãy nhớ rằng tài năng của bạn cần sự cần cù để tỏa sáng.',
                    'Hãy đặt ra những mục tiêu cao cả để thỏa mãn nhu cầu được công nhận, biến nó thành nhiên liệu đốt cháy sự trì trệ, khơi dậy nguồn năng lượng hành động.'
                ]
            ],
            'tai_chinh' => [
                'Đây là sân nhà của bạn, cho thấy tiềm năng lớn về một cuộc đời sung túc và ổn định về vật chất.',
                'Duyên với tiền bạc: Bạn có khả năng kiếm tiền xuất sắc nhờ vào trí tuệ và các mối quan hệ xã hội. Tiền bạc đến với bạn thường thông qua con đường chính ngạch, bền vững như kinh doanh, đầu tư bài bản hoặc thừa kế.',
                'Nhu cầu an toàn: Dù kiếm được nhiều tiền, bạn luôn có nỗi sợ hãi vô hình về sự thiếu thốn, thôi thúc bạn tích lũy và đầu tư cho tương lai.',
                'Quản lý tài sản: Bạn có đầu óc thực tế và biết cách giữ tiền. Bạn hợp với các khoản đầu tư an toàn.',
                'Rủi ro: điểm cần đặc biệt lưu ý của bạn trong tài chính là xu hướng đặt niềm tin khá nhanh vào người khác. Bạn có thể mủi lòng hoặc tin vào những lời hứa hẹn của bạn bè, đối tác dẫn đến việc đầu tư sai chỗ hoặc bị thất thoát tiền bạc.',
                'dinh_huong' => [
                    'Hãy học cách phân biệt rạch ròi giữa tình cảm và tiền bạc.',
                    'Lòng trắc ẩn là điểm mạnh, nhưng trong tài chính, hãy để lý trí dẫn lối để bảo vệ thành quả của mình. Chính vì vậy, trước khi xuống tiền đầu tư, hãy dùng lý trí sắc bén của mình để thẩm định thay vì tin vào lời nói của người khác.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn thường mang màu sắc của sự lý trí và ổn định, đặc biệt là đối với nữ mệnh.',
                'Sự hấp dẫn: Bạn hướng ngoại, hào phóng và thích giao lưu xã hội. Bạn có sức hút tự nhiên. Trong tình yêu, bạn thân thiện nhưng cũng giữ cho mình một chút bí ẩn và độc lập.',
                'Đối với phụ nữ, có nhiều cơ hội gặp gỡ hoặc kết hôn với người chồng tài giỏi, có địa vị hoặc giàu có. Bạn biết cách vun vén và giúp đỡ chồng thăng tiến. Hôn nhân của bạn thường ổn định và mang lại sự cao quý.',
                'Thách thức: Bạn hay lo âu và sợ sự thay đổi. Đôi khi bạn vô tình tạo ra những thử thách về mặt tâm lý hoặc phức tạp hóa vấn đề trong mối quan hệ, có thể gây căng thẳng cho cả hai. Bạn cũng cần chú ý điều hòa cảm xúc để tránh những phản ứng nóng vội.',
                'chien_luoc' => [
                    'Hãy xây dựng ngôi nhà của mình thành một bến đỗ bình yên thực sự.',
                    'Học cách thỏa hiệp và bớt bướng bỉnh.',
                    'Sự tin tưởng và tôn trọng không gian riêng của nhau là chìa khóa hạnh phúc.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn nhìn chung là tốt, tuy nhiên, tâm bệnh mới là điều đáng lo.',
                'Cảm xúc chi phối: Bạn có thể bị lo lắng thái quá. Khi không nhận được sự công nhận hoặc mọi việc không như ý, bạn có thể mất kiểm soát cảm xúc, dẫn đến stress, đau đầu hoặc các bệnh về thần kinh.',
                'Khí chất nóng nảy: Sự xung đột ngầm giữa trí tuệ và cảm xúc có thể tạo ra sự mâu thuẫn nội tâm, khiến bạn có thể nổi nóng, bực bội. Điều này không tốt cho tim mạch và huyết áp.',
                'Giải pháp cân bằng: Bạn cần sự tĩnh lặng để kiềm chế cảm xúc. Hãy thiết lập một không gian sống yên bình, tránh xa những thị phi ồn ào.',
                'Lieu_phap' => [
                    'Thiền định, nghe nhạc nhẹ hoặc các hoạt động nghệ thuật giúp bạn xoa dịu tâm trí.',
                    'Hãy học cách trở nên ôn hòa hơn để bảo vệ sức khỏe thần kinh của mình.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người ham học hỏi và có khả năng tiếp thu kiến thức đa dạng.',
                'Trí tuệ sáng tạo: Bạn có những ý tưởng tuyệt vời và góc nhìn độc đáo về thế giới. Bạn hứng thú với cả những lĩnh vực thực tế như kinh doanh lẫn trừu tượng như tâm linh, siêu hình.',
                'Học đi đôi với hành: Bạn không thích học lý thuyết suông. Kiến thức bạn nạp vào phải phục vụ cho mục tiêu thăng tiến hoặc kiếm tiền.',
                'Thách thức: Bạn cần duy trì động lực học tập liên tục. Nếu không có mục tiêu cụ thể hoặc không thấy lợi ích rõ ràng, bạn có thể nản chí và bỏ cuộc giữa chừng.',
                'dinh_huong' => [
                    'Hãy tham gia các khóa học về kỹ năng lãnh đạo, quản lý cảm xúc hoặc nghệ thuật giao tiếp.',
                    'Đây là những công cụ đắc lực giúp bạn leo lên những nấc thang cao hơn trong sự nghiệp.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người quảng giao, đi đến đâu cũng có bạn bè.',
                'Mạng lưới quan hệ rộng: Bạn thân thiện, dễ mến và biết cách hợp tác. Bạn hiểu rằng thành công không thể đến từ sự đơn độc.',
                'Quý nhân: Quý nhân của bạn thường là những người có quyền lực, địa vị hoặc những người làm việc chăm chỉ, kỷ luật. Họ sẽ hỗ trợ bạn khá nhiều trong con đường thăng tiến.',
                'Rủi ro: Bạn quá tin người. Bạn có thể bị lợi dụng lòng tốt hoặc bị lôi kéo vào những rắc rối của người khác.',
                'chien_luoc' => [
                    'Hãy chọn bạn mà chơi.',
                    'Ưu tiên kết giao với những người có tư duy tích cực và kỷ luật. Học cách nói không và thiết lập ranh giới để bảo vệ bản thân.'
                ]
            ]
        ],
        'quy_dau' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là “Dòng nước trong vắt chảy ra từ khe đá” hay “Cơn mưa rào mùa thu mát lạnh”. Bạn mang trong mình sự tinh khiết, sắc sảo và một trí tuệ nội tâm phong phú. Bạn được sinh ra trên nền tảng của tri thức và sự bảo trợ. Bạn thông minh, có khả năng tự học tuyệt vời nhưng cũng sở hữu một tâm hồn nhạy cảm, đôi khi hơi lạnh lùng và cô đơn.',
                'La Bàn Thịnh Vượng sẽ giúp bạn khai thác dòng chảy trí tuệ ấy để kiến tạo một cuộc đời thành công và ý nghĩa.'
            ],
            'su_nghiep' => [
                'Bạn không phải là mẫu người làm việc theo đám đông. Bạn có tư duy độc lập và khả năng nhìn thấu bản chất vấn đề một cách sắc bén.',
                'Tố chất lãnh đạo và tiên phong: Bạn có tầm nhìn xa và khả năng biến những ý tưởng lớn thành hiện thực. Bạn thích những khởi đầu mới và không ngại đối mặt với những vấn đề hóc búa mà người khác e ngại. Khi bạn đã quyết tâm, bạn dồn toàn bộ tâm trí vào dự án với sự tập trung cao độ.',
                'Khả năng đọc vị người khác: Bạn có trực giác khá tốt về con người. Bạn hiểu động cơ đằng sau hành động của họ và biết cách khích lệ để họ phát huy tiềm năng. Điều này giúp bạn trở thành một nhà quản lý, một người cố vấn hoặc một nhà đàm phán xuất sắc.',
                'Phong cách làm việc: Bạn thích sự hoàn hảo và trật tự. Bạn làm việc có nguyên tắc, rõ ràng và dứt khoát. Những nghề nghiệp phù hợp với bạn thường liên quan đến trí tuệ, chuyên môn cao như: Bác sĩ, luật sư, cố vấn tài chính, chuyên gia phân tích hoặc các lĩnh vực sáng tạo nghệ thuật.',
                'Thách thức: Đôi khi sự cầu toàn khiến bạn trở nên khắt khe, có xu hướng nhận xét thẳng thắn hoặc yêu cầu cao với đồng nghiệp. Bạn có xu hướng áp đặt tiêu chuẩn cao của mình lên người khác, có thể khiến người khác cảm thấy bạn quá nghiêm khắc, áp đặt hoặc khó tính.',
                'chien_luoc' => [
                    'Hãy học cách mềm mỏng hơn trong giao tiếp. Thay vì chỉ trích lỗi sai, hãy tập trung vào giải pháp và khích lệ tinh thần đồng đội. Sự nghiệp của bạn sẽ thăng hoa rực rỡ nếu bạn biết dùng sự khéo léo để thu phục nhân tâm.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là người tháo vát và có duyên với tiền bạc. Bạn biết cách xoay xở để có một cuộc sống sung túc.',
                'Sự tháo vát: Bạn hiếm khi để mình rơi vào cảnh túng quẫn. Ngay cả khi gặp khó khăn, bạn luôn tìm ra cách để vực dậy tài chính. Bạn là người cần cù, tiết kiệm và biết quản lý chi tiêu, đặc biệt là nữ giới.',
                'Tích lũy bền vững: Bạn không giàu lên nhờ may mắn bất ngờ mà nhờ sự tích lũy kiến thức và nỗ lực làm việc. Bạn có khả năng biến chuyên môn của mình thành tài sản.',
                'Làm việc vì mục tiêu lớn: Bạn thường làm việc khá chăm chỉ khi còn trẻ để xây dựng nền tảng vững chắc cho hậu vận. Bạn hiểu giá trị của đồng tiền và không tiêu xài hoang phí vào những thứ vô bổ.',
                'Rủi ro: Đôi khi, bạn có thể đặt nặng vấn đề tài chính hoặc nhìn nhận cuộc sống qua lăng kính thực tế nhiều hơn cảm xúc. Mong muốn thành công nhanh chóng đôi khi có thể khiến bạn chủ quan trước những rủi ro tiềm ẩn hoặc những cân nhắc quan trọng về mặt nguyên tắc, đạo đức.',
                'dinh_huong' => [
                    'Hãy giữ vững sự chính trực. Tiền bạc kiếm được từ trí tuệ và sự tử tế sẽ bền vững hơn nhiều so với những thủ thuật.',
                    'Hãy đầu tư vào các lĩnh vực liên quan đến giáo dục, y tế hoặc công nghệ vì đó là thế mạnh của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là một ẩn số thú vị nhưng cũng đầy thách thức. Bạn lãng mạn, chung thủy nhưng cảm xúc nội tâm đôi khi biến đổi nhanh chóng.',
                'Sự lãng mạn và cam kết: Khi yêu, bạn chân thành và muốn gắn bó lâu dài theo chế độ một vợ một chồng. Bạn khao khát được yêu thương, chiều chuộng và sẵn sàng đáp lại bằng sự quan tâm sâu sắc.',
                'Tiêu chuẩn cao: Bạn bị thu hút bởi những người thông minh, hiểu biết rộng và có khiếu hài hước. Bạn cần một người bạn đời có thể kích thích trí tuệ của bạn, khiến bạn không bao giờ cảm thấy nhàm chán.',
                'Sự mâu thuẫn nội tâm: Dù muốn cam kết, nhưng bạn lại sợ bị ràng buộc. Bạn cần không gian riêng tư và sự độc lập. Đôi khi, cảm xúc của bạn thay đổi quá nhanh khiến đối phương không theo kịp. Bạn có thể đưa ra những quyết định bốc đồng khi cảm thấy không hài lòng, dẫn đến rạn nứt mối quan hệ.',
                'Vấn đề gia đình: Một điểm đặc biệt là bạn thường có mối quan hệ phức tạp hoặc xa cách với gia đình gốc như cha mẹ, anh chị em. Tuy nhiên, bạn lại xây dựng gia đình nhỏ của mình khá ổn định nếu tìm được người thấu hiểu.',
                'chien_luoc' => [
                    'Hãy học cách kiên nhẫn trong tình yêu. Đừng vội vàng buông tay khi gặp chút trục trặc.',
                    'Hãy chia sẻ nhu cầu về sự tự do của mình với bạn đời thay vì âm thầm chịu đựng rồi bùng nổ.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng lớn từ sự nhạy cảm của hệ thần kinh và sự dư thừa năng lượng cảm xúc.',
                'Vấn đề thể chất: Bạn có thể bị thừa cân hoặc các vấn đề liên quan đến sự tích nước, thận và hệ bài tiết. Hãy chú ý chế độ ăn uống, giảm muối và đường.',
                'Sức khỏe tinh thần: Bạn có chiều sâu nội tâm và sự nhạy cảm cao, đôi khi dẫn đến việc suy tư nhiều. Bạn có thể rơi vào trạng thái buồn bã vu vơ, trầm cảm nhẹ hoặc lo âu thái quá. Trực giác mạnh đôi khi khiến bạn cảm nhận được những năng lượng tiêu cực từ môi trường xung họng, làm bạn mệt mỏi.',
                'dinh_huong' => [
                    'Hãy tin vào trực giác của mình để giải quyết các vấn đề tâm lý.',
                    'Khi cảm thấy bất an, hãy tìm về thiên nhiên hoặc những nơi yên tĩnh.'
                ],
                'Lieu_phap' => [
                    'Bạn cần giải phóng năng lượng dư thừa thông qua việc học hỏi cái mới, đi du lịch hoặc dạy dỗ con cái/trẻ nhỏ.',
                    'Những hoạt động này giúp kích hoạt sự sáng tạo và mang lại niềm vui cho bạn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Trí tuệ là tài sản lớn nhất của bạn. Bạn là minh chứng cho câu nói: “Tự học là con đường hiệu quả, vững chắc dẫn đến thành công”.',
                'Khả năng tự học: Bạn không cần ai cầm tay chỉ việc. Bạn có khả năng tự nghiên cứu, tìm tòi và giải quyết vấn đề một cách độc lập. Bạn học từ sách vở, từ quan sát và từ chính những trải nghiệm thất bại của mình.',
                'Trưởng thành qua nghịch cảnh: Bạn giống như viên ngọc càng mài càng sáng. Những khó khăn, thử thách trong cuộc sống chính là người thầy vĩ đại nhất giúp bạn trưởng thành và khôn ngoan hơn. Nếu cuộc sống quá êm đềm, bạn thường trở nên ngây thơ và thiếu chiều sâu.',
                'Thách thức: Bạn hay nghi ngờ bản thân và người khác. Cái nhìn bi quan đôi khi có thể ảnh hưởng đến những quyết định sáng suốt của bạn.',
                'dinh_huong' => [
                    'Hãy xây dựng lòng tự trọng vững chắc. Đừng để sự tự ti hay kiêu ngạo cản trở bước tiến của bạn.',
                    'Hãy dũng cảm đối mặt với thử thách, vì sau mỗi cơn bão, bạn lại mạnh mẽ hơn gấp bội.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn có sức hút tự nhiên, có thể dễ dàng kết bạn nhưng lại khó thân thiết thực sự.',
                'Sự quyến rũ: Bạn thân thiện, nhiệt tình và có khiếu ngoại giao. Trong các buổi tiệc, bạn thường là người thú vị, biết cách trò chuyện. Bạn biết cách sử dụng các mối quan hệ để hỗ trợ cho công việc của mình.',
                'Khoảng cách nội tâm: Dù bên ngoài vui vẻ, nhưng bên trong bạn luôn giữ một khoảng cách nhất định. Bạn không dễ dàng mở lòng chia sẻ bí mật với người khác. Đôi khi, sự khách quan và lý trí của bạn có thể bị hiểu lầm là xa cách hoặc tính toán.',
                'Quý nhân: Bạn sẽ nhận được sự giúp đỡ khá lớn từ những người thầy, người bạn tri kỷ vào những lúc khó khăn nhất.',
                'chien_luoc' => [
                    'Hãy trân trọng những người bạn chân thành.',
                    'Đừng để sự nghi ngờ làm hỏng những mối quan hệ tốt đẹp.',
                    'Sự chân thành sẽ đổi lấy sự chân thành.'
                ]
            ]
        ],
        'quy_suu' => [
            'tong_quan' => [
                'Hãy tưởng tượng hình ảnh đại diện cho bạn là “Những giọt nước ngầm thấm sâu trong lòng đất” hay “Một hồ nước tĩnh lặng giữa mùa đông giá lạnh”. Bề ngoài, bạn có vẻ trầm lặng, nhẫn nại và có chút lầm lì nhưng bên trong, bạn sở hữu một nội lực bền bỉ đáng kinh ngạc, một trí tuệ sâu sắc và khả năng nuôi dưỡng vạn vật âm thầm. Bạn không ồn ào, phô trương, nhưng sự hiện diện của bạn là nền tảng vững chắc cho mọi sự phát triển.',
                'La Bàn Thịnh Vượng sẽ giúp bạn hiểu rõ sức mạnh của sự tĩnh lặng để kiến tạo thành công lớn.'
            ],
            'su_nghiep' => [
                'Bạn không thuộc tuýp người ăn to nói lớn hay thích tranh giành ánh hào quang sân khấu. Bạn là người đứng sau cánh gà, là người hùng thầm lặng kiến tạo nên thành công của tập thể.',
                'Sự bền bỉ đáng kinh ngạc: Điểm mạnh lớn nhất của bạn là sự kiên trì. Khi người khác bỏ cuộc vì chán nản hay mệt mỏi, bạn vẫn lầm lũi tiến bước. Bạn có khả năng chịu đựng áp lực công việc cực tốt. Bạn tin vào giá trị của lao động chăm chỉ và sự tích lũy theo thời gian.',
                'Chuyên gia trong lĩnh vực hẹp: Bạn thích hợp với những công việc đòi hỏi sự tỉ mỉ, nghiên cứu sâu và tính chuyên môn cao. Bạn có thể là một kỹ sư giỏi, một bác sĩ tận tâm, một nhà nghiên cứu hoặc một chuyên gia tài chính, kế toán xuất sắc.',
                'Tư duy thực tế: Bạn không mơ mộng hão huyền. Mọi kế hoạch của bạn đều dựa trên thực tế và có tính khả thi cao. Bạn giải quyết vấn đề một cách bình tĩnh, từng bước một.',
                'Thách thức: Đôi khi bạn có xu hướng kiên định với nguyên tắc và khá thận trọng với sự thay đổi. Bạn ngại thay đổi và khó thích nghi với những môi trường đòi hỏi sự linh hoạt, biến hóa nhanh chóng. Sự trầm lặng đôi khi khiến năng lực của bạn bị cấp trên bỏ qua.',
                'chien_luoc' => [
                    'Hãy học cách PR bản thân một chút, đừng chỉ làm, hãy cho người khác thấy kết quả của bạn.',
                    'Mở lòng đón nhận những phương pháp làm việc mới để không bị tụt hậu.',
                    'Sự nghiệp của bạn sẽ thăng hoa nếu bạn kết hợp được sự cần cù với tư duy đổi mới.'
                ]
            ],
            'tai_chinh' => [
                'Bạn là mẫu người kiến tha lâu cũng đầy tổ. Bạn làm giàu bằng con đường tích sản chậm mà chắc.',
                'Khả năng giữ tiền: Bạn là một trong những trụ ngày có khả năng giữ tiền tốt nhất. Bạn chi tiêu có kế hoạch, tiết kiệm và luôn lo xa cho tương lai. Trong tay bạn, đồng tiền hiếm khi bị lãng phí.',
                'Đầu tư an toàn: Bạn không thích mạo hiểm hay những trò đỏ đen. Bạn ưu tiên những kênh đầu tư an toàn, bền vững như gửi tiết kiệm, mua vàng hoặc bất động sản. Đặc biệt, bạn khá có duyên với đất đai.',
                'Thịnh vượng về hậu vận: Tuổi trẻ có thể bạn không quá dư dả vì bản tính cẩn trọng, nhưng càng về già, khối tài sản bạn tích lũy được càng lớn. Bạn là đại gia ngầm chính hiệu.',
                'Rủi ro: Đôi khi sự cẩn trọng trong chi tiêu có thể khiến bạn đắn đo quá mức, khiến bạn bỏ lỡ những cơ hội đầu tư sinh lời cao hoặc làm giảm chất lượng cuộc sống. Bạn cũng hay lo lắng thái quá về tiền bạc, khiến tâm trí bạn chưa được thảnh thơi dù tình hình đang ổn định.',
                'dinh_huong' => [
                    'Hãy hào phóng hơn với bản thân và người thân. Tiền bạc là công cụ phục vụ cuộc sống, không phải ông chủ.',
                    'Hãy trích một phần lợi nhuận để đầu tư vào các mối quan hệ xã hội, điều này sẽ mang lại cho bạn những cơ hội làm ăn bất ngờ.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn yêu như cách bạn làm việc, bạn chậm rãi, chắc chắn và vô cùng chung thủy.',
                'Sự chân thành: Bạn không biết nói những lời hoa mỹ hay tán tỉnh ngọt ngào. Bạn thể hiện tình yêu bằng hành động thiết thực: nấu một bữa ăn ngon, sửa cái xe hỏng, hay âm thầm lo lắng cho tương lai của cả hai. Bạn là bờ vai vững chắc để đối phương dựa vào.',
                'Mẫu người của gia đình: Sau khi kết hôn, bạn toàn tâm toàn ý vun vén cho tổ ấm. Bạn là người chồng/người vợ trách nhiệm, hiếu thảo với cha mẹ và yêu thương con cái hết mực.',
                'Thách thức: Bạn thể hiện tình cảm bằng hành động thực tế và kín đáo nhiều hơn là sự lãng mạn bay bổng. Đôi khi sự lầm lì, ít nói của bạn khiến đối phương đôi lúc cảm thấy thiếu sự kết nối về mặt cảm xúc hoặc nghĩ rằng bạn không quan tâm. Bạn cũng có xu hướng giữ những mối bận tâm hoặc nghi ngờ trong lòng thay vì chia sẻ, lâu ngày có thể dẫn đến sự bùng nổ cảm xúc không mong muốn.',
                'chien_luoc' => [
                    'Tình yêu cần gia vị của sự lãng mạn. Hãy học cách chia sẻ cảm xúc, nói lời yêu thương nhiều hơn. Đừng để sự im lặng tạo ra khoảng cách vô hình.',
                    'Hãy tìm một người bạn đời có tính cách vui vẻ, hoạt bát để sưởi ấm và khuấy động cuộc sống có phần bình lặng của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Cơ địa của bạn thiên về tính hàn lạnh và khá nhạy cảm với môi trường ẩm ướt.',
                'Vấn đề tiêu hóa: Sự kết hợp này có thể gây ra các vấn đề về tỳ vị, dạ dày lạnh, khó tiêu hoặc đau bụng khi ăn đồ lạnh.',
                'Xương khớp và thận: Bạn cũng cần chú ý đến các bệnh về xương khớp, đau lưng mỏi gối khi thời tiết thay đổi, hoặc chức năng thận suy giảm nếu làm việc quá sức.',
                'Tinh thần: Bạn có xu hướng suy tư nhiều, đôi khi nhìn nhận vấn đề một cách thận trọng thái quá và giữ lo âu trong lòng, có thể dẫn đến trầm cảm nhẹ hoặc stress kéo dài.',
                'dinh_huong' => [
                    'Sưởi ấm là từ khóa quan trọng nhất.',
                    'Bạn cần bổ sung thực phẩm có tính nóng, ấm như gừng, tiêu, tỏi.',
                    'Tăng cường vận động để khí huyết lưu thông.'
                ],
                'Lieu_phap' => [
                    'Tắm nắng, xông hơi hoặc ngâm chân nước nóng trước khi ngủ là những liệu pháp cực tốt cho cơ thể của bạn.',
                    'Hãy cười nhiều hơn để xua tan khí lạnh trong tâm hồn.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu một trí tuệ nội tâm sâu sắc. Bạn học không nhanh như người khác, nhưng khi đã hiểu thì hiểu khá sâu và nhớ khá lâu.',
                'Tư duy chiều sâu: Bạn thích tìm hiểu ngọn ngành, gốc rễ của vấn đề. Bạn có hứng thú với những kiến thức mang tính triết học, tôn giáo, tâm linh hoặc lịch sử.',
                'Khả năng tự tu dưỡng: Bạn có đời sống nội tâm phong phú. Bạn thường tự răn mình, tự soi chiếu bản thân để hoàn thiện nhân cách. Những lúc một mình là lúc bạn học hỏi được nhiều nhất.',
                'Thách thức: Sự tự ti. Đôi khi bạn đánh giá thấp khả năng của mình, sợ sai, sợ thất bại nên không dám bứt phá.',
                'dinh_huong' => [
                    'Hãy tin tưởng vào bản thân. Bạn có những giá trị mà người khác không có được: sự kiên định và chiều sâu.',
                    'Hãy tham gia các khóa học phát triển kỹ năng mềm để trở nên linh hoạt hơn.',
                    'Đọc sách về tâm linh hoặc thiền định sẽ giúp bạn khai mở trí tuệ tiềm ẩn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn không nhiều bạn, nhưng bạn bè của bạn đều là những người tri kỷ.',
                'Chọn lọc tự nhiên: Bạn không thích những chốn ồn ào, xô bồ. Bạn chỉ kết giao với những người thật sự hiểu và trân trọng con người mộc mạc của bạn. Trong mắt bạn bè, bạn là người cực kỳ đáng tin cậy, nói ít làm nhiều và luôn giữ bí mật tuyệt đối.',
                'Quý nhân: Quý nhân của bạn thường là những người lớn tuổi, chín chắn. Họ sẽ mang đến cho bạn cơ hội và sự ấm áp.',
                'Rủi ro: Vì quá khép kín, bạn có thể bỏ lỡ những cơ hội hợp tác tốt. Đôi khi sự lầm lì khiến người lạ hiểu lầm bạn là người khó gần, kiêu ngạo.',
                'chien_luoc' => [
                    'Hãy mở rộng vòng tròn kết nối. Một nụ cười thân thiện sẽ giúp bạn xóa tan khoảng cách.',
                    'Hãy chủ động tham gia các hoạt động cộng đồng, bạn sẽ thấy cuộc sống này còn khá nhiều người thú vị đang chờ đợi bạn.'
                ]
            ]
        ],
        'quy_hoi' => [
            'tong_quan' => [
                'Bạn đại diện cho sự hoàn tất và chứa đựng tiềm năng vô hạn. Hình ảnh của bạn là “Dòng nước biển sâu thẳm, đen huyền bí hoặc màn đêm trên đại dương”. Bạn mang trong mình sức mạnh của Đại Hải Thủy – mênh mông, khó lường, tự do và cực kỳ mạnh mẽ. Bạn là người thông minh, trực giác cực nhạy và có một thế giới nội tâm phong phú mà ít ai chạm tới được.',
                'La Bàn Thịnh Vượng sẽ giúp bạn điều hướng con tàu cuộc đời trên đại dương mênh mông ấy.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra với dòng máu của sự tự do và độc lập. Bạn cảm thấy không thoải mái và khó phát triển trong sự gò bó hay những giới hạn thông thường.',
                'Tinh thần làm chủ: Bạn là người tự khởi động. Bạn không cần ai thúc giục. Bạn có tố chất lãnh đạo bẩm sinh, ý chí kiên định và khả năng cạnh tranh cực cao. Bạn phù hợp nhất với vai trò người đứng đầu, giám đốc điều hành hoặc chủ doanh nghiệp.',
                'Sáng tạo và đổi mới: Trong trụ ngày của bạn ẩn chứa ngôi sao chủ về sự sáng tạo. Vì vậy, bạn có tư duy khá mới mẻ, độc đáo. Bạn có thể thành công rực rỡ trong các lĩnh vực nghệ thuật, âm nhạc, kịch nghệ hoặc những ngành nghề đòi hỏi trí tưởng tượng phong phú.',
                'Lý tưởng nhân đạo: Bạn không chỉ làm việc vì lợi ích cá nhân. Bạn có xu hướng hướng tới cộng đồng, xã hội. Những công việc như giáo dục, tư vấn, hoạt động phi lợi nhuận hoặc chính trị cũng khá thu hút bạn.',
                'Thách thức: Bạn khá thẳng thắn, bộc trực nên có thể làm mất lòng người khác trong môi trường công sở. Bạn rất không thích sự giả tạo và xu nịnh. Đôi khi sự tự tin cao độ có thể khiến bạn bảo vệ quan điểm một cách quyết liệt và ít tiếp thu ý kiến trái chiều.',
                'chien_luoc' => [
                    'Để sự nghiệp thăng hoa, bạn cần bổ sung nền tảng, phát triển giáo dục, sáng tạo.',
                    'Hãy tìm những môi trường cho phép bạn tự do phát triển ý tưởng. Học cách ngoại giao khéo léo hơn để tránh những xung đột không đáng có.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có tiềm năng thịnh vượng khá lớn. Bạn có khả năng tạo ra của cải vật chất dồi dào nhờ vào năng lực của chính mình.',
                'Máu kinh doanh: Bạn có tư duy thực tế và nhạy bén với thị trường. Bạn biết cách biến cơ hội thành tiền bạc. Đặc biệt nếu bạn sinh vào ban ngày, khả năng kinh doanh và gầy dựng sự nghiệp của bạn càng mạnh mẽ.',
                'Thịnh vượng: Đối với phụ nữ, thường có vận tài lộc khá tốt. Bạn có khả năng độc lập tài chính, thậm chí có thể trở thành đại gia hoặc lấy được người chồng giàu có, giúp nâng cao vị thế xã hội.',
                'Đầu tư cho trí tuệ: Bạn hiểu rằng đầu tư vào bản thân, vào kiến thức và sự sáng tạo là khoản đầu tư siêu lợi nhuận.',
                'Rủi ro: Bạn cần cẩn trọng trong các giao dịch tài chính hoặc hợp tác liên quan đến bạn bè, người thân. Bạn có thể bị thất thoát tiền bạc nếu tin tưởng sai người hoặc cho vay mượn không đúng chỗ.',
                'dinh_huong' => [
                    'Hãy tập trung vào việc phát triển năng lực cá nhân.',
                    'Tránh những vụ hùn hạp làm ăn mập mờ với bạn bè.',
                    'Hãy dùng tiền để phục vụ cho sự tự do và những trải nghiệm sống phong phú của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Tình yêu đối với bạn là một cuộc phiêu lưu đầy cảm xúc. Bạn quyến rũ, bí ẩn nhưng cũng khá khó nắm bắt.',
                'Sức hút cá nhân: Bạn có duyên ngầm, hài hước và thân thiện. Bạn dễ dàng trở thành tâm điểm của sự chú ý. Người khác giới bị thu hút bởi sự phóng khoáng và thông minh của bạn.',
                'Yêu cầu về sự tự do: Đây là điều quan trọng nhất. Bạn cần một người bạn đời tôn trọng không gian riêng tư của bạn. Nếu bị kiểm soát hay ghen tuông vô lý, bạn sẽ cảm thấy ngột ngạt và tìm cách thoát ra. Hôn nhân của bạn chỉ bền vững khi có sự tin tưởng và tự do.',
                'Sự khác biệt giới tính: Đối với phụ nữ, thường có hôn nhân viên mãn, bền lâu. Bạn chung thủy và biết cách vun vén gia đình. Tuy nhiên, cần chú ý cân bằng năng lượng, thể hiện sự mềm mại và tôn trọng không gian, vai trò của đối phương. Đối với nam giới, đường tình duyên có nhiều thử thách hơn. Bạn có sức hút tự nhiên và dễ rung động. Bạn cần học cách kiềm chế và trân trọng người hiện tại để tránh đổ vỡ.',
                'Thách thức: Bạn hay nghi ngờ và có xu hướng cô lập bản thân khi gặp chuyện buồn. Bạn cũng có thể vướng vào những mối quan hệ phức tạp hoặc yêu người trái ngược tính cách hoàn toàn.',
                'chien_luoc' => [
                    'Hãy tìm một người bạn đời thông minh, thú vị và hiểu chuyện.',
                    'Hãy mở lòng chia sẻ những góc khuất trong tâm hồn để đối phương thấu hiểu.',
                    'Đừng vội vã kết hôn khi chưa thực sự sẵn sàng cam kết.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn chịu ảnh hưởng trực tiếp từ sự dư thừa năng lượng và cảm xúc, đòi hỏi sự chú trọng đặc biệt vào việc cân bằng bài tiết và thân nhiệt.',
                'Vấn đề thận và khí huyết: Bạn cần chú ý bảo vệ những cơ quan này. Tránh ăn quá mặn hoặc uống quá nhiều chất kích thích.',
                'Sức khỏe tinh thần: Bạn có thể bị ảnh hưởng bởi những cảm xúc tiêu cực, sự nghi ngờ và lo âu. Nếu năng lượng bị tắc nghẽn do suy nghĩ nhiều, ít vận động, bạn có thể rơi vào trạng thái trầm cảm hoặc mệt mỏi mãn tính.',
                'dinh_huong' => [
                    'Hãy dành thời gian hòa mình vào thiên nhiên, trồng cây hoặc đi dạo trong rừng.'
                ],
                'Lieu_phap' => [
                    'Thiền định và các bộ môn tu dưỡng tinh thần là liều thuốc quý giá giúp bạn cân bằng cảm xúc và tìm lại sự bình yên nội tại.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn sở hữu một trí tuệ trực giác và chiều sâu triết học đáng nể.',
                'Học hỏi không ngừng: Bạn tò mò về thế giới và luôn muốn khám phá những điều mới lạ. Bạn thích đi du lịch, trải nghiệm văn hóa để mở rộng tầm nhìn. Đối với bạn, việc học không bao giờ dừng lại.',
                'Tư duy phản biện: Bạn không dễ dàng tin vào những điều người khác nói. Bạn luôn đặt câu hỏi và tự mình tìm kiếm câu trả lời. Bạn có hứng thú với những vấn đề trừu tượng, triết học hoặc tâm linh.',
                'Thách thức: Thách thức lớn nhất là sự chủ quan. Đôi khi bạn quá tin vào nhận định của mình và có thể vô tình bỏ qua hay đánh giá thấp góc nhìn giá trị của người khác. Sự chủ quan này có thể khiến bạn vấp ngã.',
                'dinh_huong' => [
                    'Hãy giữ tâm thế của một chiếc ly rỗng để đón nhận kiến thức mới. Rèn luyện sự khiêm tốn và kiên nhẫn.',
                    'Hãy dùng trí tuệ của mình để giải quyết vấn đề một cách bình tĩnh thay vì phản ứng bốc đồng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người của công chúng, quảng giao và có mạng lưới bạn bè rộng khắp.',
                'Thân thiện và hòa đồng: Bạn có thể kết nối với rất nhiều kiểu người khác nhau, từ sang trọng đến bình dân. Bạn không phân biệt đối xử và luôn đối xử chân thành với mọi người.',
                'Bạn bè là con dao hai lưỡi: Dù nhiều bạn, nhưng bạn cần hết sức cẩn trọng. Một số mối quan hệ có thể trở thành đối thủ cạnh tranh hoặc không thực sự hỗ trợ cho con đường sự nghiệp của bạn. Đừng để lòng tốt của mình bị lợi dụng.',
                'Sự nổi loạn ngầm: Bạn có xu hướng thích những người cá tính, khác biệt hoặc hơi nổi loạn giống mình. Điều này mang lại sự thú vị nhưng cũng tiềm ẩn rủi ro.',
                'chien_luoc' => [
                    'Hãy chọn lọc các mối quan hệ, giữ lại bên mình những người bạn chân thành, tích cực và biết tôn trọng giới hạn.',
                    'Hãy học cách bảo vệ bản thân trước những thị phi không đáng có.'
                ]
            ]
        ],
        'quy_mao' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn đẹp và thơ mộng vô cùng: “Những hạt sương mai long lanh đọng trên lá cỏ non buổi sớm” hay “Một cơn mưa xuân nhẹ nhàng tưới mát vạn vật”. Bạn mang trong mình sự tinh tế, nhạy cảm, thông minh và một tâm hồn nghệ sĩ bay bổng. Bạn tạo nên một dòng chảy của sự sáng tạo, văn chương và sự duyên dáng tự nhiên.',
                'La Bàn Thịnh Vượng sẽ giúp bạn nâng niu và phát huy vẻ đẹp tinh tế ấy để tỏa sáng theo cách riêng.'
            ],
            'su_nghiep' => [
                'Bạn sẽ phát huy tốt nhất năng lực trong môi trường trí tuệ, sáng tạo, thay vì những công việc đòi hỏi thể lực cao hoặc cạnh tranh gay gắt. Bạn là người của trí tuệ và sự sáng tạo.',
                'Tài năng thiên bẩm: Bạn có khiếu văn chương, nghệ thuật và thẩm mỹ khá tốt. Tư duy của bạn sắc bén, linh hoạt và luôn đầy ắp những ý tưởng mới lạ. Bạn đặc biệt phù hợp với các lĩnh vực như: Viết lách, thiết kế, thời trang, truyền thông, giáo dục, tư vấn hoặc nghệ thuật.',
                'Phong cách làm việc: Bạn làm việc nhẹ nhàng nhưng hiệu quả. Bạn không thoải mái với sự áp đặt hay môi trường làm việc quá căng thẳng. Bạn thích dùng nhu thắng cương, dùng sự khéo léo để giải quyết vấn đề. Bạn là người cầu toàn, luôn muốn mọi sản phẩm mình làm ra phải thật tinh tế và hoàn hảo.',
                'Phát triển học tập: Bạn dễ dàng đạt được thành công và nổi tiếng nhờ tài năng thực lực của mình.',
                'Thách thức: Bạn khá nhạy cảm và có thể bị tổn thương bởi những lời phê bình. Áp lực công việc quá lớn có thể khiến bạn căng thẳng và muốn tìm kiếm một không gian yên tĩnh hơn. Bạn cũng thiếu tính quyết liệt, đôi khi do dự không dám nắm bắt cơ hội lớn.',
                'chien_luoc' => [
                    'Hãy chọn môi trường làm việc văn minh, tôn trọng sự sáng tạo.',
                    'Hãy rèn luyện thêm sự dũng cảm và quyết đoán. Đừng ngại thể hiện cá tính riêng, chính sự khác biệt đó làm nên thương hiệu của bạn.'
                ]
            ],
            'tai_chinh' => [
                'Tài lộc của bạn thường đến từ tài năng và sự yêu mến của mọi người, bạn có nhân duyên tốt.',
                'Lộc từ tài năng: Bạn kiếm tiền bằng chất xám, bằng sự khéo léo và kỹ năng chuyên môn. Càng trau dồi kỹ năng, thu nhập của bạn càng cao. Bạn có duyên với những nguồn tiền nhẹ nhàng nhưng đều đặn.',
                'Được quý nhân phù trợ: Bạn là người dễ mến nên thường được người khác giúp đỡ, tặng quà hoặc giới thiệu cơ hội làm ăn. Tài chính của bạn thường khá ổn định, ít khi rơi vào cảnh túng thiếu cùng cực.',
                'Chi tiêu cho cảm xúc: Bạn là người có gu thẩm mỹ, thích cái đẹp nên thường chi tiêu nhiều cho trang phục, làm đẹp, trang trí nhà cửa hoặc những sở thích tao nhã. Đôi khi bạn mua sắm chỉ để nuông chiều cảm xúc nhất thời.',
                'Rủi ro: Việc quản lý tài chính chi tiết có thể không phải là sở trường hoặc ưu tiên của bạn. Bạn có thể bị người khác lợi dụng lòng tốt để vay mượn tiền bạc.',
                'dinh_huong' => [
                    'Hãy tìm một người quản lý tài chính đáng tin cậy hoặc học cách ghi chép chi tiêu.',
                    'Đầu tư vào bản thân như học tập, ngoại hình là khoản đầu tư sinh lời nhất của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Đây là khía cạnh rực rỡ nhưng cũng mong manh nhất của bạn. Bạn là người giàu cảm xúc, lãng mạn và có sức hút mãnh liệt với người khác giới.',
                'Sự quyến rũ tự nhiên: Bạn dịu dàng, tinh tế và biết cách lắng nghe. Ở bên cạnh bạn, người khác cảm thấy nhẹ nhàng, bình yên. Bạn là mẫu người yêu lý tưởng, biết tạo ra những khoảnh khắc lãng mạn như phim.',
                'Khao khát được yêu thương: Bạn sống vì tình yêu. Bạn cần sự quan tâm, che chở và những lời nói ngọt ngào như cây cỏ cần nước. Khi yêu, bạn toàn tâm toàn ý và có xu hướng kết nối sâu sắc, dễ bị ảnh hưởng bởi cảm xúc của đối phương.',
                'Thách thức: Bạn khá nhạy cảm, có thể buồn, có thể giận, có thể tủi thân. Tâm hồn bạn mong manh như hạt sương, chỉ một cơn gió nhẹ cũng làm bạn dao động. Bạn hay lý tưởng hóa tình yêu nên có thể vỡ mộng khi đối diện với thực tế hôn nhân cơm áo gạo tiền.',
                'chien_luoc' => [
                    'Hãy học cách yêu bản thân mình trước. Đừng đặt hết hạnh phúc của mình vào tay người khác.',
                    'Hãy tìm một người bạn đời vững chãi, bao dung và thực tế để làm điểm tựa cho tâm hồn bay bổng của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn khá nhạy cảm với môi trường bên ngoài, cần được chăm sóc cẩn thận.',
                'Hệ thần kinh và thận: Bạn có thể bị suy nhược thần kinh, mất ngủ, hay lo âu hoặc các bệnh liên quan đến khí huyết kém, tay chân lạnh.',
                'Máy dự báo thời tiết: Bạn rất nhạy cảm với sự thay đổi của thời tiết, chỉ cần trời trở gió là cơ thể sẽ phản ứng ngay như đau đầu, sổ mũi, mệt mỏi.',
                'dinh_huong' => [
                    'Bạn cần một lối sống lành mạnh, nhẹ nhàng. Không nên làm việc quá sức hay thức quá khuya.'
                ],
                'Lieu_phap' => [
                    'Yoga, thiền, đi bộ trong thiên nhiên hoặc làm vườn là những hoạt động tuyệt vời giúp bạn cân bằng năng lượng.',
                    'Hãy chú ý giữ ấm cơ thể, đặc biệt là vùng chân và thắt lưng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người thông minh, học một biết mười và có trực giác tâm linh khá mạnh.',
                'Trí tuệ thẩm thấu: Bạn không học theo kiểu nhồi nhét, bạn học bằng cách thẩm thấu. Bạn cảm nhận kiến thức bằng trực giác và sự rung động của tâm hồn. Bạn có khả năng tự học ngoại ngữ, nghệ thuật khá tốt.',
                'Trực giác mạnh mẽ: Bạn có giác quan thứ 6 nhạy bén. Bạn thường có những linh cảm chính xác về người khác hoặc sự việc sắp xảy ra. Đây là món quà trời ban, hãy tin tưởng vào nó.',
                'Thách thức: việc duy trì sự kiên trì lâu dài có thể là một thách thức đối với bạn. Bạn thích nhiều thứ nhưng thường chỉ tìm hiểu lướt qua chứ không đi sâu. Xu hướng tìm kiếm sự thoải mái, tận hưởng cuộc sống hoặc sự thiếu kỷ luật đôi khi cản trở con đường học vấn của bạn.',
                'dinh_huong' => [
                    'Hãy rèn luyện sự tập trung, chọn một lĩnh vực mình yêu thích nhất và theo đuổi đến cùng.',
                    'Hãy biến sự nhạy cảm thành lợi thế để thấu hiểu con người và cuộc sống sâu sắc hơn.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người được lòng thiên hạ. Đi đến đâu bạn cũng được mọi người yêu mến, giúp đỡ.',
                'Ngoại giao khéo léo: Bạn biết cách cư xử chừng mực, lễ phép và tinh tế. Bạn hiếm khi làm mất lòng ai. Bạn là người kết nối tuyệt vời trong các mối quan hệ xã hội.',
                'Quý nhân vây quanh: Cuộc đời bạn thường gặp nhiều may mắn nhờ quý nhân phù trợ. Khi gặp khó khăn, luôn có người chìa tay ra giúp bạn.',
                'Rủi ro: Vì tính cách dĩ hòa vi quý, bạn có thể gặp khó khăn khi cần đưa ra lời từ chối. Bạn có thể bị cuốn vào những rắc rối của người khác hoặc bị lợi dụng lòng tốt. Bạn cũng sợ cô đơn nên đôi khi mở rộng mối quan hệ nhanh chóng mà chưa có đủ thời gian để chọn lọc kỹ càng.',
                'chien_luoc' => [
                    'Hãy học cách thiết lập ranh giới cá nhân. Biết từ chối là một kỹ năng cần thiết để bảo vệ năng lượng của bản thân.',
                    'Hãy trân trọng những người giúp đỡ mình và sống với lòng biết ơn, may mắn sẽ luôn mỉm cười với bạn.'
                ]
            ]
        ],
        'tan_hoi' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn thật thanh tao, nghệ sĩ và đầy chất thơ: “Một viên ngọc trai sáng bóng nằm yên bình dưới đáy nước sâu, hay một món trang sức vàng ròng được dòng nước suối trong vắt gột rửa”. Bạn sinh ra với khí chất thông minh xuất chúng, tâm hồn nhạy cảm và một khả năng sáng tạo vô biên. Bạn dùng chính tài năng và trí tuệ của mình để tạo ra giá trị cho đời, giống như dòng nước chảy ra từ viên ngọc, vừa lấp lánh vừa mang lại sự sống.',
                'La Bàn Thịnh Vượng sẽ giúp bạn bảo vệ và làm sáng viên ngọc quý bên trong mình.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu một bộ óc thiên tài và không sinh ra để làm những việc chân tay nặng nhọc hay lặp lại nhàm chán.',
                'Tư duy tiên phong: Bạn luôn nhìn thấy những giải pháp độc đáo, mới lạ mà người khác không thể nghĩ ra. Bạn ghét sự cũ kỹ, lối mòn và luôn muốn cải tiến mọi thứ tốt đẹp hơn.',
                'Phong cách tự do: Bạn làm việc hiệu quả nhất khi được tự do, không bị gò bó bởi thời gian hay quy định cứng nhắc. Bạn là kiểu người lãnh đạo bằng cách truyền cảm hứng, khơi gợi sự sáng tạo chứ không phải bằng mệnh lệnh.',
                'Lĩnh vực phù hợp: Trí tuệ và học thuật: Với tư duy sâu sắc, bạn có thể trở thành những nhà nghiên cứu, học giả, giáo sư hoặc diễn giả uyên bác. Sáng tạo và nghệ thuật: Khả năng ngôn ngữ và cảm thụ cái đẹp tuyệt vời giúp bạn thăng hoa trong viết lách, âm nhạc, điện ảnh, thiết kế. Tư vấn và chữa lành: Lòng trắc ẩn và sự nhạy cảm giúp bạn thấu hiểu người khác, phù hợp với tâm lý học, tư vấn, luật sư hoặc hoạt động xã hội.',
                'Thách thức: Điểm yếu lớn nhất của bạn là sự thiếu tập trung và thách thức trong việc duy trì sự hứng khởi lâu dài. Bạn có quá nhiều ý tưởng hay ho nhưng lại thiếu kỷ luật để biến chúng thành hiện thực. Khi nguồn cảm hứng vơi đi, bạn có xu hướng muốn chuyển hướng sang những điều mới mẻ hơn hoặc gặp chút khó khăn.',
                'chien_luoc' => [
                    'Hãy rèn luyện tính kỷ luật. Lập kế hoạch cụ thể và cam kết thực hiện nó đến cùng, dù chán cũng phải cố gắng hoàn thành.',
                    'Chọn những công việc cho phép bạn được sáng tạo và đổi mới liên tục.',
                    'Tìm những người cộng sự có tính cách thực tế, kiên định để hỗ trợ bạn triển khai ý tưởng và giữ đôi chân bạn trên mặt đất.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có duyên ngầm khá lớn với tiền bạc, bởi vì với bạn tiền luôn nằm trong đầu.',
                'Kiếm tiền bằng chất xám: Bạn tạo ra của cải bằng chính trí tuệ, kỹ năng chuyên môn và những ý tưởng đột phá của mình. Bạn tin rằng sự thịnh vượng là phần thưởng xứng đáng cho tài năng và sự sáng tạo.',
                'Hậu vận sung túc: Dù tuổi trẻ có thể còn mông lung, chưa định hình được hướng đi, nhưng càng về sau bạn càng tích lũy được nhiều tài sản bền vững. Bạn có khả năng sống một cuộc đời sung túc, thoải mái.',
                'Rủi ro: Khả năng kiếm tiền của bạn khá tốt, nhưng khả năng giữ tiền lại khá kém. Bạn là người thích hưởng thụ, yêu cái đẹp và sự tiện nghi, nên có thể vung tiền cho những món đồ xa xỉ hoặc những chuyến đi tốn kém. Tâm hồn nghệ sĩ đôi khi khiến bạn ít để tâm chi tiết đến các vấn đề tài chính phức tạp nên có thể bị người khác lợi dụng.',
                'dinh_huong' => [
                    'Hãy học cách tiết kiệm một cách kỷ luật. Đầu tư vào những tài sản dài hạn ngay như nhà đất hoặc gửi tiết kiệm dài hạn để tránh việc tiêu xài tùy hứng.',
                    'Đầu tư cho bản thân: Chi tiền cho giáo dục, học kỹ năng mới là khoản đầu tư sinh lời nhất đối với bạn.',
                    'Kiểm soát chặt chẽ các khoản chi tiêu cho sở thích cá nhân, hãy suy nghĩ kỹ trước khi mua sắm.'
                ]
            ],
            'tinh_duyen' => [
                'Bạn là một người tình lãng mạn, tinh tế và khao khát một sự kết nối tâm hồn sâu sắc.',
                'Sự hy sinh và tinh tế: Bạn quyến rũ một cách nhẹ nhàng, biết cách quan tâm và làm cho đối phương cảm thấy mình thật đặc biệt. Bạn sẵn sàng hy sinh lợi ích cá nhân vì người mình yêu.',
                'Thách thức: Đường tình duyên của bạn thường gặp nhiều sóng gió và trắc trở. Đối với phụ nữ, bạn quá thông minh và sắc sảo, đôi khi sự sắc sảo đôi khi vô tình tạo ra khoảng cách về vị thế với người bạn đời hoặc khiến đối phương cảm thấy áp lực. Tiêu chuẩn cao về trí tuệ và sự đồng điệu đôi khi khiến việc tìm kiếm người bạn đời ưng ý trở nên thử thách hơn. Cảm xúc thất thường: Sự nhạy cảm khiến cảm xúc của bạn có những bước sóng dao động phong phú, lúc nóng lúc lạnh, khiến người yêu không biết đường nào mà chiều. Sự bất an nội tâm khiến bạn hay nghi ngờ, ghen tuông và đòi hỏi sự quan tâm quá mức.',
                'dinh_huong' => [
                    'Kết hôn muộn sẽ giúp bạn trưởng thành hơn về cảm xúc, giảm bớt những xung đột không đáng có.',
                    'Hãy học cách lạt mềm buộc chặt, đừng mang sự sắc sảo của trí tuệ vào chuyện tình cảm. Đôi khi buông bỏ sự phân tích rạch ròi để cảm nhận bằng trái tim sẽ mang lại hạnh phúc.',
                    'Tìm một người bạn đời vững chãi, bao dung và kiên nhẫn để làm chỗ dựa bình yên cho tâm hồn nhạy cảm của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Cơ thể bạn khá nhạy cảm với thời tiết và môi trường, có thể bị lạnh và tiêu hao năng lượng.',
                'Thể chất: Hệ tiêu hóa và bài tiết là điểm yếu. Bạn có thể bị lạnh bụng, khó tiêu, hoặc gặp vấn đề về thận. Phổi và đường hô hấp cũng cần được bảo vệ kỹ lưỡng.',
                'Tinh thần: Bạn là người đa sầu đa cảm, hay suy nghĩ lung tung. Điều này có thể dẫn đến căng thẳng, những trạng thái mất cân bằng năng lượng sâu sắc nếu gặp chuyện buồn. Sự nhạy cảm quá mức khiến bạn có thể bị tổn thương bởi những lời nói vô tình.',
                'Liệu pháp' => [
                    'Nguyên tắc vàng là giữ ấm. Ăn uống đồ ấm nóng, tránh đồ lạnh, giữ ấm vùng bụng và chân.',
                    'Chăm sóc tinh thần: Thiền định, tiếp xúc với thiên nhiên, viết lách hoặc chơi nhạc cụ là những cách tuyệt vời để giải tỏa cảm xúc.',
                    'Hạn chế tối đa rượu bia và chất kích thích, vì cơ thể bạn phản ứng khá mạnh với những chất này.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn được trời phú cho khả năng tự học và trực giác cực kỳ nhạy bén.',
                'Thiên tài học hỏi: Bạn tiếp thu kiến thức mới cực nhanh, đặc biệt là ngôn ngữ và nghệ thuật. Trực giác mách bảo cho bạn biết bản chất thật sự của con người và sự việc mà không cần phân tích nhiều.',
                'Thách thức: Thử thách nằm ở việc duy trì sự tập trung sâu vào một lĩnh vực cụ thể. Bạn có kiến thức rộng, nhưng cần đầu tư thêm thời gian để đạt được chiều sâu chuyên gia. Nỗi sợ bị chỉ trích, phán xét cũng khiến bạn ngại ngùng, không dám thể hiện hết tài năng của mình.',
                'dinh_huong' => [
                    'Hãy chọn chuyên môn, đào sâu nghiên cứu một lĩnh vực duy nhất cho đến khi trở thành chuyên gia thực thụ.',
                    'Tìm hiểu về triết học, tâm linh để tìm thấy điểm tựa vững chắc cho tinh thần.',
                    'Rèn luyện thói quen hoàn thành những gì đã bắt đầu, đừng bỏ dở giữa chừng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người hòa đồng, khéo léo và khá được lòng mọi người xung quanh.',
                'Ngoại giao tốt: Bạn biết cách cư xử tế nhị, làm hài lòng người khác mà không đánh mất mình. Tuy nhiên, bạn cũng khá có thể bị ảnh hưởng bởi ý kiến của bạn bè và khao khát sự công nhận từ xã hội.',
                'Quý nhân: Bạn may mắn thường xuyên được những người tài giỏi, có học thức và địa vị giúp đỡ, mang lại những cơ hội quý giá.',
                'Lời khuyên' => [
                    'Hãy chọn bạn mà chơi. Tránh xa những người tiêu cực, than vãn hoặc có ý định lợi dụng lòng tốt của bạn.',
                    'Giữ vững lập trường của bản thân. Đừng để người khác chi phối quyết định cuộc đời mình.',
                    'Sự chân thành sẽ giúp bạn tìm được những người bạn tri kỷ, những người trân trọng tâm hồn đẹp đẽ của bạn.'
                ]
            ]
        ],
        'tan_mao' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn tràn đầy tính hành động và sự tinh tế thực tế: Một chiếc kéo vàng sắc bén đang tỉa tót cây cảnh, hay một viên ngọc quý được đặt trang trọng trên nhung lụa. Giống như ngọc cần mài mới sáng, cây cần tỉa mới ra dáng bon-sai. Bạn thực tế, nhạy bén, có gu thẩm mỹ tuyệt vời nhưng nội tâm luôn đầy ắp những mâu thuẫn và áp lực.',
                'La Bàn Thịnh Vượng sẽ giúp bạn sử dụng “chiếc kéo” của mình để cắt tỉa những cành thừa, biến cuộc đời thành một kiệt tác nghệ thuật.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tư duy lai tạo giữa một nhà quản lý tài ba và một nghệ nhân khó tính. Bạn không chấp nhận sự xuề xòa.',
                'Tư duy thực chiến và quyết đoán: Bạn là người dám nghĩ dám làm. Bạn ghét lý thuyết suông, ghét sự chậm chạp và muốn nhìn thấy kết quả ngay lập tức. Tính cách này giúp bạn giải quyết vấn đề nhanh gọn, đi thẳng vào trọng tâm và cực kỳ hiệu quả trong mắt cấp trên.',
                'Con mắt nhà nghề: Bạn thích mọi thứ phải trật tự, chỉn chu và đẹp mắt. Bạn có khả năng nhìn thấy giá trị tiềm ẩn trong những thứ thô sơ và biết cách mài giũa nó thành sản phẩm đắt giá. Không một chi tiết lỗi nào, dù nhỏ nhất, có thể qua mắt được bạn.',
                'Lĩnh vực phù hợp: Tài chính và kinh doanh: Với trực giác nhạy bén về tiền bạc, bạn cực hợp với đầu tư, ngân hàng, kế toán. Bạn biết cách xoay vòng vốn và tối ưu hóa lợi nhuận tài tình. Thẩm mỹ và nghệ thuật: Sự tinh tế bẩm sinh giúp bạn tỏa sáng trong thiết kế như nội thất, thời trang, kiến trúc, hoặc kinh doanh trang sức đá quý, spa làm đẹp. Quản lý và giám sát: Khả năng soi xét chi tiết biến bạn thành người giám sát, kiểm định chất lượng hoặc quản lý nhân sự xuất sắc.',
                'Thách thức: Sự cầu toàn thái quá có thể khiến bạn trở nên quá kỹ tính và đòi hỏi sự hoàn hảo khắc nghiệt. Bạn có thể gây áp lực lớn cho đồng nghiệp vì tiêu chuẩn quá cao của mình. Đôi khi bạn ưu tiên hiệu quả thực tế hơn các yếu tố cảm xúc, nhân văn.',
                'chien_luoc' => [
                    'Hãy học cách dùng lời nói nhẹ nhàng, sự khích lệ để nuôi dưỡng mối quan hệ.',
                    'Áp dụng nguyên tắc khen trước, chê sau để thu phục nhân tâm.',
                    'Đừng để những chi tiết vụn vặt làm hỏng tầm nhìn chiến lược lớn của bạn.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có xu hướng thích làm giàu.',
                'Khát khao thịnh vượng: Bạn thích sự giàu có, đẳng cấp và những tiện nghi xa xỉ. Bạn quan niệm tài chính vững mạnh là chìa khóa giải quyết hầu hết các vấn đề trong cuộc sống, đánh giá cao sức mạnh và sự tự do mà tài chính mang lại. Độ động lực kiếm tiền của bạn khá mạnh mẽ, bạn biết chớp thời cơ nhanh và không ngại rủi ro để đạt lợi nhuận cao.',
                'Dòng tiền nhanh: Bạn có duyên với những khoản tiền bất ngờ, tiền thưởng, lợi nhuận từ đầu tư mạo hiểm hoặc kinh doanh chớp nhoáng như lướt sóng hơn là làm công ăn lương ổn định.',
                'Rủi ro: Bạn kiếm tiền khá giỏi nhưng có thể chưa giữ được tiền. Nhu cầu tận hưởng cuộc sống cao đôi khi ảnh hưởng đến kế hoạch tích lũy có thể là vấn đề tài chính lớn nhất.',
                'dinh_huong' => [
                    'Hãy học cách quản lý rủi ro, đừng bao giờ bỏ hết trứng vào một giỏ vì lòng tham nhất thời.',
                    'Cần phân bổ danh mục đầu tư để tránh rủi ro từ sự hứng khởi nhất thời.',
                    'Tích lũy khi đang thịnh vượng là bài học sống còn để phòng khi sa cơ.',
                    'Cân nhắc đầu tư vào những tài sản có giá trị bền vững như bất động sản, tích sản thay vì chỉ lướt sóng ngắn hạn.'
                ]
            ],
            'tinh_duyen' => [
                'Chuyện tình cảm của bạn rực rỡ sắc màu, lãng mạn nhưng cũng đầy sóng gió.',
                'Sức hút đào hoa: Bạn quyến rũ, ăn mặc có gu, biết cách nói chuyện và làm hài lòng người khác phái. Bạn lãng mạn, hào phóng và luôn tìm kiếm những mối quan hệ thú vị, mới mẻ.',
                'Sự chiếm hữu: Bạn có xu hướng muốn che chở và bao quát cuộc sống của đối phương. Bạn có xu hướng ghen tuông, nghi ngờ vô cớ và muốn kiểm soát đối phương trong tầm mắt.',
                'Thách thức hôn nhân: Vợ chồng thường xuyên khắc khẩu, có thể xảy ra những bất đồng quan điểm hoặc tranh luận do cái tôi lớn và sự bất đồng quan điểm. Đối với nam giới, dễ rung động trước cái đẹp và sự mới lạ, cần giữ vững bản lĩnh trước các thử thách tình cảm. Đối với phụ nữ, Thông minh, quán xuyến tốt nhưng đôi khi quá chi tiết và cầu toàn làm tổn thương đối phương.',
                'chien_luoc' => [
                    'Hãy tôn trọng không gian riêng và cá tính của người bạn đời, đừng cố uốn nắn họ quá mức.',
                    'Một điều nhịn, chín điều lành là điều nên làm trong mối quan hệ của bạn.',
                    'Sự chung thủy và tin tưởng là nền tảng duy nhất giúp bạn tránh khỏi đổ vỡ.'
                ]
            ],
            'suc_khoe' => [
                'Cơ thể bạn có thể gặp phải các chấn thương tay chân hoặc các vấn đề về xương khớp.',
                'Tổn thương gan, mật: Bạn cần tuyệt đối tránh xa rượu bia và đồ cay nóng để bảo vệ lá gan.',
                'Xương khớp và thần kinh: Bạn có thể gặp chấn thương tay chân, tai nạn dao kéo, đau nhức xương khớp, thần kinh tọa hoặc căng thẳng thần kinh do áp lực kiếm tiền và giữ hình ảnh.',
                'Liệu pháp' => [
                    'Cẩn trọng khi lái xe hoặc sử dụng vật sắc nhọn.',
                    'Thực hành thư giãn, massage, yoga hoặc thiền định để làm mềm cơ thể và dịu hệ thần kinh đang căng thẳng.',
                    'Bổ sung thực phẩm có vị chua như chanh, cam và rau xanh để dưỡng gan.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, nhanh nhạy nhưng thiếu sự kiên nhẫn với những gì quá trừu tượng hay lý thuyết.',
                'Trí tuệ thực tế: Bạn học khá nhanh những gì tạo ra tiền hoặc giá trị ngay lập tức. Tư duy logic và thẩm mỹ là vũ khí mạnh nhất của bạn. Bạn học từ trải nghiệm thực tế, trường đời giỏi hơn là sách vở.',
                'Thách thức: Bạn khó ngồi yên một chỗ để nghiên cứu sâu. Sự thiếu kiên nhẫn khiến bạn hay bỏ cuộc giữa chừng, bỏ lỡ những thành tựu đỉnh cao cần thời gian vun đắp.',
                'dinh_huong' => [
                    'Hãy tập trung phát triển kỹ năng thực tế: Quản lý, đầu tư, thẩm mỹ.',
                    'Rèn luyện sự kiên trì là bài học bắt buộc nếu muốn đi xa.',
                    'Học cách nhìn sâu vào bản chất vấn đề thay vì chỉ lướt qua bề mặt hào nhoáng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người khéo léo, hoạt ngôn và biết nhìn mặt mà bắt hình dong cực chuẩn.',
                'Mạng lưới hợp tác: Bạn giỏi xây dựng quan hệ để phục vụ mục đích công việc. Bạn có nhiều bạn bè cùng gu thưởng thức và phong cách sống hoặc làm ăn buôn bán.',
                'Quý nhân: Bạn thường được người giàu có, thành đạt giúp đỡ hoặc mang lại cơ hội tài chính.',
                'Rủi ro: Các mối quan hệ đôi khi thiếu chiều sâu, dựa trên lợi ích nhiều hơn tình cảm chân thành. Sự sắc sảo và nổi bật quá mức cũng khiến bạn có thể bị tiểu nhân ghen ghét, đố kỵ.',
                'chien_luoc' => [
                    'Hãy đặt niềm tin và sự chân thành lên trên lợi ích ngắn hạn.',
                    'Sự chân thành sẽ giúp bạn tìm được tri kỷ – những người sẽ ở bên bạn ngay cả khi bạn không còn hào quang hay tiền bạc, chứ không chỉ là những bạn xã giao nhất thời.'
                ]
            ]
        ],
        'tan_suu' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn mang vẻ đẹp của sự tiềm ẩn và bền bỉ: “Một viên ngọc quý hay những hạt bụi vàng nằm sâu trong lòng đất mùa đông lạnh giá”. Bạn được sinh ra trên nền tảng của sự bảo bọc (Thổ sinh Kim) nhưng lại trong hoàn cảnh lạnh lẽo, khắc nghiệt. Điều này tạo nên một khí chất thâm trầm, kín đáo nhưng nội lực vô cùng phi thường. Bạn không cần phô trương, giá trị của bạn nằm ở sự tinh tế và sắc sảo bên trong.',
                'La Bàn Thịnh Vượng sẽ giúp bạn mài giũa lớp đá thô nhám để viên ngọc bản mệnh được tỏa sáng rực rỡ nhất.'
            ],
            'su_nghiep' => [
                'Bạn không thuộc tuýp người ồn ào tranh giành hào quang, bạn là bậc thầy của sự chuẩn bị và hoàn hảo.',
                'Phong cách thâm trầm và Bền bỉ: Bạn làm việc với sự tỉ mỉ và tinh thần trách nhiệm cực cao. Bạn không làm thì thôi, đã làm là phải chắc chắn. Khả năng tập trung và kiên nhẫn giúp bạn giải quyết những vấn đề hóc búa mà người khác bỏ cuộc.',
                'Trí tuệ sắc bén: Bạn sở hữu tư duy logic và khả năng nhìn thấu chi tiết. Bạn có thể ngồi hàng giờ để nghiên cứu số liệu hoặc tìm ra nguyên nhân gốc rễ của vấn đề.',
                'Lĩnh vực phù hợp: Chuyên gia và cố vấn: Sự chính xác giúp bạn thành công trong kế toán, tài chính, kỹ thuật hoặc y dược, đặc biệt các chuyên ngành đòi hỏi sự khéo léo như phẫu thuật, nha khoa. Nghệ thuật và thẩm mỹ: Bạn yêu cái đẹp. Bạn có thể tỏa sáng trong thiết kế, kiến trúc, thời trang nhờ gu thẩm mỹ tinh tế và có chiều sâu. Nghiên cứu và viết lách: Bạn hợp với vai trò nhà nghiên cứu, biên kịch hoặc triết gia.',
                'Thách thức: Thách thức nằm ở việc vượt qua sự thận trọng quá mức để tin tưởng vào giá trị bản thân. Bạn giống như viên ngọc sợ trầy xước nên mãi nằm trong hộp. Tính kiên định và thích sự ổn định đôi khi làm chậm nhịp thích nghi, khiến bạn chậm thích nghi với thay đổi.',
                'chien_luoc' => [
                    'Hãy tìm kiếm sự công nhận, ánh đèn sân khấu để tỏa sáng.',
                    'Đừng mãi đứng sau hậu trường, hãy tập chia sẻ ý tưởng vì thế giới cần thấy giá trị của bạn.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có duyên ngầm cực lớn với tài lộc và được mệnh danh là những đại gia ẩn mình.',
                'Kho tàng ngầm: Bạn có khả năng tích lũy và giữ tiền thuộc hàng cao thủ. Bạn trân trọng giá trị đồng tiền và không bao giờ tiêu xài hoang phí theo cảm hứng.',
                'Đầu tư an toàn: Bạn thích sự chậm mà chắc. Tài sản của bạn thường đến từ lãi suất kép, tích trữ vàng bạc, đá quý hoặc bất động sản giá trị thực. Càng về hậu vận, kho tài sản của bạn càng khổng lồ.',
                'Rủi ro: Bạn có xu hướng tiết kiệm quá mức cần thiết đối với nhu cầu cá nhân. Bạn giữ tiền quá kỹ khiến dòng tiền không được lưu thông để sinh ra giá trị mới. Đôi khi vì không rõ ràng giấy tờ mà bạn vướng vào tranh chấp ngầm.',
                'dinh_huong' => [
                    'Chi tiêu cho bản thân và hào phóng hơn để khơi thông dòng chảy năng lượng thịnh vượng.',
                    'Minh bạch tuyệt đối trong giấy tờ, sổ sách.',
                    'Đầu tư thông minh để tiền đẻ ra tiền thay vì để tiền ngủ đông trong két sắt.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn là mẫu người ngoài lạnh trong nóng, chân thành nhưng khó nắm bắt.',
                'Tình yêu thầm lặng: Bạn không giỏi nói lời hoa mỹ. Bạn thể hiện tình yêu bằng hành động thiết thực: sửa đồ đạc, nấu ăn, lo toan kinh tế. Bạn cực kỳ chung thủy và đáng tin cậy.',
                'Thách thức: Nội tâm phức tạp và sự im lặng là con dao hai lưỡi. Bạn mong đối phương tự hiểu mình mà không cần nói ra, dẫn đến những cuộc chiến tranh lạnh. Sự nhạy cảm và mong muốn gắn kết tuyệt đối cũng khiến mối quan hệ trở nên ngột ngạt. Đối với nam giới, thường nể vợ, có xu hướng nghe theo vợ. Đối với phụ nữ, đảm đang, tháo vát nhưng hay lo âu, đôi khi cảm thấy chưa thực sự được thấu hiểu sâu sắc.',
                'chien_luoc' => [
                    'Hãy học cách chia sẻ cảm xúc, đừng bắt đối phương phải đoán.',
                    'Mang sự hài hước và ấm áp vào gia đình để làm tan chảy sự lạnh lẽo.',
                    'Tìm người bạn đời bao dung, hoạt bát để cân bằng lại sự trầm tính của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Cơ địa của bạn khá nhạy cảm với sự lạnh giá và độ ẩm của môi trường.',
                'Vấn đề thể chất: Bạn cần đặc biệt chú ý đến hệ tiêu hóa vì dạ dày lạnh, hệ hô hấp như phổi, phế quản và xương khớp, tê bì, nhức mỏi.',
                'Tâm bệnh: Tâm trạng bạn dễ bị chùng xuống nếu để những lo âu dồn nén quá lâu. cảm xúc vào trong.',
                'Định hướng' => [
                    'Sưởi ấm: Luôn giữ ấm cơ thể, ăn thực phẩm có tính nóng như gừng, tiêu.',
                    'Tắm nắng thường xuyên.'
                ],
                'Liệu pháp: Vận động để khí huyết lưu thông. Hãy tìm niềm vui và tiếng cười để xua tan sự u ám trong tâm hồn.'
            ],
            'phat_trien_ban_than' => [
                'Bạn là người học tập suốt đời với khao khát tìm hiểu tận cùng sự thật.',
                'Trí tuệ chiều sâu: Bạn không chấp nhận kiến thức hời hợt. Bạn muốn đào sâu vào bản chất vấn đề. Trực giác tốt giúp bạn cảm thụ nghệ thuật và cái đẹp một cách tinh tế.',
                'Thách thức: Xu hướng ưu tiên giải pháp an toàn đôi khi hạn chế sự bứt phá. Sự tự ti ngăn cản bạn vươn tới đỉnh cao.',
                'dinh_huong' => [
                    'Hãy cởi mở tư duy để đón nhận cái mới.',
                    'Phát triển kỹ năng mềm như giao tiếp, thuyết trình để bạn được nhiều người biết đến.',
                    'Nuôi dưỡng tâm hồn bằng nghệ thuật để giảm bớt sự khô khan, logic.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người kín đáo, chọn lọc trong các mối quan hệ nhưng khá chân thành.',
                'Chất lượng hơn số lượng: Bạn không thích chốn xô bồ. Bạn chỉ mở lòng với vài người tri kỷ thực sự hiểu mình. Trong mắt bạn bè, bạn là người kín tiếng và biết giữ bí mật tuyệt đối.',
                'Tự lực cánh sinh: Bạn ít khi dựa dẫm được vào người khác. Thành công chủ yếu đến từ nỗ lực tự thân bền bỉ.',
                'Rủi ro: Bạn là người sống tình nghĩa và khắc cốt ghi tâm mọi chuyện, khó tha thứ nếu bị phản bội. Điều này vô tình làm hẹp đi con đường kết giao của bạn.',
                'chien_luoc' => [
                    'Hãy học cách buông bỏ và tha thứ.',
                    'Đừng tự cô lập mình, hãy bước ra ngoài kết nối.',
                    'Sự chân thành mộc mạc của bạn chính là nam châm thu hút những quý nhân chất lượng nhất.'
                ]
            ]
        ],
        'tan_mui' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn khá sâu sắc và đầy nội lực: "Một kho báu, một viên ngọc quý đang nằm ẩn mình sâu trong lòng đất khô cằn, hay một bông hoa sen kiên cường nở giữa bùn lầy". Vẻ bề ngoài của bạn có thể khá giản dị, khiêm nhường, không quá hào nhoáng hay phô trương, nhưng bên trong lại ẩn chứa một sức mạnh ý chí đáng kinh ngạc và một trí tuệ thâm trầm. Bạn giống như một cuốn sách hay mà trang bìa không nói lên hết nội dung, càng đọc sâu càng thấy giá trị.',
                'La Bàn Thịnh Vượng sẽ giúp bạn tìm thấy chìa khóa để khai quật kho báu của chính mình.'
            ],
            'su_nghiep' => [
                'Bạn sở hữu tinh thần khởi nghiệp và làm chủ thực thụ, bạn chăm chỉ, thực tế và cực kỳ bền bỉ.',
                'Sức mạnh của ý chi kiên định: Bạn không phải là người bỏ cuộc khi thấy khó khăn. Ngược lại, áp lực càng lớn, bạn càng trở nên cứng cỏi. Bạn có khả năng âm thầm chịu đựng và giải quyết những vấn đề hóc búa mà người khác đã buông xuôi. Phong cách làm việc của bạn là chậm mà chắc, xây dựng mọi thứ trên nền tảng vững chãi.',
                'Tư duy thực tế và sắc sảo: Bạn không phải kẻ mơ mộng hão huyền. Mọi kế hoạch của bạn đều được tính toán dựa trên lợi ích thực tế và khả năng thực thi. Bạn có con mắt tinh tường để nhìn ra đâu là cơ hội thật sự và đâu là rủi ro tiềm ẩn.',
                'Lĩnh vực phù hợp: Nghệ thuật và sáng tạo: Đây là nơi thế giới nội tâm phong phú của bạn được bùng nổ. Viết lách, thiết kế, nhiếp ảnh hay thời trang là những mảnh đất màu mỡ để bạn thể hiện cái tôi độc đáo. Quản lý và chuyên môn: Sự tỉ mỉ và trách nhiệm giúp bạn tỏa sáng trong các ngành như luật, kế toán, quản lý dự án hoặc nghiên cứu khoa học. Xã hội và cộng đồng: Bạn có xu hướng quan tâm đến các vấn đề nhân sinh, phù hợp với tâm lý học, giáo dục hoặc các hoạt động chính trị, xã hội.',
                'Thách thức: Thách thức lớn nhất là vượt qua vùng an toàn của sự thận trọng. Bạn thường suy nghĩ quá nhiều trước một cơ hội, dẫn đến việc để lỡ mất thời cơ tốt. Đôi khi, bạn cũng trở nên bảo thủ, khó chấp nhận ý kiến mới và ưu tiên những giải pháp truyền thống và an toàn đã được kiểm chứng.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những công việc mang lại cảm hứng mạnh mẽ, khiến bạn sẵn sàng dấn thân và cống hiến hết mình.',
                    'Đừng chỉ làm việc một mình, hãy kết nối, giao lưu và mở rộng mạng lưới quan hệ để khơi thông dòng chảy sáng tạo.',
                    'Tin tưởng vào trực giác của mình. Đôi khi, cảm nhận đầu tiên của bạn chính xác hơn là những phân tích lo âu.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có duyên ngầm với tiền bạc và được mệnh danh là những đại gia ẩn mình.',
                'Khả năng tích sản: Bạn có năng khiếu đặc biệt trong việc tích lũy tài sản. Bạn trân trọng giá trị của đồng tiền và luôn muốn đảm bảo sự an toàn tài chính cho tương lai. Tiền của bạn thường đến từ sự nỗ lực làm việc và đầu tư dài hạn chứ không phải từ những trò may rủi.',
                'Đầu tư giá trị: Bạn phù hợp với những kênh đầu tư chắc chắn, đặc biệt là bất động sản hoặc những tài sản hữu hình có giá trị gia tăng theo thời gian. Càng về sau, khối tài sản của bạn càng lớn mạnh.',
                'Bí quyết thịnh vượng: Để dòng tiền chảy về mạnh mẽ hơn, bạn cần sự linh hoạt. Đừng để tiền nằm chết một chỗ quá lâu. Sự giao thương, buôn bán và luân chuyển dòng vốn sẽ mang lại lợi nhuận lớn cho bạn.',
                'Rủi ro: Dù tiết kiệm, nhưng đôi khi có xu hướng chi tiêu ngẫu hứng để nuông chiều bản thân, vung tiền cho những sở thích cá nhân xa xỉ để bù đắp cảm xúc. Bạn cũng cần đề phòng việc bị người quen lợi dụng lòng tin trong chuyện tiền nong.',
                'dinh_huong' => [
                    'Hãy giữ kỷ luật trong chi tiêu, lập kế hoạch ngân sách rõ ràng để tránh những phút giây mua sắm quá đà.',
                    'Tuyệt đối tránh xa những lời mời gọi làm giàu nhanh chóng, mạo hiểm.',
                    'Giữ sự minh bạch trong mọi giao dịch tài chính là cách tốt nhất để bảo vệ túi tiền của bạn.'
                ]
            ],
            'tinh_duyen' => [
                'Bề ngoài bạn có vẻ khô khan hoặc nghiêm túc, nhưng bên trong là một trái tim lãng mạn, nhạy cảm và khao khát yêu thương mãnh liệt.',
                'Sự tận tụy và bảo bọc: Khi yêu, bạn cực kỳ trung thành và có trách nhiệm. Bạn luôn muốn che chở, lo lắng cho người mình yêu từng chút một. Bạn hướng về gia đình và mong muốn xây dựng một tổ ấm bền vững.',
                'Sự thu hút ngầm: Bạn không cần phô trương, chính sự điềm đạm và bí ẩn của bạn lại tạo nên sức hút khó cưỡng đối với người khác phái.',
                'Thách thức: Bạn hay lo âu, suy diễn và tự làm khổ mình bằng những suy nghĩ tiêu cực. Tình yêu sâu sắc khiến bạn đôi khi nảy sinh tâm lý muốn giữ gìn và bảo vệ đối phương tuyệt đối. Khi có mâu thuẫn, bạn có xu hướng thu mình lại thay vì đối thoại trực tiếp, khiến khoảng cách giữa hai người ngày càng xa.',
                'chien_luoc' => [
                    'Kết hôn muộn sẽ giúp bạn có đủ sự chín chắn và bao dung để duy trì hạnh phúc.',
                    'Học cách chia sẻ cảm xúc thật. Đừng bắt đối phương phải tự đoán ý bạn.',
                    'Hãy tìm một người bạn đời vui vẻ, lạc quan để mang lại tiếng cười và sự nhẹ nhàng cho cuộc sống vốn nhiều lo toan của bạn.'
                ]
            ],
            'suc_khoe' => [
                'Sức khỏe của bạn cần sự quan tâm đặc biệt đến những vấn đề mãn tính và hệ tiêu hóa.',
                'Thể chất: Bạn có thể gặp các vấn đề về dạ dày, tiêu hóa kém hoặc đau nhức xương khớp, tay chân khi lớn tuổi. Cơ thể bạn cần được cung cấp đủ nước và độ ẩm để duy trì sự cân bằng.',
                'Tinh thần: Sự dao động cảm xúc là yếu tố then chốt cần được cân bằng. Bạn có thể rơi vào trạng thái căng thẳng, lo âu kéo dài hoặc trạng thái năng lượng trầm lắng kéo dài nếu không biết cách giải tỏa.',
                'Liệu pháp' => [
                    'Uống nhiều nước, đi bơi hoặc sống gần không gian thoáng đãng, có hồ nước để làm dịu tinh thần.',
                    'Duy trì chế độ ăn uống điều độ, đúng giờ để bảo vệ dạ dày.',
                    'Âm nhạc, khiêu vũ hoặc các hoạt động nghệ thuật là liều thuốc tinh thần tuyệt vời giúp bạn giải tỏa căng thẳng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn là người có trí tưởng tượng phong phú và tư duy độc đáo, thường đi trước thời đại.',
                'Học từ thực tế: Bạn không thích những lý thuyết sáo rỗng. Kiến thức của bạn được đúc kết từ sự quan sát và trải nghiệm thực tế. Bạn có hứng thú sâu sắc với những lĩnh vực tâm linh, triết học hay những quy luật vận hành của cuộc sống.',
                'Thách thức: Sự phân tán năng lượng là điểm yếu lớn. Bạn quan tâm đến quá nhiều thứ cùng lúc nên khó đạt được đỉnh cao ở một lĩnh vực cụ thể. Sự tự ti đôi khi khiến bạn giấu đi tài năng của mình.',
                'dinh_huong' => [
                    'Hãy rèn luyện sự tập trung. Chọn ra một mục tiêu quan trọng nhất và theo đuổi nó đến cùng.',
                    'Biến những ý tưởng trong đầu thành sản phẩm thực tế. Đừng chỉ để nó nằm trên giấy.',
                    'Tin tưởng vào bản thân và mạnh dạn bước ra ánh sáng để thể hiện tài năng.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là người bạn chân thành, đáng tin cậy và khá biết giữ bí mật.',
                'Chất lượng hơn số lượng: Bạn không thích những mối quan hệ xã giao hời hợt. Bạn chọn lọc bạn bè khá kỹ và chỉ thực sự mở lòng với những tri kỷ hiểu mình.',
                'Quý nhân: Bạn thường nhận được sự giúp đỡ từ gia đình hoặc những người lớn tuổi, trưởng thành.',
                'Rủi ro: Đôi khi bạn quá để tâm đến việc người khác nghĩ gì về mình, dẫn đến việc sống không thật với bản thân. Bạn cũng có thể đôi khi vô tình đặt tiêu chuẩn cá nhân lên người khác, lên bạn bè thân thiết.',
                'chien_luoc' => [
                    'Hãy giữ vững lập trường và giá trị của bản thân. Đừng để áp lực đám đông chi phối.',
                    'Chủ động hơn trong giao tiếp. Một chút cởi mở sẽ giúp bạn có thêm nhiều cơ hội và sự hỗ trợ quý giá.'
                ]
            ]
        ],
        'tan_dau' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn là đỉnh cao của sự hoàn hảo và sắc sảo: "Một viên kim cương đã được cắt gọt tỉ mỉ không tì vết, hay một thanh kiếm báu sắc lẹm vừa tuốt khỏi vỏ". Bạn sinh ra với khí chất của một ngôi sao: Lấp lánh, thu hút, lạnh lùng và vô cùng cứng rắn. Bạn không chấp nhận sự tầm thường trong cuộc sống, trong từ điển của bạn chỉ có hai từ "trở nên xuất sắc" hoặc "không có gì cả". Bạn luôn khao khát vị trí trung tâm và muốn mọi thứ xung quanh mình phải thật hoàn mỹ.',
                'La Bàn Thịnh Vượng sẽ giúp bạn mài giũa sự sắc sảo ấy để kiến tạo một cuộc đời kiệt tác.'
            ],
            'su_nghiep' => [
                'Bạn mang trong mình tư duy của người dẫn đầu và chủ nghĩa hoàn hảo cực đoan.',
                'Sự sắc bén và quyết đoán: Tư duy của bạn nhanh như cắt. Bạn nhìn thấu bản chất vấn đề chỉ trong nháy mắt và đưa ra giải pháp trực diện, không vòng vo. Bạn ghét sự lề mề, chậm chạp và thiếu hiệu quả. Trong công việc, lời nói của bạn có trọng lượng, nhưng cần khéo léo để tránh gây tổn thương vô ý nếu ai đó làm sai ý bạn.',
                'Tiêu chuẩn vàng: Bạn đặt ra những tiêu chuẩn cực kỳ khắt khe cho bản thân và người khác. Sản phẩm qua tay bạn phải đẹp nhất, tốt nhất. Chính sự khó tính này giúp bạn thành công rực rỡ trong những nghề đòi hỏi sự chính xác tuyệt đối hoặc gu thẩm mỹ đỉnh cao.',
                'Lĩnh vực phù hợp: Giải trí và nghệ thuật: Bạn sinh ra để tỏa sáng. Diễn viên, ca sĩ, người mẫu, hay những người định hình phong cách như stylist, giám đốc sáng tạo là sân chơi của bạn. Kinh doanh và quản lý: Tư duy thực tế và sắc sảo giúp bạn làm tốt vai trò CEO, quản lý cấp cao hoặc tự khởi nghiệp. Chuyên môn cao: Bác sĩ phẫu thuật, nha sĩ, luật sư, những nghề cần sự chính xác như dao mổ.',
                'Thách thức: Rào cản lớn nhất chính là sự cầu toàn thái quá. Tiêu chuẩn cao đôi khi khiến bạn trở nên khắt khe chi tiết đối với người khác, khiến đồng nghiệp cảm thấy áp lực và xa lánh. Khi công việc trở nên nhàm chán, thiếu thử thách, bạn có thể mất hứng và bỏ cuộc.',
                'chien_luoc' => [
                    'Hãy tìm kiếm những môi trường cho phép bạn được công nhận và tỏa sáng. Sự tán thưởng là nhiên liệu cho bạn.',
                    'Học cách mềm mỏng hơn trong giao tiếp, đừng dùng lời nói như lưỡi dao làm tổn thương người khác.',
                    'Hãy tập trung năng lượng vào một mục tiêu duy nhất. Đừng để sự đa tài khiến bạn cái gì cũng biết nhưng không chuyên cái gì.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có năng khiếu bẩm sinh trong việc kiếm tiền và thu hút sự thịnh vượng.',
                'Tư duy làm giàu: Bạn nhìn đâu cũng thấy cơ hội kinh doanh. Bạn không chỉ muốn đủ ăn đủ mặc, bạn muốn một cuộc sống đẳng cấp, sang trọng. Bạn biết cách thương mại hóa hình ảnh, tên tuổi và tài năng của mình để đổi lấy tiền bạc.',
                'Phong cách chi tiêu: Bạn là người hào phóng và khá biết cách hưởng thụ. Bạn không tiếc tiền cho hàng hiệu, xe sang, những bữa ăn đắt đỏ để nâng tầm giá trị bản thân. Với bạn, tiêu tiền cũng là một cách để khẳng định vị thế.',
                'Rủi ro: Kiếm được nhiều, nhu cầu chi tiêu cũng tương xứng với đẳng cấp. Việc quản lý dòng tiền hưởng thụ là thách thức lớn nhất khiến bạn khó giữ được tiền. Bạn cũng có thể bị mất tiền do quá tin tưởng bạn bè, đối tác hoặc vì sĩ diện mà cho vay mượn không đòi được.',
                'dinh_huong' => [
                    'Bắt buộc phải có kỷ luật tài chính. Hãy lập ngân sách riêng cho việc hưởng thụ và tuân thủ nó.',
                    'Chuyển tiền mặt thành tài sản tích lũy như nhà cửa, đất đai, vàng bạc ngay khi kiếm được để tránh việc tiêu xài hoang phí.',
                    'Minh bạch tuyệt đối trong chuyện tiền nong với bạn bè, đối tác. Đừng để tình cảm xen vào ví tiền.'
                ]
            ],
            'tinh_duyen' => [
                'Đường tình duyên của bạn rực rỡ nhưng cũng đầy chông gai vì cái tôi quá lớn.',
                'Sức hút và khí chất đặc biệt: Bạn đẹp, có gu ăn mặc, nói chuyện duyên dáng và luôn là tâm điểm của sự chú ý. Bạn có khá nhiều vệ tinh vây quanh và biết cách làm cho tình yêu trở nên thi vị, lãng mạn.',
                'Tiêu chuẩn cao về người bạn đời: Bạn tìm kiếm một người bạn đời hoàn hảo: Vừa phải đẹp, vừa thông minh, tài giỏi lại phải biết chiều chuộng bạn. Bạn khá khó chấp nhận những khuyết điểm nhỏ nhặt của đối phương.',
                'Thách thức: Trong hôn nhân, bạn có xu hướng muốn định hướng và dẫn dắt. Bạn hay bắt lỗi, so sánh và muốn người kia phải thay đổi theo ý mình. Bạn cũng khá khó mở lòng chia sẻ những yếu đuối bên trong, thường che giấu nỗi buồn sau vẻ ngoài lạnh lùng, mạnh mẽ. Tính đào hoa cũng khiến bạn có thể xao động trước những cám dỗ mới lạ.',
                'chien_luoc' => [
                    'Học cách chấp nhận sự không hoàn hảo. Nhìn vào điểm tốt của đối phương để trân trọng họ.',
                    'Hạ cái tôi xuống. Nhà là nơi để yêu thương, không phải toà án để phân xử đúng sai.',
                    'Hãy giữ gìn sự chung thủy và vạch rõ giới hạn với các mối quan hệ xã hội để bảo vệ hạnh phúc gia đình.'
                ]
            ],
            'suc_khoe' => [
                'Cơ thể bạn chịu ảnh hưởng lớn từ hệ thần kinh và các vấn đề liên quan đến hô hấp.',
                'Hệ hô hấp: Mũi, họng và phổi là những cơ quan nhạy cảm nhất của bạn. Bạn có thể bị viêm họng, xoang, dị ứng thời tiết hoặc ho kéo dài.',
                'Thần kinh: Áp lực phải hoàn hảo khiến bạn luôn trong trạng thái căng thẳng ngầm, có thể dẫn đến đau đầu, mất ngủ.',
                'Chấn thương: Năng lượng sắc bén trong người khiến bạn có thể gặp các tai nạn nhỏ, trầy xước chân tay hoặc chấn thương khi vận động.',
                'Liệu pháp' => [
                    'Tìm sự tĩnh lặng qua thiền định hoặc yoga để thư giãn hệ thần kinh sau những giờ tập trung cao độ.',
                    'Tập hít thở sâu, giữ ấm cổ họng và tránh xa khói bụi.',
                    'Uống nhiều nước và dành thời gian nghỉ ngơi thực sự, đừng làm việc đến kiệt sức.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, học nhanh và cực kỳ thực tế.',
                'Tư duy ứng dụng: Bạn không học để biết, bạn học để làm, để kiếm tiền và giải quyết vấn đề. Khả năng quan sát và phân tích của bạn khá sắc sảo.',
                'Thách thức: Sự hài lòng quá sớm với hiện tại có thể kìm hãm bước tiến xa hơn. Đôi khi bạn nghĩ mình đã biết hết và ngừng học hỏi. Khi thất bại, bạn có xu hướng đổ lỗi cho hoàn cảnh thay vì nhìn nhận lại bản thân. Sự thiếu tự tin sâu kín bên trong đôi khi khiến bạn tự phá hoại thành quả của mình.',
                'dinh_huong' => [
                    'Luôn giữ tâm thế cởi mở, sẵn sàng học hỏi từ bất kỳ ai.',
                    'Chọn một lĩnh vực mũi nhọn và đào sâu đến cùng để trở thành chuyên gia số một.',
                    'Lắng nghe trực giác nhưng đừng để sự kiêu ngạo che mờ lý trí.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao của các bữa tiệc, nhưng cũng có thể vướng vào thị phi.',
                'Mạng lưới chất lượng: Bạn thích kết giao với những người tài giỏi, thành đạt và có địa vị. Bạn thường được những người này giúp đỡ và mang lại cơ hội tốt.',
                'Rủi ro: Sự nổi bật và thẳng thắn của bạn có thể gây ra sự ghen ghét, đố kỵ từ người khác. Bạn hay giữ bí mật và không bộc lộ cảm xúc thật nên đôi khi tạo cảm giác xa cách hoặc quá lý trí.',
                'chien_luoc' => [
                    'Chất lượng hơn số lượng. Hãy chọn lọc bạn bè kỹ càng để tránh bị lợi dụng lòng tốt.',
                    'Hãy chân thành và giữ lời hứa. Đó là cách tốt nhất để xây dựng uy tín lâu dài.',
                    'Biết ơn những người đã giúp đỡ mình là chìa khóa để duy trì vận may.'
                ]
            ]
        ],
        'tan_ty' => [
            'tong_quan' => [
                'Hình ảnh đại diện cho bạn vô cùng lộng lẫy và đặc biệt: “Một viên ngọc thô đang được nung trong lò lửa để loại bỏ tạp chất, hay một món trang sức quý giá tỏa sáng dưới ánh đèn sân khấu”. Trong Bát Tự, lá số bạn là sự khắc chế hữu tình để tạo ra thành công. Bạn được thiên phú cho một khí chất quyến rũ tự nhiên đầy mê hoặc, song ẩn sâu bên trong vẻ ngoài lộng lẫy ấy là một nội tâm luôn sục sôi những áp lực tự thân, thôi thúc bạn không ngừng vươn tới sự hoàn hảo tuyệt đối.',
                'La Bàn Thịnh Vượng sẽ giúp bạn tôi luyện bản thân thành viên ngọc vô giá.'
            ],
            'su_nghiep' => [
                'Bạn sinh ra đã mang trong mình năng lực lãnh đạo. Bạn không cần cố gắng quá nhiều vẫn thu hút sự chú ý của đám đông.',
                'Tư duy đổi mới: Sở hữu tầm nhìn xa trông rộng, tâm trí bạn là một thư viện phong phú tràn ngập những ý tưởng nguyên bản độc đáo. Chính vì thế, bạn luôn khao khát phá vỡ những lối mòn tư duy cũ kỹ để kiến tạo nên những giá trị khác biệt. Sự sáng tạo và năng lượng dồi dào giúp bạn luôn tìm ra giải pháp bên ngoài chiếc hộp.',
                'Khao khát trung tâm: Bạn làm việc hiệu quả nhất khi được tự chủ, tự do quyết định. Bạn khao khát được công nhận và thích đứng ở vị trí trung tâm, nơi ánh đèn chiếu vào.',
                'Lĩnh vực phù hợp: Nghệ thuật và biểu diễn: Sự duyên dáng và linh hoạt giúp bạn tỏa sáng tuyệt đối trong sân khấu, thời trang, người mẫu hoặc thể thao như khiêu vũ, yoga. Nhân đạo và chữa lành: Bản năng thấu cảm dẫn lối bạn đến với y tế, tâm lý học, tư vấn. Kinh doanh và đánh giá: Khả năng định giá giá trị giúp bạn thành công trong bất động sản hoặc thương mại cao cấp.',
                'Thách thức: Sự nghi ngờ bản thân là rào cản lớn nhất. Bên ngoài tự tin nhưng bên trong bạn thường do dự. Bạn sở hữu quá nhiều đam mê, đôi khi dẫn đến việc phân tán sự tập trung thay vì kiên trì với một mục tiêu dài hạn. Tiêu chuẩn thẩm mỹ và chất lượng cao đôi khi khiến bạn trở nên khắt khe hơn mức cần thiết đối với cộng sự.',
                'chien_luoc' => [
                    'Cần nâng cao trí tuệ để làm mát cái đầu nóng, giúp bạn bớt căng thẳng và suy nghĩ sáng suốt hơn.',
                    'Củng cố sự quyết đoán bằng cách tập trung vào một mục tiêu duy nhất và cam kết đến cùng.',
                    'Xây dựng uy tín bằng việc hoàn thành những gì đã bắt đầu.'
                ]
            ],
            'tai_chinh' => [
                'Bạn có số hưởng thụ. Bạn yêu thích những điều tốt đẹp, tinh tế và xa xỉ.',
                'May mắn về tài lộc: Bạn có duyên kiếm tiền từ danh tiếng, hình ảnh cá nhân hoặc các mối quan hệ xã hội. Bạn thường xuyên có quý nhân giúp đỡ về tiền bạc.',
                'Tiềm năng thịnh vượng: Bạn nắm giữ bí quyết vận hành dòng tiền thông minh, biết cách khiến tài sản sinh sôi nảy nở thông qua các kênh đầu tư chiến lược hoặc những dự án kinh doanh mang đậm dấu ấn cá nhân.',
                'Rủi ro: Nhu cầu tận hưởng cuộc sống cao là một thách thức cần được quản trị khéo léo. Bạn có thể rơi vào cảnh vung tay quá trán để nuông chiều bản thân. Bạn cũng cần cảnh giác với những biến động tài chính bất ngờ, đừng quá chủ quan vào vận may.',
                'dinh_huong' => [
                    'Lập kế hoạch tài chính kỷ luật để kiểm soát ham muốn mua sắm bốc đồng.',
                    'Dùng sự tính toán kỹ lưỡng trước khi đầu tư, đừng chỉ nhìn vào bức tranh màu hồng.',
                    'Tham vấn chuyên gia để tránh rủi ro trong các khoản đầu tư lớn.'
                ]
            ],
            'tinh_duyen' => [
                'Trong tình cảm, bạn sở hữu sức hút khó cưỡng và một vẻ đẹp chiều sâu đầy bí ẩn, luôn có nhiều vệ tinh vây quanh.',
                'Cảm xúc thăng hoa: Bạn nhạy cảm, chu đáo và biết quan tâm. Bạn tìm kiếm một mối quan hệ kích thích tinh thần, một người bạn đời thông minh và tham vọng để cùng phát triển.',
                'Thách thức: Tình yêu của bạn giống như tàu lượn siêu tốc, lúc thăng hoa, lúc trầm lắng. Cảm giác thiếu an toàn đôi khi khiến bạn muốn kiểm soát và gắn kết chặt chẽ với đối phương. Bạn mong cầu sự hoàn mỹ trong tình yêu, điều này đôi khi khiến hành trình tìm kiếm tri kỷ trở nên thử thách hơn ý để ổn định.',
                'dinh_huong' => [
                    'Học cách tin tưởng và cho đối phương không gian riêng.',
                    'Giao tiếp bằng sự hòa ái thay vì chỉ trích hay áp đặt.',
                    'Hãy ưu tiên sự bình yên trong gia đình hơn là việc thắng thua trong tranh cãi.'
                ]
            ],
            'suc_khoe' => [
                'Hệ thần kinh của bạn khá nhạy cảm và dễ bị quá tải trước những áp lực vô hình.',
                'Vấn đề thần kinh: Sự thiếu quyết đoán và ôm đồm khiến bạn có thể bị stress, mất ngủ hoặc suy nhược thần kinh.',
                'Tim mạch và máu huyết: Hỏa khắc Kim có thể gây ra các vấn đề về hệ tuần hoàn, tim mạch hoặc viêm nhiễm.',
                'Liệu pháp' => [
                    'Làm dịu tâm trí bằng thiền định, yoga hoặc bơi lội.',
                    'Học cách nói không và quản lý ưu tiên để tránh kiệt sức.',
                    'Dành thời gian nghỉ ngơi thực sự, tách rời hoàn toàn khỏi công việc là nghi thức bắt buộc để tái tạo năng lượng.'
                ]
            ],
            'phat_trien_ban_than' => [
                'Bạn thông minh, lanh lợi và luôn tìm cách nâng cấp phiên bản bản thân.',
                'Tư duy mở: Bạn thích học hỏi, chia sẻ kiến thức và luôn tìm kiếm giải pháp mới mẻ. Bạn có hứng thú mạnh mẽ với việc tự hoàn thiện.',
                'Thách thức: Sự thiếu tập trung và phân tán năng lượng. Bạn có quá nhiều ý tưởng nhưng thiếu sự cam kết. Tâm trí bất ổn khiến bạn khó đưa ra quyết định sáng suốt.',
                'dinh_huong' => [
                    'Kênh hóa sự sáng tạo vào những mục tiêu thực tế.',
                    'Thực hành chánh niệm để rèn luyện sự tĩnh tâm.',
                    'Hãy đi du lịch, trải nghiệm văn hóa để mở rộng tầm nhìn và làm giàu vốn sống.'
                ]
            ],
            'ket_noi_xa_hoi' => [
                'Bạn là ngôi sao của đám đông, đi đến đâu cũng mang lại tiếng cười và sự thú vị.',
                'Sức hút tự nhiên: Bạn quyến rũ, hài hước và có khả năng kết nối với mọi tầng lớp xã hội. Mọi người thích ở bên cạnh bạn vì sự thoải mái bạn mang lại.',
                'Quý nhân: Bạn cực kỳ may mắn khi luôn có sự hỗ trợ từ bên ngoài.',
                'Rủi ro: Đôi khi, việc đặt quá nhiều tâm tư vào sự đánh giá hay chấp thuận của người khác vô tình trở thành gánh nặng vô hình, rút cạn năng lượng tinh thần và khiến bạn cảm thấy mệt mỏi. Đôi khi vì khó chối từ mà bạn vô tình nhận quá nhiều trách nhiệm, dẫn đến việc quá tải, làm mất đi lòng tin.',
                'chien_luoc' => [
                    'Hãy trân trọng sự giúp đỡ và luôn thể hiện lòng biết ơn.',
                    'Giữ chữ tín là cách tốt nhất để duy trì các mối quan hệ chất lượng.',
                    'Xây dựng sự kết nối dựa trên sự chân thành và minh bạch.'
                ]
            ]
        ],
    ];

    // Bảng Thập Thần (so sánh Nhật Chủ với Can khác)
    protected static function relation(string $dayStem, string $otherStem): string
    {
        $dayElement  = self::$stemElements[$dayStem]  ?? null;
        $otherElement = self::$stemElements[$otherStem] ?? null;
        if (!$dayElement || !$otherElement) return 'Unknown';

        $dayYin  = self::$stemYinYang[$dayStem];
        $otherYin = self::$stemYinYang[$otherStem];

        // mapping ngũ hành
        $genCycle = [ // sinh
            'Mộc' => 'Hỏa',
            'Hỏa' => 'Thổ',
            'Thổ' => 'Kim',
            'Kim' => 'Thủy',
            'Thủy' => 'Mộc'
        ];
        $ctrlCycle = [ // khắc
            'Mộc' => 'Thổ',
            'Thổ' => 'Thủy',
            'Thủy' => 'Hỏa',
            'Hỏa' => 'Kim',
            'Kim' => 'Mộc'
        ];

        if ($dayElement === $otherElement) {
            return $dayYin === $otherYin ? 'Tỷ Kiên' : 'Kiếp Tài';
        }

        // Nhật Chủ sinh ra đối phương
        if ($genCycle[$dayElement] === $otherElement) {
            return $dayYin === $otherYin ? 'Thực Thần' : 'Thương Quan';
        }

        // Đối phương sinh Nhật Chủ
        if ($genCycle[$otherElement] === $dayElement) {
            return $dayYin === $otherYin ? 'Thiên Ấn' : 'Chính Ấn';
        }

        // Nhật Chủ khắc đối phương
        if ($ctrlCycle[$dayElement] === $otherElement) {
            return $dayYin === $otherYin ? 'Thiên Tài' : 'Chính Tài';
        }

        // Đối phương khắc Nhật Chủ
        if ($ctrlCycle[$otherElement] === $dayElement) {
            return $dayYin === $otherYin ? 'Thất Sát' : 'Chính Quan';
        }

        return 'Unknown';
    }

    protected static $DaiVan = null;

    protected static array $yangStems = ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm'];


    protected static function truongSinh(string $dayStem, ?string $branch): ?string
    {
        if (!$branch || !isset(self::$truongSinhCycle[$dayStem])) {
            return 'Không xác định';
        }

        return self::$truongSinhCycle[$dayStem][$branch] ?? 'Không xác định';
    }

    protected static function napAm(string $stem, string $branch): ?string
    {
        $key = $stem . $branch;
        return self::$napAm[$key] ?? null;
    }

    protected static function tinhCanChi($year, $month, $day)
    {
        // Mảng thiên can
        $thien_can = array('Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý');

        // Mảng địa chi
        $dia_chi = array('Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi');

        // Tính số ngày Julius
        $a = floor((14 - $month) / 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;

        $julian_day = $day + floor((153 * $m + 2) / 5) + 365 * $y + floor($y / 4) - floor($y / 100) + floor($y / 400) - 32045;

        // Công thức tính can chi ngày
        $can_index = ($julian_day + 9) % 10;
        $chi_index = ($julian_day + 1) % 12;

        return array(
            'thien_can' => $thien_can[$can_index],
            'dia_chi' => $dia_chi[$chi_index],
            'can_chi' => $thien_can[$can_index] . ' ' . $dia_chi[$chi_index],
            'can_index' => $can_index,
            'chi_index' => $chi_index
        );
    }

    protected static $photinh = [];
    protected static $battu = [];
    protected static $cantang = [];

    // Giải pháp cân bằng cho từng ngũ hành
    protected static $giaiphapcanbang = [
        'kim' => [
            'ngu_hanh' => 'Kim',
            'giai_phap' => [
                'hanh_dong' => [
                    'Tối giản hóa – dọn dẹp không gian & cuộc sống',
                    'Chủ động loại bỏ những đồ vật không còn dùng, sắp xếp lại bàn làm việc, tủ quần áo, điện thoại, máy tính… để mọi thứ gọn gàng, thoáng và sạch. Tập thói quen chỉ giữ lại những gì thật sự cần thiết, giúp đầu óc nhẹ hơn, suy nghĩ rõ hơn.',
                    'Mỗi ngày, hãy viết ra danh sách các việc cần làm (to-do list), sắp xếp thứ tự ưu tiên và cam kết hoàn thành. Kỷ luật không cần quá nhiều, chỉ cần đều – mỗi ngày một chút – nhưng phải rõ ràng, có theo dõi và đánh dấu hoàn thành.',
                    'Rạch ròi ranh giới cá nhân',
                    'Học cách nói "không" với những việc, những mối quan hệ, những yêu cầu không cần thiết hoặc vượt quá sức mình. Đặt giới hạn rõ ràng về thời gian, năng lượng, trách nhiệm, tránh để bản thân bị kéo vào drama, chuyện không liên quan.',
                    'Giao tiếp ngắn gọn – rõ ràng – đúng trọng tâm.',
                    'Khi nói hoặc viết, ưu tiên sự súc tích: đi thẳng vào ý chính, tránh vòng vo. Trước khi phát biểu, có thể dừng lại 1–2 giây để sắp xếp ý trong đầu, sau đó diễn đạt sao cho người nghe hiểu được "mấu chốt" ngay lập tức.',
                    'Nâng chuẩn bản thân (hình ảnh – kỹ năng – tác phong)',
                    'Chăm chút ngoại hình sạch sẽ, chỉnh chu, hiện đại; đồng thời liên tục cải thiện chuyên môn, kỹ năng giao tiếp, quản lý thời gian… Mỗi năm chọn 1–2 kỹ năng để nâng cấp, coi đó là cách "mài sắc" năng lượng Kim bên trong.'
                ],
                'tu_duy' => [
                    'Tư duy logic – rõ ràng – có hệ thống',
                    'Khi suy nghĩ hoặc giải quyết vấn đề, luôn hỏi: "Bản chất của việc này là gì? Bước 1, 2, 3 cần làm là gì?". Tránh để cảm xúc chi phối hoàn toàn; thay vào đó, dùng lý trí để phân tích, sắp xếp thông tin một cách mạch lạc.',
                    'Ưu tiên chất lượng hơn số lượng',
                    'Thay vì làm thật nhiều thứ cùng lúc, hãy chọn ít việc nhưng làm cho chỉnh chu, sâu và tới nơi tới chốn. Tư duy Kim hướng tới sự tinh gọn và hiệu quả, không chạy theo bề nổi hay số lượng.',
                    'Quan sát chi tiết – để ý những điểm nhỏ',
                    'Luyện thói quen nhìn vào các chi tiết mà người khác thường bỏ qua: cách trình bày, lỗi nhỏ trong tài liệu, thái độ và phản ứng tinh tế của người đối diện. Năng lực "nhìn kỹ" giúp đưa ra quyết định chuẩn hơn và nâng cấp chất lượng mọi thứ bạn làm.'
                ],
                'mau_sac' => ['Trắng', 'Bạc', 'Xám', 'Metallic'],
                'da_phong_thuy' => ['Bạch ngọc trắng', 'Clear Quartz', 'Selenite', 'White Agate']
            ]
        ],
        'moc' => [
            'ngu_hanh' => 'Mộc',
            'giai_phap' => [
                'hanh_dong' => [
                    'Mở rộng tri thức thông qua việc học các khóa mới và rèn luyện kỹ năng mới.',
                    'Việc liên tục tiếp nhận kiến thức giúp nuôi dưỡng sự phát triển nội tại, mở rộng tư duy và tạo nền tảng cho những cơ hội mới. Mỗi kỹ năng mới được học là một "mầm xanh" mới được gieo, giúp tư duy linh hoạt hơn và khả năng thích nghi mạnh hơn.',
                    'Thường xuyên tiếp xúc với thiên nhiên, đặc biệt là những nơi có nhiều cây xanh như công viên hoặc khu vực thoáng đãng.',
                    'Môi trường tự nhiên cung cấp nguồn năng lượng Mộc rất mạnh, hỗ trợ quá trình tái tạo tinh thần, làm dịu cảm xúc và giúp tâm trí trở nên sáng suốt, cân bằng hơn.',
                    'Luyện tập các bộ môn mang tính linh hoạt và mềm dẻo như yoga, pilates hoặc giãn cơ.',
                    'Những hình thức vận động này giúp cơ thể trở nên uyển chuyển, giải phóng sức căng, đồng thời khơi thông các dòng năng lượng đang bị tắc nghẽn. Đây là những hoạt động lý tưởng để tăng trưởng sự mềm mại – dẻo dai – và tính phát triển đặc trưng của hành Mộc.',
                    'Khởi động những dự án mới nhằm kích hoạt năng lượng của sự bắt đầu.',
                    'Mộc gắn liền với quá trình sinh trưởng, vì vậy bất kỳ hoạt động mới nào, dù nhỏ hay lớn, đều góp phần tạo động lực và thúc đẩy tinh thần tiến lên. Việc chủ động tạo ra "điểm khởi đầu mới" giúp luồng năng lượng được lưu thông mạnh mẽ hơn.',
                    'Xây dựng kế hoạch dài hạn và viết rõ mục tiêu.',
                    'Việc định hình đường đi cho tương lai giúp duy trì sự phát triển liên tục. Khi mục tiêu được ghi ra cụ thể, tiến trình trở nên rõ ràng, nhất quán và dễ dàng theo dõi, từ đó tạo điều kiện cho năng lượng Mộc vươn lên ổn định và bền vững.',
                ],
                'tu_duy' => [
                    'Duy trì tư duy phát triển.',
                    'Tư duy này giúp nhìn mọi trải nghiệm như một cơ hội để học hỏi và tiến bộ. Khi tin rằng bản thân có khả năng thay đổi và phát triển, sự tự tin tăng lên và không gian cho các khả năng mới được mở rộng.',
                    'Giữ tinh thần cởi mở, không tự giới hạn bản thân trong khuôn khổ cũ.',
                    'Mộc phát triển mạnh trong môi trường mở, vì vậy việc sẵn sàng đón nhận ý tưởng mới, thử nghiệm điều mới lạ hoặc bước ra khỏi vùng an toàn sẽ giúp năng lượng được nuôi dưỡng và mở rộng.',
                    'Tập trung vào giải pháp, tránh trạng thái đứng yên hoặc trì trệ.',
                    'Thay vì dừng lại quá lâu ở vấn đề, hướng tư duy vào việc tìm giải pháp giúp duy trì sự chuyển động — yếu tố đặc trưng của hành Mộc. Chỉ cần một hành động nhỏ hướng về phía trước cũng đủ để tạo nên sự thay đổi tích cực.'
                ],
                'mau_sac' => ['Xanh lá', 'Xanh rêu', 'Xanh ngọc', 'Mint'],
                'da_phong_thuy' => ['Cẩm thạch xanh', 'Green Aventurine', 'Ngọc bích (Jade)', 'Moss Agate']
            ]
        ],
        'thuy' => [
            'ngu_hanh' => 'Thủy',
            'giai_phap' => [
                'hanh_dong' => [
                    'Thực hành thiền, điều hòa hơi thở và viết nhật ký để tạo trạng thái tĩnh tại và sâu lắng.',
                    'Những hoạt động này giúp làm chậm lại nhịp sống, giảm căng thẳng và đưa tâm trí về trạng thái cân bằng. Thiền và hơi thở tạo độ lắng cho bên trong, còn việc viết lại cảm xúc hoặc suy nghĩ giúp dòng chảy tinh thần trở nên mạch lạc, nhẹ nhàng và minh bạch hơn.',
                    'Ưu tiên các hoạt động nghỉ phục hồi và đảm bảo giấc ngủ đầy đủ.',
                    'Nước cần sự yên tĩnh để lắng đọng và trong vắt. Tương tự, bạn cần thời gian nghỉ ngơi chất lượng để tinh thần được hồi phục, não bộ xử lý thông tin tốt hơn và cảm xúc không bị đè nén.',
                    'Tiếp xúc với nước: tắm biển, ngâm chân, uống đủ nước, nghe nhạc thiền hoặc âm thanh nước chảy.',
                    'Nước là nguồn nuôi dưỡng trực tiếp cho năng lượng Thủy. Khi tiếp xúc với nước – dù qua cảm giác, âm thanh hay hành động – cơ thể và tâm trí bạn được làm mát, thư giãn và tái kết nối với nguồn năng lượng gốc.',
                    'Đọc sách, nghiên cứu và học hỏi sâu – đặc biệt là những chủ đề triết học, tâm lý, tâm linh.',
                    'Thủy gắn với trí tuệ sâu xa và khả năng thấu hiểu bản chất. Việc học hỏi không chỉ mở rộng kiến thức mà còn nuôi dưỡng sự minh triết – một đặc tính nền tảng của hành Thủy.',
                    'Duy trì tính linh hoạt và khả năng thích nghi trong cuộc sống.',
                    'Nước không có hình dạng cố định – nó thích nghi với môi trường. Thực hành sự linh hoạt trong tư duy và hành động giúp bạn không bị vướng mắc vào khuôn mẫu, dễ dàng vượt qua thay đổi và tìm ra con đường phù hợp nhất.'
                ],
                'tu_duy' => [
                    'Chấp nhận dòng chảy của cuộc sống.',
                    'Thay vì cố gắng kiểm soát mọi thứ, hãy học cách buông bỏ và tin tưởng vào quá trình tự nhiên. Nước không cưỡng lại – nó chảy theo dòng, vượt chướng ngại mà không gãy. Tư duy này giúp giảm căng thẳng và tạo sự hài hòa nội tâm.',
                    'Nuôi dưỡng trực giác và khả năng lắng nghe bên trong.',
                    'Thủy kết nối sâu với tiềm thức và trực giác. Thay vì chỉ dựa vào lý trí, hãy lắng nghe "tiếng nói bên trong" – cảm nhận đầu tiên, giấc mơ, tín hiệu cơ thể – để đưa ra quyết định chính xác hơn.',
                    'Tư duy dài hạn và chiến lược.',
                    'Nước di chuyển chậm nhưng bền bỉ và mạnh mẽ. Thay vì hành động vội vàng, hãy suy nghĩ xa, lên kế hoạch kỹ lưỡng và kiên nhẫn đợi đúng thời điểm để hành động.'
                ],
                'mau_sac' => ['Xanh dương', 'Đen', 'Navy', 'Xanh đậm'],
                'da_phong_thuy' => ['Lapis Lazuli', 'Aquamarine', 'Sapphire xanh', 'Black Tourmaline']
            ]
        ],
        'hoa' => [
            'ngu_hanh' => 'Hỏa',
            'giai_phap' => [
                'hanh_dong' => [
                    'Tăng cường hoạt động thể chất đầy năng lượng như chạy bộ, nhảy, kickboxing hoặc các bộ môn vận động mạnh.',
                    'Hỏa cần được đốt cháy và giải phóng. Các hoạt động năng động giúp kích hoạt sức sống, tăng tuần hoàn máu, đốt bỏ năng lượng trì trệ và mang lại cảm giác sảng khoái, tươi mới.',
                    'Tham gia các hoạt động xã hội, giao lưu và kết nối với người khác.',
                    'Hỏa rực rỡ nhất khi ở giữa đám đông, khi được chia sẻ và lan tỏa. Việc giao tiếp, tham gia sự kiện, nói chuyện với bạn bè giúp nuôi dưỡng năng lượng Hỏa, tránh bị tắt lịm vì cô đơn hay cô lập.',
                    'Thắp nến, sử dụng ánh sáng ấm, hoặc tiếp xúc với ánh mặt trời vào buổi sáng.',
                    'Ánh sáng và nhiệt là biểu tượng trực tiếp của Hỏa. Việc tiếp xúc với chúng giúp tái nạp năng lượng, nâng cao tinh thần và củng cố sức sống bên trong.',
                    'Theo đuổi đam mê và những điều làm bạn cảm thấy hứng khởi.',
                    'Hỏa cần động lực để cháy. Nếu bạn làm những việc không có "lửa", năng lượng sẽ tắt dần. Hãy dành thời gian cho sở thích, dự án hoặc mục tiêu khiến bạn cảm thấy sống động và có ý nghĩa.',
                    'Thể hiện cảm xúc một cách chân thật – đừng kìm nén quá lâu.',
                    'Hỏa không thể tồn tại khi bị dồn nén. Hãy học cách bộc lộ cảm xúc (giận dữ, vui mừng, hào hứng) một cách lành mạnh để duy trì sự cân bằng và tránh bùng phát tiêu cực.'
                ],
                'tu_duy' => [
                    'Sống với đam mê và sự nhiệt huyết.',
                    'Hỏa cháy mạnh nhất khi có mục đích. Hãy tìm ra những gì thực sự làm bạn phấn khích và hướng năng lượng vào đó. Đừng sống chiếu lệ – sống với lửa.',
                    'Hành động nhanh, quyết đoán và dám đương đầu.',
                    'Hỏa không chần chừ. Hãy rèn luyện khả năng đưa ra quyết định nhanh chóng, hành động ngay khi cơ hội xuất hiện và không ngại đối mặt với thử thách.',
                    'Truyền cảm hứng và dẫn dắt người khác.',
                    'Hỏa có khả năng thắp sáng và truyền lửa. Hãy tận dụng năng lượng này để trở thành nguồn động lực, người dẫn đường hoặc người truyền cảm hứng cho cộng đồng.'
                ],
                'mau_sac' => ['Đỏ', 'Cam', 'Hồng', 'Tím'],
                'da_phong_thuy' => ['Mã não đỏ', 'Ruby', 'Garnet', 'Carnelian']
            ]
        ],
        'tho' => [
            'ngu_hanh' => 'Thổ',
            'giai_phap' => [
                'hanh_dong' => [
                    'Tạo thói quen ổn định và lịch trình rõ ràng trong ngày.',
                    'Thổ phát triển mạnh nhất khi có nền tảng và trật tự. Việc xây dựng thói quen hàng ngày – từ giờ thức dậy, ăn uống, làm việc đến giờ nghỉ ngơi – giúp cơ thể và tâm trí có điểm tựa vững chắc, giảm căng thẳng và tăng hiệu suất.',
                    'Tham gia các hoạt động gắn liền với đất đai như làm vườn, trồng cây, đi bộ chân đất trên cỏ.',
                    'Thổ là đất mẹ. Khi tiếp xúc trực tiếp với đất, bạn kết nối với nguồn năng lượng gốc rễ, giúp cảm giác an toàn, bình yên và có gốc rễ trở lại.',
                    'Nuôi dưỡng bản thân và người khác thông qua việc nấu ăn, chăm sóc sức khỏe hoặc xây dựng môi trường sống ấm cúng.',
                    'Thổ mang năng lượng nuôi dưỡng. Việc chăm sóc bản thân – ăn uống lành mạnh, tạo không gian sống thoải mái – giúp củng cố năng lượng Thổ và tạo cảm giác "về nhà".',
                    'Thực hành sự kiên nhẫn và chấp nhận quá trình phát triển chậm nhưng chắc.',
                    'Thổ không vội vàng – nó tích lũy từ từ. Thay vì đòi hỏi kết quả tức thì, hãy học cách kiên nhẫn, tin tưởng vào quá trình tích lũy và chấp nhận rằng mọi thứ tốt đẹp đều cần thời gian.',
                    'Xây dựng nền tảng tài chính và vật chất ổn định.',
                    'Thổ gắn với sự an toàn vật chất. Hãy tập trung vào việc tạo dựng nguồn thu nhập ổn định, tiết kiệm và đầu tư khôn ngoan để xây dựng nền móng vững chắc cho tương lai.'
                ],
                'tu_duy' => [
                    'Tư duy thực tế, có căn cứ và dựa trên kinh nghiệm.',
                    'Thổ không bay bổng – nó đứng vững trên mặt đất. Hãy rèn luyện khả năng đánh giá tình huống dựa trên thực tế, số liệu cụ thể và kinh nghiệm đã qua, thay vì dựa vào cảm tính hay hy vọng mơ hồ.',
                    'Tập trung vào việc xây dựng và duy trì – không phá vỡ hay thay đổi liên tục.',
                    'Thổ là năng lượng của sự bền vững. Thay vì liên tục thử nghiệm điều mới, hãy tập trung xây dựng những gì đã có, nuôi dưỡng và phát triển từ từ để tạo ra kết quả lâu dài.',
                    'Chấp nhận trách nhiệm và cam kết.',
                    'Thổ đại diện cho sự tin cậy và ổn định. Hãy rèn luyện khả năng cam kết với một người, một công việc, một dự án và chịu trách nhiệm đến cùng – đó là cách để tạo nền tảng vững chắc cho cuộc sống.'
                ],
                'mau_sac' => ['Vàng', 'Nâu', 'Be', 'Cam đất'],
                'da_phong_thuy' => ['Hổ phách', 'Citrine', 'Yellow Jasper', 'Tiger Eye']
            ]
        ]
    ];

    // Điểm số ngũ hành động theo bảng tính
    protected static $nguHanhDongConfig = [
        'stems' => 10, // Thiên can đếm 10 điểm
        'branches' => [
            'Tý' => [
                'normal' => ['Thủy' => 20],
                'month'  => ['Thủy' => 30], // Tháng Tý vượng hơn
            ],
            'Sửu' => [
                'normal' => ['Thổ' => 15, 'Kim' => 10, 'Thủy' => 5],
                'month'  => ['Thổ' => 20, 'Kim' => 15, 'Thủy' => 10],
            ],
            'Dần' => [
                'normal' => ['Mộc' => 15, 'Hỏa' => 10, 'Thổ' => 5],
                'month'  => ['Mộc' => 25, 'Hỏa' => 15, 'Thổ' => 10],
            ],
            'Mão' => [
                'normal' => ['Mộc' => 20],
                'month'  => ['Mộc' => 30],
            ],
            'Thìn' => [
                'normal' => ['Thổ' => 15, 'Mộc' => 15, 'Thủy' => 15],
                'month'  => ['Thổ' => 20, 'Mộc' => 20, 'Thủy' => 20],
            ],
            'Tỵ' => [
                'normal' => ['Hỏa' => 15, 'Kim' => 10, 'Thổ' => 5],
                'month'  => ['Hỏa' => 25, 'Kim' => 15, 'Thổ' => 10],
            ],
            'Ngọ' => [
                'normal' => ['Hỏa' => 20, 'Thổ' => 5],
                'month'  => ['Hỏa' => 30, 'Thổ' => 10],
            ],
            'Mùi' => [
                'normal' => ['Thổ' => 15, 'Hỏa' => 15, 'Mộc' => 15],
                'month'  => ['Thổ' => 20, 'Hỏa' => 20, 'Mộc' => 20],
            ],
            'Thân' => [
                'normal' => ['Kim' => 15, 'Thủy' => 10, 'Thổ' => 5],
                'month'  => ['Kim' => 25, 'Thủy' => 15, 'Thổ' => 10],
            ],
            'Dậu' => [
                'normal' => ['Kim' => 20],
                'month'  => ['Kim' => 30],
            ],
            'Tuất' => [
                'normal' => ['Thổ' => 15, 'Kim' => 15, 'Hỏa' => 15],
                'month'  => ['Thổ' => 20, 'Kim' => 20, 'Hỏa' => 20],
            ],
            'Hợi' => [
                'normal' => ['Thủy' => 15, 'Mộc' => 10],
                'month'  => ['Thủy' => 25, 'Mộc' => 15],
            ],
        ]
    ];

    protected static $diemiachi = [
        'Dần' => [
            'hour' => [
                'Mộc' => 15,
                'Hỏa' => 10,
                'Thổ' => 5,
            ],
            'day' => [
                'Mộc' => 20,
                'Hỏa' => 15,
                'Thổ' => 10,
            ],
            'month' => [
                'Mộc' => 40,
                'Hỏa' => 30,
                'Thổ' => 20,
            ],
            'year' => [
                'Mộc' => 20,
                'Hỏa' => 15,
                'Thổ' => 10,
            ],
        ],
        'Mão' => [
            'hour' => [
                'Mộc' => 20,
            ],
            'day' => [
                'Mộc' => 25,
            ],
            'month' => [
                'Mộc' => 50,
            ],
            'year' => [
                'Mộc' => 25,
            ],
        ],
        'Thìn' => [
            'hour' => [
                'Thổ' => 15,
                'Mộc' => 15,
                'Thủy' => 15,
            ],
            'day' => [
                'Thổ' => 20,
                'Mộc' => 20,
                'Thủy' => 20,
            ],
            'month' => [
                'Thổ' => 30,
                'Mộc' => 30,
                'Thủy' => 30,
            ],
            'year' => [
                'Thổ' => 20,
                'Mộc' => 20,
                'Thủy' => 20,
            ],
        ],
        'Tỵ' => [
            'hour' => [
                'Hỏa' => 15,
                'Kim' => 10,
                'Thổ' => 5,
            ],
            'day' => [
                'Hỏa' => 20,
                'Kim' => 15,
                'Thổ' => 10,
            ],
            'month' => [
                'Hỏa' => 40,
                'Kim' => 30,
                'Thổ' => 20,
            ],
            'year' => [
                'Hỏa' => 20,
                'Kim' => 15,
                'Thổ' => 10,
            ],
        ],
        'Ngọ' => [
            'hour' => [
                'Hỏa' => 20,
                'Thổ' => 5,
            ],
            'day' => [
                'Hỏa' => 25,
                'Thổ' => 10,
            ],
            'month' => [
                'Hỏa' => 50,
                'Thổ' => 20,
            ],
            'year' => [
                'Hỏa' => 25,
                'Thổ' => 10,
            ],
        ],
        'Mùi' => [
            'hour' => [
                'Thổ' => 15,
                'Hỏa' => 15,
                'Mộc' => 15
            ],
            'day' => [
                'Thổ' => 20,
                'Hỏa' => 20,
                'Mộc' => 20
            ],
            'month' => [
                'Thổ' => 30,
                'Hỏa' => 30,
                'Mộc' => 30
            ],
            'year' => [
                'Thổ' => 20,
                'Hỏa' => 20,
                'Mộc' => 20
            ],
        ],
        'Thân' => [
            'hour' => [
                'Kim' => 15,
                'Thủy' => 10,
                'Thổ' => 5
            ],
            'day' => [
                'Kim' => 20,
                'Thủy' => 15,
                'Thổ' => 10
            ],
            'month' => [
                'Kim' => 40,
                'Thủy' => 30,
                'Thổ' => 20
            ],
            'year' => [
                'Kim' => 20,
                'Thủy' => 15,
                'Thổ' => 10
            ],
        ],
        'Dậu' => [
            'hour' => [
                'Kim' => 20,
            ],
            'day' => [
                'Kim' => 25,
            ],
            'month' => [
                'Kim' => 50,
            ],
            'year' => [
                'Kim' => 25,
            ],
        ],
        'Tuất' => [
            'hour' => [
                'Thổ' => 15,
                'Kim' => 15,
                'Hỏa' => 15,
            ],
            'day' => [
                'Thổ' => 20,
                'Kim' => 20,
                'Hỏa' => 20,
            ],
            'month' => [
                'Thổ' => 30,
                'Kim' => 30,
                'Hỏa' => 30,
            ],
            'year' => [
                'Thổ' => 20,
                'Kim' => 20,
                'Hỏa' => 20,
            ],
        ],
        'Hợi' => [
            'hour' => [
                'Thủy' => 15,
                'Mộc' => 10,
            ],
            'day' => [
                'Thủy' => 20,
                'Mộc' => 15,
            ],
            'month' => [
                'Thủy' => 40,
                'Mộc' => 30,
            ],
            'year' => [
                'Thủy' => 20,
                'Mộc' => 15,
            ],
        ],
        'Tý' => [
            'hour' => [
                'Thủy' => 20,
            ],
            'day' => [
                'Thủy' => 25,
            ],
            'month' => [
                'Thủy' => 50,
            ],
            'year' => [
                'Thủy' => 25,
            ],
        ],
        'Sửu' => [
            'hour' => [
                'Thổ' => 15,
                'Kim' => 15,
                'Thủy' => 15,
            ],
            'day' => [
                'Thổ' => 20,
                'Kim' => 20,
                'Thủy' => 20,
            ],
            'month' => [
                'Thổ' => 30,
                'Kim' => 30,
                'Thủy' => 30,
            ],
            'year' => [
                'Thổ' => 20,
                'Kim' => 20,
                'Thủy' => 20,
            ],
        ]
    ];

    protected static $dieuchinhcong = [
        'Dần' => [
            'hour' => [
                'Mộc' => 15,
                'Hỏa' => 10,
                'Thổ' => 5,
            ],
            'day' => [
                'Mộc' => 15,
                'Hỏa' => 10,
                'Thổ' => 5,
            ],
            'month' => [
                'Mộc' => 20,
                'Hỏa' => 15,
                'Thổ' => 10,
            ],
            'year' => [
                'Mộc' => 15,
                'Hỏa' => 10,
                'Thổ' => 5,
            ],
        ],
        'Mão' => [
            'hour' => [
                'Mộc' => 15,
            ],
            'day' => [
                'Mộc' => 15,
            ],
            'month' => [
                'Mộc' => 20,
            ],
            'year' => [
                'Mộc' => 15,
            ],
        ],
        'Thìn' => [
            'hour' => [
                'Thổ' => 15,
                'Mộc' => 10,
                'Thủy' => 10,
            ],
            'day' => [
                'Thổ' => 15,
                'Mộc' => 10,
                'Thủy' => 10,
            ],
            'month' => [
                'Thổ' => 20,
                'Mộc' => 15,
                'Thủy' => 15,
            ],
            'year' => [
                'Thổ' => 15,
                'Mộc' => 10,
                'Thủy' => 10,
            ],
        ],
        'Tỵ' => [
            'hour' => [
                'Hỏa' => 15,
                'Kim' => 10,
                'Thổ' => 5,
            ],
            'day' => [
                'Hỏa' => 15,
                'Kim' => 10,
                'Thổ' => 5,
            ],
            'month' => [
                'Hỏa' => 20,
                'Kim' => 15,
                'Thổ' => 10,
            ],
            'year' => [
                'Hỏa' => 15,
                'Kim' => 10,
                'Thổ' => 5,
            ],
        ],
        'Ngọ' => [
            'hour' => [
                'Hỏa' => 15,
                'Thổ' => 5,
            ],
            'day' => [
                'Hỏa' => 15,
                'Thổ' => 5,
            ],
            'month' => [
                'Hỏa' => 20,
                'Thổ' => 10,
            ],
            'year' => [
                'Hỏa' => 15,
                'Thổ' => 5,
            ],
        ],
        'Mùi' => [
            'hour' => [
                'Thổ' => 15,
                'Hỏa' => 10,
                'Mộc' => 10
            ],
            'day' => [
                'Thổ' => 15,
                'Hỏa' => 10,
                'Mộc' => 10
            ],
            'month' => [
                'Thổ' => 20,
                'Hỏa' => 15,
                'Mộc' => 15
            ],
            'year' => [
                'Thổ' => 15,
                'Hỏa' => 10,
                'Mộc' => 10
            ],
        ],
        'Thân' => [
            'hour' => [
                'Kim' => 15,
                'Thủy' => 10,
                'Thổ' => 5
            ],
            'day' => [
                'Kim' => 15,
                'Thủy' => 10,
                'Thổ' => 5
            ],
            'month' => [
                'Kim' => 20,
                'Thủy' => 15,
                'Thổ' => 10
            ],
            'year' => [
                'Kim' => 15,
                'Thủy' => 10,
                'Thổ' => 5
            ],
        ],
        'Dậu' => [
            'hour' => [
                'Kim' => 15,
            ],
            'day' => [
                'Kim' => 15,
            ],
            'month' => [
                'Kim' => 20,
            ],
            'year' => [
                'Kim' => 15,
            ],
        ],
        'Tuất' => [
            'hour' => [
                'Thổ' => 15,
                'Kim' => 10,
                'Hỏa' => 10,
            ],
            'day' => [
                'Thổ' => 15,
                'Kim' => 10,
                'Hỏa' => 10,
            ],
            'month' => [
                'Thổ' => 20,
                'Kim' => 15,
                'Hỏa' => 15,
            ],
            'year' => [
                'Thổ' => 15,
                'Kim' => 10,
                'Hỏa' => 10,
            ],
        ],
        'Hợi' => [
            'hour' => [
                'Thủy' => 15,
                'Mộc' => 10,
            ],
            'day' => [
                'Thủy' => 15,
                'Mộc' => 10,
            ],
            'month' => [
                'Thủy' => 20,
                'Mộc' => 15,
            ],
            'year' => [
                'Thủy' => 15,
                'Mộc' => 10,
            ],
        ],
        'Tý' => [
            'hour' => [
                'Thủy' => 15,
            ],
            'day' => [
                'Thủy' => 15,
            ],
            'month' => [
                'Thủy' => 20,
            ],
            'year' => [
                'Thủy' => 15,
            ],
        ],
        'Sửu' => [
            'hour' => [
                'Thổ' => 15,
                'Kim' => 10,
                'Thủy' => 10,
            ],
            'day' => [
                'Thổ' => 15,
                'Kim' => 10,
                'Thủy' => 10,
            ],
            'month' => [
                'Thổ' => 20,
                'Kim' => 15,
                'Thủy' => 15,
            ],
            'year' => [
                'Thổ' => 15,
                'Kim' => 10,
                'Thủy' => 10,
            ],
        ]
    ];

    public static function phantramnguhanh(array $batTu, array $allMenh, array $arrMenh, array $arrayMenhCan)
    {
        // Bước 1: Tính điểm Thiên Can
        $scores = self::tinhdiemthiencan($batTu);
        // Bước 2: Tính điểm Địa Chi (cộng dồn vào $scores)
        $scores = self::tinhdiemdiachi($batTu, $scores, $allMenh);
        // Bước 3: Điều chỉnh theo mùa sinh (cộng/trừ điểm)
        $step3 = self::dieuchinhtang($scores, $batTu, $arrMenh);
        $scores = $step3['scores'] ?? $scores;
        $keyHasPlus = $step3['keyHasPlus'] ?? [];
        $scores = self::dieuchinhgiam($scores, $batTu, $arrMenh, $keyHasPlus);

        $scores = self::dieuchinhtheomuasinh($scores, $batTu, $arrayMenhCan);
        foreach ($scores as $key => $score) {
            if ($score < 0) {
                $scores[$key] = 0;
            }
            if ($score > 100) {
                $scores[$key] = 100;
            }
        }
        return $scores;
    }

    public static function chatluongnguhanh($menhNhatChu, $dataCrawler): array
    {
        $vongtuongsinh = self::arrTuongSinhNguHanh($menhNhatChu);
        $vongtuongsinhNienVan = self::arrTuongSinhNienVan($menhNhatChu);
        if (!isset($dataCrawler['five_structures']) || !isset($dataCrawler['strength_data'])) {
            return [
                'nguhanh' => $vongtuongsinh,
                'nienvan' => $vongtuongsinhNienVan,
                'chatluongthapthan' => [],
            ];
        }
        $scores = [];
        $scoresNienVan = [];
        foreach ($vongtuongsinh as $key => $value) {
            $scores[$value] = 100 - (int)$dataCrawler['five_structures'][$key] ?? 0;
        }
        foreach ($vongtuongsinhNienVan as $key => $value) {
            $scoresNienVan[$value] = 100 - (int)$dataCrawler['five_structures'][$key] ?? 0;
        }

        return [
            'nguhanh' => $scores,
            'nienvan' => $scoresNienVan,
            'chatluongthapthan' => $dataCrawler['strength_data']['data'] ?? [],
        ];
    }

    public static function tinhdiemdiachi(array $batTu, array &$scores, array $allMenh): array
    {
        foreach ($batTu as $key => $pillar) {
            $chi = $pillar['chi']['dia_chi'] ?? '';
            if ($chi && isset(self::$diemiachi[$chi][$key])) {
                foreach (self::$diemiachi[$chi][$key] as $el => $score) {
                    if ($chi == 'Dần' && $el == 'Hỏa' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Tý', 'Sửu', 'Hợi']))) {
                        $scores['hoa'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Thìn' && $el == 'Mộc' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Thân', 'Dậu', 'Tuất']))) {
                        $scores['moc'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Thìn' && $el == 'Thủy' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Tỵ', 'Ngọ', 'Mùi']))) {
                        $scores['thuy'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Tỵ' && $el == 'Kim' && !in_array($el, $allMenh)) {
                        $scores['kim'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Mùi' && $el == 'Hỏa' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Hợi', 'Tý', 'Sửu']))) {
                        $scores['hoa'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Mùi' && $el == 'Mộc' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Thân', 'Dậu', 'Tuất']))) {
                        $scores['moc'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Thân' && $el == 'Thủy' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Tý', 'Ngọ', 'Mùi']))) {
                        $scores['thuy'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Tuất' && $el == 'Kim' && !in_array($el, $allMenh)) {
                        $scores['kim'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Tuất' && $el == 'Hỏa' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Hợi', 'Tý', 'Sửu']))) {
                        $scores['hoa'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Hợi' && $el == 'Mộc' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Thân', 'Dậu', 'Tuất']))) {
                        $scores['moc'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Sửu' && $el == 'Thủy' && !in_array($el, $allMenh) && !($key == 'month'  &&  in_array($chi, ['Tỵ', 'Ngọ', 'Mùi']))) {
                        $scores['thuy'] += $key == 'month' ? 50 : 30;
                    } else if ($chi == 'Sửu' && $el == 'Kim' && !in_array($el, $allMenh)) {
                        $scores['thuy'] += $key == 'month' ? 50 : 30;
                    } else {
                        $mapVNtoKey = [
                            'Kim' => 'kim',
                            'Mộc' => 'moc',
                            'Thủy' => 'thuy',
                            'Hỏa' => 'hoa',
                            'Thổ' => 'tho'
                        ];
                        $scores[$mapVNtoKey[$el]] += $score;
                    }
                }
            }
        }
        return $scores;
    }

    protected static function dieuchinhtang(array $scores, array $batTu, array $arrMenh)
    {
        $keyHasPlus = [];
        $mapVNtoKey = [
            'Kim' => 'kim',
            'Mộc' => 'moc',
            'Thủy' => 'thuy',
            'Hỏa' => 'hoa',
            'Thổ' => 'tho'
        ];
        $scorePlus = [
            'kim' => [
                'score' => 0,
                'key' => '',
            ],
            'moc' => [
                'score' => 0,
                'key' => '',
            ],
            'thuy' => [
                'score' => 0,
                'key' => '',
            ],
            'hoa' => [
                'score' => 0,
                'key' => '',
            ],
            'tho' => [
                'score' => 0,
                'key' => '',
            ],
        ];
        foreach ($batTu as $key => $pillar) {
            $can = $pillar['can']['thien_can'] ?? '';
            $chi = $pillar['chi']['dia_chi'] ?? '';
            $canchi = $can . ' ' . $chi;
            if (in_array($canchi, [
                'Bính Dần',
                'Đinh Mão',
                'Canh Thìn',
                'Kỷ Tỵ',
                'Mậu Ngọ',
                'Tân Mùi',
                'Nhâm Dần',
                'Quý Dậu',
                'Canh Tuất',
                'Ất Hợi',
                'Giáp Tý',
                'Tân Sửu',
            ])) {
                $scores[$mapVNtoKey[self::getMenh($can)]] += 5;
            }

            foreach ($arrMenh[$key]['can_tang'] as $menh) {
                foreach ($arrMenh as $k => $el) {
                    $score = self::$dieuchinhcong[$chi][$key][$menh] ?? 0;

                    if ($menh == $el['can'] && $scorePlus[$mapVNtoKey[$menh]]['score'] < $score && $key == 'day') {
                        $scorePlus[$mapVNtoKey[$menh]]['score'] = $score;
                        $scorePlus[$mapVNtoKey[$menh]]['key'] = $key;
                    } else if ($key != 'day' && $menh == $el['can'] && $scorePlus[$mapVNtoKey[$menh]]['score'] < $score && $k != 'day') {
                        $scorePlus[$mapVNtoKey[$menh]]['score'] = $score;
                        $scorePlus[$mapVNtoKey[$menh]]['key'] = $key;
                    }
                }
            }

            if ($key == 'month') {
                if ($can == 'Mậu' && $chi == 'Thìn') {
                    $scores['tho'] += 30;
                    $scores['moc'] += 20;
                } else if ($can == 'Kỷ' && $chi == 'Mùi') {
                    $scores['tho'] += 30;
                    $scores['hoa'] += 20;
                } else if ($can == 'Mậu' && $chi == 'Tuất') {
                    $scores['tho'] += 30;
                    $scores['kim'] += 20;
                } else if ($can == 'Kỷ' && $chi == 'Sửu') {
                    $scores['tho'] += 30;
                    $scores['thuy'] += 20;
                }
            }
        }
        foreach ($scorePlus as $key => $score) {
            $scores[$key] += $score['score'];
            $keyHasPlus[] = $score['key'];
        }
        return [
            'scores' => $scores,
            'keyHasPlus' => array_unique($keyHasPlus),
        ];
    }

    protected static $nguhanhmuasinhthangsinh = [
        'Dần' => [
            'season' => 'Mộc',
            'month' => 'Mộc',
        ],
        'Mão' => [
            'season' => 'Mộc',
            'month' => 'Mộc',
        ],
        'Thìn' => [
            'season' => 'Mộc',
            'month' => 'Thổ',
        ],
        'Tỵ' => [
            'season' => 'Hỏa',
            'month' => 'Hỏa',
        ],
        'Ngọ' => [
            'season' => 'Hỏa',
            'month' => 'Hỏa',
        ],
        'Mùi' => [
            'season' => 'Hỏa',
            'month' => 'Thổ',
        ],
        'Thân' => [
            'season' => 'Kim',
            'month' => 'Kim',
        ],
        'Dậu' => [
            'season' => 'Kim',
            'month' => 'Kim',
        ],
        'Tuất' => [
            'season' => 'Kim',
            'month' => 'Thổ',
        ],
        'Hợi' => [
            'season' => 'Thủy',
            'month' => 'Thủy',
        ],
        'Tý' => [
            'season' => 'Thủy',
            'month' => 'Thủy',
        ],
        'Sửu' => [
            'season' => 'Thủy',
            'month' => 'Thổ',
        ],
    ];

    protected static function dieuchinhgiam(array $scores, array $batTu, array $arrMenh, array $keyHasPlus)
    {
        $mapVNtoKey = [
            'Kim' => 'kim',
            'Mộc' => 'moc',
            'Thủy' => 'thuy',
            'Hỏa' => 'hoa',
            'Thổ' => 'tho'
        ];
        $khiphu = [
            'Thìn' => ['Mộc', 'Thủy'],
            'Mùi' => ['Hỏa', 'Mộc'],
            'Tuất' => ['Kim', 'Hỏa'],
            'Sửu' => ['Thủy', 'Kim'],
        ];
        $chiMonth = $batTu['month']['chi']['dia_chi'] ?? '';
        foreach ($batTu as $key => $pillar) {
            $chi = $pillar['chi']['dia_chi'] ?? '';
            $can = $pillar['can']['thien_can'] ?? '';
            if (in_array($chi, array_keys($khiphu)) && in_array($key, $keyHasPlus)) {
                foreach ($arrMenh[$key]['can_tang'] as $menh) {
                    foreach ($arrMenh as $k => $el) {
                        if ($menh == $el['can'] && $key == 'day' && in_array($menh, $khiphu[$chi]) && !in_array($menh, array_values(self::$nguhanhmuasinhthangsinh[$chiMonth]))) {
                            $scores[$mapVNtoKey[$menh]] -= 10;
                        } else if ($key != 'day' && $menh == $el['can'] && $k != 'day' && in_array($menh, $khiphu[$chi]) && !in_array($menh, array_values(self::$nguhanhmuasinhthangsinh[$chiMonth]))) {
                            $scores[$mapVNtoKey[$menh]] -= 10;
                        }
                    }
                }
            }

            if ($key != 'month' && $can . ' ' . $chi == 'Bính Ngọ') {
                $scores['hoa'] -= 10;
                $scores['thuy'] -= 10;
            } else if ($key == 'month' && $can . ' ' . $chi == 'Bính Ngọ') {
                $scores['thuy'] -= 10;
            } else if ($key != 'month' && $can . ' ' . $chi == 'Nhâm Tý') {
                $scores['hoa'] -= 10;
                $scores['thuy'] -= 10;
            } else if ($key == 'month' && $can . ' ' . $chi == 'Nhâm Tý') {
                $scores['hoa'] -= 10;
            } else if ($key != 'month' && $can . ' ' . $chi == 'Tân Dậu') {
                $scores['kim'] -= 10;
            } else if ($key != 'month' && $can . ' ' . $chi == 'Ất Mão') {
                $scores['moc'] -= 10;
            }
        }

        $duplicate = self::findMatchingCanChiPairsSimple($batTu);
        if (count($duplicate) > 0) {
            foreach ($duplicate as $matchingPair) {
                $priority_pillar = $matchingPair['priority_pillar'];
                $detail = array_filter($matchingPair['details'], function ($item) use ($priority_pillar) {
                    return $item['pillar'] == $priority_pillar;
                })[0] ?? [];
                if ($detail) {
                    foreach ($detail['can_tang'] as $canTang) {
                        $menh = self::$stemElements[$canTang['can_tang']];
                        $scores[$mapVNtoKey[$menh]] = $scores[$mapVNtoKey[$menh]] / 2;
                    }
                }
            }
        }
        // dd($batTu);
        return $scores;
    }

    protected static function findMatchingCanChiPairsSimple(array $baziArray): array
    {
        $pairs = [];
        $matches = [];

        // Map priority
        $priorityMap = [
            'hour' => 4,
            'day' => 3,
            'year' => 2,
            'month' => 1
        ];

        // Duyệt qua các trụ theo đúng thứ tự priority để pillar có priority cao hơn được lưu trước
        $pillarsInOrder = ['hour', 'day', 'year', 'month'];

        foreach ($pillarsInOrder as $pillar) {
            if (!isset($baziArray[$pillar])) continue;

            $can = $baziArray[$pillar]['can']['thien_can'] ?? '';
            $chi = $baziArray[$pillar]['chi']['dia_chi'] ?? '';

            if ($can && $chi) {
                $pair = $can . ' ' . $chi;

                if (isset($pairs[$pair])) {
                    // Đã có trước đó -> tìm thấy cặp trùng
                    if (!isset($matches[$pair])) {
                        // Tìm pillar có priority cao nhất
                        $pillarsInPair = [$pairs[$pair]['pillar'], $pillar];
                        $highestPriorityPillar = $pillarsInPair[0]; // Mặc định là pillar đầu tiên (vì duyệt theo priority)

                        // Thêm vào matches
                        $matches[$pair] = [
                            'pair' => $pair,
                            'thien_can' => $can,
                            'dia_chi' => $chi,
                            'priority_pillar' => $highestPriorityPillar, // Pillar có priority cao nhất
                            'pillars' => $pillarsInPair,
                            'count' => 2,
                            'details' => [
                                $pairs[$pair],
                                [
                                    'pillar' => $pillar,
                                    'can_detail' => $baziArray[$pillar]['can'],
                                    'chi_detail' => $baziArray[$pillar]['chi'],
                                    'can_tang' => $baziArray[$pillar]['can_tang']
                                ]
                            ]
                        ];
                    } else {
                        // Cập nhật cặp đã tồn tại
                        $matches[$pair]['count']++;
                        $matches[$pair]['pillars'][] = $pillar;
                        $matches[$pair]['details'][] = [
                            'pillar' => $pillar,
                            'can_detail' => $baziArray[$pillar]['can'],
                            'chi_detail' => $baziArray[$pillar]['chi'],
                            'can_tang' => $baziArray[$pillar]['can_tang']
                        ];

                        // Kiểm tra xem pillar mới có priority cao hơn không
                        $currentPriority = $priorityMap[$matches[$pair]['priority_pillar']];
                        $newPriority = $priorityMap[$pillar];
                        if ($newPriority > $currentPriority) {
                            $matches[$pair]['priority_pillar'] = $pillar;
                        }
                    }
                }

                // Lưu cặp hiện tại
                $pairs[$pair] = [
                    'pillar' => $pillar,
                    'can_detail' => $baziArray[$pillar]['can'],
                    'chi_detail' => $baziArray[$pillar]['chi'],
                    'can_tang' => $baziArray[$pillar]['can_tang']
                ];
            }
        }

        return array_values($matches);
    }

    protected static $dieuchinhtheomuasinh = [
        'Dần' => [
            'default' => [
                'Hỏa' => 20,
                'Thổ' => -5,
                'Kim' => -5,
            ],
            'skip' => []
        ],
        'Mão' => [
            'default' => [
                'Hỏa' => 20,
                'Thổ' => -5,
                'Kim' => -5,
            ],
            'skip' => []
        ],
        'Thìn' => [
            'default' => [
                'Hỏa' => 10,
                'Mộc' => 10,
                'Thủy' => 5,
                'Thổ' => -5,
            ],
            'skip' => ['Thủy']
        ],
        'Tỵ' => [
            'default' => [
                'Thổ' => 10,
                'Thủy' => -10,
                'Kim' => -5,
            ],
            'skip' => []
        ],
        'Ngọ' => [
            'default' => [
                'Thổ' => 10,
                'Thủy' => -10,
                'Kim' => -5,
            ],
            'skip' => []
        ],
        'Mùi' => [
            'default' => [
                'Hỏa' => 10,
                'Thổ' => 5,
                'Kim' => 5,
                'Thủy' => -10,
            ],
            'skip' => ['Kim']
        ],
        'Thân' => [
            'default' => [
                'Thủy' => 10,
                'Mộc' => -10,
                'Hỏa' => -5,
            ],
            'skip' => []
        ],
        'Dậu' => [
            'default' => [
                'Thủy' => 10,
                'Mộc' => -10,
                'Hỏa' => -5,
            ],
            'skip' => []
        ],
        'Tuất' => [
            'default' => [
                'Kim' => 10,
                'Thủy' => 5,
                'Hỏa' => -15,
                'Mộc' => -10,
            ],
            'skip' => ['Thủy']
        ],
        'Hợi' => [
            'default' => [
                'Mộc' => 10,
                'Hỏa' => -10,
                'Thổ' => -5,
            ],
            'skip' => []
        ],
        'Tý' => [
            'default' => [
                'Thủy' => 10,
                'Mộc' => -10,
                'Hỏa' => -5,
            ],
            'skip' => []
        ],
        'Sửu' => [
            'default' => [
                'Thủy' => 10,
                'Mộc' => 10,
                'Kim' => 5,
                'Hỏa' => -10,
                'Thổ' => -5,
            ],
            'skip' => ['Mộc', 'Kim']
        ],
    ];

    /**
     * Tính can chi từ năm dương lịch
     * @param int $year Năm dương lịch
     * @return array ['can' => string, 'chi' => string]
     */
    protected static function getYearCanChi($year)
    {
        // Công thức tính: Giáp Tý là năm 1984 (hoặc 4 mod 60)
        // Can index = (year - 4) % 10
        // Chi index = (year - 4) % 12
        $stemIndex = ($year - 4) % 10;
        $branchIndex = ($year - 4) % 12;

        // Xử lý số âm (nếu year < 4)
        if ($stemIndex < 0) $stemIndex += 10;
        if ($branchIndex < 0) $branchIndex += 12;

        return [
            'can' => self::$stems[$stemIndex],
            'chi' => self::$branches[$branchIndex]
        ];
    }

    protected static function daivan($yearStem, $birthDay, $birthMonth, $birthYear, $gender, $monthStem, $monthBranch, $dayStem, $dayBranch)
    {
        $yangStems = ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm'];
        $isYang = in_array($yearStem, $yangStems, true);
        $forward = (($isYang && $gender == 'male') || (!$isYang && $gender == 'female'));
        $sesson = [
            [
                'start' => '04-02',
                'end' => '05-03',
            ],
            [
                'start' => '06-03',
                'end' => '04-04',
            ],
            [
                'start' => '05-04',
                'end' => '05-05',
            ],
            [
                'start' => '06-05',
                'end' => '05-06',
            ],
            [
                'start' => '06-06',
                'end' => '06-07',
            ],
            [
                'start' => '07-07',
                'end' => '07-08',
            ],
            [
                'start' => '08-08',
                'end' => '07-09',
            ],
            [
                'start' => '08-09',
                'end' => '07-10',
            ],
            [
                'start' => '08-10',
                'end' => '06-11',
            ],
            [
                'start' => '07-11',
                'end' => '06-12',
            ],
            [
                'start' => '07-12',
                'end' => '05-01',
            ],
            [
                'start' => '06-01',
                'end' => '03-02',
            ],
        ];

        // Tạo DateTime từ ngày, tháng, năm sinh
        $birthDateTime = new \DateTime(sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay));

        // Tạo danh sách tất cả các ngày tiết khí trong năm sinh và năm trước/sau
        $seasonDates = [];
        foreach ([$birthYear - 1, $birthYear, $birthYear + 1] as $year) {
            foreach ($sesson as $season) {
                // Chuyển đổi định dạng từ DD-MM sang MM-DD
                // Thêm ngày bắt đầu
                $startParts = explode('-', $season['start']);
                $startDate = new \DateTime(sprintf('%d-%02d-%02d', $year, $startParts[1], $startParts[0]));
                $seasonDates[] = $startDate;

                // Thêm ngày kết thúc
                $endParts = explode('-', $season['end']);
                $endDate = new \DateTime(sprintf('%d-%02d-%02d', $year, $endParts[1], $endParts[0]));
                $seasonDates[] = $endDate;
            }
        }

        // Sắp xếp các ngày theo thứ tự
        usort($seasonDates, function ($a, $b) {
            return $a <=> $b;
        });

        // Tìm ngày tiết khí gần nhất
        $closestDate = null;
        $minDays = PHP_INT_MAX;

        if ($forward) {
            // Tính thuận: tìm ngày tiết khí tiếp theo (sau ngày sinh)
            foreach ($seasonDates as $seasonDate) {
                if ($seasonDate > $birthDateTime) {
                    $diff = $birthDateTime->diff($seasonDate);
                    $days = $diff->days;
                    if ($days < $minDays) {
                        $minDays = $days;
                        $closestDate = $seasonDate;
                    }
                    break; // Lấy ngày đầu tiên sau ngày sinh
                }
            }
        } else {
            // Tính nghịch: tìm ngày tiết khí trước đó (trước ngày sinh)
            foreach (array_reverse($seasonDates) as $seasonDate) {
                if ($seasonDate < $birthDateTime) {
                    $diff = $seasonDate->diff($birthDateTime);
                    $days = $diff->days;
                    if ($days < $minDays) {
                        $minDays = $days;
                        $closestDate = $seasonDate;
                    }
                    break; // Lấy ngày đầu tiên trước ngày sinh
                }
            }
        }

        // Tính tuổi bắt đầu đại vận: chia số ngày cho 3 và làm tròn
        $startAge = round($minDays / 3);

        // Tìm vị trí của can tháng và chi tháng trong mảng
        $stemIndex = array_search($monthStem, self::$stems);
        $branchIndex = array_search($monthBranch, self::$branches);

        // Tính bảng đại vận
        $bangdaivan = [];
        for ($i = 0; $i < 9; $i++) {
            if ($forward) {
                // Thuận: đi theo vòng thuận (tăng dần)
                $canIndex = ($stemIndex + $i + 1) % 10;
                $chiIndex = ($branchIndex + $i + 1) % 12;
            } else {
                // Nghịch: đi theo vòng nghịch (giảm dần)
                $canIndex = ($stemIndex - $i - 1 + 10 * 10) % 10; // Thêm 10*10 để tránh số âm
                $chiIndex = ($branchIndex - $i - 1 + 12 * 10) % 12; // Thêm 12*10 để tránh số âm
            }
            $chi = self::$branches[$chiIndex];
            $cantang = [];
            foreach ($chi ? (self::$hiddenStems[$chi] ?? []) : [] as $stem) {
                $dauStem = self::$dau_ngu_hanh[$stem] ?? '';
                $menhStem = $dauStem . ' ' . self::getMenh($stem);
                $cantang[] = [
                    'can_tang' => $stem,
                    'menh' => $menhStem,
                    'pho_tinh' => self::relation($dayStem, $stem)
                ];
            }
            $list_year = [];
            for ($j = 0; $j <= 9; $j++) {
                $nam = $birthYear + ($startAge + ($i * 10)) - 1 + $j;
                $canChi = self::getYearCanChi($nam);
                $list_year[] = [
                    'nam' => $nam,
                    'can_chi' => $canChi['can'] . ' ' . $canChi['chi'],
                    'khong_vong' => self::checkKhongVong($dayStem, $dayBranch, $canChi['chi'])
                ];
            }
            $thienCan = self::$stems[$canIndex];
            $diaChi = self::$branches[$chiIndex];
            $dauThienCan = self::$dau_ngu_hanh[$thienCan] ?? '';
            // $amDuongThienCan = self::$am_duong_thien_can[$thienCan] ?? '';
            $dauDiaChi = self::$dau_dia_chi[strtolower($diaChi)] ?? '';
            $menhThienCan = $thienCan !== '' ? self::getMenh($thienCan) : '';
            $menhDiaChi = $diaChi !== '' ? self::getMenhDiaChi($diaChi) : '';
            $bangdaivan[$i] = [
                'age' => $startAge + ($i * 10),
                'can' => [
                    'thien_can' => $thienCan,
                    'am_duong' => $dauThienCan,
                    'menh' => $menhThienCan,
                    'chu_tinh' => self::getChuTinhWebVN($dayStem, $thienCan),
                ],
                'chi' => [
                    'dia_chi' => $diaChi,
                    'am_duong' => $dauDiaChi,
                    'menh' => $menhDiaChi,
                    'khong_vong' => self::checkKhongVong($dayStem, $dayBranch, $diaChi)
                ],
                'cantang' => $cantang,
                'list_year' => $list_year,
            ];
        }
        return $bangdaivan;
    }

    /**
     * Dựng một Niên Vận (Can/Chi/Tàng Can/Thập Thần) cho năm dương lịch, cùng cấu trúc với phần tử trong nienVan().
     */
    public static function buildNienVanForYear(int $year, string $dayStem, string $dayBranch): array
    {
        $canChi = self::getYearCanChi($year);
        $cantang = [];
        foreach ($canChi['chi'] ? (self::$hiddenStems[$canChi['chi']] ?? []) : [] as $stem) {
            $dauStem = self::$dau_ngu_hanh[$stem] ?? '';
            $menhStem = $dauStem . ' ' . self::getMenh($stem);
            $cantang[] = [
                'can_tang' => $stem,
                'menh' => $menhStem,
                'pho_tinh' => self::relation($dayStem, $stem),
            ];
        }

        return [
            'nam' => $year,
            'can' => [
                'thien_can' => $canChi['can'],
                'am_duong' => self::$dau_ngu_hanh[$canChi['can']] ?? '',
                'menh' => self::getMenh($canChi['can']),
                'chu_tinh' => self::getChuTinhWebVN($dayStem, $canChi['can']),
            ],
            'chi' => [
                'dia_chi' => $canChi['chi'],
                'am_duong' => self::$dau_dia_chi[strtolower($canChi['chi'])] ?? '',
                'menh' => self::getMenhDiaChi($canChi['chi']),
                'khong_vong' => self::checkKhongVong($dayStem, $dayBranch, $canChi['chi']),
            ],
            'cantang' => $cantang,
        ];
    }

    protected static function nienVan($year, $dayStem, $dayBranch)
    {
        if (empty($year)) {
            $year = intval(date('Y'));
        }

        $years = [$year - 1, $year, $year + 1];
        $data = [];
        foreach ($years as $y) {
            $data[] = self::buildNienVanForYear($y, $dayStem, $dayBranch);
        }

        return $data;
    }

    protected static function checkKhongVong($dayStem, $dayBranch, $chi)
    {

        $khongVongItem = array_values(array_filter(self::$khong_vong_data, function ($item) use ($dayStem, $dayBranch) {
            return $item['tru_ngay'] == $dayStem . ' ' . $dayBranch;
        }))[0];
        // dump($khongVongItem);
        if (!empty($khongVongItem) && in_array($chi, $khongVongItem['dia_chi_khong_vong'])) {
            return true;
        }
        return false;
    }

    protected static function dieuchinhtheomuasinh(array $scores, array $batTu, array $arrayMenhCan)
    {

        $monthBranch = $batTu['month']['chi']['dia_chi'] ?? '';
        $mapVNtoKey = [
            'Kim' => 'kim',
            'Mộc' => 'moc',
            'Thủy' => 'thuy',
            'Hỏa' => 'hoa',
            'Thổ' => 'tho'
        ];
        $dieuchinhtheomuasinh = self::$dieuchinhtheomuasinh[$monthBranch] ?? [];
        if (!empty($dieuchinhtheomuasinh)) {
            foreach ($dieuchinhtheomuasinh['default'] as $key => $value) {
                if (!in_array($key, $dieuchinhtheomuasinh['skip']) || (in_array($key, $dieuchinhtheomuasinh['skip']) && !in_array($key, $arrayMenhCan))) {
                    $scores[$mapVNtoKey[$key]] += $value;
                }
            }
        }
        return $scores;
    }

    public static function tinhdiemthiencan(array $batTu): array
    {
        $scores = [
            'kim' => 0,
            'moc' => 0,
            'thuy' => 0,
            'hoa' => 0,
            'tho' => 0,
        ];

        $mapVNtoKey = [
            'Kim' => 'kim',
            'Mộc' => 'moc',
            'Thủy' => 'thuy',
            'Hỏa' => 'hoa',
            'Thổ' => 'tho'
        ];

        // Đếm số lần xuất hiện của từng ngũ hành ở Thiên Can và Địa Chi
        $stemCounts = ['Kim' => 0, 'Mộc' => 0, 'Thủy' => 0, 'Hỏa' => 0, 'Thổ' => 0];
        $branchCounts = ['Kim' => 0, 'Mộc' => 0, 'Thủy' => 0, 'Hỏa' => 0, 'Thổ' => 0];

        foreach ($batTu as $pillar) {
            // Đếm Thiên Can
            $can = $pillar['can']['thien_can'] ?? '';
            if ($can && isset(self::$stemElements[$can])) {
                $element = self::$stemElements[$can];
                if (isset($stemCounts[$element])) {
                    $stemCounts[$element]++;
                }
            }

            // Đếm Địa Chi (dựa vào config ngũ hành động để biết chi chứa hành gì)
            $chi = $pillar['chi']['dia_chi'] ?? '';
            if ($chi && isset(self::$nguHanhDongConfig['branches'][$chi])) {
                $elementsInBranch = array_keys(self::$nguHanhDongConfig['branches'][$chi]['normal'] ?? []);
                foreach ($elementsInBranch as $el) {
                    if (isset($branchCounts[$el])) {
                        $branchCounts[$el]++;
                    }
                }
            }
        }

        // Tính điểm cho từng NGŨ HÀNH (không lặp qua từng trụ)
        foreach ($stemCounts as $element => $count) {
            $key = $mapVNtoKey[$element] ?? null;

            if ($key && $count > 0) {
                // Logic: Ngũ hành chỉ có duy nhất 1 vị trí trên Thiên Can (stemCounts == 1)
                // và không có ở bất kỳ vị trí nào của Địa Chi (branchCounts == 0) thì cộng 30 điểm
                // còn lại mặc định cộng (10 điểm * số lần xuất hiện)
                if ($count === 1 && $branchCounts[$element] === 0) {
                    $scores[$key] = 30;
                } else {
                    $scores[$key] = $count * 10;
                }
            }
        }

        return $scores;
    }

    public static function calc(string $fullName, int $y, int $m, int $d, ?int $hour = null, ?int $minute = null, $g = 'male', $y_detail = null, bool $needStrength = true)
    {
        if (! self::calcCacheEnabled()) {
            return self::doCalc($fullName, $y, $m, $d, $hour, $minute, $g, $y_detail, $needStrength);
        }

        $cacheKey = self::buildCalcCacheKey($fullName, $y, $m, $d, $hour, $minute, $g, $y_detail, $needStrength);
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && $cached !== [] && self::isCalcCacheHit($cached, $needStrength)) {
            return $cached;
        }

        try {
            return Cache::lock('bazi_calc_lock:'.$cacheKey, 90)->block(75, function () use (
                $cacheKey,
                $fullName,
                $y,
                $m,
                $d,
                $hour,
                $minute,
                $g,
                $y_detail,
                $needStrength
            ): array {
                $cached = Cache::get($cacheKey);
                if (is_array($cached) && $cached !== [] && self::isCalcCacheHit($cached, $needStrength)) {
                    return $cached;
                }

                $result = self::doCalc($fullName, $y, $m, $d, $hour, $minute, $g, $y_detail, $needStrength);
                if ($result !== [] && self::shouldPersistCalcCache($result, $needStrength)) {
                    $ttl = (int) config('bazi.calc_cache_ttl_seconds', 21600);
                    Cache::put($cacheKey, $result, now()->addSeconds($ttl));
                }

                return $result;
            });
        } catch (LockTimeoutException) {
            return self::doCalc($fullName, $y, $m, $d, $hour, $minute, $g, $y_detail, $needStrength);
        }
    }

    protected static function isCalcCacheHit(array $cached, bool $needStrength): bool
    {
        return ! $needStrength || ! self::calcResultStrengthEmpty($cached);
    }

    protected static function shouldPersistCalcCache(array $result, bool $needStrength): bool
    {
        return ! $needStrength || ! self::calcResultStrengthEmpty($result);
    }

    protected static function calcResultStrengthEmpty(array $result): bool
    {
        return self::isNguHanhDongEmpty($result['ngu_hanh_dong'] ?? [])
            && self::isChatLuongThapThanEmpty($result['chat_luong_thap_than'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function isNguHanhDongEmpty(array $data): bool
    {
        if ($data === []) {
            return true;
        }

        foreach ($data as $value) {
            if (is_numeric($value) && (float) $value > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, mixed>  $data
     */
    protected static function isChatLuongThapThanEmpty(array $data): bool
    {
        if ($data === []) {
            return true;
        }

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            if ((int) ($item['natal'] ?? 0) > 0 || (int) ($item['annual'] ?? 0) > 0) {
                return false;
            }
        }

        return true;
    }

    protected static function buildCrawlerCacheKey(
        int $y,
        int $m,
        int $d,
        ?int $hour,
        ?int $minute,
        bool $unknownTime,
        int $genderCode
    ): string {
        return sprintf(
            'bazi_crawl:%04d-%02d-%02d:%s:%s:%d:%d',
            $y,
            $m,
            $d,
            $hour === null ? 'x' : $hour,
            $minute === null ? 'x' : $minute,
            $unknownTime ? 1 : 0,
            $genderCode
        );
    }

    protected static function calcCacheEnabled(): bool
    {
        if (! config('bazi.calc_cache_enabled', true)) {
            return false;
        }

        if (request()->has('no_cache') || request()->has('calc_sim')) {
            return false;
        }

        return true;
    }

    protected static function buildCalcCacheKey(
        string $fullName,
        int $y,
        int $m,
        int $d,
        ?int $hour,
        ?int $minute,
        $g,
        ?int $y_detail,
        bool $needStrength
    ): string {
        $yearDetail = $y_detail ?? (int) date('Y');

        return 'bazi_calc:v1:'.hash('xxh128', json_encode([
            'name' => $fullName,
            'y' => $y,
            'm' => $m,
            'd' => $d,
            'h' => $hour,
            'min' => $minute,
            'g' => (string) $g,
            'y_detail' => $yearDetail,
            'need_strength' => $needStrength,
        ], JSON_UNESCAPED_UNICODE));
    }

    public static function doCalc(string $fullName, int $y, int $m, int $d, ?int $hour = null, ?int $minute = null, $g = 'male', $y_detail = null, bool $needStrength = true)
    {
        // Nếu không truyền y_detail, lấy năm hiện tại
        if ($y_detail === null) {
            $y_detail = (int) date('Y');
        }

        $searchDate = sprintf("%04d-%02d-%02d", $y, $m, $d);
        $searchTime = $hour !== null ? sprintf("%02d:%02d:00", $hour, $minute ?? 0) : '00:00:00';

        // Tìm bản ghi gần nhất và nhỏ hơn hoặc bằng thời điểm cần tra
        $napGiap = \App\Models\NapGiap::where('thoi_diem_bat_dau_ngay', '<=', $searchDate)
            ->where(function ($query) use ($searchDate, $searchTime) {
                $query->where('thoi_diem_bat_dau_ngay', '<', $searchDate)
                    ->orWhere(function ($q) use ($searchDate, $searchTime) {
                        $q->where('thoi_diem_bat_dau_ngay', '=', $searchDate)
                            ->where('thoi_diem_bat_dau_gio', '<=', $searchTime);
                    });
            })
            ->orderBy('thoi_diem_bat_dau_ngay', 'desc')
            ->orderBy('thoi_diem_bat_dau_gio', 'desc')
            ->first();
        if (!$napGiap) {
            return [];
        }

        $yearStem = explode(' ', $napGiap->nap_giap_nam)[0];
        $yearBranch = explode(' ', $napGiap->nap_giap_nam)[1];
        $monthStem = explode(' ', $napGiap->nap_giap_thang)[0];
        $monthBranch = explode(' ', $napGiap->nap_giap_thang)[1];

        // Tính Can Chi của ngày từ ngày dương lịch (không dùng thư viện ngoài)
        [$dayStem, $dayBranch] = [null, null];
        [$hourStem, $hourBranch] = [null, null];

        // Chuyển ngày Gregorian -> JDN rồi xác định can-chi ngày
        $canChiNgay = self::tinhCanChi($y, $m, $d);
        $dayStem = $canChiNgay['thien_can'];
        $dayBranch = $canChiNgay['dia_chi'];

        // Nếu có giờ truyền vào thì tính can chi giờ
        if ($hour !== null) {
            // Chi giờ (giờ đôi: 23-0 => Tý(0), 1-2 => Sửu(1), ...)
            $chiIndex = intdiv($hour + 1, 2) % 12;
            $hourBranch = self::$branches[$chiIndex];

            // Quan trọng: Trong lịch Tứ Trụ, ngày mới bắt đầu từ 23:00 (giờ Tý)
            // Nếu hour >= 23, cần dùng Can của ngày tiếp theo để tính Can giờ
            $dayStemForHour = $dayStem;
            if ($hour >= 23) {
                // Tính Can Chi của ngày tiếp theo
                $nextDate = date('Y-m-d', strtotime("$y-$m-$d +1 day"));
                list($nextY, $nextM, $nextD) = explode('-', $nextDate);
                $canChiNgayTiepTheo = self::tinhCanChi($nextY, $nextM, $nextD);
                $dayStemForHour = $canChiNgayTiepTheo['thien_can'];
            }

            // Can giờ phụ thuộc vào can ngày: hourCanIndex = (dayCanIndex*2 + chiIndex) % 10
            $dayCanIndex = array_search($dayStemForHour, self::$stems, true);
            $dayCanIndex = $dayCanIndex === false ? 0 : $dayCanIndex;
            $hourCanIndex = ($dayCanIndex * 2 + $chiIndex) % 10;
            $hourStem = self::$stems[$hourCanIndex];
        }

        $pillars = [
            'year'  => ['can' => $yearStem,  'chi' => $yearBranch],
            'month' => ['can' => $monthStem, 'chi' => $monthBranch],
            'day'   => ['can' => $dayStem,   'chi' => $dayBranch],
            'hour'  => ['can' => $hourStem,  'chi' => $hourBranch],
        ];
        $allMenh = [];
        $arrMenh = [];
        $menhCanTang = [];
        // Tính Can Tàng & Phó Tinh
        $cantang = [];
        $arrayMenhCan = [];
        foreach ($pillars as $k => $p) {
            $chi = $p['chi'];
            foreach ($chi ? (self::$hiddenStems[$chi] ?? []) : [] as $stem) {
                $dauStem = self::$dau_ngu_hanh[$stem] ?? '';
                $menhStem = $dauStem . ' ' . self::getMenh($stem);
                $allMenh[] = self::getMenh($stem);
                $cantang[$k][] = [
                    'can_tang' => $stem,
                    'menh' => $menhStem,
                    'pho_tinh' => self::relation($dayStem, $stem)
                ];
                $menhCanTang[$k][] = self::getMenh($stem);
            }
        }

        $chuTinh = [
            'year'  => self::getChuTinhWebVN($dayStem, $yearStem),
            'month' => self::getChuTinhWebVN($dayStem, $monthStem),
            'day'   => 'Nhật Can',
            'hour'  => $hourStem ? self::getChuTinhWebVN($dayStem, $hourStem) : '',
        ];

        $batTu = [];
        foreach ($pillars as $k => $p) {
            $thienCan = $p['can'] ?? '';
            $diaChi = $p['chi'] ?? '';
            $dauThienCan = self::$dau_ngu_hanh[$thienCan] ?? '';
            $amDuongThienCan = self::$am_duong_thien_can[$thienCan] ?? '';
            $dauDiaChi = self::$dau_dia_chi[strtolower($diaChi)] ?? '';
            $menhThienCan = $thienCan !== '' ? self::getMenh($thienCan) : '';
            $menhDiaChi = $diaChi !== '' ? self::getMenhDiaChi($diaChi) : '';
            $allMenh[] = $menhThienCan;
            $allMenh[] = $menhDiaChi;
            $batTu[$k] = [
                'can' => [
                    'thien_can' => $thienCan,
                    'menh' => $dauThienCan . ' ' . $menhThienCan,
                    'chu_tinh' => $chuTinh[$k] ?? '',
                    'am_duong_thien_can' => $amDuongThienCan . ' ' . $menhThienCan
                ],
                'chi' => [
                    'dia_chi' => $diaChi,
                    'menh' => $dauDiaChi . ' ' . $menhDiaChi,
                    'khong_vong' => self::checkKhongVong($dayStem, $dayBranch, $diaChi)
                ],
                'can_tang' => $cantang[$k] ?? []
            ];
            $arrMenh[$k] = [
                'can' => $menhThienCan,
                'chi' => $menhDiaChi,
                'can_tang' => $menhCanTang[$k] ?? [],
            ];
            $arrayMenhCan[] = $menhThienCan;
        }
        $hykythan = HyKyThan::findByThienCanDiaChi($dayStem, $monthBranch);
        $tongquantinhcach = array_filter(self::$tongquantinhcach, function ($item) use ($dayStem) {
            return $item['can'] == $dayStem;
        });

        // Lấy giải pháp cân bằng dựa trên hỷ thần
        $giaiphapCanbang = self::getGiaiPhapCanBang($hykythan['hy_than_ngu_hanh'] ?? '');
        $cackhiacach = self::$cac_khia_canh_cuoc_song[Str::slug($dayStem) . '_' . Str::slug($dayBranch)] ?? [];
        // Lấy mệnh
        $menh = self::$menh_nap_am[$yearStem . ' ' . $yearBranch] ?? '';

        // Lấy danh sách sim hợp mệnh
        // $sims = self::getSimsByMenh($menh);
        $sims = [];
        $allMenh = array_unique($allMenh);
        $bangdaivan = self::daivan($yearStem, $d, $m, $y, $g, $monthStem, $monthBranch, $dayStem, $dayBranch);
        $nienVan = self::nienVan($y_detail, $dayStem, $dayBranch);
        // Tính ngũ hành động
        // $nguHanhDong = self::calculateNguHanhDong($batTu);
        // $phantramnguhanh = self::phantramnguhanh($batTu, $allMenh, $arrMenh, $arrayMenhCan);
        $phantramnguhanh = [];
        $phantramnienvan = [];
        $chatluongthapthan = [];
        if ($needStrength && !request()->has('export_pdf') && !request()->has('calc_sim')) {
            $genderCode = $g == 'male' ? 0 : 1;
            $unknownTime = $hour === null;
            $crawlerCacheKey = self::buildCrawlerCacheKey($y, $m, $d, $hour, $minute, $unknownTime, $genderCode);

            try {
                $crawlerData = \Illuminate\Support\Facades\Cache::remember(
                    $crawlerCacheKey,
                    now()->addHours(24),
                    function () use ($d, $m, $y, $hour, $minute, $unknownTime, $genderCode) {
                        $crawler = new JoeyYapCrawlerService();
                        $data = $crawler->queryProfilesStrengthChart(
                            day: $d,
                            month: $m,
                            year: $y,
                            hour: $hour,
                            min: $minute,
                            isTimeOfBirthUnknown: $unknownTime,
                            gender: $genderCode,
                        );

                        if (! isset($data['five_structures'], $data['strength_data'])) {
                            throw new \RuntimeException('Crawler response thiếu five_structures/strength_data');
                        }

                        return $data;
                    }
                );
                $chatluongnguhanh = self::chatluongnguhanh(Str::slug($arrMenh['day']['can']), $crawlerData);
                $phantramnguhanh = $chatluongnguhanh['nguhanh'];
                $phantramnienvan = $chatluongnguhanh['nienvan'];
                $chatluongthapthan = $chatluongnguhanh['chatluongthapthan'];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Cache::forget($crawlerCacheKey);
                \Illuminate\Support\Facades\Log::warning('BaZiServiceV2: Crawler thất bại – ' . $e->getMessage());
            }
        }
        $chiSoBieuDoCot = self::tinhChiSoBieuDoCot(
            $batTu,
            $chatluongthapthan,
            $phantramnguhanh,
            $batTu['day']['can']['thien_can'] ?? ''
        );

        $dayStem = $batTu['day']['can']['thien_can'] ?? null;
        $dayBranch = $batTu['day']['chi']['dia_chi'] ?? null;
        $quyNhanVanXuong = null;
        if ($dayStem && isset(self::$quyNhanVanXuong[$dayStem])) {
            $row = self::$quyNhanVanXuong[$dayStem];
            $quyNhanVanXuong = [
                'thien_can_ngay' => $dayStem,
                'quy_nhan' => implode(', ', $row['quy_nhan'] ?? []),
                'van_xuong' => $row['van_xuong'] ?? null,
            ];
        }

        if ($dayBranch) {
            $dichMa = null;
            $daoHoa = null;
            $coThan = null;

            if (in_array($dayBranch, ['Dần', 'Ngọ', 'Tuất'], true)) {
                $dichMa = 'Thân';
            } elseif (in_array($dayBranch, ['Thân', 'Tý', 'Thìn'], true)) {
                $dichMa = 'Dần';
            } elseif (in_array($dayBranch, ['Hợi', 'Mão', 'Mùi'], true)) {
                $dichMa = 'Tỵ';
            } elseif (in_array($dayBranch, ['Tỵ', 'Dậu', 'Sửu'], true)) {
                $dichMa = 'Hợi';
            }

            if (in_array($dayBranch, ['Hợi', 'Mão', 'Mùi'], true)) {
                $daoHoa = 'Mão';
            } elseif (in_array($dayBranch, ['Tý', 'Thìn', 'Thân'], true)) {
                $daoHoa = 'Dậu';
            } elseif (in_array($dayBranch, ['Dần', 'Ngọ', 'Tuất'], true)) {
                $daoHoa = 'Tý';
            } elseif (in_array($dayBranch, ['Tỵ', 'Dậu', 'Sửu'], true)) {
                $daoHoa = 'Ngọ';
            }

            if (in_array($dayBranch, ['Dần', 'Mão', 'Thìn'], true)) {
                $coThan = 'Tỵ';
            } elseif (in_array($dayBranch, ['Tỵ', 'Ngọ', 'Mùi'], true)) {
                $coThan = 'Thân';
            } elseif (in_array($dayBranch, ['Thân', 'Dậu', 'Tuất'], true)) {
                $coThan = 'Hợi';
            } elseif (in_array($dayBranch, ['Hợi', 'Tý', 'Sửu'], true)) {
                $coThan = 'Dần';
            }

            if ($dichMa || $daoHoa || $coThan) {
                $quyNhanVanXuong = $quyNhanVanXuong ?? [];
                $quyNhanVanXuong['dia_chi_ngay'] = $dayBranch;
                $quyNhanVanXuong['dich_ma'] = $dichMa;
                $quyNhanVanXuong['dao_hoa'] = $daoHoa;
                $quyNhanVanXuong['co_than'] = $coThan;
            }
        }

        $lucThanData = self::$lucthan[$batTu['day']['can']['thien_can'] ?? ''] ?? [];
        $bieuDoNguHanh = self::createBieuDoNguHanh($phantramnguhanh, $lucThanData, $phantramnienvan);
        return [
            'bat_tu' => $batTu,
            'hy_ky_than' => $hykythan,
            'tong_quan_tinh_cach' => array_values($tongquantinhcach)[0] ?? null,
            'giai_phap_can_bang' => $giaiphapCanbang,
            'menh' => $menh,
            'chi_so_bieu_do_cot' => $chiSoBieuDoCot,
            'quy_nhan_van_xuong' => $quyNhanVanXuong,
            'sims' => $sims,
            'ngu_hanh_dong' => $phantramnguhanh,
            'luc_than' => self::$lucthan[$batTu['day']['can']['thien_can'] ?? ''] ?? '',
            'cac_khia_canh_cuoc_song' => $cackhiacach,
            'bang_dai_van' => $bangdaivan,
            'nien_van' => $nienVan,
            'phan_tram_nien_van' => $phantramnienvan,
            'chat_luong_thap_than' => $chatluongthapthan,
            'bieu_do_ngu_hanh' => $bieuDoNguHanh,
        ];
    }

    public static function computeChiSoBieuDoCot(array $batTu, array $chatLuongThapThan, array $nguHanhDong): array
    {
        $dayStem = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));

        return self::tinhChiSoBieuDoCot($batTu, $chatLuongThapThan, $nguHanhDong, $dayStem);
    }

    protected static function tinhChiSoBieuDoCot(array $batTu, array $chatLuongThapThan, array $nguHanhDong, string $dayStem): array
    {
        $natalByName = [];
        $annualByName = [];
        foreach ($chatLuongThapThan as $item) {
            $name = trim($item['name'] ?? '');
            if ($name !== '') {
                $natalByName[$name] = (int) ($item['natal'] ?? 0);
                $annualByName[$name] = (int) ($item['annual'] ?? 0);
            }
        }

        $lucThan = self::$lucthan[$dayStem] ?? [];
        $vQuanQuy = 0;
        $vTheTai = 0;
        $vHuynhDe = 0;
        $vTuTon = 0;
        $vPhuMau = 0;
        foreach ($lucThan as $key => $value) {
            $v = trim($value ?? '');
            if ($v === 'Quan Quỷ') {
                $vQuanQuy = (int) ($nguHanhDong[$key] ?? 0);
            } elseif ($v === 'Thê Tài') {
                $vTheTai = (int) ($nguHanhDong[$key] ?? 0);
            } elseif ($v === 'Huynh Đệ') {
                $vHuynhDe = (int) ($nguHanhDong[$key] ?? 0);
            } elseif ($v === 'Tử Tôn') {
                $vTuTon = (int) ($nguHanhDong[$key] ?? 0);
            } elseif ($v === 'Phụ Mẫu') {
                $vPhuMau = (int) ($nguHanhDong[$key] ?? 0);
            }
        }

        $hourCanTang = $batTu['hour']['can_tang'] ?? [];
        $monthCanTang = $batTu['month']['can_tang'] ?? [];
        $yearCanTang = $batTu['year']['can_tang'] ?? [];
        $dayCanTang = $batTu['day']['can_tang'] ?? [];
        $chuTinhThang = trim($batTu['month']['can']['chu_tinh'] ?? '');
        $chuTinhNam = trim($batTu['year']['can']['chu_tinh'] ?? '');
        $chuTinhGio = trim($batTu['hour']['can']['chu_tinh'] ?? '');

        $compute = function (array $valueByName) use ($batTu, $lucThan, $nguHanhDong, $hourCanTang, $monthCanTang, $yearCanTang, $dayCanTang, $chuTinhThang, $chuTinhNam, $chuTinhGio, $vQuanQuy, $vTheTai, $vHuynhDe, $vTuTon, $vPhuMau) {
            $getPart = function (array $canTangArr) use ($valueByName) {
                $count = count($canTangArr);
                $vPhu = 0;
                $vChinh = 0;
                $vDuKhi = 0;
                if ($count === 1) {
                    $vChinh = isset($canTangArr[0]['pho_tinh']) ? ($valueByName[trim($canTangArr[0]['pho_tinh'])] ?? 0) : 0;
                } else {
                    $vPhu = isset($canTangArr[0]['pho_tinh']) ? ($valueByName[trim($canTangArr[0]['pho_tinh'])] ?? 0) : 0;
                    $vChinh = isset($canTangArr[1]['pho_tinh']) ? ($valueByName[trim($canTangArr[1]['pho_tinh'])] ?? 0) : 0;
                    $vDuKhi = isset($canTangArr[2]['pho_tinh']) ? ($valueByName[trim($canTangArr[2]['pho_tinh'])] ?? 0) : 0;
                }
                return ($vChinh + $vPhu / 2 + $vDuKhi / 3) / 3;
            };
            $vThang = $valueByName[$chuTinhThang] ?? 0;
            $vNam = $valueByName[$chuTinhNam] ?? 0;
            $suNghiep = round(($vThang + $vNam + $vQuanQuy) / 3, 2);
            $hourPart = $getPart($hourCanTang);
            $monthPart = $getPart($monthCanTang);
            $taiChinh = round(($hourPart + $monthPart + $vTheTai) / 3, 2);
            $vGioThienCan = $valueByName[$chuTinhGio] ?? 0;
            $phatTrienBanThan = round(($vGioThienCan + $hourPart + $vHuynhDe) / 3, 2);
            $yearPart = $getPart($yearCanTang);
            $ketNoiXaHoi = round(($vNam + $yearPart + $vTuTon) / 3, 2);
            $dayPart = $getPart($dayCanTang);
            $tinhCamNam = round(($dayPart + $vTheTai) / 2, 2);
            $tinhCamNu = round(($dayPart + $vQuanQuy) / 2, 2);
            $vTyKien = (int) ($valueByName['Tỷ Kiên'] ?? 0);
            $sucKhoe = round(($vTyKien + $vPhuMau) / 2, 2);
            return [
                'su_nghiep' => $suNghiep,
                'tai_chinh' => $taiChinh,
                'phat_trien_ban_than' => $phatTrienBanThan,
                'ket_noi_xa_hoi' => $ketNoiXaHoi,
                'tinh_cam_nam' => $tinhCamNam,
                'tinh_cam_nu' => $tinhCamNu,
                'suc_khoe' => $sucKhoe,
            ];
        };

        $natal = $compute($natalByName);
        $annual = $compute($annualByName);

        $keys = ['su_nghiep', 'tai_chinh', 'phat_trien_ban_than', 'ket_noi_xa_hoi', 'tinh_cam_nam', 'tinh_cam_nu', 'suc_khoe'];
        $chenhLechPhanTram = [];
        foreach ($keys as $key) {
            $vNatal = $natal[$key] ?? 0;
            $vAnnual = $annual[$key] ?? 0;
            if ($vNatal != 0) {
                $value = round(($vAnnual - $vNatal) / $vNatal * 100, 2);
            } else {
                $value = $vAnnual > 0 ? 100.0 : 0.0;
            }
            $trangThai = $value > 0 ? 'tăng' : ($value < 0 ? 'giảm' : 'không đổi');
            $chenhLechPhanTram[] = [
                'key' => $key,
                'trang_thai' => $trangThai,
                'value' => abs($value),
            ];
        }

        return [
            'natal' => $natal,
            'annual' => $annual,
            'chenh_lech_phan_tram' => $chenhLechPhanTram,
        ];
    }

    /**
     * Lấy giải pháp cân bằng dựa trên hỷ thần
     * @param string $hyThanNguHanh Chuỗi các ngũ hành hỷ thần, phân tách bởi dấu phẩy (VD: "Mộc, Hỏa")
     * @return array Mảng các giải pháp cân bằng tương ứng
     */
    protected static function getGiaiPhapCanBang(string $hyThanNguHanh): array
    {
        if (empty($hyThanNguHanh)) {
            return [];
        }

        // Tách các ngũ hành
        $nguHanhList = array_map('trim', explode(',', $hyThanNguHanh));
        $result = array_filter(self::$giaiphapcanbang, function ($item) use ($nguHanhList) {
            return in_array($item['ngu_hanh'], $nguHanhList);
        });

        return array_values($result);
    }

    public static function getMenh($can)
    {
        // Xác định mệnh theo thiên can
        if (!$can) return '';
        $can = trim($can);
        if (in_array($can, ['Giáp', 'Ất'], true)) return 'Mộc';
        if (in_array($can, ['Bính', 'Đinh'], true)) return 'Hỏa';
        if (in_array($can, ['Canh', 'Tân'], true)) return 'Kim';
        if (in_array($can, ['Mậu', 'Kỷ'], true)) return 'Thổ';
        return 'Thủy';
    }
    public static function getMenhThuong($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Thổ';
        if ($menh == 'Kim') return 'Thủy';
        if ($menh == 'Thủy') return 'Mộc';
        if ($menh == 'Mộc') return 'Hỏa';
        if ($menh == 'Thổ') return 'Kim';
        return null;
    }

    public static function bikhac($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Kim';
        if ($menh == 'Kim') return 'Mộc';
        if ($menh == 'Thủy') return 'Hỏa';
        if ($menh == 'Mộc') return 'Thổ';
        if ($menh == 'Thổ') return 'Thủy';
        return null;
    }

    public static function menhKhac($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Thủy';
        if ($menh == 'Kim') return 'Hỏa';
        if ($menh == 'Thủy') return 'Thổ';
        if ($menh == 'Mộc') return 'Kim';
        if ($menh == 'Thổ') return 'Mộc';
        return null;
    }

    public static function sinhMenh($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Thổ';
        if ($menh == 'Kim') return 'Thủy';
        if ($menh == 'Thủy') return 'Mộc';
        if ($menh == 'Mộc') return 'Hỏa';
        if ($menh == 'Thổ') return 'Kim';
        return null;
    }

    /**
     * Tạo mảng vòng tương sinh (creation cycle) bắt đầu từ ngũ hành đầu tiên
     * Ví dụ: arrTuongSinh('Hỏa') => ['Hỏa' => 0, 'Thổ' => 0, 'Kim' => 0, 'Thủy' => 0, 'Mộc' => 0]
     * 
     * @param string $firstElement Ngũ hành đầu tiên (Mộc, Hỏa, Thổ, Kim, Thủy)
     * @return array Mảng các ngũ hành theo vòng tương sinh với giá trị 0
     */
    public static function arrTuongSinhNguHanh($firstElement)
    {
        // Định nghĩa vòng tương sinh: Mộc sinh Hỏa, Hỏa sinh Thổ, Thổ sinh Kim, Kim sinh Thủy, Thủy sinh Mộc
        $tuongSinhCycle = [
            'moc' => 'hoa',
            'hoa' => 'tho',
            'tho' => 'kim',
            'kim' => 'thuy',
            'thuy' => 'moc'
        ];

        $firstElement = trim($firstElement);

        // Kiểm tra ngũ hành hợp lệ
        if (!isset($tuongSinhCycle[$firstElement])) {
            return [];
        }

        $result = [];
        $current = $firstElement;

        // Duyệt qua 5 ngũ hành theo vòng tương sinh
        for ($i = 0; $i < 5; $i++) {
            switch ($i) {
                case 0:
                    $iKey = 'natal_a';
                    break;
                case 1:
                    $iKey = 'natal_b';
                    break;
                case 2:
                    $iKey = 'natal_c';
                    break;
                case 3:
                    $iKey = 'natal_d';
                    break;
                case 4:
                    $iKey = 'natal_e';
                    break;
            }
            $result[$iKey] = $current;
            $current = $tuongSinhCycle[$current];
        }

        return self::rotateArray($result, 3);
    }

    public static function arrTuongSinhNienVan($firstElement)
    {
        $tuongSinhCycle = [
            'moc' => 'hoa',
            'hoa' => 'tho',
            'tho' => 'kim',
            'kim' => 'thuy',
            'thuy' => 'moc'
        ];
        $firstElement = trim($firstElement);

        // Kiểm tra ngũ hành hợp lệ
        if (!isset($tuongSinhCycle[$firstElement])) {
            return [];
        }

        $result = [];
        $current = $firstElement;

        // Duyệt qua 5 ngũ hành theo vòng tương sinh
        for ($i = 0; $i < 5; $i++) {
            switch ($i) {
                case 0:
                    $iKey = 'annual_a';
                    break;
                case 1:
                    $iKey = 'annual_b';
                    break;
                case 2:
                    $iKey = 'annual_c';
                    break;
                case 3:
                    $iKey = 'annual_d';
                    break;
                case 4:
                    $iKey = 'annual_e';
                    break;
            }
            $result[$iKey] = $current;
            $current = $tuongSinhCycle[$current];
        }

        return self::rotateArray($result, 3);
    }

    private static function rotateArray($array, $steps)
    {
        $n = count($array);
        if ($n === 0) return $array;

        $steps = $steps % $n;

        if ($steps == 0) return $array;

        $part2 = array_slice($array, -$steps);
        $part1 = array_slice($array, 0, $n - $steps);

        return array_merge($part2, $part1);
    }



    protected static function getChuTinhWebVN(string $dayStem, string $otherStem): string
    {
        $chuTinhTable = [
            'Giáp' => [
                'Giáp' => 'Tỷ Kiên',
                'Ất'   => 'Kiếp Tài',
                'Bính' => 'Thực Thần',
                'Đinh' => 'Thương Quan',
                'Mậu'  => 'Thiên Tài',
                'Kỷ'   => 'Chính Tài',
                'Canh' => 'Thất Sát',
                'Tân'  => 'Chính Quan',
                'Nhâm' => 'Thiên Ấn',
                'Quý'  => 'Chính Ấn',
            ],
            'Ất' => [
                'Giáp' => 'Kiếp Tài',
                'Ất'   => 'Tỷ Kiên',
                'Bính' => 'Thương Quan',
                'Đinh' => 'Thực Thần',
                'Mậu'  => 'Chính Tài',
                'Kỷ'   => 'Thiên Tài',
                'Canh' => 'Chính Quan',
                'Tân'  => 'Thất Sát',
                'Nhâm' => 'Chính Ấn',
                'Quý'  => 'Thiên Ấn',
            ],
            'Bính' => [
                'Giáp' => 'Thiên Ấn',
                'Ất'   => 'Chính Ấn',
                'Bính' => 'Tỷ Kiên',
                'Đinh' => 'Kiếp Tài',
                'Mậu'  => 'Thực Thần',
                'Kỷ'   => 'Thương Quan',
                'Canh' => 'Thiên Tài',
                'Tân'  => 'Chính Tài',
                'Nhâm' => 'Thất Sát',
                'Quý'  => 'Chính Quan',
            ],
            'Đinh' => [
                'Giáp' => 'Chính Ấn',
                'Ất'   => 'Thiên Ấn',
                'Bính' => 'Kiếp Tài',
                'Đinh' => 'Tỷ Kiên',
                'Mậu'  => 'Thương Quan',
                'Kỷ'   => 'Thực Thần',
                'Canh' => 'Chính Tài',
                'Tân'  => 'Thiên Tài',
                'Nhâm' => 'Chính Quan',
                'Quý'  => 'Thất Sát',
            ],
            'Mậu' => [
                'Giáp' => 'Thất Sát',
                'Ất'   => 'Chính Quan',
                'Bính' => 'Thiên Ấn',
                'Đinh' => 'Chính Ấn',
                'Mậu'  => 'Tỷ Kiên',
                'Kỷ'   => 'Kiếp Tài',
                'Canh' => 'Thực Thần',
                'Tân'  => 'Thương Quan',
                'Nhâm' => 'Thiên Tài',
                'Quý'  => 'Chính Tài',
            ],
            'Kỷ' => [
                'Giáp' => 'Chính Quan',
                'Ất'   => 'Thất Sát',
                'Bính' => 'Chính Ấn',
                'Đinh' => 'Thiên Ấn',
                'Mậu'  => 'Kiếp Tài',
                'Kỷ'   => 'Tỷ Kiên',
                'Canh' => 'Thương Quan',
                'Tân'  => 'Thực Thần',
                'Nhâm' => 'Chính Tài',
                'Quý'  => 'Thiên Tài',
            ],
            'Canh' => [
                'Giáp' => 'Thiên Tài',
                'Ất'   => 'Chính Tài',
                'Bính' => 'Thất Sát',
                'Đinh' => 'Chính Quan',
                'Mậu'  => 'Thiên Ấn',
                'Kỷ'   => 'Chính Ấn',
                'Canh' => 'Tỷ Kiên',
                'Tân'  => 'Kiếp Tài',
                'Nhâm' => 'Thực Thần',
                'Quý'  => 'Thương Quan',
            ],
            'Tân' => [
                'Giáp' => 'Chính Tài',
                'Ất'   => 'Thiên Tài',
                'Bính' => 'Chính Quan',
                'Đinh' => 'Thất Sát',
                'Mậu'  => 'Chính Ấn',
                'Kỷ'   => 'Thiên Ấn',
                'Canh' => 'Kiếp Tài',
                'Tân'  => 'Tỷ Kiên',
                'Nhâm' => 'Thương Quan',
                'Quý'  => 'Thực Thần',
            ],
            'Nhâm' => [
                'Giáp' => 'Thực Thần',
                'Ất'   => 'Thương Quan',
                'Bính' => 'Thiên Tài',
                'Đinh' => 'Chính Tài',
                'Mậu'  => 'Thất Sát',
                'Kỷ'   => 'Chính Quan',
                'Canh' => 'Thiên Ấn',
                'Tân'  => 'Chính Ấn',
                'Nhâm' => 'Tỷ Kiên',
                'Quý'  => 'Kiếp Tài',
            ],
            'Quý' => [
                'Giáp' => 'Thương Quan',
                'Ất'   => 'Thực Thần',
                'Bính' => 'Chính Tài',
                'Đinh' => 'Thiên Tài',
                'Mậu'  => 'Chính Quan',
                'Kỷ'   => 'Thất Sát',
                'Canh' => 'Chính Ấn',
                'Tân'  => 'Thiên Ấn',
                'Nhâm' => 'Kiếp Tài',
                'Quý'  => 'Tỷ Kiên',
            ],
        ];

        return $chuTinhTable[$dayStem][$otherStem] ?? '';
    }

    // Ngũ hành của Thiên Can
    protected static $elementMap = [
        'Giáp' => 'Mộc',
        'Ất'   => 'Mộc',
        'Bính' => 'Hỏa',
        'Đinh' => 'Hỏa',
        'Mậu'  => 'Thổ',
        'Kỷ'   => 'Thổ',
        'Canh' => 'Kim',
        'Tân'  => 'Kim',
        'Nhâm' => 'Thủy',
        'Quý'  => 'Thủy',
    ];

    public static function getMenhDiaChi($chi)
    {
        if ($chi == 'Tý' || $chi == 'Hợi')
            return 'Thủy';
        if ($chi == 'Sửu' || $chi == 'Thìn' || $chi == 'Mùi' || $chi == 'Tuất')
            return 'Thổ';
        if ($chi == 'Dần' || $chi == 'Mão')
            return 'Mộc';
        if ($chi == 'Tỵ' || $chi == 'Ngọ')
            return 'Hỏa';
        if ($chi == 'Thân' || $chi == 'Dậu')
            return 'Kim';
    }

    public static function getMenhThienCan($can)
    {
        if ($can == 'Giáp' || $can == 'Ất')
            return 'Mộc';
        if ($can == 'Bính' || $can == 'Đinh')
            return 'Hỏa';
        if ($can == 'Mậu' || $can == 'Kỷ')
            return 'Thổ';
        if ($can == 'Canh' || $can == 'Tân')
            return 'Kim';
        if ($can == 'Nhâm' || $can == 'Quý')
            return 'Thủy';
    }

    /**
     * Lấy danh sách sim hợp mệnh từ API
     * @param string $menh Mệnh ngũ hành (Kim, Mộc, Thủy, Hỏa, Thổ)
     * @return array Danh sách sim
     */
    protected static function getSimsByMenh(string $menh): array
    {
        if (empty($menh)) {
            return [];
        }

        try {
            // Chuẩn hóa mệnh: bỏ dấu, viết thường, không khoảng trắng, theo yêu cầu API
            // Ví dụ: "Thủy" -> "thuy", "Mộc" -> "moc"
            $menhNormalized = Str::slug(mb_strtolower($menh, 'UTF-8'), '');
            $response = Http::timeout(10)->get('https://simstk.vuongkimbao.com/api/sims', [
                'page' => 1,
                'limit' => 20,
                'api_key' => 'thIUpx957gQ7mzCutmgGl0yFSpSgOOHy',
                'menh' => $menhNormalized,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success'] && isset($data['data'])) {
                    return $data['data'];
                }
            }
        } catch (\Exception $e) {
            // Log error nếu cần
            Log::error('Error fetching sims: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Gọi API bát tự phong thủy để lấy thông tin bát tự
     * 
     * @param string $fullName Họ và tên
     * @param string $gender Giới tính (male/female)
     * @param string $birthDate Ngày sinh (Y-m-d format, e.g., 1997-10-12)
     * @param int $birthTimeHour Giờ sinh (0-23)
     * @param int $birthTimeMinute Phút sinh (0-59)
     * @return array|null Dữ liệu trả về từ API hoặc null nếu có lỗi
     */
    public static function callBatTuChanMinhApi(
        string $fullName,
        string $gender,
        string $birthDate,
        int $birthTimeHour,
        int $birthTimeMinute,
        $isBirthTimeUnknown
    ): ?array {
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer wtg65oozKb7vGcvtp15XeyA7FfbKGwiATxVFPKt5XutDD7uU5yrogRmspxUbdVoq',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->asForm()
                ->post('https://battu.chanminhphongthuy.com/app/index3', [
                    'full_name' => $fullName,
                    'gender' => $gender == 'female' ? '0' : '1',
                    'birth_date' => $birthDate,
                    'birth_time_hour' => $isBirthTimeUnknown == '0' ?  $birthTimeHour : '0',
                    'birth_time_minute' => $isBirthTimeUnknown == '0' ? $birthTimeMinute : '0',
                    'is_birth_time_unknown' => $isBirthTimeUnknown
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Log lỗi nếu request không thành công
            Log::error('API Bát Tự error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error calling Bat Tu API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo biểu đồ ngũ hành với thông tin lục thân
     * @param array $nguHanhDong Mảng phần trăm ngũ hành bản mệnh
     * @param array $lucThan Mảng lục thân tương ứng với từng ngũ hành
     * @param array $phanTramNienVan Mảng phần trăm ngũ hành niên vận
     * @return array Mảng chứa index, tên ngũ hành, điểm bản mệnh, điểm niên vận và lục thân
     */
    protected static function createBieuDoNguHanh(array $nguHanhDong, array $lucThan, array $phanTramNienVan = []): array
    {
        // Map tên ngũ hành từ key sang tiếng Việt
        $nguHanhNames = [
            'kim' => 'Kim',
            'moc' => 'Mộc',
            'thuy' => 'Thủy',
            'hoa' => 'Hỏa',
            'tho' => 'Thổ'
        ];

        $result = [];
        $index = 0;

        // Duyệt qua từng ngũ hành theo thứ tự của ngu_hanh_dong
        foreach ($nguHanhDong as $key => $diem) {
            $result[] = [
                'index' => $index,
                'ten_ngu_hanh' => $nguHanhNames[$key] ?? ucfirst($key),
                'diem_ngu_hanh' => $diem,
                'diem_nien_van' => $phanTramNienVan[$key] ?? 0,
                'luc_than' => $lucThan[$key] ?? ''
            ];
            $index++;
        }

        return $result;
    }
}
