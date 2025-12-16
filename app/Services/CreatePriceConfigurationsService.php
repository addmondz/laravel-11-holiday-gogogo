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
                                'configuration_prices' => $this->generateRandomPrices($roomPax, $generateRandomPrices, $roomType),
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

    public function generateRandomPrices(int $pax = 4, bool $generateRandomPrices = true, $roomType = null): array
    {
        $faker = Faker::create();

        $base  = [];
        $surch = [];

        $combinations = $this->generatePaxCombinations($pax, $roomType);

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

    /**
     * Remove price entries whose pax > newRoomPax, and (optionally) add missing ones up to newRoomPax.
     * Works on both base and surch for ALL PackageConfiguration rows of the given roomType.
     *
     * @return int Number of configurations updated
     */
    public function updateConfigsToPaxAndFill(int $roomTypeId, int $newRoomPax, bool $fillMissing = true): int
    {
        if ($newRoomPax < 1) {
            throw new \InvalidArgumentException('newRoomPax must be >= 1');
        }

        $updatedCount = 0;

        // Fetch all configs for this room type across package/season/date
        $configs = PackageConfiguration::where('room_type_id', $roomTypeId)->get();

        // Get room type to check max pax limits
        $roomType = RoomType::find($roomTypeId);

        // Build the authoritative list of valid keys up to $newRoomPax using your generator
        // This will automatically filter out combinations that exceed room type max limits
        $allowedCombos = $this->generatePaxCombinations($newRoomPax, $roomType);
        $allowedSet    = array_flip($allowedCombos);

        DB::beginTransaction();
        try {
            foreach ($configs as $cfg) {
                $prices = $cfg->configuration_prices ?? [];

                // Normalize structure to: [ [ base => [], surch => [] ] ]
                [$base, $surch] = $this->extractBaseAndSurch($prices);

                // 1) Prune keys whose total pax exceeds the new limit
                $this->pruneExcessKeys($base, $surch, $allowedSet);

                // 2) Optionally fill missing keys up to the new limit (zeros)
                if ($fillMissing) {
                    $this->addMissingZeroCombos($base, $surch, $allowedCombos);
                }

                // Only save if something changed
                $newPayload = [[
                    AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE => $base,
                    AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR  => $surch,
                ]];

                // Compare shallowly to avoid noisy writes
                if ($this->payloadChanged($prices, $newPayload)) {
                    $cfg->configuration_prices = $newPayload;
                    $cfg->save();
                    $updatedCount++;
                }
            }

            DB::commit();
            Log::info("updateConfigsToPaxAndFill: updated {$updatedCount} configuration(s) for room_type_id={$roomTypeId}, newRoomPax={$newRoomPax}, fillMissing=" . ($fillMissing ? '1' : '0'));
            return $updatedCount;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('updateConfigsToPaxAndFill failed: ' . $e->getMessage(), ['room_type_id' => $roomTypeId, 'newRoomPax' => $newRoomPax]);
            throw $e;
        }
    }

    /**
     * Extract base/surch arrays from your stored payload while keeping your constants respected.
     */
    private function extractBaseAndSurch(array $prices): array
    {
        // expected: [ [ base => [...], surch => [...] ] ]
        $first = $prices[0] ?? [];

        $baseKey  = AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE; // usually 'base'
        $surchKey = AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR;  // usually 'surch'

        $base  = isset($first[$baseKey])  && is_array($first[$baseKey])  ? $first[$baseKey]  : [];
        $surch = isset($first[$surchKey]) && is_array($first[$surchKey]) ? $first[$surchKey] : [];

        return [$base, $surch];
    }

    /**
     * Remove any combo keys not present in $allowedSet (i.e., totals > new pax).
     */
    private function pruneExcessKeys(array &$base, array &$surch, array $allowedSet): void
    {
        foreach (array_keys($base) as $key) {
            if (!isset($allowedSet[$key])) {
                unset($base[$key]);
            }
        }
        foreach (array_keys($surch) as $key) {
            if (!isset($allowedSet[$key])) {
                unset($surch[$key]);
            }
        }
    }

    /**
     * Add zeroed entries for any missing allowed combos (both base & surch),
     * respecting your per-person shape for base and flat a/c/i for surch.
     */
    private function addMissingZeroCombos(array &$base, array &$surch, array $allowedCombos): void
    {
        foreach ($allowedCombos as $combo) {
            if (!isset($base[$combo])) {
                $counts        = $this->parsePaxCombination($combo); // ['adults'=>X,'children'=>Y,'infants'=>Z]
                $base[$combo]  = $this->buildZeroBaseEntry($counts);
            }
            if (!isset($surch[$combo])) {
                $surch[$combo] = $this->buildZeroSurchEntry();
            }
        }
    }

    /**
     * Build zeroed "base" entry with a1..aN, c1..cN, i1..iN keys.
     */
    private function buildZeroBaseEntry(?array $counts): array
    {
        $entry = [];
        if ($counts === null) {
            return $entry;
        }

        for ($i = 1; $i <= ($counts['adults']   ?? 0); $i++) {
            $entry["a{$i}"] = 0;
        }
        for ($i = 1; $i <= ($counts['children'] ?? 0); $i++) {
            $entry["c{$i}"] = 0;
        }
        for ($i = 1; $i <= ($counts['infants']  ?? 0); $i++) {
            $entry["i{$i}"] = 0;
        }

        return $entry;
    }

    /**
     * Build zeroed "surch" entry (flat a/c/i like in your sample).
     */
    private function buildZeroSurchEntry(): array
    {
        return ['a' => 0, 'c' => 0, 'i' => 0];
    }

    /**
     * Lightweight compare to avoid unnecessary writes.
     */
    private function payloadChanged($old, $new): bool
    {
        return json_encode($old, JSON_UNESCAPED_UNICODE) !== json_encode($new, JSON_UNESCAPED_UNICODE);
    }

    function generatePaxCombinations(int $pax, $roomType = null): array
    {
        if ($pax < 1) return [];

        // Get room type max limits (null means no limit)
        $maxAdults = $roomType?->max_adults;
        $maxChildren = $roomType?->max_children;
        $maxInfants = $roomType?->max_infants;

        $combinations = [];

        // Total party size from 1 up to $pax
        for ($total = 1; $total <= $pax; $total++) {
            for ($a = 1; $a <= $total; $a++) {           // Adults first
                // Skip if exceeds room type max adults limit
                if ($maxAdults !== null && $a > $maxAdults) {
                    continue;
                }

                for ($c = 0; $c <= $total - $a; $c++) {  // Then children
                    // Skip if exceeds room type max children limit
                    if ($maxChildren !== null && $c > $maxChildren) {
                        continue;
                    }

                    $i = $total - $a - $c;               // Infants fill the rest
                    
                    // Skip if exceeds room type max infants limit
                    if ($maxInfants !== null && $i > $maxInfants) {
                        continue;
                    }

                    $combinations[] = sprintf('%d_a_%d_c_%d_i', $a, $c, $i);
                }
            }
        }

        // Ensure consistent ordering: Adults → Children → Infants
        usort($combinations, function ($x, $y) {
            [$a1, $c1, $i1] = sscanf($x, "%d_a_%d_c_%d_i");
            [$a2, $c2, $i2] = sscanf($y, "%d_a_%d_c_%d_i");

            return [$a1, $c1, $i1] <=> [$a2, $c2, $i2];
        });

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

    /**
     * Clean price configurations for a room type that exceed max_adults, max_children, or max_infants limits.
     * 
     * @param \App\Models\RoomType $roomType
     * @return array Statistics: ['configs_processed' => int, 'configs_cleaned' => int, 'combinations_removed' => int]
     */
    public function cleanPriceConfigurationsByMaxPax($roomType): array
    {
        $maxAdults = $roomType->max_adults ?? null;
        $maxChildren = $roomType->max_children ?? null;
        $maxInfants = $roomType->max_infants ?? null;

        // Get all configurations for this room type
        $configurations = PackageConfiguration::where('room_type_id', $roomType->id)->get();

        if ($configurations->isEmpty()) {
            return [
                'configs_processed' => 0,
                'configs_cleaned' => 0,
                'combinations_removed' => 0,
            ];
        }

        $configsProcessed = 0;
        $configsCleaned = 0;
        $combinationsRemoved = 0;

        foreach ($configurations as $config) {
            Log::info('cleanPriceConfigurationsByMaxPax - $config: ' . $config->id);
            $configsProcessed++;
            $prices = $config->configuration_prices ?? [];

            if (empty($prices) || !is_array($prices) || empty($prices[0])) {
                continue;
            }

            $first = $prices[0];
            $base = $first['base'] ?? [];
            $surch = $first['surch'] ?? [];

            // Clean base combinations
            $cleanedBase = [];
            $removedFromBase = 0;
            foreach ($base as $combo => $entry) {
                $counts = $this->parsePaxCombination($combo);
                
                if ($counts === null) {
                    // Keep invalid combinations (shouldn't happen, but just in case)
                    $cleanedBase[$combo] = $entry;
                    continue;
                }

                // Check if combination exceeds any max limit
                $exceedsLimit = false;
                if ($maxAdults !== null && $counts['adults'] > $maxAdults) {
                    $exceedsLimit = true;
                }
                if ($maxChildren !== null && $counts['children'] > $maxChildren) {
                    $exceedsLimit = true;
                }
                if ($maxInfants !== null && $counts['infants'] > $maxInfants) {
                    $exceedsLimit = true;
                }

                // Safely check if combination is disabled
                $disabledCombinations = $roomType->disabled_pax_combinations ?? [];
                if (is_array($disabledCombinations) && in_array($combo, $disabledCombinations)) {
                    $exceedsLimit = true;
                }

                if ($exceedsLimit) {
                    $removedFromBase++;
                    $combinationsRemoved++;
                    // Log::info('Removed combination from base', [
                    //     'room_type_id' => $roomType->id,
                    //     'config_id' => $config->id,
                    //     'combination' => $combo,
                    //     'counts' => $counts,
                    //     'max_limits' => [
                    //         'adults' => $maxAdults,
                    //         'children' => $maxChildren,
                    //         'infants' => $maxInfants,
                    //     ]
                    // ]);
                } else {
                    $cleanedBase[$combo] = $entry;
                }
            }

            // Clean surch combinations
            $cleanedSurch = [];
            $removedFromSurch = 0;
            foreach ($surch as $combo => $entry) {
                $counts = $this->parsePaxCombination($combo);
                
                if ($counts === null) {
                    // Keep invalid combinations
                    $cleanedSurch[$combo] = $entry;
                    continue;
                }

                // Check if combination exceeds any max limit
                $exceedsLimit = false;
                if ($maxAdults !== null && $counts['adults'] > $maxAdults) {
                    $exceedsLimit = true;
                }
                if ($maxChildren !== null && $counts['children'] > $maxChildren) {
                    $exceedsLimit = true;
                }
                if ($maxInfants !== null && $counts['infants'] > $maxInfants) {
                    $exceedsLimit = true;
                }

                // Safely check if combination is disabled
                $disabledCombinations = $roomType->disabled_pax_combinations ?? [];
                if (is_array($disabledCombinations) && in_array($combo, $disabledCombinations)) {
                    $exceedsLimit = true;
                }

                if ($exceedsLimit) {
                    $removedFromSurch++;
                    $combinationsRemoved++;
                    // Log::info('Removed combination from surch', [
                    //     'room_type_id' => $roomType->id,
                    //     'config_id' => $config->id,
                    //     'combination' => $combo,
                    //     'counts' => $counts,
                    //     'max_limits' => [
                    //         'adults' => $maxAdults,
                    //         'children' => $maxChildren,
                    //         'infants' => $maxInfants,
                    //     ]
                    // ]);
                } else {
                    $cleanedSurch[$combo] = $entry;
                }
            }

            // Update configuration if changes were made
            if ($removedFromBase > 0 || $removedFromSurch > 0) {
                $configsCleaned++;
                $config->configuration_prices = [[
                    'base' => $cleanedBase,
                    'surch' => $cleanedSurch,
                ]];
                $config->save();
            }
        }

        return [
            'configs_processed' => $configsProcessed,
            'configs_cleaned' => $configsCleaned,
            'combinations_removed' => $combinationsRemoved,
        ];
    }
}
