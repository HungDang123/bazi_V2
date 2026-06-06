<?php

namespace App\Console\Commands;

use App\Models\NapGiap;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportNapGiapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:nap-giap {file : Path to Excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Nạp Giáp data from Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }
        NapGiap::truncate();
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getSheetByName('Nạp giáp');
            if (!$worksheet) {
                $this->error("Sheet 'Nạp giáp' not found in Excel file");
                return 1;
            }

            $highestRow = $worksheet->getHighestRow();
            $this->info("Found {$highestRow} rows to process");

            // Start from row 2 to skip header
            for ($row = 2; $row <= $highestRow; $row++) {
                $excelDate = $worksheet->getCell('C'.$row)->getValue();
                if (!is_numeric($excelDate)) continue;

                // Chuyển đổi từ Excel date number sang Unix timestamp
                // Excel bắt đầu từ 1/1/1900, PHP từ 1/1/1970
                // Excel có bug coi 1900 là năm nhuận nên trừ đi 1
                $unixDate = ($excelDate - 25569) * 86400;
                $date = new \DateTime();
                $date->setTimestamp($unixDate);
                
                $excelTime = $worksheet->getCell('D'.$row)->getValue();
                // Chuyển số thập phân của Excel thành giờ (vd: 0.25555 = 6:08)
                $totalHours = $excelTime * 24;
                $hour = floor($totalHours);
                $minute = round(($totalHours - $hour) * 60); // Sử dụng round thay vì floor

                $data = [
                    'nap_giap_nam' => $worksheet->getCell('A'.$row)->getValue(),
                    'nap_giap_thang' => $worksheet->getCell('B'.$row)->getValue(),
                    'thoi_diem_bat_dau_ngay' => $date->format('Y-m-d'),
                    'thoi_diem_bat_dau_gio' => sprintf('%02d:%02d:00', $hour, $minute),
                ];
                // Skip empty rows
                if (empty($data['nap_giap_nam']) || empty($data['nap_giap_thang'])) {
                    continue;
                }

                try {
                    NapGiap::create($data);
                    $this->info("Imported row {$row}");
                } catch (\Exception $e) {
                    $this->error("Error importing row {$row}: " . $e->getMessage());
                }
            }

            $this->info('Import completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }
}
