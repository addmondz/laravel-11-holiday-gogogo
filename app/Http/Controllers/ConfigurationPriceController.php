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
use App\Services\EnsurePriceConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigurationPriceController extends Controller
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    protected EnsurePriceConfigService $ensurePriceConfigService;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService, EnsurePriceConfigService $ensurePriceConfigService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
        $this->ensurePriceConfigService = $ensurePriceConfigService;
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

        $this->ensurePriceConfigService->syncPriceConfigurations($request->package_id, $request->season_type_id, $request->date_type_id, $roomTypeIds);

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
                        AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE => $room[AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE],
                        AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR => $room[AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR],
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
            // target configurations
            'target_configurations' => 'required|array|min:1',
            'target_configurations.*.season_type_id' => 'required|exists:season_types,id',
            'target_configurations.*.date_type_id'   => 'required|exists:date_types,id',
            'target_configurations.*.room_type_id'   => 'nullable|exists:room_types,id',
            // includes
            'include_base_charges'  => 'boolean',
            'include_surcharges'    => 'boolean',
        ]);

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

            foreach ($validated['target_configurations'] as $targetConfig) {
                foreach ($sourceConfigs as $sourceConfig) {
                    $fromRoomTypeId = $sourceConfig->room_type_id;
                    $toSeasonTypeId = (int)$targetConfig['season_type_id'];
                    $toDateTypeId   = (int)$targetConfig['date_type_id'];
                    $toRoomTypeId   = (int)$targetConfig['room_type_id'] ?? null;

                    // Skip identical
                    if (
                        $sourceConfig->season_type_id === $toSeasonTypeId &&
                        $sourceConfig->date_type_id   === $toDateTypeId &&
                        $sourceConfig->room_type_id   === $toRoomTypeId
                    ) {
                        $errors[] = "Skipping identical config (room {$fromRoomTypeId})";
                        continue;
                    }

                    $filterArray = [
                        'package_id'     => $validated['package_id'],
                        'season_type_id' => $toSeasonTypeId,
                        'date_type_id'   => $toDateTypeId,
                    ];
                    if ($toRoomTypeId) {
                        $filterArray['room_type_id'] = $toRoomTypeId;
                    }

                    // Check if target already exists
                    $existingTargetConfig = PackageConfiguration::where($filterArray)->first();

                    if (!$existingTargetConfig) {
                        // Create if missing
                        $existingTargetConfig = new PackageConfiguration();
                        $existingTargetConfig->package_id     = $validated['package_id'];
                        $existingTargetConfig->season_type_id = $toSeasonTypeId;
                        $existingTargetConfig->date_type_id   = $toDateTypeId;
                        $existingTargetConfig->room_type_id   = $toRoomTypeId;
                        $existingTargetConfig->configuration_prices = [[AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE => [], AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR => []]];
                        $existingTargetConfig->save();
                    }

                    // Now copy base/surch the same way as before
                    $configurationPrices = $existingTargetConfig->configuration_prices;

                    if (empty($configurationPrices)) {
                        $configurationPrices = [[AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE => [], AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR => []]];
                    }

                    if (!empty($validated['include_base_charges'])) {
                        $configurationPrices[0][AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE] = $this->compareAndUpdatePriceConfiguration($configurationPrices[0][AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE], $sourceConfig->configuration_prices[0][AppConstants::DATABASE_CONFIG_PRICE_INDEX_BASE]);
                    }

                    if (!empty($validated['include_surcharges'])) {
                        $configurationPrices[0][AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR] = $this->compareAndUpdatePriceConfiguration($configurationPrices[0][AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR], $sourceConfig->configuration_prices[0][AppConstants::DATABASE_CONFIG_PRICE_INDEX_SUR]);
                    }

                    $existingTargetConfig->update([
                        'configuration_prices' => $configurationPrices
                    ]);

                    $duplicatedCount++;
                }
            }

            DB::commit();

            if (count($errors) > 0) {
                Log::info('Duplication completed with some errors' . PHP_EOL . json_encode($errors, JSON_PRETTY_PRINT));
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

    private function compareAndUpdatePriceConfiguration($target, $source)
    {
        foreach ($source as $key => $value) {
            if (isset($target[$key])) {
                $target[$key] = $value;
            }
        }
        return $target;
    }
}
