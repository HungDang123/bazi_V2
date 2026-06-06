<?php

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = $argv[1] ?? '';
if ($file === '') {
    echo "Usage: php debug_excel_generic.php <relative-or-absolute-xlsx-path>\n";
    exit(1);
}
if (!is_file($file)) {
    // thử tương đối từ base_path
    $alt = __DIR__ . DIRECTORY_SEPARATOR . $file;
    if (is_file($alt)) {
        $file = $alt;
    } else {
        echo "File not found: {$file}\n";
        exit(1);
    }
}

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);

$sheet = $spreadsheet->getActiveSheet();
$title = $sheet->getTitle();

echo "File: {$file}\n";
echo "Sheet title: {$title}\n";

$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();
echo "Rows: {$highestRow}, Cols: {$highestCol}\n";

// In 20 dòng đầu cột A-H
for ($row = 1; $row <= min(20, $highestRow); $row++) {
    $cells = [];
    for ($col = 'A'; $col <= 'H'; $col++) {
        $value = $sheet->getCell($col . $row)->getCalculatedValue();
        $value = is_string($value) ? trim($value) : $value;
        $cells[] = $col . $row . '=' . (string) $value;
    }
    echo implode(' | ', $cells) . "\n";
}

