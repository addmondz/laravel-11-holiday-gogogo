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

        $this->ensureOnlyOnePriceConfiguration($query, $request->all());

        $configurations = $query->get();
        // Log::info('configurations:' . json_encode($configurations, JSON_PRETTY_PRINT));

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
                    // Example code: "1_a_0_c_1_i_a" (1 adult, 0 children, 1 infant, adult price)
                    preg_match('/(\d+)_a_(\d+)_c_(\d+)_i_([aci])/', $code, $matches);
                    if (!$matches) continue;

                    [$full, $adults, $children, $infants, $personType] = $matches;
                    $key = "{$adults}_{$children}_{$infants}_{$type}";

                    if (!isset($structuredPrices[$key])) {
                        $structuredPrices[$key] = [
                            'type' => $type,
                            'number_of_adults' => (int) $adults,
                            'number_of_children' => (int) $children,
                            'number_of_infants' => (int) $infants,
                            'adult_price' => null,
                            'child_price' => null,
                            'infant_price' => null,
                        ];
                    }

                    if ($personType === 'a') {
                        $structuredPrices[$key]['adult_price'] = number_format($price, 2, '.', '');
                    } elseif ($personType === 'c') {
                        $structuredPrices[$key]['child_price'] = number_format($price, 2, '.', '');
                    } elseif ($personType === 'i') {
                        $structuredPrices[$key]['infant_price'] = number_format($price, 2, '.', '');
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
            'prices' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['prices'] as $room_type_id => $prices) {
                $this->handlePriceConfigurationNew($prices, $validated['package_id'], $validated['season_type_id'], $validated['date_type_id'], $room_type_id);
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

    public function ensureOnlyOnePriceConfiguration($query, $requestParam): bool
    {
        // Newest first by created_at, then id (tiebreaker)
        $ids = (clone $query)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('id');

        if ($ids->count() <= 1) return false; // nothing to clean

        // Everything except the most recent, as a values-only array
        $deletedIds = $ids->slice(1)->values()->all();

        // Delete those
        (clone $query)->whereKey($deletedIds)->delete();

        // Logs: deleted ids as body-only array (no keys)
        Log::info('Cleaned duplicate price configurations; kept most recent id='. $ids->first());
        Log::info('Deleted ids Length: ' . count($deletedIds));
        Log::info('Deleted ids: ' . json_encode($deletedIds));
        Log::info('requestParam: ' . json_encode($requestParam));

        return true;
    }
}
