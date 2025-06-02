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
    Booking,
    DateBlocker
};
use App\Services\CreatePriceConfigurationsService;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
    }

    public function run(): void
    {
        $dummyPackagesCount = 21;
        $dummyOtherPackagesCount = 11;
        $earliestDate = Carbon::parse('1970-01-01');
        $earliestNextDate = Carbon::parse('1970-01-02');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        User::create([
            'name' => 'At Ease Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // You can set the password as per your choice
        ]);

        for ($i = 0; $i <= $dummyOtherPackagesCount; $i++) {
            User::create([
                'name' => 'Test User ' . $i,
                'email' => 'testuser' . $i . '@test.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'), // You can set the password as per your choice
            ]);
        }

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
        for ($i = 0; $i < $dummyOtherPackagesCount; $i++) {
            SeasonType::create(['name' => 'Test Season ' . $i]);
        }

        // 🟡 DATE TYPES
        // defualt date type is weekday / weekend
        $weekend = DateType::create(['name' => 'Weekend']);
        $weekday = DateType::create(['name' => 'Weekday']);
        $roomsur60 = DateType::create(['name' => 'Roomsur 60']);
        $roomsur30 = DateType::create(['name' => 'Roomsur 30']);
        for ($i = 0; $i < $dummyOtherPackagesCount; $i++) {
            DateType::create(['name' => 'Test Date Type ' . $i]);
        }

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
                'description' => ($days + 1) . "D{$days}N " . strtolower($packageType['title']) . " in {$location}. " . $faker->sentence(8),
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
                'package_id' => $pkg->id,
                'images' => [
                    'room-types/test1.jpg',
                    'room-types/test2.png',
                    'room-types/test3.jpg'
                ]
            ]);

            $superiorChalet = RoomType::create([
                'name' => 'Superior Chalet',
                'description' => 'Luxury chalet with private balcony',
                'max_occupancy' => 4,
                'package_id' => $pkg->id,
                'images' => [
                    'room-types/test1.jpg',
                    'room-types/test2.png',
                    'room-types/test3.jpg'
                ]
            ]);

            $standardRoom = RoomType::create([
                'name' => 'Standard Room',
                'description' => 'Comfortable room with basic amenities',
                'max_occupancy' => 2,
                'package_id' => $pkg->id,
                'images' => [
                    'room-types/test1.jpg',
                    'room-types/test2.png',
                    'room-types/test3.jpg'
                ]
            ]);

            $grandDeluxeRoom = RoomType::create([
                'name' => 'Grand Deluxe Room',
                'description' => 'Luxurious room with premium amenities',
                'max_occupancy' => 4,
                'package_id' => $pkg->id,
                'images' => [
                    'room-types/test1.jpg',
                    'room-types/test2.png',
                    'room-types/test3.jpg'
                ]
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

            $startDate = Carbon::parse('2025-01-01');
            for ($i = 0; $i < $dummyOtherPackagesCount; $i++) {
                Season::create([
                    'season_type_id' => SeasonType::where('name', 'Test Season ' . $i)->first()->id,
                    'start_date' => $startDate,
                    'end_date' => $startDate->addDays(1),
                    'package_id' => $pkg->id
                ]);
                $startDate->addDays(1);
            }

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

            $startDate = Carbon::parse('2025-01-01');
            for ($i = 0; $i < $dummyOtherPackagesCount; $i++) {
                DateTypeRange::create([
                    'date_type_id' => DateType::where('name', 'Test Date Type ' . $i)->first()->id,
                    'start_date' => $startDate,
                    'end_date' => $startDate->addDays(1),
                    'package_id' => $pkg->id
                ]);
                $startDate->addDays(1);
            }

            $seasonType = SeasonType::all();
            $dateType = DateType::all();
            $roomType = RoomType::where('package_id', $pkg->id)->get();
            $this->priceConfigurationService->createPriceConfigurationsService($pkg, $roomType, $seasonType, $dateType, true);

            // 🔒 DATE BLOCKERS
            $startDate = now();
            for ($i = 0; $i < $dummyOtherPackagesCount; $i++) {
                $currentStart = $startDate->copy()->addDays($i);
                $currentEnd = $currentStart->copy()->addDay();
                $roomTypeId = RoomType::where('package_id', $pkg->id)->get()->random()->id;

                DateBlocker::create([
                    'package_id' => $pkg->id,
                    'start_date' => $currentStart,
                    'end_date' => $currentEnd,
                    'room_type_id' => $roomTypeId
                ]);
            }

            dump('completed ' . $pkg->id);
        }

        $this->call([
            BookingSeeder::class,
        ]);
    }
}
