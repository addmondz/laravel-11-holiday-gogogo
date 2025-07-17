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
    User,
    RoomType,
    Booking,
    DateBlocker
};
use App\Services\CreatePriceConfigurationsService;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    public $enabledDefaultSeasonAndDateType;
    public $dummyPackagesCount = 2;
    public $dummyOtherPackagesCount = 11;
    public $enableDateBlockerDummyData = false;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
        $this->enabledDefaultSeasonAndDateType = env('ENABLED_DEFAULT_SEASON_AND_DATE_TYPE', false);
    }

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        SeasonType::truncate();
        Season::truncate();
        DateType::truncate();
        DateTypeRange::truncate();
        Package::truncate();
        PackageAddOn::truncate();
        PackageConfiguration::truncate();
        RoomType::truncate();
        Booking::truncate();

        User::create([
            'name' => 'At Ease Admin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
        ]);

        $defaultSeason = null;
        $weekend = DateType::create(['name' => 'Weekend']);
        $weekday = DateType::create(['name' => 'Weekday']);

        if ($this->enabledDefaultSeasonAndDateType) {
            $defaultSeason = SeasonType::create(['name' => 'Default']);
        }

        $isProduction = env('APP_ENV', 'production');
        $isProduction = false;
        if ($isProduction) {
            return;
        }

        $dummyPackagesCount = $this->dummyPackagesCount;
        $dummyOtherPackagesCount = $this->dummyOtherPackagesCount;
        $earliestDate = Carbon::parse('1970-01-01');
        $earliestNextDate = Carbon::parse('1970-01-02');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🔒 SEASON TYPES
        $earlyBird = SeasonType::create(['name' => 'Early Bird']);
        $peakSeason = SeasonType::create(['name' => 'Peak Season']);
        $ph120 = SeasonType::create(['name' => 'Public Holiday 120']);
        $ph60 = SeasonType::create(['name' => 'Public Holiday 60']);

        // 🔒 DATE TYPES
        $roomsur60 = DateType::create(['name' => 'Roomsur 60']);
        $roomsur30 = DateType::create(['name' => 'Roomsur 30']);
        for ($i = 0; $i < $dummyOtherPackagesCount; $i++) {
            DateType::create(['name' => 'Test Date Type ' . $i]);
        }

        $this->call([
            CustomDummySeeder::class,
            DummySeeder::class,
        ]);
    }
}
