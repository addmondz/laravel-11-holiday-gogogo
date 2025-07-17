<?php

namespace Database\Seeders;

use App\Models\DateType;
use App\Models\DateTypeRange;
use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\Season;
use App\Models\SeasonType;
use App\Services\CreatePriceConfigurationsService;
use App\Services\GeneratePackageUid;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Can;

class CustomDummySeeder extends Seeder
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    public $enabledDefaultSeasonAndDateType;
    public $dummyPackagesCount = 1;
    public $enableDateBlockerDummyData = false;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
        $this->enabledDefaultSeasonAndDateType = env('ENABLED_DEFAULT_SEASON_AND_DATE_TYPE', false);
    }

    public function run(): void
    {
        $faker = Faker::create();
        $images = ["packages/test1.jpg", "packages/test2.jpg", "packages/test3.jpg"];
        $result = array_map(fn($p) => preg_replace('/\.jpg$/', Str::random(30) . '.jpg', $p), $images);


        $pkg = Package::create([
            'name' => "3d2n Redang Beach Resort Snorkeling Package",
            'description' => 'Redang Beach Resort is situated on one of the most beautiful stretches of beach on Redang Island – Long Beach. The resort offers direct access to powdery white sands, crystal-clear turquoise waters, and vibrant coral reefs. The resort provides a range of accommodation options to suit different preferences and budgets. Redang Island is renowned for its breathtaking snorkeling sites, and Redang Beach Resort provides easy access to some of the best snorkeling spots. The resort can arrange snorkeling trips to locations with vibrant coral reefs teeming with colorful fish and other marine creatures. The clear, warm waters offer excellent visibility, allowing snorkelers to immerse themselves in the beauty of the underwater world. Other than the snorkeling trip, a wide range of activities guests can add on or arrange on their own, such as kayaking, beach volleyball, jungle trekking, and beach soccer. Guests can also unwind and relax by the swimming pool.',
            'icon_photo' => null,
            'images' => ["packages/img1.jpg", "packages/img2.jpg", "packages/img3.jpg"],
            'display_price_adult' => 799,
            'display_price_child' => 49,
            'package_min_days' => 3,
            'package_max_days' => 3,
            'terms_and_conditions' => 'Itinerary and package content is subject to last minute changes due to weather or operational issue. Activity stated (if included) is provided on complimentary basis, no refund will be made for cancellation of activities due to weather or operational issue. Other terms and conditions for booking. All the photos shown are for reference purpose only, there may be different design/decoration/setup on actual unit. Children aged between 4 years old and 11 years old (based on year of birth) will be charged according to child rate',
            'location' => 'Redang',
            'package_start_date' => '2025-07-01',
            'package_end_date' => '2025-12-31',
            'uuid' => (new GeneratePackageUid())->execute("3d2n Redang Beach Resort Snorkeling Package"),
        ]);

        $yearEndSeasonType = SeasonType::create([
            'name' => 'Year End',
        ]);
        $earlyBirdSeasonType = SeasonType::firstOrCreate([
            'name' => 'Early Bird',
        ]);
        $peakSeasonType = SeasonType::firstOrCreate([
            'name' => 'Peak Season',
        ]);

        $yearEndSeason = Season::create([
            'season_type_id' => $yearEndSeasonType->id,
            'start_date' => '2025-10-01',
            'end_date' => '2025-10-18',
            'package_id' => $pkg->id,
        ]);

        // $earlyBirdSeason = Season::create([
        //     'season_type_id' => $earlyBirdSeasonType->id,
        //     'start_date' => '2025-03-07',
        //     'end_date' => '2025-03-27',
        //     'package_id' => $pkg->id,
        // ]);

        // $peakSeason = Season::create([
        //     'season_type_id' => $peakSeasonType->id,
        //     'start_date' => '2024-03-28',
        //     'end_date' => '2025-04-30',
        //     'package_id' => $pkg->id,
        // ]);


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
        $SURWEEKDateType = DateType::create([
            'name' => 'SURWEEK',
        ]);
        $SURWEEKs= [
            ['2025/03/07', '2025/03/08'],
            ['2025/03/14', '2025/03/15'],
            ['2025/03/21', '2025/03/22'],
            ['2025/03/28', '2025/03/29'],
            ['2025/04/11', '2025/04/12'],
            ['2025/04/18', '2025/04/19'],
            ['2025/04/25', '2025/04/26'],
        ];
        foreach ($SURWEEKs as $SURWEEK) {
            DateTypeRange::create([
                'date_type_id' => $SURWEEKDateType->id,
                'start_date' => $SURWEEK[0],
                'end_date' => $SURWEEK[1],
                'package_id' => $pkg->id
            ]);
        }

        $SHPHDateType = DateType::create([
            'name' => 'SHPH',
        ]);
        $SHPHs = [
            ['2025/03/18', '2025/03/19'],
            ['2025/03/31', '2025/04/04'],
            ['2025/05/01', '2025/05/12'],
            ['2025/05/29', '2025/06/09'],
            ['2025/06/27', '2025/06/27'],
            ['2025/08/31', '2025/09/01'],
            ['2025/09/05', '2025/09/05'],
        ];
        foreach ($SHPHs as $SHPH) {
            DateTypeRange::create([
                'date_type_id' => $SHPHDateType->id,
                'start_date' => $SHPH[0],
                'end_date' => $SHPH[1],
                'package_id' => $pkg->id
            ]);
        }

        $this->priceConfigurationService->createPriceConfigurationsService($pkg, [], [], [], true);
    }

    private function downloadImages(array $urls): array
    {
        $storedPaths = [];

        foreach ($urls as $url) {
            try {
                $contents = file_get_contents($url);
                if ($contents === false) {
                    throw new \Exception("Could not read image from URL: $url");
                }

                $extension = pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'packages/' . Str::random(20) . '.' . $extension;
                Storage::disk('public')->put($filename, $contents);
                $storedPaths[] = $filename; // e.g. "packages/abc123.jpg"

            } catch (\Exception $e) {
                Log::error("Failed to download image: $url - " . $e->getMessage());
            }
        }

        return $storedPaths;
    }
}
