<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Models\Season;
use App\Models\DateTypeRange;
use App\Models\PackageConfiguration;
use App\Models\ConfigurationPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigurationPriceController extends Controller
{
    private function handlePriceConfiguration(array $validated)
    {
        $combinations = AppConstants::ADULT_CHILD_COMBINATIONS;
        $configuration = PackageConfiguration::firstOrCreate(
            [
                'package_id' => $validated['package_id'],
                'season_type_id' => $validated['season_type_id'],
                'date_type_id' => $validated['date_type_id'],
                'room_type_id' => $validated['room_type']
            ]
        );

        // Build configuration_prices JSON
        $configurationPrices = [];

        foreach (['base_charge' => 'b', 'sur_charge' => 's'] as $inputType => $jsonKey) {
            if (!isset($validated['prices'][$inputType])) continue;

            foreach ($validated['prices'][$inputType] as $price) {
                $combination = [
                    'adults' => $price['number_of_adults'],
                    'children' => $price['number_of_children']
                ];
                if (!in_array($combination, $combinations)) {
                    continue;
                }

                $adultKey = "{$combination['adults']}_a_{$combination['children']}_c_a";
                $childKey = "{$combination['adults']}_a_{$combination['children']}_c_c";

                if (!isset($configurationPrices[$jsonKey])) {
                    $configurationPrices[$jsonKey] = [];
                }

                if (!empty($price['adult_price'])) {
                    $configurationPrices[$jsonKey][$adultKey] = (float) $price['adult_price'];
                }

                if (!empty($price['child_price'])) {
                    $configurationPrices[$jsonKey][$childKey] = (float) $price['child_price'];
                }
            }
        }

        $configuration->configuration_prices = json_encode($configurationPrices);
        $configuration->save();

        return $configuration;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type' => 'required|exists:room_types,id',
            'prices' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            $this->handlePriceConfiguration($validated);
            DB::commit();
            return back()->with('success', 'Configuration prices created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ConfigurationPrice store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'Error creating configuration prices: ' . $e->getMessage());
        }
    }

    public function updatePrices(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type' => 'required|exists:room_types,id',
            'prices' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            $this->handlePriceConfiguration($validated);
            DB::commit();
            return response()->json(['message' => 'Configuration prices updated successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ConfigurationPrice update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['message' => 'Error updating configuration prices: ' . $e->getMessage()], 500);
        }
    }

    public function fetchPricesSearchIndex(Request $request)
    {
        $configs = PackageConfiguration::where('package_id', $request->package_id)
            ->where('season_type_id', $request->season_type_id)
            ->where('date_type_id', $request->date_type_id)
            ->where('room_type_id', $request->room_type_id)
            ->get();

        $response = $configs->map(function ($config) {
            $rawPrices = json_decode($config->configuration_prices, true);
            $structuredPrices = [];

            foreach (AppConstants::CONFIGURATION_PRICE_TYPES_SNAKE_CASE as $typeKey => $typeValue) {
                if (!isset($rawPrices[$typeKey])) continue;

                foreach ($rawPrices[$typeKey] as $code => $price) {
                    // Example code: "1_a_2_c_a"
                    preg_match('/(\d+)_a_(\d+)_c_([ac])/', $code, $matches);
                    if (!$matches) continue;

                    [$full, $adults, $children, $personType] = $matches;

                    $titleCaseType = ucwords(str_replace('_', ' ', $typeKey)); // Convert to "Base Charge", etc.
                    $key = "{$adults}_{$children}_{$titleCaseType}";

                    if (!isset($structuredPrices[$key])) {
                        $structuredPrices[$key] = [
                            'id' => null, // Always null like in API 2
                            'package_configuration_id' => $config->id,
                            'type' => $typeValue,
                            'number_of_adults' => (int) $adults,
                            'number_of_children' => (int) $children,
                            'adult_price' => null,
                            'child_price' => null,
                            'created_at' => $config->created_at,
                            'updated_at' => $config->updated_at,
                            'deleted_at' => null,
                        ];
                    }

                    if ($personType === 'a') {
                        $structuredPrices[$key]['adult_price'] = number_format($price, 2, '.', '');
                    } elseif ($personType === 'c') {
                        $structuredPrices[$key]['child_price'] = number_format($price, 2, '.', '');
                    }
                }
            }

            return [
                'id' => $config->id,
                'package_id' => $config->package_id,
                'season_id' => $config->season_id,
                'date_type_id' => $config->date_type_id,
                'room_type_id' => $config->room_type_id,
                'created_at' => $config->created_at,
                'updated_at' => $config->updated_at,
                'deleted_at' => $config->deleted_at,
                'prices' => array_values($structuredPrices),
            ];
        });

        return response()->json($response);
    }

    public function fetchSeasonIdBySeasonTypeAndPackageId($season_type_id, $package_id)
    {
        return Season::where('season_type_id', $season_type_id)
            ->where('package_id', $package_id)
            ->first()?->id;
    }

    public function fetchDateTypeIdByDateTypeAndPackageId($date_type_id, $package_id)
    {
        return DateTypeRange::where('date_type_id', $date_type_id)
            ->where('package_id', $package_id)
            ->first()?->id;
    }

    /**
     * Fetch price configurations for room types based on filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPricesRoomTypes(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
        ]);

        $configurations = PackageConfiguration::with('roomType')
            ->where('package_id', $request->package_id)
            ->where('season_type_id', $request->season_type_id)
            ->where('date_type_id', $request->date_type_id)
            ->get();

        // If no configurations exist, return empty array
        if ($configurations->isEmpty()) {
            return response()->json([]);
        }

        $response = $configurations->map(function ($config) {
            $rawPrices = json_decode($config->configuration_prices, true);
            $structuredPrices = [];

            // Process base charge and surcharge prices
            foreach (['b' => 'base_charge', 's' => 'sur_charge'] as $jsonKey => $type) {
                if (!isset($rawPrices[$jsonKey])) continue;

                foreach ($rawPrices[$jsonKey] as $code => $price) {
                    // Example code: "1_a_2_c_a" (1 adult, 2 children, adult price)
                    preg_match('/(\d+)_a_(\d+)_c_([ac])/', $code, $matches);
                    if (!$matches) continue;

                    [$full, $adults, $children, $personType] = $matches;
                    $key = "{$adults}_{$children}_{$type}";

                    if (!isset($structuredPrices[$key])) {
                        $structuredPrices[$key] = [
                            'type' => $type,
                            'number_of_adults' => (int) $adults,
                            'number_of_children' => (int) $children,
                            'adult_price' => null,
                            'child_price' => null,
                        ];
                    }

                    if ($personType === 'a') {
                        $structuredPrices[$key]['adult_price'] = number_format($price, 2, '.', '');
                    } elseif ($personType === 'c') {
                        $structuredPrices[$key]['child_price'] = number_format($price, 2, '.', '');
                    }
                }
            }

            return [
                'id' => $config->id,
                'package_id' => $config->package_id,
                'season_type_id' => $config->season_type_id,
                'date_type_id' => $config->date_type_id,
                'room_type_id' => $config->room_type_id,
                'room_type' => $config->roomType,
                'prices' => array_values($structuredPrices),
                'created_at' => $config->created_at,
                'updated_at' => $config->updated_at,
            ];
        });

        return response()->json($response);
    }

    public function updateRoomTypePrices(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type_id' => 'required|exists:room_types,id',
            'prices' => 'required|array',
            'prices.*.type' => 'required|in:base_charge,sur_charge',
            'prices.*.number_of_adults' => 'required|integer|min:1',
            'prices.*.number_of_children' => 'required|integer|min:0',
            'prices.*.adult_price' => 'required|numeric|min:0',
            'prices.*.child_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $configuration = PackageConfiguration::firstOrCreate(
                [
                    'package_id' => $validated['package_id'],
                    'season_type_id' => $validated['season_type_id'],
                    'date_type_id' => $validated['date_type_id'],
                    'room_type_id' => $validated['room_type_id']
                ]
            );

            // Build configuration_prices JSON
            $configurationPrices = [];

            foreach ($validated['prices'] as $price) {
                $type = $price['type'] === 'base_charge' ? 'b' : 's';
                $adults = $price['number_of_adults'];
                $children = $price['number_of_children'];

                $adultKey = "{$adults}_a_{$children}_c_a";
                $childKey = "{$adults}_a_{$children}_c_c";

                if (!isset($configurationPrices[$type])) {
                    $configurationPrices[$type] = [];
                }

                $configurationPrices[$type][$adultKey] = (float) $price['adult_price'];
                $configurationPrices[$type][$childKey] = (float) $price['child_price'];
            }

            $configuration->configuration_prices = json_encode($configurationPrices);
            $configuration->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Room type prices updated successfully.',
                'configuration' => $configuration
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Room type price update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating room type prices: ' . $e->getMessage()
            ], 500);
        }
    }
}
