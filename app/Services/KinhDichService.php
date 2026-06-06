<?php

namespace App\Services;

use App\Models\Que64;
use App\Models\Sim;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KinhDichService
{
    // Bảng tra cứu quẻ biến được load từ file
    protected $queBienLookup = [
        'Thuần Càn1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 5,
            'ten' => 'Thiên Phong Cấu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Càn2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 3,
            'ten' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Càn3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 2,
            'ten' => 'Thiên Trạch Lý',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Càn4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 1,
            'ten' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Càn5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 1,
            'ten' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Càn6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 1,
            'ten' => 'Trạch Thiên Quải',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Trạch Lý1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 6,
            'ten' => 'Thiên Thủy Tụng',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Trạch Lý2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 4,
            'ten' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Trạch Lý3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 1,
            'ten' => 'Thuần Càn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Trạch Lý4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 2,
            'ten' => 'Phong Trạch Trung Phu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Trạch Lý5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 2,
            'ten' => 'Hỏa Trạch Khuê',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Trạch Lý6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 2,
            'ten' => 'Thuần Đoài',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Hỏa Đồng Nhân1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 7,
            'ten' => 'Thiên Sơn Độn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Hỏa Đồng Nhân2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 1,
            'ten' => 'Thuần Càn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Hỏa Đồng Nhân3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 4,
            'ten' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Hỏa Đồng Nhân4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 3,
            'ten' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Hỏa Đồng Nhân5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 3,
            'ten' => 'Thuần Ly',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Hỏa Đồng Nhân6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 3,
            'ten' => 'Trạch Hỏa Cách',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Lôi Vô Vọng1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 8,
            'ten' => 'Thiên Địa Bĩ',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Lôi Vô Vọng2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 2,
            'ten' => 'Thiên Trạch Lý',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Lôi Vô Vọng3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 3,
            'ten' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Lôi Vô Vọng4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 4,
            'ten' => 'Phong Lôi Ích',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Lôi Vô Vọng5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 4,
            'ten' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Lôi Vô Vọng6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 4,
            'ten' => 'Trạch Lôi Tùy',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Phong Cấu1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 1,
            'ten' => 'Thuần Càn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Phong Cấu2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 7,
            'ten' => 'Thiên Sơn Độn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Phong Cấu3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 6,
            'ten' => 'Thiên Thủy Tụng',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Phong Cấu4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 5,
            'ten' => 'Thuần Tốn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Phong Cấu5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 5,
            'ten' => 'Hỏa Phong Đỉnh',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Phong Cấu6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 5,
            'ten' => 'Trạch Phong Đại Quá',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Thủy Tụng1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 2,
            'ten' => 'Thiên Trạch Lý',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Thủy Tụng2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 8,
            'ten' => 'Thiên Địa Bĩ',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Thủy Tụng3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 5,
            'ten' => 'Thiên Phong Cấu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Thủy Tụng4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 6,
            'ten' => 'Phong Thủy Hoán',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Thủy Tụng5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 6,
            'ten' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Thủy Tụng6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 6,
            'ten' => 'Trạch Thủy Khốn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Sơn Độn1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 3,
            'ten' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Sơn Độn2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 5,
            'ten' => 'Thiên Phong Cấu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Sơn Độn3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 8,
            'ten' => 'Thiên Địa Bĩ',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Sơn Độn4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 7,
            'ten' => 'Phong Sơn Tiệm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Sơn Độn5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 7,
            'ten' => 'Hỏa Sơn Lữ',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Sơn Độn6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 7,
            'ten' => 'Trạch Sơn Hàm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Địa Bĩ1' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 4,
            'ten' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thiên Địa Bĩ2' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 6,
            'ten' => 'Thiên Thủy Tụng',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Địa Bĩ3' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 7,
            'ten' => 'Thiên Sơn Độn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Địa Bĩ4' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 8,
            'ten' => 'Phong Địa Quan',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thiên Địa Bĩ5' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 8,
            'ten' => 'Hỏa Địa Tấn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thiên Địa Bĩ6' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 8,
            'ten' => 'Trạch Địa Tụy',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thiên Quải1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 5,
            'ten' => 'Trạch Phong Đại Quá',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thiên Quải2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 3,
            'ten' => 'Trạch Hỏa Cách',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thiên Quải3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 2,
            'ten' => 'Thuần Đoài',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thiên Quải4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 1,
            'ten' => 'Thủy Thiên Nhu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Thiên Quải5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 1,
            'ten' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Thiên Quải6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 1,
            'ten' => 'Thuần Càn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Đoài1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 6,
            'ten' => 'Trạch Thủy Khốn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Đoài2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 4,
            'ten' => 'Trạch Lôi Tùy',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Đoài3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 1,
            'ten' => 'Trạch Thiên Quải',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Đoài4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Thủy Trạch Tiết',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Đoài5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 2,
            'ten' => 'Lôi Trạch Quy Muội',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Đoài6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 2,
            'ten' => 'Thiên Trạch Lý',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Hỏa Cách1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 7,
            'ten' => 'Trạch Sơn Hàm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Hỏa Cách2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 1,
            'ten' => 'Trạch Thiên Quải',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Hỏa Cách3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 4,
            'ten' => 'Trạch Lôi Tùy',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Hỏa Cách4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 3,
            'ten' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Hỏa Cách5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 3,
            'ten' => 'Lôi Hỏa Phong',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Hỏa Cách6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 3,
            'ten' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Lôi Tùy1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 8,
            'ten' => 'Trạch Địa Tụy',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Lôi Tùy2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 2,
            'ten' => 'Thuần Đoài',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Lôi Tùy3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 3,
            'ten' => 'Trạch Hỏa Cách',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Lôi Tùy4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 4,
            'ten' => 'Thủy Lôi Truân',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Lôi Tùy5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 4,
            'ten' => 'Thuần Chấn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Lôi Tùy6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 4,
            'ten' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Phong Đại Quá1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 1,
            'ten' => 'Trạch Thiên Quải',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Phong Đại Quá2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 7,
            'ten' => 'Trạch Sơn Hàm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Phong Đại Quá3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 6,
            'ten' => 'Trạch Thủy Khốn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Phong Đại Quá4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 5,
            'ten' => 'Thủy Phong Tỉnh',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Phong Đại Quá5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 5,
            'ten' => 'Lôi Phong Hằng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Phong Đại Quá6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 5,
            'ten' => 'Thiên Phong Cấu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Thủy Khốn1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 2,
            'ten' => 'Thuần Đoài',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thủy Khốn2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 8,
            'ten' => 'Trạch Địa Tụy',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thủy Khốn3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 5,
            'ten' => 'Trạch Phong Đại Quá',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Thủy Khốn4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 6,
            'ten' => 'Thuần Khảm',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Thủy Khốn5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 2,
            'ten' => 'Lôi Thủy Giải',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Thủy Khốn6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 6,
            'ten' => 'Thiên Thủy Tụng',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Sơn Hàm1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 3,
            'ten' => 'Trạch Hỏa Cách',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Sơn Hàm2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 5,
            'ten' => 'Trạch Phong Đại Quá',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Sơn Hàm3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 8,
            'ten' => 'Trạch Địa Tụy',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Sơn Hàm4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 7,
            'ten' => 'Thủy Sơn Kiển',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Sơn Hàm5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 7,
            'ten' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Sơn Hàm6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 7,
            'ten' => 'Thiên Sơn Độn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Địa Tụy1' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 4,
            'ten' => 'Trạch Lôi Tùy',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Địa Tụy2' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 6,
            'ten' => 'Trạch Thủy Khốn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Trạch Địa Tụy3' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 7,
            'ten' => 'Trạch Sơn Hàm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Địa Tụy4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 8,
            'ten' => 'Thủy Địa Tỷ',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Trạch Địa Tụy5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 8,
            'ten' => 'Lôi Địa Dự',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Trạch Địa Tụy6' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 8,
            'ten' => 'Thiên Địa Bĩ',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Thiên Đại Hữu1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 5,
            'ten' => 'Hỏa Phong Đỉnh',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Thiên Đại Hữu2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 3,
            'ten' => 'Thuần Ly',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Thiên Đại Hữu3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 2,
            'ten' => 'Hỏa Trạch Khuê',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Thiên Đại Hữu4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 1,
            'ten' => 'Sơn Thiên Đại Súc',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Thiên Đại Hữu5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 1,
            'ten' => 'Thuần Càn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Thiên Đại Hữu6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 1,
            'ten' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Trạch Khuê1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 6,
            'ten' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Trạch Khuê2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 4,
            'ten' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Trạch Khuê3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 1,
            'ten' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Trạch Khuê4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 2,
            'ten' => 'Sơn Trạch Tổn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Trạch Khuê5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 2,
            'ten' => 'Thiên Trạch Lý',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Trạch Khuê6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 2,
            'ten' => 'Lôi Trạch Quy Muội',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Ly1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 7,
            'ten' => 'Hỏa Sơn Lữ',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Ly2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 1,
            'ten' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Ly3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 4,
            'ten' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Ly4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 3,
            'ten' => 'Sơn Hỏa Bí',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Ly5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 3,
            'ten' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Ly6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 3,
            'ten' => 'Lôi Hỏa Phong',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Lôi Phệ Hạp1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 8,
            'ten' => 'Hỏa Địa Tấn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Lôi Phệ Hạp2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 2,
            'ten' => 'Hỏa Trạch Khuê',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Lôi Phệ Hạp3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 3,
            'ten' => 'Thuần Ly',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Lôi Phệ Hạp4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 4,
            'ten' => 'Sơn Lôi Di',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Lôi Phệ Hạp5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 4,
            'ten' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Lôi Phệ Hạp6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 4,
            'ten' => 'Thuần Chấn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Phong Đỉnh1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 1,
            'ten' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Phong Đỉnh2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 7,
            'ten' => 'Hỏa Sơn Lữ',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Phong Đỉnh3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 6,
            'ten' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Phong Đỉnh4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 5,
            'ten' => 'Sơn Phong Cổ',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Phong Đỉnh5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 5,
            'ten' => 'Thiên Phong Cấu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Phong Đỉnh6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 5,
            'ten' => 'Lôi Phong Hằng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Thủy Vị Tế1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 2,
            'ten' => 'Hỏa Trạch Khuê',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Thủy Vị Tế2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 8,
            'ten' => 'Hỏa Địa Tấn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Thủy Vị Tế3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 5,
            'ten' => 'Hỏa Phong Đỉnh',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Thủy Vị Tế4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 6,
            'ten' => 'Sơn Thủy Mông',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Thủy Vị Tế5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 6,
            'ten' => 'Thiên Thủy Tụng',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Thủy Vị Tế6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 2,
            'ten' => 'Lôi Thủy Giải',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Sơn Lữ1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 3,
            'ten' => 'Thuần Ly',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Sơn Lữ2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 5,
            'ten' => 'Hỏa Phong Đỉnh',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Sơn Lữ3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 8,
            'ten' => 'Hỏa Địa Tấn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Hỏa Sơn Lữ4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 7,
            'ten' => 'Thuần Cấn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Sơn Lữ5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 7,
            'ten' => 'Thiên Sơn Độn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Sơn Lữ6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 7,
            'ten' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Địa Tấn1' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 4,
            'ten' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Địa Tấn2' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 6,
            'ten' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Địa Tấn3' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 7,
            'ten' => 'Hỏa Sơn Lữ',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Hỏa Địa Tấn4' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 8,
            'ten' => 'Sơn Địa Bác',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Địa Tấn5' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 8,
            'ten' => 'Thiên Địa Bĩ',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Hỏa Địa Tấn6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 8,
            'ten' => 'Lôi Địa Dự',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thiên Đại Tráng1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 5,
            'ten' => 'Lôi Phong Hằng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thiên Đại Tráng2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 3,
            'ten' => 'Lôi Hỏa Phong',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thiên Đại Tráng3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 2,
            'ten' => 'Lôi Trạch Quy Muội',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Thiên Đại Tráng4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 1,
            'ten' => 'Địa Thiên Thái',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thiên Đại Tráng5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 1,
            'ten' => 'Trạch Thiên Quải',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Thiên Đại Tráng6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 1,
            'ten' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Trạch Quy Muội1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 2,
            'ten' => 'Lôi Thủy Giải',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Trạch Quy Muội2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 4,
            'ten' => 'Thuần Chấn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Trạch Quy Muội3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 1,
            'ten' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Trạch Quy Muội4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 2,
            'ten' => 'Địa Trạch Lâm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Trạch Quy Muội5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 2,
            'ten' => 'Thuần Đoài',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Trạch Quy Muội6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 2,
            'ten' => 'Hỏa Trạch Khuê',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Hỏa Phong1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 7,
            'ten' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Hỏa Phong2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 1,
            'ten' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Hỏa Phong3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 4,
            'ten' => 'Thuần Chấn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Hỏa Phong4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 3,
            'ten' => 'Địa Hỏa Minh Di',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Hỏa Phong5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 3,
            'ten' => 'Trạch Hỏa Cách',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Hỏa Phong6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 3,
            'ten' => 'Thuần Ly',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Chấn1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 8,
            'ten' => 'Lôi Địa Dự',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Chấn2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 2,
            'ten' => 'Lôi Trạch Quy Muội',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Chấn3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 3,
            'ten' => 'Lôi Hỏa Phong',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Chấn4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 4,
            'ten' => 'Địa Lôi Phục',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Chấn5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 4,
            'ten' => 'Trạch Lôi Tùy',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Chấn6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 4,
            'ten' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Phong Hằng1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 1,
            'ten' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Phong Hằng2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 7,
            'ten' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Phong Hằng3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Lôi Thủy Giải',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Phong Hằng4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 5,
            'ten' => 'Địa Phong Thăng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Phong Hằng5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 5,
            'ten' => 'Trạch Phong Đại Quá',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Phong Hằng6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 5,
            'ten' => 'Hỏa Phong Đỉnh',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thủy Giải1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 2,
            'ten' => 'Lôi Trạch Quy Muội',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Thủy Giải2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 8,
            'ten' => 'Lôi Địa Dự',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thủy Giải3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 5,
            'ten' => 'Lôi Phong Hằng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thủy Giải4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 6,
            'ten' => 'Địa Thủy Sư',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Thủy Giải5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 6,
            'ten' => 'Trạch Thủy Khốn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Thủy Giải6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 6,
            'ten' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Sơn Tiểu Quá1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 3,
            'ten' => 'Lôi Hỏa Phong',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Sơn Tiểu Quá2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 5,
            'ten' => 'Lôi Phong Hằng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Sơn Tiểu Quá3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 8,
            'ten' => 'Lôi Địa Dự',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Sơn Tiểu Quá4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 7,
            'ten' => 'Địa Sơn Khiêm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Sơn Tiểu Quá5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 7,
            'ten' => 'Trạch Sơn Hàm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Sơn Tiểu Quá6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 7,
            'ten' => 'Hỏa Sơn Lữ',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Địa Dự1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 4,
            'ten' => 'Thuần Chấn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Địa Dự2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Lôi Thủy Giải',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Lôi Địa Dự3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 7,
            'ten' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Lôi Địa Dự4' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 8,
            'ten' => 'Thuần Khôn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Địa Dự5' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 8,
            'ten' => 'Trạch Địa Tụy',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Lôi Địa Dự6' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 8,
            'ten' => 'Hỏa Địa Tấn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Thiên Tiểu Súc1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 5,
            'ten' => 'Thuần Tốn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Thiên Tiểu Súc2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 3,
            'ten' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Thiên Tiểu Súc3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 2,
            'ten' => 'Phong Trạch Trung Phu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Thiên Tiểu Súc4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 1,
            'ten' => 'Thuần Càn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Thiên Tiểu Súc5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 1,
            'ten' => 'Sơn Thiên Đại Súc',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Thiên Tiểu Súc6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 1,
            'ten' => 'Thủy Thiên Nhu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Trạch Trung Phu1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 6,
            'ten' => 'Phong Thủy Hoán',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Trạch Trung Phu2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 4,
            'ten' => 'Phong Lôi Ích',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Trạch Trung Phu3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 1,
            'ten' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Trạch Trung Phu4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 2,
            'ten' => 'Thiên Trạch Lý',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Trạch Trung Phu5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 2,
            'ten' => 'Sơn Trạch Tổn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Trạch Trung Phu6' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Thủy Trạch Tiết',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Hỏa Gia Nhân1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 7,
            'ten' => 'Phong Sơn Tiệm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Hỏa Gia Nhân2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 1,
            'ten' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Hỏa Gia Nhân3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 4,
            'ten' => 'Phong Lôi Ích',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Hỏa Gia Nhân4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 3,
            'ten' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Hỏa Gia Nhân5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 3,
            'ten' => 'Sơn Hỏa Bí',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Hỏa Gia Nhân6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 3,
            'ten' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Lôi Ích1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 8,
            'ten' => 'Phong Địa Quan',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Lôi Ích2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 2,
            'ten' => 'Phong Trạch Trung Phu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Lôi Ích3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 3,
            'ten' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Lôi Ích4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 4,
            'ten' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Lôi Ích5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 4,
            'ten' => 'Sơn Lôi Di',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Lôi Ích6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 4,
            'ten' => 'Thủy Lôi Truân',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Tốn1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 1,
            'ten' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Tốn2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 7,
            'ten' => 'Phong Sơn Tiệm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Tốn3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 6,
            'ten' => 'Phong Thủy Hoán',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Tốn4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 5,
            'ten' => 'Thiên Phong Cấu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Tốn5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 5,
            'ten' => 'Sơn Phong Cổ',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Tốn6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 5,
            'ten' => 'Thủy Phong Tỉnh',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Thủy Hoán1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 2,
            'ten' => 'Phong Trạch Trung Phu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Thủy Hoán2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 8,
            'ten' => 'Phong Địa Quan',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Thủy Hoán3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 5,
            'ten' => 'Thuần Tốn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Thủy Hoán4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 6,
            'ten' => 'Thiên Thủy Tụng',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Thủy Hoán5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 6,
            'ten' => 'Sơn Thủy Mông',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Thủy Hoán6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 6,
            'ten' => 'Thuần Khảm',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Sơn Tiệm1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 3,
            'ten' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Sơn Tiệm2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 5,
            'ten' => 'Thuần Tốn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Sơn Tiệm3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 8,
            'ten' => 'Phong Địa Quan',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Sơn Tiệm4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 7,
            'ten' => 'Thiên Sơn Độn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Sơn Tiệm5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 7,
            'ten' => 'Thuần Cấn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Sơn Tiệm6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 7,
            'ten' => 'Thủy Sơn Kiển',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Địa Quan1' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 4,
            'ten' => 'Phong Lôi Ích',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Phong Địa Quan2' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 6,
            'ten' => 'Phong Thủy Hoán',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Địa Quan3' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 7,
            'ten' => 'Phong Sơn Tiệm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Phong Địa Quan4' => [
            'so_du_thuong' => 1,
            'so_du_ha' => 8,
            'ten' => 'Thiên Địa Bĩ',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Địa Quan5' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 8,
            'ten' => 'Sơn Địa Bác',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Phong Địa Quan6' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 8,
            'ten' => 'Thủy Địa Tỷ',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Thiên Nhu1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 5,
            'ten' => 'Thủy Phong Tỉnh',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Thiên Nhu2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 3,
            'ten' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Thiên Nhu3' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Thủy Trạch Tiết',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Thiên Nhu4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 1,
            'ten' => 'Trạch Thiên Quải',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Thiên Nhu5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 1,
            'ten' => 'Địa Thiên Thái',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Thiên Nhu6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 1,
            'ten' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Trạch Tiết1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 6,
            'ten' => 'Thuần Khảm',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Trạch Tiết2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 4,
            'ten' => 'Thủy Lôi Truân',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Trạch Tiết3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 1,
            'ten' => 'Thủy Thiên Nhu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Trạch Tiết4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 2,
            'ten' => 'Thuần Đoài',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Trạch Tiết5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 2,
            'ten' => 'Địa Trạch Lâm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Trạch Tiết6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 2,
            'ten' => 'Phong Trạch Trung Phu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Hỏa Ký Tế1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 7,
            'ten' => 'Thủy Sơn Kiển',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Hỏa Ký Tế2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 1,
            'ten' => 'Thủy Thiên Nhu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Hỏa Ký Tế3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 4,
            'ten' => 'Thủy Lôi Truân',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Hỏa Ký Tế4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 3,
            'ten' => 'Trạch Hỏa Cách',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Hỏa Ký Tế5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 3,
            'ten' => 'Địa Hỏa Minh Di',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Hỏa Ký Tế6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 3,
            'ten' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Lôi Truân1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 8,
            'ten' => 'Thủy Địa Tỷ',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Lôi Truân2' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Thủy Trạch Tiết',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Lôi Truân3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 3,
            'ten' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Lôi Truân4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 4,
            'ten' => 'Trạch Lôi Tùy',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Lôi Truân5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 4,
            'ten' => 'Địa Lôi Phục',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Lôi Truân6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 4,
            'ten' => 'Phong Lôi Ích',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Phong Tỉnh1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 1,
            'ten' => 'Thủy Thiên Nhu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Phong Tỉnh2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 7,
            'ten' => 'Thủy Sơn Kiển',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Phong Tỉnh3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 6,
            'ten' => 'Thuần Khảm',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Phong Tỉnh4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 5,
            'ten' => 'Trạch Phong Đại Quá',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Phong Tỉnh5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 5,
            'ten' => 'Địa Phong Thăng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Phong Tỉnh6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 5,
            'ten' => 'Thuần Tốn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Khảm1' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Thủy Trạch Tiết',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Khảm2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 8,
            'ten' => 'Thủy Địa Tỷ',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Khảm3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 5,
            'ten' => 'Thủy Phong Tỉnh',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Khảm4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 6,
            'ten' => 'Trạch Thủy Khốn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Khảm5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 6,
            'ten' => 'Địa Thủy Sư',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Khảm6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 6,
            'ten' => 'Phong Thủy Hoán',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Sơn Kiển1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 3,
            'ten' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Sơn Kiển2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 5,
            'ten' => 'Thủy Phong Tỉnh',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Sơn Kiển3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 8,
            'ten' => 'Thủy Địa Tỷ',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Sơn Kiển4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 7,
            'ten' => 'Trạch Sơn Hàm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Thủy Sơn Kiển5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 7,
            'ten' => 'Địa Sơn Khiêm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Sơn Kiển6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 7,
            'ten' => 'Phong Sơn Tiệm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Địa Tỷ1' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 4,
            'ten' => 'Thủy Lôi Truân',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Địa Tỷ2' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 6,
            'ten' => 'Thuần Khảm',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Địa Tỷ3' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 7,
            'ten' => 'Thủy Sơn Kiển',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thủy Địa Tỷ4' => [
            'so_du_thuong' => 2,
            'so_du_ha' => 8,
            'ten' => 'Trạch Địa Tụy',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Địa Tỷ5' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 8,
            'ten' => 'Thuần Khôn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thủy Địa Tỷ6' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 8,
            'ten' => 'Phong Địa Quan',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Thiên Đại Súc1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 5,
            'ten' => 'Sơn Phong Cổ',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Thiên Đại Súc2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 3,
            'ten' => 'Sơn Hỏa Bí',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Thiên Đại Súc3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 2,
            'ten' => 'Sơn Trạch Tổn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Thiên Đại Súc4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 1,
            'ten' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Thiên Đại Súc5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 1,
            'ten' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Thiên Đại Súc6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 1,
            'ten' => 'Địa Thiên Thái',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Trạch Tổn1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 6,
            'ten' => 'Sơn Thủy Mông',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Trạch Tổn2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 4,
            'ten' => 'Sơn Lôi Di',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Trạch Tổn3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 1,
            'ten' => 'Sơn Thiên Đại Súc',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Trạch Tổn4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 2,
            'ten' => 'Hỏa Trạch Khuê',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Trạch Tổn5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 2,
            'ten' => 'Phong Trạch Trung Phu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Trạch Tổn6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 2,
            'ten' => 'Địa Trạch Lâm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Hỏa Bí1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 7,
            'ten' => 'Thuần Cấn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Hỏa Bí2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 1,
            'ten' => 'Sơn Thiên Đại Súc',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Hỏa Bí3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 4,
            'ten' => 'Sơn Lôi Di',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Hỏa Bí4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 3,
            'ten' => 'Thuần Ly',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Hỏa Bí5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 3,
            'ten' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Hỏa Bí6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 3,
            'ten' => 'Địa Hỏa Minh Di',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Lôi Di1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 8,
            'ten' => 'Sơn Địa Bác',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Lôi Di2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 2,
            'ten' => 'Sơn Trạch Tổn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Lôi Di3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 3,
            'ten' => 'Sơn Hỏa Bí',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Lôi Di4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 4,
            'ten' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Lôi Di5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 4,
            'ten' => 'Phong Lôi Ích',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Lôi Di6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 4,
            'ten' => 'Địa Lôi Phục',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Phong Cổ1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 1,
            'ten' => 'Sơn Thiên Đại Súc',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Phong Cổ2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 7,
            'ten' => 'Thuần Cấn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Phong Cổ3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 6,
            'ten' => 'Sơn Thủy Mông',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Phong Cổ4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 5,
            'ten' => 'Hỏa Phong Đỉnh',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Phong Cổ5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 5,
            'ten' => 'Thuần Tốn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Phong Cổ6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 5,
            'ten' => 'Địa Phong Thăng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Thủy Mông1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 2,
            'ten' => 'Sơn Trạch Tổn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Thủy Mông2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 8,
            'ten' => 'Sơn Địa Bác',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Thủy Mông3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 5,
            'ten' => 'Sơn Phong Cổ',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Thủy Mông4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 6,
            'ten' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Thủy Mông5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 6,
            'ten' => 'Phong Thủy Hoán',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Thủy Mông6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 6,
            'ten' => 'Địa Thủy Sư',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Cấn1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 3,
            'ten' => 'Sơn Hỏa Bí',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Cấn2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 5,
            'ten' => 'Sơn Phong Cổ',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Cấn3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 8,
            'ten' => 'Sơn Địa Bác',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Cấn4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 7,
            'ten' => 'Hỏa Sơn Lữ',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Cấn5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 7,
            'ten' => 'Phong Sơn Tiệm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Cấn6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 7,
            'ten' => 'Địa Sơn Khiêm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Địa Bác1' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 4,
            'ten' => 'Sơn Lôi Di',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Địa Bác2' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 6,
            'ten' => 'Sơn Thủy Mông',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Sơn Địa Bác3' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 7,
            'ten' => 'Thuần Cấn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Địa Bác4' => [
            'so_du_thuong' => 3,
            'so_du_ha' => 8,
            'ten' => 'Hỏa Địa Tấn',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tích Cực',
        ],
        'Sơn Địa Bác5' => [
            'so_du_thuong' => 5,
            'so_du_ha' => 8,
            'ten' => 'Phong Địa Quan',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Trung Bình',
        ],
        'Sơn Địa Bác6' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 8,
            'ten' => 'Thuần Khôn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Thiên Thái1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 5,
            'ten' => 'Địa Phong Thăng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Thiên Thái2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 3,
            'ten' => 'Địa Hỏa Minh Di',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Thiên Thái3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 2,
            'ten' => 'Địa Trạch Lâm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Thiên Thái4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 1,
            'ten' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Thiên Thái5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 1,
            'ten' => 'Thủy Thiên Nhu',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Thiên Thái6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 1,
            'ten' => 'Sơn Thiên Đại Súc',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Trạch Lâm1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 6,
            'ten' => 'Địa Thủy Sư',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Trạch Lâm2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 4,
            'ten' => 'Địa Lôi Phục',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Trạch Lâm3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 1,
            'ten' => 'Địa Thiên Thái',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Trạch Lâm4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 2,
            'ten' => 'Lôi Trạch Quy Muội',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Trạch Lâm5' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 6,
            'ten' => 'Thủy Trạch Tiết',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Trạch Lâm6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 2,
            'ten' => 'Sơn Trạch Tổn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Hỏa Minh Di1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 7,
            'ten' => 'Địa Sơn Khiêm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Hỏa Minh Di2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 1,
            'ten' => 'Địa Thiên Thái',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Hỏa Minh Di3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 4,
            'ten' => 'Địa Lôi Phục',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Hỏa Minh Di4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 3,
            'ten' => 'Lôi Hỏa Phong',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Hỏa Minh Di5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 3,
            'ten' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Hỏa Minh Di6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 3,
            'ten' => 'Sơn Hỏa Bí',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Lôi Phục1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 8,
            'ten' => 'Thuần Khôn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Lôi Phục2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 2,
            'ten' => 'Địa Trạch Lâm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Lôi Phục3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 3,
            'ten' => 'Địa Hỏa Minh Di',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Lôi Phục4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 4,
            'ten' => 'Thuần Chấn',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Lôi Phục5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 4,
            'ten' => 'Thủy Lôi Truân',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Lôi Phục6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 4,
            'ten' => 'Sơn Lôi Di',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Phong Thăng1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 1,
            'ten' => 'Địa Thiên Thái',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Phong Thăng2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 7,
            'ten' => 'Địa Sơn Khiêm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Phong Thăng3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 6,
            'ten' => 'Địa Thủy Sư',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Phong Thăng4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 5,
            'ten' => 'Lôi Phong Hằng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Phong Thăng5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 5,
            'ten' => 'Thủy Phong Tỉnh',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Phong Thăng6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 5,
            'ten' => 'Sơn Phong Cổ',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Thủy Sư1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 2,
            'ten' => 'Địa Trạch Lâm',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Thủy Sư2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 8,
            'ten' => 'Thuần Khôn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Thủy Sư3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 5,
            'ten' => 'Địa Phong Thăng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Thủy Sư4' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 2,
            'ten' => 'Lôi Thủy Giải',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Địa Thủy Sư5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 6,
            'ten' => 'Thuần Khảm',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Thủy Sư6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 6,
            'ten' => 'Sơn Thủy Mông',
            'ngu_hanh' => 'Hỏa',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Sơn Khiêm1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 3,
            'ten' => 'Địa Hỏa Minh Di',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Sơn Khiêm2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 5,
            'ten' => 'Địa Phong Thăng',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Sơn Khiêm3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 8,
            'ten' => 'Thuần Khôn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Địa Sơn Khiêm4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 7,
            'ten' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Sơn Khiêm5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 7,
            'ten' => 'Thủy Sơn Kiển',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Địa Sơn Khiêm6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 7,
            'ten' => 'Thuần Cấn',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Khôn1' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 4,
            'ten' => 'Địa Lôi Phục',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Khôn2' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 6,
            'ten' => 'Địa Thủy Sư',
            'ngu_hanh' => 'Thủy',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Khôn3' => [
            'so_du_thuong' => 8,
            'so_du_ha' => 7,
            'ten' => 'Địa Sơn Khiêm',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
        'Thuần Khôn4' => [
            'so_du_thuong' => 4,
            'so_du_ha' => 8,
            'ten' => 'Lôi Địa Dự',
            'ngu_hanh' => 'Mộc',
            'phan_loai' => 'Tích Cực',
        ],
        'Thuần Khôn5' => [
            'so_du_thuong' => 6,
            'so_du_ha' => 8,
            'ten' => 'Thủy Địa Tỷ',
            'ngu_hanh' => 'Thổ',
            'phan_loai' => 'Trung Bình',
        ],
        'Thuần Khôn6' => [
            'so_du_thuong' => 7,
            'so_du_ha' => 8,
            'ten' => 'Sơn Địa Bác',
            'ngu_hanh' => 'Kim',
            'phan_loai' => 'Tiêu Cực',
        ],
    ];

    // Mapping số dư (1-8) sang 8 quẻ cơ bản
    protected $soDuToQue = [
        1 => 'Càn',
        2 => 'Đoài',
        3 => 'Ly',
        4 => 'Chấn',
        5 => 'Tốn',
        6 => 'Khảm',
        7 => 'Cấn',
        8 => 'Khôn',
    ];

    // Mapping quẻ cơ bản sang số dư
    protected $queToSoDu = [
        'Càn' => 1,
        'Đoài' => 2,
        'Ly' => 3,
        'Chấn' => 4,
        'Tốn' => 5,
        'Khảm' => 6,
        'Cấn' => 7,
        'Khôn' => 8,
    ];

    // Mapping quẻ sang Ngũ Hành
    protected $queToNguHanh = [
        'Càn' => 'Kim',
        'Đoài' => 'Kim',
        'Ly' => 'Hỏa',
        'Chấn' => 'Mộc',
        'Tốn' => 'Mộc',
        'Khảm' => 'Thủy',
        'Cấn' => 'Thổ',
        'Khôn' => 'Thổ',
    ];

    // Dữ liệu 64 quẻ (theo thứ tự STT trong sheet)
    protected $data64Que = [
        ['gia_dinh' => 'Càn cung', 'ten' => 'Thuần Càn', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 1, 'so_du_ha' => 1, 'que_thuong' => 'Càn', 'que_ha' => 'Càn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Hỏa Thiên Đại Hữu', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 3, 'so_du_ha' => 1, 'que_thuong' => 'Ly', 'que_ha' => 'Càn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Thiên Phong Cấu', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 1, 'so_du_ha' => 5, 'que_thuong' => 'Càn', 'que_ha' => 'Tốn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Thiên Sơn Độn', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 1, 'so_du_ha' => 7, 'que_thuong' => 'Càn', 'que_ha' => 'Cấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Thiên Địa Bĩ', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 1, 'so_du_ha' => 8, 'que_thuong' => 'Càn', 'que_ha' => 'Khôn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Hỏa Địa Tấn', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 3, 'so_du_ha' => 8, 'que_thuong' => 'Ly', 'que_ha' => 'Khôn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Phong Địa Quan', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 5, 'so_du_ha' => 8, 'que_thuong' => 'Tốn', 'que_ha' => 'Khôn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Càn cung', 'ten' => 'Sơn Địa Bác', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 7, 'so_du_ha' => 8, 'que_thuong' => 'Cấn', 'que_ha' => 'Khôn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Sơn Thiên Đại Súc', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 7, 'so_du_ha' => 1, 'que_thuong' => 'Cấn', 'que_ha' => 'Càn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Thiên Trạch Lý', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 1, 'so_du_ha' => 2, 'que_thuong' => 'Càn', 'que_ha' => 'Đoài', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Hỏa Trạch Khuê', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 3, 'so_du_ha' => 2, 'que_thuong' => 'Ly', 'que_ha' => 'Đoài', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Phong Trạch Trung Phu', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 5, 'so_du_ha' => 2, 'que_thuong' => 'Tốn', 'que_ha' => 'Đoài', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Sơn Trạch Tổn', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 7, 'so_du_ha' => 2, 'que_thuong' => 'Cấn', 'que_ha' => 'Đoài', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Sơn Hỏa Bí', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 7, 'so_du_ha' => 3, 'que_thuong' => 'Cấn', 'que_ha' => 'Ly', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Phong Sơn Tiệm', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 5, 'so_du_ha' => 7, 'que_thuong' => 'Tốn', 'que_ha' => 'Cấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Cấn cung', 'ten' => 'Thuần Cấn', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 7, 'so_du_ha' => 7, 'que_thuong' => 'Cấn', 'que_ha' => 'Cấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Trạch Lôi Tùy', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 2, 'so_du_ha' => 4, 'que_thuong' => 'Đoài', 'que_ha' => 'Chấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Thuần Chấn', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 4, 'so_du_ha' => 4, 'que_thuong' => 'Chấn', 'que_ha' => 'Chấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Trạch Phong Đại Quá', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 2, 'so_du_ha' => 5, 'que_thuong' => 'Đoài', 'que_ha' => 'Tốn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Lôi Phong Hằng', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 4, 'so_du_ha' => 5, 'que_thuong' => 'Chấn', 'que_ha' => 'Tốn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Thủy Phong Tỉnh', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 6, 'so_du_ha' => 5, 'que_thuong' => 'Khảm', 'que_ha' => 'Tốn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Địa Phong Thăng', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 8, 'so_du_ha' => 5, 'que_thuong' => 'Khôn', 'que_ha' => 'Tốn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Lôi Thủy Giải', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 4, 'so_du_ha' => 6, 'que_thuong' => 'Khảm', 'que_ha' => 'Đoài', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Chấn cung', 'ten' => 'Lôi Địa Dự', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 4, 'so_du_ha' => 8, 'que_thuong' => 'Chấn', 'que_ha' => 'Khôn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Thuần Đoài', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 2, 'so_du_ha' => 2, 'que_thuong' => 'Đoài', 'que_ha' => 'Đoài', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Lôi Trạch Quy Muội', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 4, 'so_du_ha' => 2, 'que_thuong' => 'Chấn', 'que_ha' => 'Đoài', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Trạch Thủy Khốn', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 2, 'so_du_ha' => 6, 'que_thuong' => 'Đoài', 'que_ha' => 'Khảm', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Trạch Sơn Hàm', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 2, 'so_du_ha' => 7, 'que_thuong' => 'Đoài', 'que_ha' => 'Cấn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Lôi Sơn Tiểu Quá', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 4, 'so_du_ha' => 7, 'que_thuong' => 'Chấn', 'que_ha' => 'Cấn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Thủy Sơn Kiển', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 6, 'so_du_ha' => 7, 'que_thuong' => 'Khảm', 'que_ha' => 'Cấn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Địa Sơn Khiêm', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 8, 'so_du_ha' => 7, 'que_thuong' => 'Khôn', 'que_ha' => 'Cấn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Đoài cung', 'ten' => 'Trạch Địa Tụy', 'ngu_hanh' => 'Kim', 'so_du_thuong' => 2, 'so_du_ha' => 8, 'que_thuong' => 'Đoài', 'que_ha' => 'Khôn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Thủy Trạch Tiết', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 6, 'so_du_ha' => 2, 'que_thuong' => 'Chấn', 'que_ha' => 'Khảm', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Trạch Hỏa Cách', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 2, 'so_du_ha' => 3, 'que_thuong' => 'Đoài', 'que_ha' => 'Ly', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Lôi Hỏa Phong', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 4, 'so_du_ha' => 3, 'que_thuong' => 'Chấn', 'que_ha' => 'Ly', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Thủy Hỏa Ký Tế', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 6, 'so_du_ha' => 3, 'que_thuong' => 'Khảm', 'que_ha' => 'Ly', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Địa Hỏa Minh Di', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 8, 'so_du_ha' => 3, 'que_thuong' => 'Khôn', 'que_ha' => 'Ly', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Thủy Lôi Truân', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 6, 'so_du_ha' => 4, 'que_thuong' => 'Khảm', 'que_ha' => 'Chấn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Thuần Khảm', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 6, 'so_du_ha' => 6, 'que_thuong' => 'Khảm', 'que_ha' => 'Khảm', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Khảm cung', 'ten' => 'Địa Thủy Sư', 'ngu_hanh' => 'Thủy', 'so_du_thuong' => 8, 'so_du_ha' => 6, 'que_thuong' => 'Khôn', 'que_ha' => 'Khảm', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Trạch Thiên Quải', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 2, 'so_du_ha' => 1, 'que_thuong' => 'Đoài', 'que_ha' => 'Càn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Lôi Thiên Đại Tráng', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 4, 'so_du_ha' => 1, 'que_thuong' => 'Chấn', 'que_ha' => 'Càn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Thủy Thiên Nhu', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 6, 'so_du_ha' => 1, 'que_thuong' => 'Khảm', 'que_ha' => 'Càn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Địa Thiên Thái', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 8, 'so_du_ha' => 1, 'que_thuong' => 'Khôn', 'que_ha' => 'Càn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Địa Trạch Lâm', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 8, 'so_du_ha' => 2, 'que_thuong' => 'Khôn', 'que_ha' => 'Đoài', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Địa Lôi Phục', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 8, 'so_du_ha' => 4, 'que_thuong' => 'Khôn', 'que_ha' => 'Chấn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Thủy Địa Tỷ', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 6, 'so_du_ha' => 8, 'que_thuong' => 'Khảm', 'que_ha' => 'Khôn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Khôn cung', 'ten' => 'Thuần Khôn', 'ngu_hanh' => 'Thổ', 'so_du_thuong' => 8, 'so_du_ha' => 8, 'que_thuong' => 'Khôn', 'que_ha' => 'Khôn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Thiên Hỏa Đồng Nhân', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 1, 'so_du_ha' => 3, 'que_thuong' => 'Càn', 'que_ha' => 'Ly', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Thuần Ly', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 3, 'so_du_ha' => 3, 'que_thuong' => 'Ly', 'que_ha' => 'Ly', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Hỏa Phong Đỉnh', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 3, 'so_du_ha' => 5, 'que_thuong' => 'Ly', 'que_ha' => 'Tốn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Thiên Thủy Tụng', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 1, 'so_du_ha' => 6, 'que_thuong' => 'Càn', 'que_ha' => 'Khảm', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Hỏa Thủy Vị Tế', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 3, 'so_du_ha' => 6, 'que_thuong' => 'Ly', 'que_ha' => 'Khảm', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Phong Thủy Hoán', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 5, 'so_du_ha' => 6, 'que_thuong' => 'Tốn', 'que_ha' => 'Khảm', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Sơn Thủy Mông', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 7, 'so_du_ha' => 6, 'que_thuong' => 'Cấn', 'que_ha' => 'Khảm', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Ly cung', 'ten' => 'Hỏa Sơn Lữ', 'ngu_hanh' => 'Hỏa', 'so_du_thuong' => 3, 'so_du_ha' => 7, 'que_thuong' => 'Ly', 'que_ha' => 'Cấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Phong Thiên Tiểu Súc', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 5, 'so_du_ha' => 1, 'que_thuong' => 'Tốn', 'que_ha' => 'Càn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Phong Hỏa Gia Nhân', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 5, 'so_du_ha' => 3, 'que_thuong' => 'Tốn', 'que_ha' => 'Ly', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Thiên Lôi Vô Vọng', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 1, 'so_du_ha' => 4, 'que_thuong' => 'Càn', 'que_ha' => 'Chấn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Hỏa Lôi Phệ Hạp', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 3, 'so_du_ha' => 4, 'que_thuong' => 'Ly', 'que_ha' => 'Chấn', 'phan_loai' => 'Tiêu Cực'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Phong Lôi Ích', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 5, 'so_du_ha' => 4, 'que_thuong' => 'Tốn', 'que_ha' => 'Chấn', 'phan_loai' => 'Tích Cực'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Sơn Lôi Di', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 7, 'so_du_ha' => 4, 'que_thuong' => 'Cấn', 'que_ha' => 'Chấn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Thuần Tốn', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 5, 'so_du_ha' => 5, 'que_thuong' => 'Tốn', 'que_ha' => 'Tốn', 'phan_loai' => 'Trung Bình'],
        ['gia_dinh' => 'Tốn cung', 'ten' => 'Sơn Phong Cổ', 'ngu_hanh' => 'Mộc', 'so_du_thuong' => 7, 'so_du_ha' => 5, 'que_thuong' => 'Cấn', 'que_ha' => 'Tốn', 'phan_loai' => 'Trung Bình'],
    ];

    protected $chamDiemSimKhachHang = [
        [
            'stt' => 1,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 1,
            'ten_que' => 'Thuần Càn',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 2,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 1,
            'ten_que' => 'Hỏa Thiên Đại Hữu',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 3,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 5,
            'ten_que' => 'Thiên Phong Cấu',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 4,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 7,
            'ten_que' => 'Thiên Sơn Độn',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 5,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 8,
            'ten_que' => 'Thiên Địa Bĩ',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 0.5,
            'tai_chinh_va_co_hoi' => 0.5,
            'moi_quan_he_tinh_cam' => 0.5,
            'phat_trien_ban_than' => 0.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 0.5,
            'suc_khoe_va_nang_luong' => 0.5
        ],
        [
            'stt' => 6,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 8,
            'ten_que' => 'Hỏa Địa Tấn',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 4.5,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 7,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 8,
            'ten_que' => 'Phong Địa Quan',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 2.5,
            'tai_chinh_va_co_hoi' => 2.5,
            'moi_quan_he_tinh_cam' => 2.5,
            'phat_trien_ban_than' => 2.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 8,
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 8,
            'ten_que' => 'Sơn Địa Bác',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 0.5,
            'tai_chinh_va_co_hoi' => 0.5,
            'moi_quan_he_tinh_cam' => 0.5,
            'phat_trien_ban_than' => 0.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 0.5,
            'suc_khoe_va_nang_luong' => 0.5
        ],
        [
            'stt' => 9,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 1,
            'ten_que' => 'Sơn Thiên Đại Súc',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 10,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 2,
            'ten_que' => 'Thiên Trạch Lý',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 11,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 2,
            'ten_que' => 'Hỏa Trạch Khuê',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 12,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 2,
            'ten_que' => 'Phong Trạch Trung Phu',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 13,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 2,
            'ten_que' => 'Sơn Trạch Tổn',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 0.5,
            'tai_chinh_va_co_hoi' => 0.5,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 0.5
        ],
        [
            'stt' => 14,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 3,
            'ten_que' => 'Sơn Hỏa Bí',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 15,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 7,
            'ten_que' => 'Phong Sơn Tiệm',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 16,
            'gia_dinh_quai' => 'Cấn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 7,
            'ten_que' => 'Thuần Cấn',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 17,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 4,
            'ten_que' => 'Trạch Lôi Tùy',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 2.5,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 2.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 2.5
        ],
        [
            'stt' => 18,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 4,
            'ten_que' => 'Thuần Chấn',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 19,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 5,
            'ten_que' => 'Trạch Phong Đại Quá',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 20,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 5,
            'ten_que' => 'Lôi Phong Hằng',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 21,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 5,
            'ten_que' => 'Thủy Phong Tỉnh',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 22,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 5,
            'ten_que' => 'Địa Phong Thăng',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 23,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 6,
            'ten_que' => 'Lôi Thủy Giải',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 24,
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 8,
            'ten_que' => 'Lôi Địa Dự',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 25,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 2,
            'ten_que' => 'Thuần Đoài',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 26,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 2,
            'ten_que' => 'Lôi Trạch Quy Muội',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 27,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 6,
            'ten_que' => 'Trạch Thủy Khốn',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 28,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 7,
            'ten_que' => 'Trạch Sơn Hàm',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 29,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 7,
            'ten_que' => 'Lôi Sơn Tiểu Quá',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 30,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 7,
            'ten_que' => 'Thủy Sơn Kiển',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 31,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 7,
            'ten_que' => 'Địa Sơn Khiêm',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 32,
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 8,
            'ten_que' => 'Trạch Địa Tụy',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 33,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 2,
            'ten_que' => 'Thủy Trạch Tiết',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 34,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 3,
            'ten_que' => 'Trạch Hỏa Cách',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 35,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 3,
            'ten_que' => 'Lôi Hỏa Phong',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 36,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 3,
            'ten_que' => 'Thủy Hỏa Ký Tế',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 37,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 3,
            'ten_que' => 'Địa Hỏa Minh Di',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 38,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 4,
            'ten_que' => 'Thủy Lôi Truân',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 39,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 6,
            'ten_que' => 'Thuần Khảm',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 40,
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 6,
            'ten_que' => 'Địa Thủy Sư',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 41,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 1,
            'ten_que' => 'Trạch Thiên Quải',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 42,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 1,
            'ten_que' => 'Lôi Thiên Đại Tráng',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 43,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 1,
            'ten_que' => 'Thủy Thiên Nhu',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 44,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 1,
            'ten_que' => 'Địa Thiên Thái',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 45,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 2,
            'ten_que' => 'Địa Trạch Lâm',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 46,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 4,
            'ten_que' => 'Địa Lôi Phục',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'stt' => 47,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 8,
            'ten_que' => 'Thủy Địa Tỷ',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 48,
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 8,
            'ten_que' => 'Thuần Khôn',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 49,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 3,
            'ten_que' => 'Thiên Hỏa Đồng Nhân',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 50,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 3,
            'ten_que' => 'Thuần Ly',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 51,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 5,
            'ten_que' => 'Hỏa Phong Đỉnh',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 52,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 6,
            'ten_que' => 'Thiên Thủy Tụng',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 53,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 6,
            'ten_que' => 'Hỏa Thủy Vị Tế',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 2.5,
            'tai_chinh_va_co_hoi' => 2.5,
            'moi_quan_he_tinh_cam' => 2.5,
            'phat_trien_ban_than' => 2.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 2.5,
            'suc_khoe_va_nang_luong' => 2.5
        ],
        [
            'stt' => 54,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 6,
            'ten_que' => 'Phong Thủy Hoán',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 55,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 6,
            'ten_que' => 'Sơn Thủy Mông',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 56,
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 7,
            'ten_que' => 'Hỏa Sơn Lữ',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 2.5,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 2.5
        ],
        [
            'stt' => 57,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 1,
            'ten_que' => 'Phong Thiên Tiểu Súc',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 2,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 2,
            'suc_khoe_va_nang_luong' => 1
        ],
        [
            'stt' => 58,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 3,
            'ten_que' => 'Phong Hỏa Gia Nhân',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 59,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 4,
            'ten_que' => 'Thiên Lôi Vô Vọng',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 0.5,
            'tai_chinh_va_co_hoi' => 0.5,
            'moi_quan_he_tinh_cam' => 0.5,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 0.5,
            'suc_khoe_va_nang_luong' => 0.5
        ],
        [
            'stt' => 60,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 4,
            'ten_que' => 'Hỏa Lôi Phệ Hạp',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 2,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 61,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 4,
            'ten_que' => 'Phong Lôi Ích',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'stt' => 62,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 4,
            'ten_que' => 'Sơn Lôi Di',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'stt' => 63,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 5,
            'ten_que' => 'Thuần Tốn',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'stt' => 64,
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 5,
            'ten_que' => 'Sơn Phong Cổ',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 2,
            'tai_chinh_va_co_hoi' => 2,
            'moi_quan_he_tinh_cam' => 1,
            'phat_trien_ban_than' => 1,
            'ket_noi_xa_hoi_va_danh_tieng' => 1,
            'suc_khoe_va_nang_luong' => 1
        ]
    ];

    protected $chamDiemSimVKB = [
        [
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 1,
            'ten_que' => 'Thuận Càn',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 1,
            'ten_que' => 'Hóa Thien Đại Hữu',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 8,
            'ten_que' => 'Hóa Địa Tấn',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 4.5,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 1,
            'ten_que' => 'Sơn Thiên Đại Súc',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 2,
            'ten_que' => 'Phong Trach Trung Phu',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Càn cung',
            'so_du_que_thuong' => 7,
            'so_du_que_ha' => 3,
            'ten_que' => 'Sơn Hóa Bí',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 5,
            'ten_que' => 'Lối Phong Hằng',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 5,
            'ten_que' => 'Địa Phong Thăng',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 6,
            'ten_que' => 'Lối Thủy Giải',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Chấn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 8,
            'ten_que' => 'Lối Địa Dự',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Đoài cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 7,
            'ten_que' => 'Trach Sơn Hàm',
            'ngu_hanh_que' => 'Kim',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 2,
            'so_du_que_ha' => 3,
            'ten_que' => 'Trach Hóa Cách',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 3,
            'tai_chinh_va_co_hoi' => 3,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3
        ],
        [
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 3,
            'ten_que' => 'Lối Hòa Phong',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Khảm cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 6,
            'ten_que' => 'Địa Thủy Sư',
            'ngu_hanh_que' => 'Thủy',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 4,
            'so_du_que_ha' => 1,
            'ten_que' => 'Lối Thiên Đại Tráng',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 6,
            'so_du_que_ha' => 1,
            'ten_que' => 'Thủy Thiên Nhu',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 1,
            'tai_chinh_va_co_hoi' => 1,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3,
            'ket_noi_xa_hoi_va_danh_tieng' => 3,
            'suc_khoe_va_nang_luong' => 2
        ],
        [
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 1,
            'ten_que' => 'Địa Thiên Thái',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 4.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 2,
            'ten_que' => 'Địa Trach Lâm',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Khôn cung',
            'so_du_que_thuong' => 8,
            'so_du_que_ha' => 4,
            'ten_que' => 'Địa Lối Phục',
            'ngu_hanh_que' => 'Thổ',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 3,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 3.5
        ],
        [
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 1,
            'so_du_que_ha' => 3,
            'ten_que' => 'Thiên Hóa Đồng Nhân',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'gia_dinh_quai' => 'Ly cung',
            'so_du_que_thuong' => 3,
            'so_du_que_ha' => 5,
            'ten_que' => 'Hóa Phong Đinh',
            'ngu_hanh_que' => 'Hỏa',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 4,
            'ket_noi_xa_hoi_va_danh_tieng' => 3.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 3,
            'ten_que' => 'Phong Hóa Gia Nhân',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 3.5,
            'tai_chinh_va_co_hoi' => 3.5,
            'moi_quan_he_tinh_cam' => 4,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4.5,
            'suc_khoe_va_nang_luong' => 4
        ],
        [
            'gia_dinh_quai' => 'Tốn cung',
            'so_du_que_thuong' => 5,
            'so_du_que_ha' => 4,
            'ten_que' => 'Phong Lối Ích',
            'ngu_hanh_que' => 'Mộc',
            'su_nghiep_va_dia_vi' => 4,
            'tai_chinh_va_co_hoi' => 4,
            'moi_quan_he_tinh_cam' => 3.5,
            'phat_trien_ban_than' => 3.5,
            'ket_noi_xa_hoi_va_danh_tieng' => 4,
            'suc_khoe_va_nang_luong' => 4
        ]
    ];

    /**
     * Tính quẻ từ số điện thoại
     * 
     * @param string $phoneNumber Số điện thoại
     * @return array Kết quả tương ứng với các cột N2, O2, P2, Q2, R2, S2, T2, U2, V2, W2, X2
     */
    public function tinhQue($phoneNumber)
    {
        // Loại bỏ các ký tự không phải số
        $phoneNumber = preg_replace('/[^0-9]/', '', (string) $phoneNumber);

        if (empty($phoneNumber)) {
            return $this->getEmptyResult();
        }

        // Xử lý trường hợp mất số 0 ở đầu: nếu input có 9-10 chữ số và bắt đầu bằng 7,8,9
        // thì có thể đã mất số 0 ở đầu. Với số điện thoại Việt Nam thường có 10 chữ số
        // Nếu có 9 chữ số, thử thêm số 0 ở đầu để đạt 10 chữ số
        if (strlen($phoneNumber) == 9 && preg_match('/^[7-9]/', $phoneNumber)) {
            $phoneNumber = '0' . $phoneNumber;
        }

        $digits = str_split($phoneNumber);
        $length = count($digits);

        // Tính số dư quẻ thượng: tổng 5 số đầu chia 8 (nếu dư = 0 thì = 8)
        $digits5Dau = array_slice($digits, 0, 5);
        $sum5Dau = array_sum($digits5Dau);
        $soDuQueThuong = $sum5Dau % 8;
        if ($soDuQueThuong == 0) {
            $soDuQueThuong = 8;
        }

        // Tính số dư quẻ hạ: tổng 5 số cuối chia 8 (nếu dư = 0 thì = 8)
        $digits5Cuoi = array_slice($digits, -5);
        $sum5Cuoi = array_sum($digits5Cuoi);
        $soDuQueHa = $sum5Cuoi % 8;
        if ($soDuQueHa == 0) {
            $soDuQueHa = 8;
        }

        // Tính tổng toàn bộ để tính động hào
        $sum = array_sum($digits);
        // Tìm quẻ gốc
        $queGoc = $this->timQueGoc($soDuQueThuong, $soDuQueHa);

        // Tính động hào (chia 6, lấy dư, nếu dư = 0 thì = 6)
        $dongHao = $sum % 6;
        if ($dongHao == 0) {
            $dongHao = 6;
        }

        // Tính quẻ biến
        $queBien = $this->tinhQueBien($queGoc, $dongHao);
        // Kết quả tương ứng với các cột
        return [
            'so_du_que_thuong' => $soDuQueThuong, // N2
            'so_du_que_ha' => $soDuQueHa, // O2
            'que_goc' => $queGoc['ten'] ?? '', // P2
            'ngu_hanh' => $queGoc['ngu_hanh'] ?? '', // Q2
            'phan_loai' => $queGoc['phan_loai'] ?? '', // R2
            'dong_hao' => $dongHao, // S2
            'so_du_que_thuong_bien' => $queBien['so_du_thuong'] ?? null, // T2
            'so_du_que_ha_bien' => $queBien['so_du_ha'] ?? null, // U2
            'que_bien' => $queBien['ten'] ?? '', // V2
            'ngu_hanh_bien' => $queBien['ngu_hanh'] ?? '', // W2
            'phan_loai_bien' => $queBien['phan_loai'] ?? '', // X2
        ];
    }

    public function diemSimKhachHang($phoneNumber, $hyThan, $kyThan)
    {
        $queData = $this->tinhQue($phoneNumber);
        $queGoc = array_find($this->chamDiemSimKhachHang, function ($item) use ($queData) {
            return $item['ten_que'] == $queData['que_goc'];
        });
        $queBien = array_find($this->chamDiemSimKhachHang, function ($item) use ($queData) {
            return $item['ten_que'] == $queData['que_bien'];
        });
        $diemQueGoc = 0;
        if (!empty($queGoc)) {
            $nguHanhQueGoc = $queGoc['ngu_hanh_que'];
            $diemQueGoc = ($queGoc['su_nghiep_va_dia_vi']
                + $queGoc['tai_chinh_va_co_hoi']
                + $queGoc['moi_quan_he_tinh_cam']
                + $queGoc['phat_trien_ban_than']
                + $queGoc['ket_noi_xa_hoi_va_danh_tieng']
                + $queGoc['suc_khoe_va_nang_luong'] + 2) / 6;
            if (in_array($nguHanhQueGoc, $hyThan)) {
                $diemQueGoc += 1;
            } elseif (in_array($nguHanhQueGoc, $kyThan)) {
                $diemQueGoc -= 2;
            }
        }
        $diemQueBien = 0;
        if (!empty($queBien)) {
            $nguHanhQueBien = $queBien['ngu_hanh_que'];
            $diemQueBien = ($queBien['su_nghiep_va_dia_vi']
                + $queBien['tai_chinh_va_co_hoi']
                + $queBien['moi_quan_he_tinh_cam']
                + $queBien['phat_trien_ban_than']
                + $queBien['ket_noi_xa_hoi_va_danh_tieng']
                + $queBien['suc_khoe_va_nang_luong'] + 2) / 6;
            if (in_array($nguHanhQueBien, $hyThan)) {
                $diemQueBien += 1;
            } elseif (in_array($nguHanhQueBien, $kyThan)) {
                $diemQueBien -= 2;
            }
        }

        $tongDiem = round($diemQueGoc + $diemQueBien, 2) - 1;
        $type = '';
        switch (true) {
            case $tongDiem < 3:
                $type = 'Đại Hung';
                break;
            case $tongDiem >= 3 && $tongDiem < 5:
                $type = 'Hung';
                break;
            case $tongDiem >= 5 && $tongDiem < 7:
                $type = 'Bình Hoà';
                break;
            case $tongDiem >= 7 && $tongDiem < 8:
                $type = 'Tiểu Cát';
                break;
            case $tongDiem >= 8 && $tongDiem < 9:
                $type = 'Cát';
                break;
            case $tongDiem >= 9:
                $type = 'Dại Cát';
                break;
        }
        $tongDiem = $tongDiem < 0 ? 0 : $tongDiem;
        // chuyển tên quẻ gốc sang viết hoa toàn bộ để tìm trong bảng 64 quẻ
        $tenQueGoc = mb_strtoupper($queData['que_goc'], 'UTF-8');

        $tongQuanQueGoc = Que64::where('name', $tenQueGoc)->first();

        return [
            'tong_diem' => $tongDiem,
            'type' => $type,
            'tong_quan_que_goc' => $tongQuanQueGoc
        ];
    }

    public function diemSimVKB($phoneNumberId, $hyThan)
    {
        $sim = Sim::where('phone_number', $phoneNumberId)->first();
        if (empty($sim)) {
            return [
                'success' => false,
                'message' => 'Số điện thoại không tồn tại'
            ];
        }
        $queData = $this->tinhQue($sim->phone_number);
        $queGoc = array_find($this->chamDiemSimKhachHang, function ($item) use ($queData) {
            return $item['ten_que'] == $queData['que_goc'];
        });
        $queBien = array_find($this->chamDiemSimKhachHang, function ($item) use ($queData) {
            return $item['ten_que'] == $queData['que_bien'];
        });
        $diemQueGoc = 0;
        if (!empty($queGoc)) {
            $nguHanhQueGoc = $queGoc['ngu_hanh_que'];
            $diemQueGoc = ($queGoc['su_nghiep_va_dia_vi']
                + $queGoc['tai_chinh_va_co_hoi']
                + $queGoc['moi_quan_he_tinh_cam']
                + $queGoc['phat_trien_ban_than']
                + $queGoc['ket_noi_xa_hoi_va_danh_tieng']
                + $queGoc['suc_khoe_va_nang_luong'] + 2) / 6;
            if (in_array($nguHanhQueGoc, $hyThan)) {
                $diemQueGoc += 1;
            }
        }
        $diemQueBien = 0;
        if (!empty($queBien)) {
            $nguHanhQueBien = $queBien['ngu_hanh_que'];
            $diemQueBien = ($queBien['su_nghiep_va_dia_vi']
                + $queBien['tai_chinh_va_co_hoi']
                + $queBien['moi_quan_he_tinh_cam']
                + $queBien['phat_trien_ban_than']
                + $queBien['ket_noi_xa_hoi_va_danh_tieng']
                + $queBien['suc_khoe_va_nang_luong'] + 2) / 6;
            if (in_array($nguHanhQueBien, $hyThan)) {
                $diemQueBien += 1;
            }
        }

        $tongDiem = round($diemQueGoc + $diemQueBien, 2) - 1;
        $type = '';
        switch (true) {
            case $tongDiem < 3:
                $type = 'Đại Hung';
                break;
            case $tongDiem >= 3 && $tongDiem < 5:
                $type = 'Hung';
                break;
            case $tongDiem >= 5 && $tongDiem < 7:
                $type = 'Bình Hoà';
                break;
            case $tongDiem >= 7 && $tongDiem < 8:
                $type = 'Tiểu Cát';
                break;
            case $tongDiem >= 8 && $tongDiem < 9:
                $type = 'Cát';
                break;
            case $tongDiem >= 9:
                $type = 'Dại Cát';
                break;
        }
        $tongDiem = $tongDiem < 0 ? 0 : $tongDiem;
        $tenQueGoc = mb_strtoupper($queData['que_goc'], 'UTF-8');
        $tongQuanQueGoc = Que64::where('name', $tenQueGoc)->first();

        return [
            'success' => true,
            'tong_diem' => $tongDiem,
            'type' => $type,
            'tong_quan_que_goc' => $tongQuanQueGoc
        ];
    }

    /**
     * Tính điểm cho nhiều sim cùng lúc (batch processing để tối ưu performance)
     * @param array $phoneNumbers Mảng các số điện thoại
     * @param array $hyThan Mảng hỷ thần
     * @return array Mảng kết quả với key là số điện thoại
     */
    public function diemSimVKBBatch($phoneNumbers, $hyThan)
    {
        $results = [];
        
        // Lấy tất cả sim cùng lúc
        $sims = Sim::whereIn('phone_number', $phoneNumbers)->get()->keyBy('phone_number');
        
        // Cache cho Que64
        $que64Cache = [];
        
        foreach ($phoneNumbers as $phoneNumber) {
            $sim = $sims->get($phoneNumber);
            
            if (empty($sim)) {
                $results[$phoneNumber] = [
                    'success' => false,
                    'message' => 'Số điện thoại không tồn tại',
                    'tong_diem' => 0,
                    'type' => 'N/A'
                ];
                continue;
            }
            
            $queData = $this->tinhQue($sim->phone_number);
            $queGoc = array_find($this->chamDiemSimKhachHang, function ($item) use ($queData) {
                return $item['ten_que'] == $queData['que_goc'];
            });
            $queBien = array_find($this->chamDiemSimKhachHang, function ($item) use ($queData) {
                return $item['ten_que'] == $queData['que_bien'];
            });
            
            $diemQueGoc = 0;
            if (!empty($queGoc)) {
                $nguHanhQueGoc = $queGoc['ngu_hanh_que'];
                $diemQueGoc = ($queGoc['su_nghiep_va_dia_vi']
                    + $queGoc['tai_chinh_va_co_hoi']
                    + $queGoc['moi_quan_he_tinh_cam']
                    + $queGoc['phat_trien_ban_than']
                    + $queGoc['ket_noi_xa_hoi_va_danh_tieng']
                    + $queGoc['suc_khoe_va_nang_luong'] + 2) / 6;
                if (in_array($nguHanhQueGoc, $hyThan)) {
                    $diemQueGoc += 1;
                }
            }
            
            $diemQueBien = 0;
            if (!empty($queBien)) {
                $nguHanhQueBien = $queBien['ngu_hanh_que'];
                $diemQueBien = ($queBien['su_nghiep_va_dia_vi']
                    + $queBien['tai_chinh_va_co_hoi']
                    + $queBien['moi_quan_he_tinh_cam']
                    + $queBien['phat_trien_ban_than']
                    + $queBien['ket_noi_xa_hoi_va_danh_tieng']
                    + $queBien['suc_khoe_va_nang_luong'] + 2) / 6;
                if (in_array($nguHanhQueBien, $hyThan)) {
                    $diemQueBien += 1;
                }
            }

            $tongDiem = round($diemQueGoc + $diemQueBien, 2) - 1;
            $type = '';
            switch (true) {
                case $tongDiem < 3:
                    $type = 'Đại Hung';
                    break;
                case $tongDiem >= 3 && $tongDiem < 5:
                    $type = 'Hung';
                    break;
                case $tongDiem >= 5 && $tongDiem < 7:
                    $type = 'Bình Hoà';
                    break;
                case $tongDiem >= 7 && $tongDiem < 8:
                    $type = 'Tiểu Cát';
                    break;
                case $tongDiem >= 8 && $tongDiem < 9:
                    $type = 'Cát';
                    break;
                case $tongDiem >= 9:
                    $type = 'Dại Cát';
                    break;
            }
            $tongDiem = $tongDiem < 0 ? 0 : $tongDiem;
            
            $results[$phoneNumber] = [
                'success' => true,
                'tong_diem' => $tongDiem,
                'type' => $type
            ];
        }
        
        return $results;
    }

    /**
     * Trả về kết quả rỗng
     */
    protected function getEmptyResult()
    {
        return [
            'so_du_que_thuong' => null,
            'so_du_que_ha' => null,
            'que_goc' => '',
            'ngu_hanh' => '',
            'phan_loai' => '',
            'dong_hao' => null,
            'so_du_que_thuong_bien' => null,
            'so_du_que_ha_bien' => null,
            'que_bien' => '',
            'ngu_hanh_bien' => '',
            'phan_loai_bien' => '',
        ];
    }

    /**
     * Tìm quẻ gốc dựa vào số dư quẻ thượng và quẻ hạ
     */
    protected function timQueGoc($soDuThuong, $soDuHa)
    {
        foreach ($this->data64Que as $que) {
            if ($que['so_du_thuong'] == $soDuThuong && $que['so_du_ha'] == $soDuHa) {
                return $que;
            }
        }

        return null;
    }

    protected function timQueBien($tenQue)
    {
        foreach ($this->data64Que as $que) {
            if ($que['ten'] == $tenQue) {
                return $que;
            }
        }

        return null;
    }

    /**
     * Tính quẻ biến từ quẻ gốc và động hào
     * Sử dụng bảng tra cứu từ file CSV
     */
    protected function tinhQueBien($queGoc, $dongHao)
    {
        if (!$queGoc) {
            return null;
        }

        // Load bảng tra cứu
        $lookup = $this->queBienLookup;

        // Tạo key tra cứu: Tên quẻ + Động hào
        $key = $queGoc['ten'] . $dongHao;

        // Tra cứu trong bảng
        if (isset($lookup[$key])) {
            $queBienData = $lookup[$key];
            // Tìm quẻ biến từ số dư
            $queBien = $this->timQueBien($queBienData['ten']);
            return $queBien;
        }

        return null;
    }

    public function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters (spaces, dashes, parentheses, etc.)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Remove country code prefixes
        // +84, 84, or 0 at the beginning
        $phoneNumber = preg_replace('/^(\+?84|0)/', '', $phoneNumber);
        
        return $phoneNumber;
    }
}
