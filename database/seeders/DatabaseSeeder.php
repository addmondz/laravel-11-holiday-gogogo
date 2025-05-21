<?php

namespace Database\Seeders;

use App\Constants\AppConstants;
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
    User,
    RoomType,
    Booking
};
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $dummyPackagesCount = 2;
        // $dummyPackagesCount = 20;
        $earliestDate = Carbon::parse('1970-01-01');
        $earliestNextDate = Carbon::parse('1970-01-02');

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
        RoomType::truncate();
        Booking::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🟢 SEASON TYPES
        $defaultSeason = SeasonType::create(['name' => 'Default']);
        $earlyBird = SeasonType::create(['name' => 'Early Bird']);
        $peakSeason = SeasonType::create(['name' => 'Peak Season']);
        $ph120 = SeasonType::create(['name' => 'Public Holiday 120']);
        $ph60 = SeasonType::create(['name' => 'Public Holiday 60']);

        // 🟡 DATE TYPES
        // defualt date type is weekday / weekend
        $weekend = DateType::create(['name' => 'Weekend']);
        $weekday = DateType::create(['name' => 'Weekday']);
        $roomsur60 = DateType::create(['name' => 'Roomsur 60']);
        $roomsur30 = DateType::create(['name' => 'Roomsur 30']);

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
                'min_days' => 5,
                'max_days' => 5,
                'price_range' => [300, 800],
            ],
            [
                'title' => 'Mountain Adventure',
                'icon' => 'packages/mountain.png',
                'min_days' => 7,
                'max_days' => 7,
                'price_range' => [400, 1000],
            ],
            [
                'title' => 'City Tour',
                'icon' => 'packages/city.png',
                'min_days' => 4,
                'max_days' => 4,
                'price_range' => [200, 600],
            ],
            [
                'title' => 'Island Escape',
                'icon' => 'packages/island.png',
                'min_days' => 6,
                'max_days' => 6,
                'price_range' => [800, 2000],
            ],
        ];

        for ($i = 0; $i < $dummyPackagesCount; $i++) {
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

            // 🗓️ SEASONS (date ranges for season types)
            // Create non-overlapping seasons for each package
            // Default season (Jan 1 - Feb 28)
            Season::create([
                'season_type_id' => $defaultSeason->id,
                'start_date' => $earliestDate,
                'end_date' => $earliestNextDate,
                'package_id' => $pkg->id
            ]);

            // Early Bird season (Mar 1 - May 31)
            Season::create([
                'season_type_id' => $earlyBird->id,
                'start_date' => '2025-03-01',
                'end_date' => '2025-05-31',
                'package_id' => $pkg->id
            ]);

            // Peak Season (Jun 1 - Aug 31)
            Season::create([
                'season_type_id' => $peakSeason->id,
                'start_date' => '2025-06-01',
                'end_date' => '2025-08-31',
                'package_id' => $pkg->id
            ]);

            // Public Holiday 120 (Sep 1 - Oct 31)
            Season::create([
                'season_type_id' => $ph120->id,
                'start_date' => '2025-09-01',
                'end_date' => '2025-10-31',
                'package_id' => $pkg->id
            ]);

            // Public Holiday 60 (Nov 1 - Dec 31)
            Season::create([
                'season_type_id' => $ph60->id,
                'start_date' => '2025-11-01',
                'end_date' => '2025-12-31',
                'package_id' => $pkg->id
            ]);

            // 📆 DATE TYPE RANGES
            // Create non-overlapping date type ranges for each package
            // Weekday (Jan 1 - Dec 31, Monday to Thursday)
            DateTypeRange::create([
                'date_type_id' => $weekday->id,
                'start_date' => $earliestDate,
                'end_date' => $earliestNextDate,
                'package_id' => $pkg->id
            ]);

            // Weekend (Jan 1 - Dec 31, Friday to Sunday)
            DateTypeRange::create([
                'date_type_id' => $weekend->id,
                'start_date' => $earliestDate,
                'end_date' => $earliestNextDate,
                'package_id' => $pkg->id
            ]);

            // Roomsur 30 (Jul 5 - Jul 6)
            DateTypeRange::create([
                'date_type_id' => $roomsur30->id,
                'start_date' => '2025-07-05',
                'end_date' => '2025-07-06',
                'package_id' => $pkg->id
            ]);

            // Roomsur 60 (Dec 1 - Dec 15)
            DateTypeRange::create([
                'date_type_id' => $roomsur60->id,
                'start_date' => '2025-12-01',
                'end_date' => '2025-12-15',
                'package_id' => $pkg->id
            ]);

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

            $season = Season::all();
            foreach ($season as $season) {
                $dateType = DateType::all();
                foreach ($dateType as $dateType) {
                    $roomType = RoomType::all();
                    foreach ($roomType as $roomType) {
                        foreach ($combinations as $combo) {
                            $keyPrefix = "{$combo['adults']}_a_{$combo['children']}_c";

                            // Base charge prices
                            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_a"] = $faker->randomFloat(2, 100, 1000);
                            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_c"] = $faker->randomFloat(2, 50, 500);

                            // Surcharge prices
                            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_a"] = $faker->randomFloat(2, 50, 1000);
                            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_c"] = $faker->randomFloat(2, 25, 500);
                        }

                        PackageConfiguration::create([
                            'package_id' => $pkg->id,
                            'season_id' => $season->id,
                            'date_type_id' => $dateType->id,
                            'room_type_id' => $roomType->id,
                            'configuration_prices' => json_encode($configurationPrices)
                        ]);
                    }
                }
            }
            dump('completed ' . $pkg->id);
        }

        $this->call([
            BookingSeeder::class,
        ]);
    }
}
