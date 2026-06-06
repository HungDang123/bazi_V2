<?php

ini_set('memory_limit', '1024M');

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$fields = ['tong_quan', 'su_nghiep', 'tai_chinh', 'tinh_duyen', 'suc_khoe', 'phat_trien_ban_than', 'ket_noi_xa_hoi'];

echo "=== Kiểm tra 5 quẻ đầu ===\n";
foreach (App\Models\Que64::take(5)->get() as $que) {
    echo "Quẻ: " . $que->name . "\n";
    foreach ($fields as $field) {
        if ($field === 'tong_quan') {
            $len = strlen($que->$field ?? '');
            echo "  $field: " . ($len > 0 ? "✓ ($len chars)" : "✗ EMPTY") . "\n";
        } else {
            $lenTich = strlen($que->$field['tich_cuc'] ?? '');
            $lenTieu = strlen($que->$field['tieu_cuc'] ?? '');
            echo "  $field: tích cực=" . ($lenTich > 0 ? "✓($lenTich)" : "✗") . " tiêu cực=" . ($lenTieu > 0 ? "✓($lenTieu)" : "✗") . "\n";
        }
    }
    echo "\n";
}

echo "=== Thống kê tổng ===\n";
$total = App\Models\Que64::count();
echo "Tổng số quẻ: $total\n";

$allFields = App\Models\Que64::all();
$emptyCount = [];
foreach ($fields as $field) {
    $emptyCount[$field] = ['tich_cuc' => 0, 'tieu_cuc' => 0, 'empty' => 0];
    
    foreach ($allFields as $que) {
        if ($field === 'tong_quan') {
            if (empty($que->$field)) {
                $emptyCount[$field]['empty']++;
            }
        } else {
            if (empty($que->$field['tich_cuc'])) {
                $emptyCount[$field]['tich_cuc']++;
            }
            if (empty($que->$field['tieu_cuc'])) {
                $emptyCount[$field]['tieu_cuc']++;
            }
        }
    }
}

echo "\nKết quả kiểm tra:\n";
foreach ($emptyCount as $field => $counts) {
    if ($field === 'tong_quan') {
        if ($counts['empty'] == 0) {
            echo "✓ $field: đầy đủ\n";
        } else {
            echo "✗ $field: thiếu {$counts['empty']} quẻ\n";
        }
    } else {
        $msg = [];
        if ($counts['tich_cuc'] > 0) $msg[] = "tích cực thiếu {$counts['tich_cuc']}";
        if ($counts['tieu_cuc'] > 0) $msg[] = "tiêu cực thiếu {$counts['tieu_cuc']}";
        
        if (empty($msg)) {
            echo "✓ $field: đầy đủ\n";
        } else {
            echo "✗ $field: " . implode(", ", $msg) . "\n";
        }
    }
}

