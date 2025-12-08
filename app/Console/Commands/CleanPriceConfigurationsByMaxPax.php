<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\PackageConfiguration;
use App\Services\CreatePriceConfigurationsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanPriceConfigurationsByMaxPax extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan clean:price-configurations-by-max-pax
     */
    protected $signature = 'clean:price-configurations-by-max-pax';

    /**
     * The console command description.
     */
    protected $description = 'Clean price configurations that exceed package max_adults, max_children, or max_infants limits.';

    protected CreatePriceConfigurationsService $priceConfigService;

    public function __construct(CreatePriceConfigurationsService $priceConfigService)
    {
        parent::__construct();
        $this->priceConfigService = $priceConfigService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find all packages with max_pax limits set
        $packages = Package::where(function ($query) {
            $query->whereNotNull('max_adults')
                ->orWhereNotNull('max_children')
                ->orWhereNotNull('max_infants');
        })->get();

        if ($packages->isEmpty()) {
            $this->info('No packages found with max_pax limits set.');
            return 0;
        }

        $this->info("Found {$packages->count()} package(s) with max_pax limits.");

        $totalConfigsProcessed = 0;
        $totalConfigsCleaned = 0;
        $totalCombinationsRemoved = 0;

        DB::beginTransaction();
        try {
            foreach ($packages as $package) {
                $this->line("Processing Package: {$package->name} (ID: {$package->id})");
                
                $maxAdults = $package->max_adults ?? null;
                $maxChildren = $package->max_children ?? null;
                $maxInfants = $package->max_infants ?? null;

                $this->line("  Max Limits - Adults: " . ($maxAdults ?? 'unlimited') . 
                           ", Children: " . ($maxChildren ?? 'unlimited') . 
                           ", Infants: " . ($maxInfants ?? 'unlimited'));

                // Use the service method to clean configurations
                $stats = $this->priceConfigService->cleanPriceConfigurationsByMaxPax($package);

                $totalConfigsProcessed += $stats['configs_processed'];
                $totalConfigsCleaned += $stats['configs_cleaned'];
                $totalCombinationsRemoved += $stats['combinations_removed'];

                if ($stats['configs_cleaned'] > 0) {
                    $this->info("  ✓ Cleaned {$stats['configs_cleaned']} configuration(s), removed {$stats['combinations_removed']} combination(s)");
                } else {
                    $this->line("  ✓ No cleaning needed for this package");
                }
            }

            DB::commit();
            $this->info("\n✅ Cleaning completed successfully!");

            $this->info("Summary:");
            $this->info("  - Packages processed: {$packages->count()}");
            $this->info("  - Configurations processed: {$totalConfigsProcessed}");
            $this->info("  - Configurations cleaned: {$totalConfigsCleaned}");
            $this->info("  - Total combinations removed: {$totalCombinationsRemoved}");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            Log::error('CleanPriceConfigurationsByMaxPax failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
