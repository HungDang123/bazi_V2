<?php

namespace Database\Seeders;

use App\Models\DiaChiThang;
use Illuminate\Database\Seeder;

class DiaChiThangSeeder extends Seeder
{
    /**
     * Run the seeders.
     * Mapping Địa Chi trụ tháng với Ngũ Hành, Âm Dương, Mùa Sinh
     */
    public function run(): void
    {
        $data = [
            ['dia_chi' => 'Dần',  'ngu_hanh' => 'Mộc', 'am_duong' => '+', 'menh_full' => '+ Mộc',  'mua_sinh' => 'Mùa Xuân', 'ngu_hanh_mua_sinh' => 'Mộc',  'sort_order' => 1],
            ['dia_chi' => 'Mão',  'ngu_hanh' => 'Mộc', 'am_duong' => '-', 'menh_full' => '- Mộc',  'mua_sinh' => 'Mùa Xuân', 'ngu_hanh_mua_sinh' => 'Mộc',  'sort_order' => 2],
            ['dia_chi' => 'Thìn', 'ngu_hanh' => 'Thổ', 'am_duong' => '+', 'menh_full' => '+ Thổ',  'mua_sinh' => 'Mùa Xuân', 'ngu_hanh_mua_sinh' => 'Mộc',  'sort_order' => 3],
            ['dia_chi' => 'Tỵ',   'ngu_hanh' => 'Hỏa', 'am_duong' => '-', 'menh_full' => '- Hỏa',  'mua_sinh' => 'Mùa Hè',  'ngu_hanh_mua_sinh' => 'Hỏa',  'sort_order' => 4],
            ['dia_chi' => 'Ngọ',  'ngu_hanh' => 'Hỏa', 'am_duong' => '+', 'menh_full' => '+ Hỏa',  'mua_sinh' => 'Mùa Hè',  'ngu_hanh_mua_sinh' => 'Hỏa',  'sort_order' => 5],
            ['dia_chi' => 'Mùi',  'ngu_hanh' => 'Thổ', 'am_duong' => '-', 'menh_full' => '- Thổ',  'mua_sinh' => 'Mùa Hè',  'ngu_hanh_mua_sinh' => 'Hỏa',  'sort_order' => 6],
            ['dia_chi' => 'Thân', 'ngu_hanh' => 'Kim', 'am_duong' => '+', 'menh_full' => '+ Kim',  'mua_sinh' => 'Mùa Thu', 'ngu_hanh_mua_sinh' => 'Kim',  'sort_order' => 7],
            ['dia_chi' => 'Dậu',  'ngu_hanh' => 'Kim', 'am_duong' => '-', 'menh_full' => '- Kim',  'mua_sinh' => 'Mùa Thu', 'ngu_hanh_mua_sinh' => 'Kim',  'sort_order' => 8],
            ['dia_chi' => 'Tuất', 'ngu_hanh' => 'Thổ', 'am_duong' => '+', 'menh_full' => '+ Thổ',  'mua_sinh' => 'Mùa Thu', 'ngu_hanh_mua_sinh' => 'Kim',  'sort_order' => 9],
            ['dia_chi' => 'Hợi',  'ngu_hanh' => 'Thủy', 'am_duong' => '-', 'menh_full' => '- Thủy', 'mua_sinh' => 'Mùa Đông', 'ngu_hanh_mua_sinh' => 'Thủy', 'sort_order' => 10],
            ['dia_chi' => 'Tý',   'ngu_hanh' => 'Thủy', 'am_duong' => '+', 'menh_full' => '+ Thủy', 'mua_sinh' => 'Mùa Đông', 'ngu_hanh_mua_sinh' => 'Thủy', 'sort_order' => 11],
            ['dia_chi' => 'Sửu',  'ngu_hanh' => 'Thổ', 'am_duong' => '-', 'menh_full' => '- Thổ',  'mua_sinh' => 'Mùa Đông', 'ngu_hanh_mua_sinh' => 'Thủy', 'sort_order' => 12],
        ];

        foreach ($data as $row) {
            DiaChiThang::updateOrCreate(
                ['dia_chi' => $row['dia_chi']],
                $row
            );
        }

        $this->command->info('Đã tạo 12 mục địa chi trụ tháng.');
    }
}
