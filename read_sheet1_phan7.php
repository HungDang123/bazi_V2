<?php

ini_set('memory_limit', '512M');
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = __DIR__ . '/PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx';
$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);

$sheet = $spreadsheet->getSheet(0); // Sheet 1
$title = $sheet->getTitle();
$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();
$colCount = Coordinate::columnIndexFromString($highestCol);

echo "Sheet: {$title}\nRows: {$highestRow}, Cols: {$highestCol} ({$colCount})\n\n";

for ($row = 1; $row <= $highestRow; $row++) {
    echo "--- Row {$row} ---\n";
    for ($c = 1; $c <= $colCount; $c++) {
        $colLetter = Coordinate::stringFromColumnIndex($c);
        $value = $sheet->getCell($colLetter . $row)->getCalculatedValue();
        if (is_string($value)) $value = trim($value);
        $len = is_string($value) ? strlen($value) : 0;
        $preview = is_string($value) ? mb_substr($value, 0, 80) . ($len > 80 ? '...' : '') : (string)$value;
        echo "  Col {$colLetter}: len={$len} | " . str_replace("\n", "\\n", $preview) . "\n";
    }
    echo "\n";
}
