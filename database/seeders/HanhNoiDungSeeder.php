<?php

namespace Database\Seeders;

use App\Models\Hanh;
use App\Models\HanhNoiDung;
use Illuminate\Database\Seeder;

class HanhNoiDungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rangeTypes = [
            ['slug' => 'khuyet_0', 'title_suffix' => 'bị khuyết 0%', 'sort_order' => 1],
            ['slug' => 'duoi_30', 'title_suffix' => 'dưới 30%', 'sort_order' => 2],
            ['slug' => '30_60', 'title_suffix' => 'từ 30% đến 60%', 'sort_order' => 3],
            ['slug' => '60_80', 'title_suffix' => 'từ 60% đến 80%', 'sort_order' => 4],
            ['slug' => 'tren_80', 'title_suffix' => 'trên 80%', 'sort_order' => 5],
        ];

        Hanh::orderBy('sort_order')->get()->each(function (Hanh $hanh) use ($rangeTypes) {
            foreach ($rangeTypes as $type) {
                HanhNoiDung::firstOrCreate(
                    [
                        'hanh_id' => $hanh->id,
                        'slug' => $type['slug'],
                    ],
                    [
                        'title' => "{$hanh->name} {$type['title_suffix']}",
                        'content' => null,
                        'sort_order' => $type['sort_order'],
                    ]
                );
            }
        });

        $this->command->info('Đã tạo 25 mục nội dung hành (5 hành × 5 khoảng).');
    }
}
