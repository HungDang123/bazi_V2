<?php

namespace Database\Seeders;

use App\Models\Hanh;
use Illuminate\Database\Seeder;

class HanhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hanhList = [
            ['name' => 'Hỏa', 'slug' => 'hoa', 'sort_order' => 1, 'image' => 'resources/views/pdfs/quyen-1/ngu-hanh/hoa.png'],
            ['name' => 'Kim', 'slug' => 'kim', 'sort_order' => 2, 'image' => 'resources/views/pdfs/quyen-1/ngu-hanh/kim.png'],
            ['name' => 'Mộc', 'slug' => 'moc', 'sort_order' => 3, 'image' => 'resources/views/pdfs/quyen-1/ngu-hanh/moc.png'],
            ['name' => 'Thổ', 'slug' => 'tho', 'sort_order' => 4, 'image' => 'resources/views/pdfs/quyen-1/ngu-hanh/tho.png'],
            ['name' => 'Thủy', 'slug' => 'thuy', 'sort_order' => 5, 'image' => 'resources/views/pdfs/quyen-1/ngu-hanh/thuy.png'],
        ];

        foreach ($hanhList as $hanh) {
            Hanh::updateOrCreate(
                ['slug' => $hanh['slug']],
                [
                    'name' => $hanh['name'],
                    'sort_order' => $hanh['sort_order'],
                    'image' => $hanh['image'],
                ]
            );
        }

        $this->command->info('Đã tạo 5 hành ngũ hành.');
    }
}
