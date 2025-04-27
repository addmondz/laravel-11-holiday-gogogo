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
    User,
    RoomType
};
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        User::create([
            'name' => 'At Ease Admin',
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
        RoomType::truncate();

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
            'location' => 'Pulau Redang',
            'package_start_date' => '2025-01-01',
            'package_end_date' => '2025-12-31',
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

        $package = Package::create([
            'name' => 'Langkawi Package',
            'description' => '5D4N relaxing beach resort package.',
            'icon_photo' => 'packages/beach.png',
            'display_price_adult' => 1299.00,
            'display_price_child' => 999.00,
            'package_min_days' => 5,
            'package_max_days' => 10,
            'terms_and_conditions' => 'Non-refundable. Subject to availability.',
            'location' => 'Langkawi',
            'package_start_date' => '2025-01-01',
            'package_end_date' => '2025-12-31',
        ]);

        // ➕ PACKAGE ADD-ONS
        PackageAddOn::create([
            'package_id' => $package->id,
            'name' => 'Flight Ticket',
            'description' => 'Round trip flight ticket',
            'adult_price' => 1000.00,
            'child_price' => 500.00,
        ]);

        PackageAddOn::create([
            'package_id' => $package->id,
            'name' => '5D4N Langkawi 5 Star Hotel',
            'description' => '5D4N Langkawi 5 Star Hotel',
            'adult_price' => 1500.00,
            'child_price' => 1000.00,
        ]);

        $packages = Package::all();

        foreach ($packages as $pkg) {
            // 🏠 ROOM TYPES
            $deluxeRoom = RoomType::create([
                'name' => 'Deluxe Room',
                'description' => 'Spacious room with modern amenities',
                'max_occupancy' => 2,
                'package_id' => $pkg->id
            ]);

            $superiorChalet = RoomType::create([
                'name' => 'Superior Chalet',
                'description' => 'Luxury chalet with private balcony',
                'max_occupancy' => 4,
                'package_id' => $pkg->id
            ]);

            $standardRoom = RoomType::create([
                'name' => 'Standard Room',
                'description' => 'Comfortable room with basic amenities',
                'max_occupancy' => 2,
                'package_id' => $pkg->id
            ]);
        }

        // ⚙️ PACKAGE CONFIGURATIONS & 💰 PRICES FOR ALL COMBINATIONS
        $roomTypes = [
            $deluxeRoom,
            $superiorChalet,
            $standardRoom
        ];
        $combinations = [
            ['adults' => 1, 'children' => 0],
            ['adults' => 1, 'children' => 1],
            ['adults' => 1, 'children' => 2],
            ['adults' => 1, 'children' => 3],
            ['adults' => 1, 'children' => 4],
            ['adults' => 2, 'children' => 0],
            ['adults' => 2, 'children' => 1],
            ['adults' => 2, 'children' => 2],
            ['adults' => 2, 'children' => 3],
            ['adults' => 3, 'children' => 0],
            ['adults' => 3, 'children' => 1],
            ['adults' => 3, 'children' => 2],
            ['adults' => 4, 'children' => 0],
            ['adults' => 4, 'children' => 1],
            ['adults' => 5, 'children' => 0],
        ];

        $seasons = Season::all();
        $dateTypes = DateType::all();

        foreach ($packages as $pkg) {
            foreach ($seasons as $season) {
                foreach ($dateTypes as $dateType) {
                    foreach ($roomTypes as $roomType) {
                        $config = PackageConfiguration::create([
                            'package_id' => $pkg->id,
                            'season_id' => $season->id,
                            'date_type_id' => $dateType->id,
                            'room_type_id' => $roomType->id,
                        ]);

                        foreach ($combinations as $combo) {
                            ConfigurationPrice::create([
                                'package_configuration_id' => $config->id,
                                'type' => 'base_charge',
                                'number_of_adults' => $combo['adults'],
                                'number_of_children' => $combo['children'],
                                'adult_price' => 100.00,
                                'child_price' => 50.00,
                            ]);
                        }

                        // Example surcharge
                        ConfigurationPrice::create([
                            'package_configuration_id' => $config->id,
                            'type' => 'sur_charge',
                            'number_of_adults' => 2,
                            'number_of_children' => 2,
                            'adult_price' => 60.00,
                            'child_price' => 30.00,
                        ]);

                        // Example extra charge
                        ConfigurationPrice::create([
                            'package_configuration_id' => $config->id,
                            'type' => 'ext_charge',
                            'number_of_adults' => 1,
                            'number_of_children' => 0,
                            'adult_price' => 40.00,
                            'child_price' => 0.00,
                        ]);
                    }
                }
            }
        }
    }
}
