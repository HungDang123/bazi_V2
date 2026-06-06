<?php

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . DIRECTORY_SEPARATOR . 'PHAN5_V_SUC_KHOE.xlsx';

if (!file_exists($file)) {
    echo "File not found: {$file}\n";
    exit(1);
}

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);

$sheet = $spreadsheet->getActiveSheet();
$title = $sheet->getTitle();

echo "Sheet title: {$title}\n";

$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();

echo "Rows: {$highestRow}, Cols: {$highestCol}\n";

// In 15 dòng đầu để xem cấu trúc
for ($row = 1; $row <= min(20, $highestRow); $row++) {
    $cells = [];
    for ($col = 'A'; $col <= 'H'; $col++) {
        $cells[] = $col . $row . '=' . trim((string) $sheet->getCell($col . $row)->getCalculatedValue());
    }
    echo implode(' | ', $cells) . "\n";
}

