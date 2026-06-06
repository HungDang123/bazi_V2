<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sim;

class SimStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sim:stats {--operator= : Filter by network operator} {--status= : Filter by status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show sim statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== SIM STATISTICS ===");
        
        $query = Sim::query();
        
        if ($operator = $this->option('operator')) {
            $query->where('network_operator', $operator);
            $this->info("Filtered by operator: {$operator}");
        }
        
        if ($status = $this->option('status')) {
            $query->where('status', $status);
            $this->info("Filtered by status: {$status}");
        }
        
        $total = $query->count();
        $this->info("Total sims: {$total}");
        
        if (!$this->option('operator')) {
            // Network operator breakdown
            $vinaphone = Sim::where('network_operator', 'vinaphone')->count();
            $mobifone = Sim::where('network_operator', 'mobifone')->count();
            $viettel = Sim::where('network_operator', 'viettel')->count();
            
            $this->info("\nNetwork operators:");
            $this->info("Vinaphone: {$vinaphone}");
            $this->info("Mobifone: {$mobifone}");
            $this->info("Viettel: {$viettel}");
        }
        
        if (!$this->option('status')) {
            // Status breakdown
            $confirmed = Sim::where('status', 'confirmed')->count();
            $pending = Sim::where('status', 'pending')->count();
            $sold = Sim::where('status', 'sold')->count();
            
            $this->info("\nStatus breakdown:");
            $this->info("Confirmed: {$confirmed}");
            $this->info("Pending: {$pending}");
            $this->info("Sold: {$sold}");
        }
        
        // Cost analysis
        $totalCost = $query->sum('cost_price');
        $avgCost = $total > 0 ? $query->avg('cost_price') : 0;
        
        $this->info("\nCost analysis:");
        $this->info("Total cost: " . number_format($totalCost, 0, ',', '.') . " VND");
        $this->info("Average cost: " . number_format($avgCost, 0, ',', '.') . " VND");
        
        // Five element breakdown
        if (!$this->option('status') && !$this->option('operator')) {
            $kim = Sim::where('five_element', 'Kim')->count();
            $moc = Sim::where('five_element', 'Mộc')->count(); 
            $thuy = Sim::where('five_element', 'Thủy')->count();
            $hoa = Sim::where('five_element', 'Hỏa')->count();
            $tho = Sim::where('five_element', 'Thổ')->count();
            $null = Sim::whereNull('five_element')->count();
            
            $this->info("\nNgũ hành breakdown:");
            $this->info("Kim: {$kim}");
            $this->info("Mộc: {$moc}");
            $this->info("Thủy: {$thuy}");
            $this->info("Hỏa: {$hoa}");
            $this->info("Thổ: {$tho}");
            if ($null > 0) {
                $this->info("Chưa xác định: {$null}");
            }
        }
        
        // Top 10 most expensive sims
        if ($total > 0) {
            $this->info("\nTop 10 most expensive sims:");
            $topSims = $query->orderBy('cost_price', 'desc')->limit(10)->get();
            
            foreach ($topSims as $sim) {
                $this->line("  {$sim->phone_number} - " . number_format($sim->cost_price, 0, ',', '.') . " VND - {$sim->network_operator} - {$sim->status}");
            }
        }

        return 0;
    }
}
