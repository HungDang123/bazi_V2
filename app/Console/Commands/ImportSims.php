<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sim;
use App\Models\Que64;
use Illuminate\Support\Facades\Storage;
use App\Services\KinhDichService;

class ImportSims extends Command
{
    protected $kinhDichService;

    public function __construct(KinhDichService $kinhDichService)
    {
        parent::__construct();
        $this->kinhDichService = $kinhDichService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sim:import {--files=* : Multiple CSV files to import} {--stats : Show statistics after import} {--dry-run : Preview import without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import sim data from multiple CSV files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = $this->option('files');
        
        // If no files specified, use default files
        if (empty($files)) {
            $files = ['sims_data_1.csv', 'sims_data_2.csv', 'sims_data_3.csv'];
        }
        $ques = Que64::all(['id', 'name']);
        // Build que mapping from database
        $queMapping = $this->buildQueMapping($ques);
        
        $totalImported = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($files as $fileName) {
            $this->info("\n=== Processing file: {$fileName} ===");
            $result = $this->importFile($fileName, $queMapping);
            
            $totalImported += $result['imported'];
            $totalSkipped += $result['skipped']; 
            $totalErrors += $result['errors'];
        }

        $this->info("\n=== TOTAL SUMMARY ===");
        $this->info("Total imported: {$totalImported} records");
        $this->info("Total skipped: {$totalSkipped} records");
        $this->info("Total errors: {$totalErrors} records");

        if ($this->option('stats') && !$this->option('dry-run')) {
            $this->showStatistics();
        }

        return 0;
    }

    /**
     * Build que name to ID mapping
     */
    private function buildQueMapping($ques)
    {
        $this->info("Building que mapping...");
        $mapping = [];

        foreach ($ques as $que) {
            $normalizedName = $this->normalizeQueName($que->name);
            $mapping[$normalizedName] = $que->id;
        }
        
        $this->info("Loaded " . count($mapping) . " que mappings");
        return $mapping;
    }

    /**
     * Normalize que name for mapping (remove accents, convert to uppercase)
     */
    private function normalizeQueName($name)
    {
        // Convert to uppercase and remove extra spaces
        $name = mb_strtoupper(trim($name), 'UTF-8');
        
        // Remove all accents and special characters
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        
        // Remove any remaining non-alphanumeric characters except spaces
        $name = preg_replace('/[^A-Z0-9\s]/', '', $name);
        
        // Normalize multiple spaces to single space
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }

    /**
     * Import single file
     */
    private function importFile($fileName, $queMapping)
    {
        $fullPath = storage_path('app/' . $fileName);
        
        if (!file_exists($fullPath)) {
            $this->error("File {$fileName} not found in storage/app directory");
            return ['imported' => 0, 'skipped' => 0, 'errors' => 1];
        }

        $this->info("Starting import from: {$fileName}");
        
        $csvContent = file_get_contents($fullPath);
        $lines = explode("\n", $csvContent);
        
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $dryRun = $this->option('dry-run');
        $totalDataLines = 0;

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No data will be saved");
        }

        foreach ($lines as $lineNumber => $line) {
            // Skip empty lines and header lines (dữ liệu bắt đầu từ dòng 12 - index 11)
            if (empty(trim($line)) || $lineNumber < 11) {
                continue;
            }

            $data = str_getcsv($line);
            
            // Skip if not enough columns or first column is not numeric (row number)
            if (count($data) < 12) {
                $this->warn("Line {$lineNumber}: Not enough columns (" . count($data) . ")");
                continue;
            }
            
            if (!is_numeric($data[0])) {
                $this->warn("Line {$lineNumber}: First column is not numeric: {$data[0]}");
                continue;
            }
            
            $totalDataLines++;

            try {
                // Parse CSV columns based on the structure seen
                $phoneNumber = $data[1] ?? null;
                $costPrice = $data[2] ?? null;
                $sellingPrice = $data[3] ?? null;
                $networkOperator = strtolower($data[4] ?? '');
                $upperTrigram = $data[5] ?? null;
                $lowerTrigram = $data[6] ?? null;
                $upperTrigramName = $data[7] ?? null;
                $lowerTrigramName = $data[8] ?? null;
                $movingLine = $data[9] ?? null;
                $queName = trim($data[10] ?? ''); // Cột name của quẻ
                $fiveElement = trim($data[11] ?? ''); // Cột ngũ hành
                $statusText = $data[12] ?? 'Chưa chốt';

                // Skip if no phone number
                if (empty($phoneNumber)) {
                    continue;
                }

                // Chỉ import sim đã chốt
                if ($statusText !== 'Chốt') {
                    $skipped++;
                    continue;
                }

                // Map network operator
                $networkOperatorMap = [
                    'vinaphone' => 'vinaphone',
                    'mobifone' => 'mobifone',
                    'viettel' => 'viettel'
                ];
                
                if (!isset($networkOperatorMap[$networkOperator])) {
                    $this->warn("Unknown network operator: {$networkOperator} for phone: {$phoneNumber}");
                    $errors++;
                    continue;
                }

                // Map que name to ID
                $queId = null;
                $queBienId = null;
                if (!empty($queName)) {
                    $normalizedQueName = $this->normalizeQueName($queName);
                    if (isset($queMapping[$normalizedQueName])) {
                        $queId = $queMapping[$normalizedQueName];
                        
                        // Tính quẻ biến nếu có động hào
                        if (!empty($movingLine) && !empty($upperTrigram) && !empty($lowerTrigram)) {
                            try {
                                $result = $this->kinhDichService->tinhQue($phoneNumber);
                                
                                if (isset($result['que_bien']) && !empty($result['que_bien'])) {
                                    $queBienName = $result['que_bien'];
                                    $normalizedQueBienName = $this->normalizeQueName($queBienName);
                                    if (isset($queMapping[$normalizedQueBienName])) {
                                        $queBienId = $queMapping[$normalizedQueBienName];
                                    }
                                }
                            } catch (\Exception $e) {
                                $this->warn("Error calculating que bien for phone: {$phoneNumber} - " . $e->getMessage());
                            }
                        }
                    } else {
                        $this->warn("Unknown que name: '{$queName}' (normalized: '{$normalizedQueName}') for phone: {$phoneNumber}");
                    }
                }

                // Map five element
                $fiveElementMapping = [
                    'Kim' => 'Kim',
                    'Mộc' => 'Mộc', 
                    'Moc' => 'Mộc',
                    'Thủy' => 'Thủy',
                    'Thuy' => 'Thủy', 
                    'Hỏa' => 'Hỏa',
                    'Hoa' => 'Hỏa',
                    'Thổ' => 'Thổ',
                    'Tho' => 'Thổ'
                ];
                
                $mappedFiveElement = null;
                if (!empty($fiveElement) && isset($fiveElementMapping[$fiveElement])) {
                    $mappedFiveElement = $fiveElementMapping[$fiveElement];
                }

                // Sim mới import mặc định là chưa bán
                $status = 'available';

                // Check if sim already exists
                if (Sim::where('phone_number', $phoneNumber)->exists()) {
                    $this->info("Sim {$phoneNumber} already exists, skipping...");
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    // Create sim record
                    Sim::create([
                        'phone_number' => $phoneNumber,
                        'cost_price' => (float) str_replace(['.', ','], ['', '.'], $costPrice),
                        'selling_price' => $sellingPrice ? (float) str_replace(['.', ','], ['', '.'], $sellingPrice) : null,
                        'network_operator' => $networkOperatorMap[$networkOperator],
                        'upper_trigram' => $upperTrigram ? (int) $upperTrigram : null,
                        'lower_trigram' => $lowerTrigram ? (int) $lowerTrigram : null,
                        'upper_trigram_name' => $upperTrigramName,
                        'lower_trigram_name' => $lowerTrigramName,
                        'moving_line' => $movingLine ? (int) $movingLine : null,
                        'que_id' => $queId, // Sử dụng ID đã map
                        'que_bien_id' => $queBienId, // ID quẻ biến
                        'five_element' => $mappedFiveElement,
                        'status' => $status,
                    ]);
                } else {
                    $this->line("Would import: {$phoneNumber} - {$networkOperatorMap[$networkOperator]} - {$status} - Que ID: {$queId} - Que Bien ID: {$queBienId} - Five Element: {$mappedFiveElement}");
                }

                $imported++;
                
                if ($imported % 50 === 0) {
                    $this->info("Imported {$imported} records from {$fileName}...");
                }

            } catch (\Exception $e) {
                $this->error("Error importing line {$lineNumber} from {$fileName}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Completed {$fileName}:");
        $this->info("Total data lines processed: {$totalDataLines}");
        $this->info("Imported: {$imported} records");
        $this->info("Skipped: {$skipped} records");
        $this->info("Errors: {$errors} records");

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors];
    }

    /**
     * Show import statistics
     */
    private function showStatistics()
    {
        $this->info("\n=== STATISTICS ===");
        
        $total = Sim::count();
        $available = Sim::where('status', 'available')->count();
        $sold = Sim::where('status', 'sold')->count();
        
        $this->info("Total sims: {$total}");
        $this->info("Available (Chưa bán): {$available}");
        $this->info("Sold (Đã bán): {$sold}");
        
        // Network operator breakdown
        $vinaphone = Sim::where('network_operator', 'vinaphone')->count();
        $mobifone = Sim::where('network_operator', 'mobifone')->count();
        $viettel = Sim::where('network_operator', 'viettel')->count();
        
        $this->info("\nNetwork operators:");
        $this->info("Vinaphone: {$vinaphone}");
        $this->info("Mobifone: {$mobifone}");
        $this->info("Viettel: {$viettel}");
        
        // Cost analysis
        $totalCost = Sim::sum('cost_price');
        $availableCost = Sim::where('status', 'available')->sum('cost_price');
        $soldCost = Sim::where('status', 'sold')->sum('cost_price');
        
        $this->info("\nCost analysis:");
        $this->info("Total cost: " . number_format($totalCost, 0, ',', '.') . " VND");
        $this->info("Available cost: " . number_format($availableCost, 0, ',', '.') . " VND");
        $this->info("Sold cost: " . number_format($soldCost, 0, ',', '.') . " VND");
    }
}
