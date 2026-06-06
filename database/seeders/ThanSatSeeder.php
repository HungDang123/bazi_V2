<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThanSatRule;
use App\Models\ThanSatRuleDetail;

class ThanSatSeeder extends Seeder
{
    public function run()
    {
        $filePath = storage_path('app/than_sat_data.json');
        
        if (!file_exists($filePath)) {
            $this->command->error("File than_sat_data.json not found!");
            return;
        }
        
        $jsonData = json_decode(file_get_contents($filePath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON format: " . json_last_error_msg());
            return;
        }
        
        // Import rules
        foreach ($jsonData['than_sat_rules'] as $rule) {
            ThanSatRule::create($rule);
        }
        
        $this->command->info("Imported " . count($jsonData['than_sat_rules']) . " rules");
        
        // Import rule details
        foreach ($jsonData['than_sat_rule_details'] as $detail) {
            ThanSatRuleDetail::create($detail);
        }
        
        $this->command->info("Imported " . count($jsonData['than_sat_rule_details']) . " rule details");
    }
}