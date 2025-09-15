<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Models\PackageConfiguration;
use App\Models\RoomType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;

class CreatePriceConfigurationsService
{
    /**
     * Create price configurations for a package
     *
     * @param int $packageId
     * @param array $roomTypes Array of room type IDs
     * @param array $seasonTypes Array of season type IDs
     * @param array $dateTypes Array of date type IDs
     * @return array Created configurations
     */
    public function createPriceConfigurationsService($package, $roomTypes = [], $seasonTypes = [], $dateTypes = [], $generateRandomPrices = false)
    {
        if ($roomTypes == []) {
            $roomTypes = RoomType::where('package_id', $package->id)->get();
        }

        if ($seasonTypes == []) {
            $seasonTypes = $package->uniqueSeasonTypes();
        }

        if ($dateTypes == []) {
            $dateTypes = $package->uniqueDateTypes();
        }

        try {
            DB::beginTransaction();

            $createdConfigurations = [];

            // Create configurations for each combination
            foreach ($roomTypes as $roomType) {
                $roomPax = $roomType->max_occupancy;
                foreach ($seasonTypes as $seasonType) {
                    foreach ($dateTypes as $dateType) {
                        // Create the package configuration

                        $configuration = PackageConfiguration::where('package_id', $package->id)
                            ->where('season_type_id', $seasonType->id)
                            ->where('date_type_id', $dateType->id)
                            ->where('room_type_id', $roomType->id)
                            ->first();

                        if (!$configuration) {
                            $configuration = PackageConfiguration::create([
                                'package_id' => $package->id,
                                'season_type_id' => $seasonType->id,
                                'date_type_id' => $dateType->id,
                                'room_type_id' => $roomType->id,
                                'configuration_prices' => $this->generateRandomPrices($roomPax, $generateRandomPrices),
                            ]);

                            // Log::info('configuration created: ' . json_encode($configuration, JSON_PRETTY_PRINT));
                        } else {
                            // Log::info('configuration already exists: ' . json_encode($configuration, JSON_PRETTY_PRINT));
                        }

                        $createdConfigurations[] = $configuration;
                    }
                }
            }

            DB::commit();
            return $createdConfigurations;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create price configurations: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateRandomPricesOld()
    {
        $faker = Faker::create();
        $combinations = AppConstants::ADULT_CHILD_COMBINATIONS;
        $configurationPrices = [];
        foreach ($combinations as $combo) {
            $keyPrefix = "{$combo['adults']}_a_{$combo['children']}_c_{$combo['infants']}_i";

            // Base charge prices
            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_a"] = $faker->randomFloat(2, 100, 1000);
            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_c"] = $faker->randomFloat(2, 50, 500);
            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_i"] = $faker->randomFloat(2, 0, 100);

            // Surcharge prices
            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_a"] = $faker->randomFloat(2, 50, 100);
            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_c"] = $faker->randomFloat(2, 25, 50);
            $configurationPrices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_i"] = $faker->randomFloat(2, 0, 10);
        }

        return $configurationPrices;
    }

    public function generateRandomPrices(int $pax = 4, bool $generateRandomPrices = true): array
    {
        $faker = Faker::create();

        $base  = [];
        $surch = [];

        // ✅ Only generate for the requested pax
        $combinations = $this->generatePaxCombinations($pax);

        foreach ($combinations as $combo) {
            $counts = $this->parsePaxCombination($combo); // ['adults'=>X,'children'=>Y,'infants'=>Z]
            if ($counts === null) {
                continue;
            }

            // Build per-person base prices like a1..aN, c1..cN, i1..iN
            $entry = [];

            for ($i = 1; $i <= $counts['adults']; $i++) {
                $entry["a{$i}"] = $generateRandomPrices ? round($faker->randomFloat(2, 75, 150), 2) : 0;
            }
            for ($i = 1; $i <= $counts['children']; $i++) {
                $entry["c{$i}"] = $generateRandomPrices ? round($faker->randomFloat(2, 60, 120), 2) : 0;
            }
            for ($i = 1; $i <= $counts['infants']; $i++) {
                $entry["i{$i}"] = $generateRandomPrices ? round($faker->randomFloat(2, 20, 60), 2) : 0;
            }

            if (!empty($entry)) {
                $base[$combo] = $entry;

                // Flat per-type surcharge (your sample uses only 'a' and 'c')
                $surch[$combo] = [
                    'a' => $generateRandomPrices ? round($faker->randomFloat(2, 2, 20), 2) : 0,
                    'c' => $generateRandomPrices ? round($faker->randomFloat(2, 2, 15), 2) : 0,
                    'i' => $generateRandomPrices ? round($faker->randomFloat(2, 1, 10), 2) : 0,
                ];
            }
        }

        return [
            [
                'base'  => $base,
                'surch' => $surch,
            ],
        ];
    }

    function generatePaxCombinations(int $pax): array
    {
        if ($pax < 1) return [];

        $combinations = [];

        for ($a = 1; $a <= $pax; $a++) {
            for ($c = 0; $c <= $pax - $a; $c++) {
                $i = $pax - $a - $c; // remaining infants
                $combinations[] = sprintf('%d_a_%d_c_%d_i', $a, $c, $i);
            }
        }

        return $combinations;
    }

    function parsePaxCombination(string $str): ?array
    {
        if (preg_match('/^(\d+)_a_(\d+)_c_(\d+)_i$/', $str, $m)) {
            return [
                'adults'   => (int) $m[1],
                'children' => (int) $m[2],
                'infants'  => (int) $m[3],
            ];
        }
        return null;
    }
}
