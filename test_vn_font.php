<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

@mkdir(storage_path('app/temp'), 0755, true);
$out = storage_path('app/temp/vn-font-test.pdf');

App\Services\PdfRenderService::saveView('pdfs.quyen-1.la-so-ngu-hanh-ban-menh', [
    'pages' => [[
        'bgPath'         => resource_path('views/pdfs/quyen-1/page-20-bg.png'),
        'showTitle'      => false,
        'showImage'      => false,
        'hanhName'       => 'KIM',
        'percent'        => 60,
        'imagePath'      => '',
        'titleImagePath' => '',
        'blocks'         => [
            ['type' => 'item_title', 'text' => 'Cơ chế vận hành tư duy'],
            ['type' => 'para', 'text' => 'Hành Kim tự nhiên đại diện cho năng lượng thu hoạch, sự quyết đoán và tính rõ ràng. Lá số cho thấy trạng thái năng lượng.'],
        ],
    ]],
], $out);

echo file_exists($out) ? "OK\n" : "FAIL\n";
echo "UFM: " . (file_exists(storage_path('fonts/SVN-Poppins-Bold.ufm')) ? 'yes' : 'no') . "\n";
