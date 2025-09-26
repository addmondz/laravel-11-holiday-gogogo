<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Models\Season;
use App\Models\DateTypeRange;
use App\Models\PackageConfiguration;
use App\Models\ConfigurationPrice;
use App\Models\DateType;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\SeasonType;
use App\Services\CreatePriceConfigurationsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigurationPriceController extends Controller
{
    protected CreatePriceConfigurationsService $priceConfigurationService;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
    }

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

    private function handlePriceConfigurationNew(array $prices, $package_id, $season_type_id, $date_type_id, $room_type)
    {
        $combinations = AppConstants::ADULT_CHILD_COMBINATIONS;
        $configuration = PackageConfiguration::firstOrCreate(
            [
                'package_id' => $package_id,
                'season_type_id' => $season_type_id,
                'date_type_id' => $date_type_id,
                'room_type_id' => $room_type
            ]
        );

        // Build configuration_prices JSON
        $configurationPrices = [];

        foreach (['base_charge' => 'b', 'sur_charge' => 's'] as $inputType => $jsonKey) {
            if (!isset($prices[$inputType])) continue;

            foreach ($prices[$inputType] as $price) {
                $combination = [
                    'adults' => $price['number_of_adults'],
                    'children' => $price['number_of_children'],
                    'infants' => $price['number_of_infants']
                ];
                if (!in_array($combination, $combinations)) {
                    continue;
                }

                $adultKey = "{$combination['adults']}_a_{$combination['children']}_c_{$combination['infants']}_i_a";
                $childKey = "{$combination['adults']}_a_{$combination['children']}_c_{$combination['infants']}_i_c";
                $infantKey = "{$combination['adults']}_a_{$combination['children']}_c_{$combination['infants']}_i_i";

                if (!isset($configurationPrices[$jsonKey])) {
                    $configurationPrices[$jsonKey] = [];
                }

                if (!empty($price['adult_price'])) {
                    $configurationPrices[$jsonKey][$adultKey] = (float) $price['adult_price'];
                }

                if (!empty($price['child_price'])) {
                    $configurationPrices[$jsonKey][$childKey] = (float) $price['child_price'];
                }

                if (!empty($price['infant_price'])) {
                    $configurationPrices[$jsonKey][$infantKey] = (float) $price['infant_price'];
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
        // Log::info('fetchPricesRoomTypes', $request->all());
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type_id' => 'nullable|exists:room_types,id'
        ]);

        $query = PackageConfiguration::with('roomType')
            ->where('package_id', $request->package_id)
            ->where('season_type_id', $request->season_type_id)
            ->where('date_type_id', $request->date_type_id);

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        $roomTypeIds = RoomType::where('package_id', $request->package_id)->pluck('id');

        $this->ensureItHasAllCombinations($query, $request->all(), $roomTypeIds);

        $this->ensureOnlyOnePriceConfiguration($query, $request->all(), $roomTypeIds);

        $configurations = $query->get();
        // Log::info('configurations:' . json_encode($configurations, JSON_PRETTY_PRINT));

        // If no configurations exist, return empty array
        if ($configurations->isEmpty()) {
            return response()->json([]);
        }

        $response = $configurations->map(function ($config) {
            $prices = $config->configuration_prices;

            if (is_string($prices)) {
                $decoded = json_decode($prices, true);
                // handle double-encoding if it happens elsewhere
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                $prices = $decoded;
            }

            return [
                'id'                    => $config->id,
                'package_id'            => $config->package_id,
                'season_type_id'        => $config->season_type_id,
                'date_type_id'          => $config->date_type_id,
                'room_type_id'          => $config->room_type_id,
                'prices'                => $prices,
                'room_type_name'        => $config->roomType->name,
                'room_type_capacity'    => $config->roomType->max_occupancy,
            ];
        });

        return response()->json($response);
    }

    public function updateRoomTypePrices(Request $request)
    {
        $validated = $request->validate([
            'rooms.*.package_configuration_id' => 'required',
            'rooms.*.base' => 'required|array',
            'rooms.*.surch' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['rooms'] as $room) {
                $packageConfiguration = PackageConfiguration::find($room['package_configuration_id']);
                $pricesArray = [
                    [
                        'base' => $room['base'],
                        'surch' => $room['surch'],
                    ]
                ];
                // $packageConfiguration->configuration_prices = json_encode($pricesArray);
                $packageConfiguration->configuration_prices = $pricesArray;
                $packageConfiguration->save();
            }
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

    public function updatePriceConfigurationByPax(Request $request)
    {
        $validated = $request->validate([
            'package_id'           => 'required|exists:packages,id',
            'adult'                => 'required|integer|min:0',
            'child'                => 'required|integer|min:0',
            'infant'               => 'required|integer|min:0',
            'adult_base_charge'    => 'required|numeric|min:0',
            'child_base_charge'    => 'required|numeric|min:0',
            'infant_base_charge'   => 'required|numeric|min:0',
            'adult_surcharge'      => 'required|numeric|min:0',
            'child_surcharge'      => 'required|numeric|min:0',
            'infant_surcharge'     => 'required|numeric|min:0',
        ]);

        $roomTypes = RoomType::where('package_id', $validated['package_id'])->get();

        $season_type_ids = Season::where('package_id', $validated['package_id'])->distinct('season_type_id')->pluck('season_type_id');
        $seasonTypes = SeasonType::whereIn('id', $season_type_ids)->get();

        $date_type_ids = DateTypeRange::where('package_id', $validated['package_id'])->distinct('date_type_id')->pluck('date_type_id');
        $dateTypes = DateType::whereIn('id', $date_type_ids)->get();

        $combinations = AppConstants::ADULT_CHILD_COMBINATIONS;
        $baseKey = AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE;
        $surKey = AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE;

        foreach ($roomTypes as $room) {
            foreach ($seasonTypes as $season) {
                foreach ($dateTypes as $date) {
                    $exists = PackageConfiguration::where([
                        'package_id' => $validated['package_id'],
                        'room_type_id' => $room->id,
                        'season_type_id' => $season->id,
                        'date_type_id' => $date->id,
                    ])->first();

                    if (!$exists) {
                        $prices = [];

                        foreach ($combinations as $c) {
                            $k = "{$c['adults']}_a_{$c['children']}_c_{$c['infants']}_i";
                            $prices[$baseKey]["{$k}_a"] = 0;
                            $prices[$baseKey]["{$k}_c"] = 0;
                            $prices[$baseKey]["{$k}_i"] = 0;
                            $prices[$surKey]["{$k}_a"] = 0;
                            $prices[$surKey]["{$k}_c"] = 0;
                            $prices[$surKey]["{$k}_i"] = 0;
                        }

                        $new = PackageConfiguration::create([
                            'package_id' => $validated['package_id'],
                            'room_type_id' => $room->id,
                            'season_type_id' => $season->id,
                            'date_type_id' => $date->id,
                            'configuration_prices' => json_encode($prices),
                        ]);

                        $newlyCreatedConfigs[] = $new;
                    }
                }
            }
        }

        $adultKey = "{$validated['adult']}_a_{$validated['child']}_c_{$validated['infant']}_i_a";
        $childKey = "{$validated['adult']}_a_{$validated['child']}_c_{$validated['infant']}_i_c";
        $infantKey = "{$validated['adult']}_a_{$validated['child']}_c_{$validated['infant']}_i_i";

        $packageConfigurations = PackageConfiguration::where('package_id', $validated['package_id'])->get();

        foreach ($packageConfigurations as $packageConfiguration) {
            $price = json_decode($packageConfiguration->configuration_prices, true);
            $price['b'][$adultKey] = $validated['adult_base_charge'];
            $price['b'][$childKey] = $validated['child_base_charge'];
            $price['b'][$infantKey] = $validated['infant_base_charge'];
            $price['s'][$adultKey] = $validated['adult_surcharge'];
            $price['s'][$childKey] = $validated['child_surcharge'];
            $price['s'][$infantKey] = $validated['infant_surcharge'];
            $packageConfiguration->configuration_prices = json_encode($price);
            $packageConfiguration->save();
        }

        // You can now use $validated safely
        return response()->json(['message' => 'Configuration prices updated successfully.']);
    }

    public function createPriceConfiguration(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
            // 'room_type_id' => 'required|exists:room_types,id',
        ]);

        $roomType = isset($validated['room_type_id']) ? RoomType::find($validated['room_type_id']) : null;
        $seasonType = SeasonType::find($validated['season_type_id']);
        $dateType = DateType::find($validated['date_type_id']);

        $this->priceConfigurationService->createPriceConfigurationsService(
            Package::find($validated['package_id']),
            isset($validated['room_type_id']) ? [$roomType] : [],
            [$seasonType],
            [$dateType],
            false
        );

        return response()->json(['message' => 'Configuration prices created successfully.']);
    }


    public function ensureOnlyOnePriceConfiguration($query, $requestParam, $roomTypeIds)
    {
        $cloneQuery = $query->clone();
        $request_room_type_id = $requestParam['room_type_id'] ?? null;
        $configToDisplay = $request_room_type_id ? 1 : $roomTypeIds->count();

        if ($cloneQuery->get()->count() > $configToDisplay) {
            Log::info('Need cleaning');
            $this->initializePriceConfigurationsCleaning();
        } else {
            // Log::info('No need cleaning');
        }
    }

    public function initializePriceConfigurationsCleaning()
    {
        Log::info('initializePriceConfigurationsCleaning START');

        $duplicates = PackageConfiguration::select(
            'package_id',
            'season_type_id',
            'date_type_id',
            'room_type_id',
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('package_id', 'season_type_id', 'date_type_id', 'room_type_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Find all duplicates in this group
            $all = PackageConfiguration::where('package_id', $duplicate->package_id)
                ->where('season_type_id', $duplicate->season_type_id)
                ->where('date_type_id', $duplicate->date_type_id)
                ->where('room_type_id', $duplicate->room_type_id)
                ->orderByDesc('updated_at')
                ->get();

            if ($all->isEmpty()) {
                continue;
            }

            // First one (latest updated) is the one to keep
            $keep = $all->first();
            $delete = $all->skip(1); // everything else

            // Delete duplicates
            PackageConfiguration::whereIn('id', $delete->pluck('id'))->delete();

            // Logging
            Log::info("Duplicate group found", [
                'package_id'     => $duplicate->package_id,
                'season_type_id' => $duplicate->season_type_id,
                'date_type_id'   => $duplicate->date_type_id,
                'room_type_id'   => $duplicate->room_type_id,
                'keep_id'        => $keep->id,
                'keep_updated_at' => $keep->updated_at,
                'deleted_ids'    => $delete->pluck('id')->toArray(),
            ]);
        }

        Log::info('initializePriceConfigurationsCleaning END');
    }


    public function ensureItHasAllCombinations($query, $requestParam, $roomTypeIds)
    {
        // Clone the query with the PHP operator (not a method)
        $cloneQuery = clone $query;

        // Get optional room_type_id from request/array
        $requestRoomTypeId = is_array($requestParam)
            ? ($requestParam['room_type_id'] ?? null)
            : $requestParam->input('room_type_id');

        // Target set: either the single requested id, or all provided ids
        $targetIds = $requestRoomTypeId
            ? collect([(int) $requestRoomTypeId])
            : $roomTypeIds->map(fn($id) => (int) $id)->unique()->values();

        $configToDisplay = $targetIds->count();

        // Fetch existing room_type_id values from the same (cloned) scope
        $existing = (clone $cloneQuery)
            ->get(['room_type_id'])
            ->pluck('room_type_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        // Compute what’s missing (value-based, not key-based)
        $missing = $targetIds->diff($existing)->values();

        // Structured logs
        // Log::info(
        //     'ensureItHasAllCombinationsCheck ' . PHP_EOL .
        //     json_encode([
        //         'expected_count'         => $configToDisplay,
        //         'existing_count'         => $existing->count(),
        //         'target_ids'             => $targetIds->all(),
        //         'existing_room_type_ids' => $existing->all(),
        //         'missing_room_type_ids'  => $missing->all(),
        //     ], JSON_PRETTY_PRINT)
        // );

        if ($missing->isEmpty()) {
            // Log::info('All combinations present.');
            return;
        }

        $packageId = $requestParam['package_id'] ?? null;
        $seasonTypeId = $requestParam['season_type_id'] ?? null;
        $dateTypeId = $requestParam['date_type_id'] ?? null;

        Log::info(
            'Need to create missing combinations' . PHP_EOL .
                json_encode([
                    'count'          => $missing->count(),
                    'room_type_ids'  => $missing->all(),
                    'package_id'     => $packageId,
                    'season_type_id' => $seasonTypeId,
                    'date_type_id'   => $dateTypeId,
                ], JSON_PRETTY_PRINT)
        );

        $roomTypes = RoomType::whereIn('id', $missing->all())->get();
        $seasonType = SeasonType::find($seasonTypeId);
        $dateType = DateType::find($dateTypeId);

        $this->priceConfigurationService->createPriceConfigurationsService(
            Package::find($packageId),
            $roomTypes,
            [$seasonType],
            [$dateType],
            false
        );

        Log::info('Missing combinations created successfully.');
    }

    public function fetchPricesRoomTypesTest(Request $request)
    {
        $json = '[
    {
        "id": 53,
        "package_id": 3,
        "season_type_id": 1,
        "date_type_id": 1,
        "room_type_id": 9,
        "room_type": {
            "id": 9,
            "name": "Deluxe Room",
            "package_id": 3,
            "description": "Spacious room with modern amenities",
            "max_occupancy": 2,
            "images": [
                "room-types\/test1.jpg",
                "room-types\/test2.png",
                "room-types\/test3.jpg"
            ],
            "created_at": "2025-07-23T21:51:04.000000Z",
            "updated_at": "2025-07-23T21:51:04.000000Z",
            "deleted_at": null
        },
        "prices": [
            {
                "base": {
                  "6_a_0_c_0_i": { "a1": 100.00, "a2": 95.00, "a3": 90.00, "a4": 85.00, "a5": 80.00, "a6": 75.00 },
                  "5_a_0_c_0_i": { "a1": 100.00, "a2": 95.00, "a3": 90.00, "a4": 85.00 , "a5": 80.00 },
                  "4_a_0_c_0_i": { "a1": 100.00, "a2": 95.00, "a3": 90.00, "a4": 85.00 },
                  "3_a_1_c_0_i": { "a1": 120.00, "a2": 115.00, "a3": 110.00, "c1": 70.00 },
                  "3_a_0_c_1_i": { "a1": 110.00, "a2": 105.00, "a3": 100.00, "i1": 40.00 },
                  "2_a_2_c_0_i": { "a1": 130.00, "a2": 125.00, "c1": 80.00, "c2": 75.00 },
                  "2_a_1_c_1_i": { "a1": 140.00, "a2": 135.00, "c1": 85.00, "i1": 35.00 },
                  "2_a_0_c_2_i": { "a1": 115.00, "a2": 110.00, "i1": 30.00, "i2": 25.00 },
                  "1_a_3_c_0_i": { "a1": 150.00, "c1": 70.00, "c2": 65.00, "c3": 60.00 },
                  "1_a_2_c_1_i": { "a1": 125.00, "c1": 75.00, "c2": 70.00, "i1": 28.00 },
                  "1_a_1_c_2_i": { "a1": 135.00, "c1": 80.00, "c2": 75.00, "i1": 32.00 },
                  "1_a_0_c_3_i": { "a1": 140.00, "c1": 85.00, "c2": 80.00, "c3": 75.00 }
                },
                "surch": {
                  "4_a_0_c_0_i": { "a": 10.00, "c": 9.00},
                  "3_a_1_c_0_i": { "a": 12.00, "c": 6.00},
                  "3_a_0_c_1_i": { "a": 11.00, "c": 8.00},
                  "2_a_2_c_0_i": { "a": 13.00, "c": 9.00},
                  "2_a_1_c_1_i": { "a": 14.00, "c": 2.00},
                  "2_a_0_c_2_i": { "a": 10.00, "c": 6.00},
                  "1_a_3_c_0_i": { "a": 15.00, "c": 7.00},
                  "1_a_2_c_1_i": { "a": 11.00, "c": 7.00},
                  "1_a_1_c_2_i": { "a": 13.00, "c": 8.00},
                  "1_a_0_c_3_i": { "a": 12.00, "c": 8.00}
                }
              }
        ],
        "created_at": "2025-07-23T21:51:04.000000Z",
        "updated_at": "2025-07-23T21:51:04.000000Z"
    },
    {
        "id": 53,
        "package_id": 3,
        "season_type_id": 1,
        "date_type_id": 1,
        "room_type_id": 10,
        "room_type": {
            "id": 10,
            "name": "New New Room",
            "package_id": 3,
            "description": "Spacious room with modern amenities",
            "max_occupancy": 2,
            "images": [
                "room-types\/test1.jpg",
                "room-types\/test2.png",
                "room-types\/test3.jpg"
            ],
            "created_at": "2025-07-23T21:51:04.000000Z",
            "updated_at": "2025-07-23T21:51:04.000000Z",
            "deleted_at": null
        },
        "prices": [
            {
                "base": {
                  "4_a_0_c_0_i": { "a1": 100.00, "a2": 95.00, "a3": 90.00, "a4": 85.00 },
                  "3_a_1_c_0_i": { "a1": 120.00, "a2": 115.00, "a3": 110.00, "c1": 70.00 },
                  "3_a_0_c_1_i": { "a1": 110.00, "a2": 105.00, "a3": 100.00, "i1": 40.00 },
                  "2_a_2_c_0_i": { "a1": 130.00, "a2": 125.00, "c1": 80.00, "c2": 75.00 },
                  "2_a_1_c_1_i": { "a1": 140.00, "a2": 135.00, "c1": 85.00, "i1": 35.00 },
                  "2_a_0_c_2_i": { "a1": 115.00, "a2": 110.00, "i1": 30.00, "i2": 25.00 },
                  "1_a_3_c_0_i": { "a1": 150.00, "c1": 70.00, "c2": 65.00, "c3": 60.00 },
                  "1_a_2_c_1_i": { "a1": 125.00, "c1": 75.00, "c2": 70.00, "i1": 28.00 },
                  "1_a_1_c_2_i": { "a1": 135.00, "c1": 80.00, "c2": 75.00, "i1": 32.00 },
                  "1_a_0_c_3_i": { "a1": 140.00, "c1": 85.00, "c2": 80.00, "c3": 75.00 }
                },
                "surch": {
                  "4_a_0_c_0_i": { "a": 10.00, "c": 9.00, "i": 10.00},
                  "3_a_1_c_0_i": { "a": 12.00, "c": 6.00, "i": 12.00},
                  "3_a_0_c_1_i": { "a": 11.00, "c": 8.00, "i": 11.00},
                  "2_a_2_c_0_i": { "a": 13.00, "c": 9.00, "i": 13.00},
                  "2_a_1_c_1_i": { "a": 14.00, "c": 2.00, "i": 14.00},
                  "2_a_0_c_2_i": { "a": 10.00, "c": 6.00, "i": 10.00},
                  "1_a_3_c_0_i": { "a": 15.00, "c": 7.00, "i": 15.00},
                  "1_a_2_c_1_i": { "a": 11.00, "c": 7.00, "i": 11.00},
                  "1_a_1_c_2_i": { "a": 13.00, "c": 8.00, "i": 13.00},
                  "1_a_0_c_3_i": { "a": 12.00, "c": 8.00, "i": 12.00}
                }
              }
        ],
        "created_at": "2025-07-23T21:51:04.000000Z",
        "updated_at": "2025-07-23T21:51:04.000000Z"
    }
]';

        return response($json, 200)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Duplicate price configuration to multiple room types
     */
    public function duplicateToMultiple(Request $request)
    {
        $validated = $request->validate([
            'package_id'            => 'required|exists:packages,id',
            // source
            'source_season_type_id' => 'required|exists:season_types,id',
            'source_date_type_id'   => 'required|exists:date_types,id',
            'source_room_type_id'   => 'nullable|exists:room_types,id',
            // target
            'target_season_type_id' => 'required|exists:season_types,id',
            'target_date_type_id'   => 'required|exists:date_types,id',
            'target_room_type_id'   => 'nullable|exists:room_types,id',
            // includes
            'include_base_charges'  => 'boolean',
            'include_surcharges'    => 'boolean',
        ]);

        Log::info('duplicateToMultiple payload', $validated);

        if (
            $validated['source_season_type_id'] === $validated['target_season_type_id'] &&
            $validated['source_date_type_id']   === $validated['target_date_type_id'] &&
            (
                (empty($validated['source_room_type_id']) && empty($validated['target_room_type_id'])) ||
                (!empty($validated['source_room_type_id']) && !empty($validated['target_room_type_id']) &&
                    (int)$validated['source_room_type_id'] === (int)$validated['target_room_type_id'])
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Source and target cannot be the same',
            ], 400);
        }

        // Get all source configs
        $sourceQuery = PackageConfiguration::query()
            ->where('package_id',     $validated['package_id'])
            ->where('season_type_id', $validated['source_season_type_id'])
            ->where('date_type_id',   $validated['source_date_type_id']);

        if (!empty($validated['source_room_type_id'])) {
            $sourceQuery->where('room_type_id', $validated['source_room_type_id']);
        }

        $sourceConfigs = $sourceQuery->get();

        if ($sourceConfigs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No source configurations found to duplicate.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $duplicatedCount = 0;
            $errors = [];

            foreach ($sourceConfigs as $config) {
                $fromRoomTypeId = $config->room_type_id;
                $toSeasonTypeId = (int)$validated['target_season_type_id'];
                $toDateTypeId   = (int)$validated['target_date_type_id'];
                $toRoomTypeId   = !empty($validated['target_room_type_id'])
                    ? (int)$validated['target_room_type_id']
                    : (int)$fromRoomTypeId;

                // Skip identical
                if (
                    $config->season_type_id === $toSeasonTypeId &&
                    $config->date_type_id   === $toDateTypeId &&
                    $config->room_type_id   === $toRoomTypeId
                ) {
                    $errors[] = "Skipping identical config (room {$fromRoomTypeId})";
                    continue;
                }

                // Check if target already exists
                $targetConfig = PackageConfiguration::where([
                    'package_id'     => $validated['package_id'],
                    'season_type_id' => $toSeasonTypeId,
                    'date_type_id'   => $toDateTypeId,
                    'room_type_id'   => $toRoomTypeId,
                ])->first();

                if (!$targetConfig) {
                    // Create if missing
                    $targetConfig = new PackageConfiguration();
                    $targetConfig->package_id     = $validated['package_id'];
                    $targetConfig->season_type_id = $toSeasonTypeId;
                    $targetConfig->date_type_id   = $toDateTypeId;
                    $targetConfig->room_type_id   = $toRoomTypeId;
                    $targetConfig->configuration_prices = [['base' => [], 'surch' => []]];
                    $targetConfig->save();
                }

                // Now copy base/surch the same way as before
                $configurationPrices = $targetConfig->configuration_prices;

                if (empty($configurationPrices)) {
                    $configurationPrices = [['base' => [], 'surch' => []]];
                }

                if (!empty($validated['include_base_charges'])) {
                    $configurationPrices[0]['base'] = $config->configuration_prices[0]['base'] ?? [];
                }

                if (!empty($validated['include_surcharges'])) {
                    $configurationPrices[0]['surch'] = $config->configuration_prices[0]['surch'] ?? [];
                }

                $targetConfig->update([
                    'configuration_prices' => $configurationPrices
                ]);

                $duplicatedCount++;
            }

            DB::commit();

            Log::info('Duplication completed with some errors' . PHP_EOL . json_encode($errors, JSON_PRETTY_PRINT));

            if (count($errors) > 0) {
                return response()->json([
                    'success'          => false,
                    'message'          => 'Duplication completed with some errors',
                    'duplicated_count' => $duplicatedCount,
                    'errors'           => $errors,
                ], 207);
            }

            return response()->json([
                'success'          => true,
                'message'          => "Successfully duplicated configuration to {$duplicatedCount} configuration(s)",
                'duplicated_count' => $duplicatedCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Duplication failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate configuration: ' . $e->getMessage(),
            ], 500);
        }
    }
}
