<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DateBlocker;
use App\Models\DateType;
use App\Models\DateTypeRange;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\Season;
use App\Models\SeasonType;
use App\Models\User;
use App\Services\CreatePriceConfigurationsService;
use App\Services\GeneratePackageUid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\PackageAddOn;
use Faker\Factory as Faker;

class DummySeeder extends Seeder
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    public $enabledDefaultSeasonAndDateType;
    public $dummyPackagesCount = 5;
    public $dummyOtherPackagesCount = 11;
    public $enableDateBlockerDummyData = false;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
        $this->enabledDefaultSeasonAndDateType = env('ENABLED_DEFAULT_SEASON_AND_DATE_TYPE', false);
    }
    public function run(): void
    {
        if ($this->enabledDefaultSeasonAndDateType) {
            for ($i = 0; $i < $this->dummyOtherPackagesCount; $i++) {
                SeasonType::create(['name' => 'Test Season ' . $i]);
            }

            for ($i = 0; $i <= $this->dummyOtherPackagesCount; $i++) {
                User::create([
                    'name' => 'Test User ' . $i,
                    'email' => 'testuser' . $i . '@test.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('12345678'),
                ]);
            }
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
            ['title' => 'Beach Getaway', 'icon' => 'packages/beach.png', 'min_days' => 5, 'max_days' => 5, 'price_range' => [300, 800]],
            ['title' => 'Mountain Adventure', 'icon' => 'packages/mountain.png', 'min_days' => 7, 'max_days' => 7, 'price_range' => [400, 1000]],
            ['title' => 'City Tour', 'icon' => 'packages/city.png', 'min_days' => 4, 'max_days' => 4, 'price_range' => [200, 600]],
            ['title' => 'Island Escape', 'icon' => 'packages/island.png', 'min_days' => 6, 'max_days' => 6, 'price_range' => [800, 2000]],
        ];
        
        for ($i = 0; $i < $this->dummyPackagesCount; $i++) {
            $packageType = $faker->randomElement($packageTypes);
            $location = $faker->randomElement($locations);
            $days = $faker->numberBetween($packageType['min_days'], $packageType['max_days']);
            $packageName = $location . ' ' . $packageType['title'];

            $pkg = Package::create([
                'name' => $packageName,
                'description' => ($days + 1) . "D{$days}N " . strtolower($packageType['title']) . " in {$location}. " . $faker->sentence(8),
                'icon_photo' => $packageType['icon'],
                'display_price_adult' => $faker->randomFloat(2, $packageType['price_range'][0], $packageType['price_range'][1]),
                'display_price_child' => $faker->randomFloat(2, $packageType['price_range'][0] * 0.6, $packageType['price_range'][1] * 0.8),
                'package_min_days' => $packageType['min_days'],
                'package_max_days' => $packageType['max_days'],
                'terms_and_conditions' => 'Non-refundable. Subject to availability. ' . $faker->sentence(8),
                'location' => $location,
                'package_start_date' => '2025-01-01',
                'package_end_date' => '2025-12-31',
                'uuid' => (new GeneratePackageUid())->execute($packageName),
                'weekend_days' => [0, 6],
            ]);

            $dummyPackageAddOnCount = 3;
            if ($this->enableDateBlockerDummyData) {
                $dummyPackageAddOnCount = $this->dummyOtherPackagesCount;
            }

            for ($packageAddOn = 0; $packageAddOn < $dummyPackageAddOnCount; $packageAddOn++) {
                PackageAddOn::create([
                    'package_id' => $pkg->id,
                    'name' => 'Add-on ' . $packageAddOn,
                    'description' => 'Add-on ' . $packageAddOn . ' description',
                    'adult_price' => $faker->randomFloat(2, 10, 50),
                    'child_price' => $faker->randomFloat(2, 5, 25),
                    'infant_price' => $faker->randomFloat(2, 0, 10),
                ]);
            }

            $weekday = DateType::where('name', 'Weekday')->first();
            $weekend = DateType::where('name', 'Weekend')->first();
            $earliestDate = Carbon::parse('1970-01-01');
            $earliestNextDate = Carbon::parse('1970-01-02');

            DateTypeRange::create([
                'date_type_id' => $weekday->id,
                'start_date' => $earliestDate,
                'end_date' => $earliestNextDate,
                'package_id' => $pkg->id
            ]);

            DateTypeRange::create([
                'date_type_id' => $weekend->id,
                'start_date' => $earliestDate,
                'end_date' => $earliestNextDate,
                'package_id' => $pkg->id
            ]);

            if ($this->enabledDefaultSeasonAndDateType) {

                $defaultSeason = SeasonType::where('name', 'Default')->first();
                $earliestDate = Carbon::parse('1970-01-01');
                $earliestNextDate = Carbon::parse('1970-01-02');
                $weekday = DateType::where('name', 'Weekday')->first();
                $weekend = DateType::where('name', 'Weekend')->first();

                Season::create([
                    'season_type_id' => $defaultSeason->id,
                    'start_date' => $earliestDate,
                    'end_date' => $earliestNextDate,
                    'package_id' => $pkg->id
                ]);
            }
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

            if ($this->enableDateBlockerDummyData) {
                // 🔒 DATE BLOCKERS
                $startDate = now();
                for ($i = 0; $i < $this->dummyOtherPackagesCount; $i++) {
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
            }

            $oneMonthAgo = now()->subMonth();
            $oneMonthLater = now()->addMonth();

            // register season type
            Season::create([
                'season_type_id' => SeasonType::first()->id,
                'start_date' => $oneMonthAgo,
                'end_date' => $oneMonthLater,
                'package_id' => $pkg->id
            ]);
            $defaultDateType = DateType::whereNotIn('name', ['weekday', 'weekend'])->get()->random();

            // register date type range
            DateTypeRange::create([
                'date_type_id' => $defaultDateType->id,
                'start_date' => $oneMonthAgo,
                'end_date' => $oneMonthLater,
                'package_id' => $pkg->id
            ]);

            // fetch package first season and date type range
            $this->priceConfigurationService->createPriceConfigurationsService($pkg, [], [], [], true);

            dump('completed ' . $pkg->id);
        }

        if ($this->enabledDefaultSeasonAndDateType) {
            $this->call([
                BookingSeeder::class,
            ]);
        }
    }
}
