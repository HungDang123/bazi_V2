<?php

ini_set('memory_limit', '512M');
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx';
if (!is_file($file)) {
    echo "File not found.\n";
    exit(1);
}

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);

$sheetNames = $spreadsheet->getSheetNames();
echo "Sheets: " . implode(', ', $sheetNames) . "\n\n";

foreach ($sheetNames as $idx => $name) {
    $sheet = $spreadsheet->getSheet($idx);
    $title = $sheet->getTitle();
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();
    echo "=== Sheet: {$title} (Rows: {$highestRow}, Cols: {$highestCol}) ===\n";

    $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
    for ($row = 1; $row <= min(50, $highestRow); $row++) {
        $cells = [];
        for ($c = 1; $c <= min($colCount, 10); $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $value = $sheet->getCell($colLetter . $row)->getCalculatedValue();
            if (is_string($value)) $value = trim($value);
            $cells[] = (string) $value;
        }
        echo implode("\t| ", $cells) . "\n";
    }
    echo "\n";
}
