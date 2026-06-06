<?php

ini_set('memory_limit', '512M');
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = __DIR__ . '/PHẦN 7 - BÀI HỌC CUỘC SỐNG.xlsx';
$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);

for ($sheetIdx = 1; $sheetIdx <= 6; $sheetIdx++) { // sheets 2-7 (index 1-6)
    $sheet = $spreadsheet->getSheet($sheetIdx);
    $title = $sheet->getTitle();
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();
    $colCount = Coordinate::columnIndexFromString($highestCol);

    echo "\n========== Sheet " . ($sheetIdx + 1) . ": {$title} (Rows: {$highestRow}) ==========\n";

    for ($row = 1; $row <= min(25, $highestRow); $row++) {
        $a = trim((string) $sheet->getCell('A' . $row)->getCalculatedValue());
        $b = trim((string) $sheet->getCell('B' . $row)->getCalculatedValue());
        $c = trim((string) $sheet->getCell('C' . $row)->getCalculatedValue());
        $d = trim((string) $sheet->getCell('D' . $row)->getCalculatedValue());
        $e = $sheet->getCell('E' . $row)->getCalculatedValue();
        $e = is_string($e) ? trim($e) : (string) $e;
        $ePreview = mb_substr($e, 0, 60) . (mb_strlen($e) > 60 ? '...' : '');

        $hasContent = $a !== '' || $b !== '' || $c !== '' || $d !== '' || $e !== '';
        if (!$hasContent) continue;

        echo "R{$row}\tA:[{$a}]\tB:[{$b}]\tC:[{$c}]\tD:[{$d}]\tE:[{$ePreview}]\n";
    }
}
