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
                foreach ($seasonTypes as $seasonType) {
                    foreach ($dateTypes as $dateType) {
                        // Create the package configuration
                        $configuration = PackageConfiguration::create([
                            'package_id' => $package->id,
                            'season_type_id' => $seasonType->id,
                            'date_type_id' => $dateType->id,
                            'room_type_id' => $roomType->id,
                            'configuration_prices' => $generateRandomPrices ? json_encode($this->generateRandomPrices()) : NULL,
                        ]);
                        
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

    public function generateRandomPrices()
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
} 