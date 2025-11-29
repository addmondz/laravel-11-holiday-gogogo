<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;

class FixPackageMaxDaysDifferentFromMinDays extends Command
{
    protected $signature = 'fix:package-max-days-different-from-min-days';
    protected $description = 'Fix package_max_days different from package_min_days.';

    public function handle()
    {
        Package::whereColumn('package_max_days', '!=', 'package_min_days')
            ->chunkById(500, function ($packages) {

                foreach ($packages as $package) {
                    $oldMax = $package->package_max_days;
                    $min    = $package->package_min_days;

                    $package->update([
                        'package_max_days' => $min,
                    ]);

                    // FIXED → no array in second param
                    $this->info("Package {$package->id} fixed (from {$oldMax} to {$min})");
                }
            });

        $this->info("Done fixing packages.");
    }
}
