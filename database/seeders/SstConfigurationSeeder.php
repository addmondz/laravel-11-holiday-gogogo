<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class SstConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if SST configuration already exists
        $existingSstConfig = AppSetting::where('type', 'sst_configuration')->first();
        
        if (!$existingSstConfig) {
            AppSetting::create([
                'name' => 'SST Configuration',
                'type' => 'sst_configuration',
                'value' => [
                    'status' => 0, // 0 = disabled by default
                    'sst_percent' => 6.0 // 6% default
                ]
            ]);
            
            $this->command->info('SST Configuration seeded successfully.');
        } else {
            $this->command->info('SST Configuration already exists, skipping...');
        }
    }
}
