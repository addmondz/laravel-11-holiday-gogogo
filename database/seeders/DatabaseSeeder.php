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
use Faker\Factory as Faker;
use Illuminate\Support\Str;

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
        $defaultSeason = SeasonType::create(['name' => 'Default']);

        // 🟡 DATE TYPES
        $weekend = DateType::create(['name' => 'Weekend']);
        $weekday = DateType::create(['name' => 'Weekday']);
        $roomsur60 = DateType::create(['name' => 'Roomsur 60']);
        $roomsur30 = DateType::create(['name' => 'Roomsur 30']);

        // // 🧳 PACKAGE
        // $package = Package::create([
        //     'name' => 'Beach Getaway Package',
        //     'description' => '3D2N relaxing beach resort package.',
        //     'icon_photo' => 'packages/beach.png',
        //     'display_price_adult' => 399.00,
        //     'display_price_child' => 299.00,
        //     'package_min_days' => 2,
        //     'package_max_days' => 3,
        //     'terms_and_conditions' => 'Non-refundable. Subject to availability.',
        //     'location' => 'Pulau Redang',
        //     'package_start_date' => '2025-01-01',
        //     'package_end_date' => '2025-12-31',
        // ]);

        // $package = Package::create([
        //     'name' => 'Langkawi Package',
        //     'description' => '5D4N relaxing beach resort package.',
        //     'icon_photo' => 'packages/beach.png',
        //     'display_price_adult' => 1299.00,
        //     'display_price_child' => 999.00,
        //     'package_min_days' => 5,
        //     'package_max_days' => 10,
        //     'terms_and_conditions' => 'Non-refundable. Subject to availability.',
        //     'location' => 'Langkawi',
        //     'package_start_date' => '2025-01-01',
        //     'package_end_date' => '2025-12-31',
        // ]);

        $faker = Faker::create();
        $locations = [
            'Pulau Redang',
            'Langkawi',
            'Pulau Tioman',
            'Pulau Perhentian',
            'Bali',
            'Phuket',
            'Boracay',
            'Maldives',
            'Krabi',
            'Jeju Island'
        ];

        $packageTypes = [
            [
                'title' => 'Beach Getaway',
                'icon' => 'packages/beach.png',
                'min_days' => 3,
                'max_days' => 5,
                'price_range' => [300, 800],
            ],
            [
                'title' => 'Mountain Adventure',
                'icon' => 'packages/mountain.png',
                'min_days' => 4,
                'max_days' => 7,
                'price_range' => [400, 1000],
            ],
            [
                'title' => 'City Tour',
                'icon' => 'packages/city.png',
                'min_days' => 2,
                'max_days' => 4,
                'price_range' => [200, 600],
            ],
            [
                'title' => 'Island Escape',
                'icon' => 'packages/island.png',
                'min_days' => 5,
                'max_days' => 10,
                'price_range' => [800, 2000],
            ],
        ];

        // for ($i = 0; $i < 25 $i++) {
        for ($i = 0; $i < 1; $i++) {
            $packageType = $faker->randomElement($packageTypes);
            $location = $faker->randomElement($locations);
            $minDays = $packageType['min_days'];
            $maxDays = $packageType['max_days'];

            // Random days within the range
            $days = $faker->numberBetween($minDays, $maxDays);

            Package::create([
                'name' => $location . ' ' . $packageType['title'],
                'description' => "{$days}D" . ($days - 1) . "N " . strtolower($packageType['title']) . " in {$location}. " . $faker->sentence(8),
                'icon_photo' => $packageType['icon'],
                'display_price_adult' => $faker->randomFloat(2, $packageType['price_range'][0], $packageType['price_range'][1]),
                'display_price_child' => $faker->randomFloat(2, $packageType['price_range'][0] * 0.6, $packageType['price_range'][1] * 0.8),
                'package_min_days' => $minDays,
                'package_max_days' => $maxDays,
                'terms_and_conditions' => 'Non-refundable. Subject to availability. ' . $faker->sentence(8),
                'location' => $location,
                'package_start_date' => '2025-01-01',
                'package_end_date' => '2025-12-31',
                'uuid' => Str::uuid(),
            ]);
        }

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

            $defaultSeasonSeason = Season::create([
                'season_type_id' => $defaultSeason->id,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'priority' => 3,
                'package_id' => $pkg->id
            ]);

            // 🗓️ SEASONS (date ranges for season types)
            $earlyBirdSeason = Season::create([
                'season_type_id' => $earlyBird->id,
                'start_date' => '2025-01-01',
                'end_date' => '2025-02-28',
                'priority' => 1,
                'package_id' => $pkg->id
            ]);

            $peakSeasonSeason = Season::create([
                'season_type_id' => $peakSeason->id,
                'start_date' => '2025-06-01',
                'end_date' => '2025-08-31',
                'priority' => 2,
                'package_id' => $pkg->id
            ]);

            // 📆 DATE TYPE RANGES
            DateTypeRange::create([
                'date_type_id' => $roomsur30->id,
                'start_date' => '2025-07-05',
                'end_date' => '2025-07-06',
                'package_id' => $pkg->id
            ]);

            DateTypeRange::create([
                'date_type_id' => $roomsur60->id,
                'start_date' => '2025-12-01',
                'end_date' => '2025-12-15',
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
            $packageRoomTypes = RoomType::where('package_id', $pkg->id)->get();
            foreach ($seasons as $season) {
                foreach ($dateTypes as $dateType) {
                    foreach ($packageRoomTypes as $packageRoomType) {
                        $config = PackageConfiguration::create([
                            'package_id' => $pkg->id,
                            'season_id' => $season->id,
                            'date_type_id' => $dateType->id,
                            'room_type_id' => $packageRoomType->id,
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

                            // surcharge
                            ConfigurationPrice::create([
                                'package_configuration_id' => $config->id,
                                'type' => 'sur_charge',
                                'number_of_adults' => $combo['adults'],
                                'number_of_children' => $combo['children'],
                                'adult_price' => 60.00,
                                'child_price' => 30.00,
                            ]);

                            // base charge
                            ConfigurationPrice::create([
                                'package_configuration_id' => $config->id,
                                'type' => 'ext_charge',
                                'number_of_adults' => $combo['adults'],
                                'number_of_children' => $combo['children'],
                                'adult_price' => 40.00,
                                'child_price' => 0.00,
                            ]);
                        }
                    }
                }
            }
        }

        // create dummy season type and date types
        for ($i = 0; $i < 20; $i++) {
            SeasonType::create([
                'name' => 'Dummy Season Type Test ' . $i + 1,
            ]);

            DateType::create([
                'name' => 'Dummy Date Type Test ' . $i + 1,
            ]);
        }
    }
}
