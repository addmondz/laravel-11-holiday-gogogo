<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{
    SeasonType,
    Season,
    DateType,
    DateTypeRange,
    Package,
    PackageAddOn,
    PackageConfiguration,
    ConfigurationPrice,
    User
};
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // You can set the password as per your choice
        ]);

        SeasonType::truncate();
        Season::truncate();
        DateType::truncate();
        DateTypeRange::truncate();
        Package::truncate();
        PackageAddOn::truncate();
        PackageConfiguration::truncate();
        ConfigurationPrice::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🟢 SEASON TYPES
        $earlyBird = SeasonType::create(['name' => 'Early Bird']);
        $peakSeason = SeasonType::create(['name' => 'Peak Season']);
        $ph120 = SeasonType::create(['name' => 'Public Holiday 120']);
        $ph60 = SeasonType::create(['name' => 'Public Holiday 60']);

        // 🗓️ SEASONS (date ranges for season types)
        $earlyBirdSeason = Season::create([
            'season_type_id' => $earlyBird->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-28',
            'priority' => 1,
        ]);

        $peakSeasonSeason = Season::create([
            'season_type_id' => $peakSeason->id,
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-31',
            'priority' => 2,
        ]);

        // 🟡 DATE TYPES
        $weekend = DateType::create(['name' => 'Weekend']);
        $weekday = DateType::create(['name' => 'Weekday']);
        $roomsur60 = DateType::create(['name' => 'Roomsur 60']);
        $roomsur30 = DateType::create(['name' => 'Roomsur 30']);

        // 📆 DATE TYPE RANGES
        DateTypeRange::create([
            'date_type_id' => $weekend->id,
            'start_date' => '2025-07-05',
            'end_date' => '2025-07-06',
        ]);

        DateTypeRange::create([
            'date_type_id' => $roomsur60->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-15',
        ]);

        // 🧳 PACKAGE
        $package = Package::create([
            'name' => 'Beach Getaway Package',
            'description' => '3D2N relaxing beach resort package.',
            'icon_photo' => 'packages/beach.png',
            'display_price_adult' => 399.00,
            'display_price_child' => 299.00,
            'package_min_days' => 2,
            'package_max_days' => 3,
            'terms_and_conditions' => 'Non-refundable. Subject to availability.',
            'location' => 'Langkawi',
            'package_start_date' => '2025-01-01',
            'package_end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        // ➕ PACKAGE ADD-ONS
        PackageAddOn::create([
            'package_id' => $package->id,
            'name' => 'Ferry Ticket',
            'description' => 'Round trip ferry ticket',
            'adult_price' => 40.00,
            'child_price' => 20.00,
        ]);

        PackageAddOn::create([
            'package_id' => $package->id,
            'name' => 'Travel Insurance',
            'description' => 'Covers up to RM10,000 in emergency cases',
            'adult_price' => 15.00,
            'child_price' => 10.00,
        ]);

        // ⚙️ PACKAGE CONFIGURATIONS
        $config1 = PackageConfiguration::create([
            'package_id' => $package->id,
            'season_id' => $earlyBirdSeason->id,
            'date_type_id' => $weekday->id,
            'room_type' => 'Deluxe Room',
        ]);

        $config2 = PackageConfiguration::create([
            'package_id' => $package->id,
            'season_id' => $peakSeasonSeason->id,
            'date_type_id' => $weekend->id,
            'room_type' => 'Superior Chalet',
        ]);

        // 💰 CONFIGURATION PRICES
        ConfigurationPrice::insert([
            [
                'package_configuration_id' => $config1->id,
                'type' => 'base_charge',
                'number_of_adults' => 2,
                'number_of_children' => 1,
                'adult_price' => 320.00,
                'child_price' => 180.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_configuration_id' => $config2->id,
                'type' => 'sur_charge',
                'number_of_adults' => 2,
                'number_of_children' => 2,
                'adult_price' => 120.00,
                'child_price' => 90.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'package_configuration_id' => $config2->id,
                'type' => 'ext_charge',
                'number_of_adults' => 1,
                'number_of_children' => 0,
                'adult_price' => 80.00,
                'child_price' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
