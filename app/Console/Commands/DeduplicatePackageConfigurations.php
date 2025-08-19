<?php

namespace App\Console\Commands;

use App\Models\PackageConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeduplicatePackageConfigurations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan deduplicate:package-configurations
     */
    protected $signature = 'deduplicate:package-configurations';

    /**
     * The console command description.
     */
    protected $description = 'Remove duplicate package_configurations, keeping only the most recent one.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Adjust columns used for uniqueness as needed
        $uniqueColumns = ['package_id', 'season_type_id', 'date_type_id', 'room_type_id'];

        $duplicates = DB::table('package_configurations')
            ->select($uniqueColumns)
            ->groupBy($uniqueColumns)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $query = DB::table('package_configurations');
            foreach ($uniqueColumns as $col) {
                $query->where($col, $dup->$col);
            }

            $ids = $query
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->pluck('id');

            if ($ids->count() > 1) {
                $toDelete = $ids->slice(1);
                PackageConfiguration::whereIn('id', $toDelete)->delete();

                Log::info('Deduplicated package_configurations', [
                    'kept_id'   => $ids->first(),
                    'deleted'   => $toDelete->all(),
                    'criteria'  => (array) $dup,
                ]);

                $this->info("Cleaned duplicates for " . json_encode($dup) . "; kept id={$ids->first()}");
            }
        }

        $this->info('Deduplication completed.');
    }
}
