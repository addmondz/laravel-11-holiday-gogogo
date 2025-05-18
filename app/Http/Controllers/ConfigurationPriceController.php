<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationPrice;
use App\Models\Package;
use App\Models\Season;
use App\Models\DateType;
use App\Models\DateTypeRange;
use App\Models\PackageConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\RoomType;
use Illuminate\Support\Facades\Log;

class ConfigurationPriceController extends Controller
{
    public function index()
    {
        return Inertia::render('ConfigurationPrices/Index', [
            'packages' => Package::all(),
            'seasons' => Season::with('type')->get(),
            'dateTypes' => DateType::all(),
            'roomTypes' => RoomType::all(),
        ]);
    }

    public function create(Request $request)
    {
        $packageId = $request->query('package_id');
        $seasonId = $request->query('season_id');
        $dateTypeId = $request->query('date_type_id');
        $roomTypeId = $request->query('room_type');

        return Inertia::render('ConfigurationPrices/Create', [
            'packages' => Package::all(),
            'seasons' => Season::with('type')->get(),
            'dateTypes' => DateType::all(),
            'roomTypes' => RoomType::all(),
            'prefilled' => [
                'package_id' => $packageId,
                'season_id' => $seasonId,
                'date_type_id' => $dateTypeId,
                'room_type' => $roomTypeId
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_id' => 'required|exists:seasons,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type' => 'required|exists:room_types,id',
            'prices' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Create or get package configuration
            $configuration = PackageConfiguration::firstOrCreate([
                'package_id' => $validated['package_id'],
                'season_id' => $validated['season_id'],
                'date_type_id' => $validated['date_type_id'],
                'room_type_id' => $validated['room_type']
            ]);

            // Define all charge types
            $chargeTypes = ['base_charge', 'sur_charge'];

            // Create prices for each type
            foreach ($chargeTypes as $type) {
                // Get prices for this type from the request, or use empty array if not provided
                $prices = $validated['prices'][$type] ?? [];

                // Create empty prices for all combinations (1-4 adults, 0-3 children)
                for ($adults = 1; $adults <= 4; $adults++) {
                    for ($children = 0; $children <= 3; $children++) {
                        // Find if there's a matching price in the provided data
                        $matchingPrice = collect($prices)->first(function ($price) use ($adults, $children) {
                            return $price['number_of_adults'] == $adults && $price['number_of_children'] == $children;
                        });

                        if ($matchingPrice && (!empty($matchingPrice['adult_price']) || !empty($matchingPrice['child_price']))) {
                            // Use the provided price
                            ConfigurationPrice::create([
                                'package_configuration_id' => $configuration->id,
                                'type' => $type,
                                'number_of_adults' => $adults,
                                'number_of_children' => $children,
                                'adult_price' => $matchingPrice['adult_price'] ?? 0,
                                'child_price' => $matchingPrice['child_price'] ?? 0
                            ]);
                        } else {
                            // Create empty price
                            ConfigurationPrice::create([
                                'package_configuration_id' => $configuration->id,
                                'type' => $type,
                                'number_of_adults' => $adults,
                                'number_of_children' => $children,
                                'adult_price' => 0,
                                'child_price' => 0
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return back()->with('success', 'Configuration prices created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating configuration prices: ' . $e->getMessage());
        }
    }

    public function show(ConfigurationPrice $configurationPrice)
    {
        $configurationPrice->load(['configuration']);

        return Inertia::render('ConfigurationPrices/Show', [
            'price' => $configurationPrice
        ]);
    }

    public function edit(Request $request)
    {
        $packageId = $request->query('package_id');
        $seasonId = $request->query('season_id');
        $dateTypeId = $request->query('date_type_id');
        $roomTypeId = $request->query('room_type');

        // Find the configuration
        $configuration = PackageConfiguration::where('package_id', $packageId)
            ->where('season_id', $seasonId)
            ->where('date_type_id', $dateTypeId)
            ->where('room_type_id', $roomTypeId)
            ->first();

        if (!$configuration) {
            return redirect()->back()->with('error', 'Configuration not found');
        }

        // Get all prices for this configuration
        $prices = ConfigurationPrice::where('package_configuration_id', $configuration->id)
            ->get()
            ->groupBy('type');

        return Inertia::render('ConfigurationPrices/Edit', [
            'packages' => Package::all(),
            'seasons' => Season::with('type')->get(),
            'dateTypes' => DateType::all(),
            'roomTypes' => RoomType::all(),
            'configuration' => $configuration,
            'prices' => $prices,
            'prefilled' => [
                'package_id' => $packageId,
                'season_id' => $seasonId,
                'date_type_id' => $dateTypeId,
                'room_type' => $roomTypeId
            ]
        ]);
    }

    public function update(Request $request, PackageConfiguration $configurationPrice)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_type_id' => 'required|exists:season_types,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type' => 'required|exists:room_types,id',
            'prices' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Get the actual season_id from season_type_id
            $season = Season::where('season_type_id', $validated['season_type_id'])
                ->where('package_id', $validated['package_id'])
                ->firstOrFail();

            // Get the actual date_type_id from date_type_id
            $dateTypeRange = DateTypeRange::where('date_type_id', $validated['date_type_id'])
                ->where('package_id', $validated['package_id'])
                ->firstOrFail();

            // Update the configuration
            $configurationPrice->update([
                'package_id' => $validated['package_id'],
                'season_id' => $season->id,
                'date_type_id' => $dateTypeRange->id,
                'room_type_id' => $validated['room_type']
            ]);

            // Delete old prices
            ConfigurationPrice::where('package_configuration_id', $configurationPrice->id)->delete();

            // Insert new prices
            foreach ($validated['prices'] as $type => $prices) {
                foreach ($prices as $price) {
                    if (!empty($price['adult_price']) || !empty($price['child_price'])) {
                        ConfigurationPrice::create([
                            'package_configuration_id' => $configurationPrice->id,
                            'type' => $type,
                            'number_of_adults' => $price['number_of_adults'] ?? 0,
                            'number_of_children' => $price['number_of_children'] ?? 0,
                            'adult_price' => $price['adult_price'] ?? 0,
                            'child_price' => $price['child_price'] ?? 0,
                        ]);
                    }
                }
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

    public function destroy(ConfigurationPrice $configurationPrice)
    {
        $configurationPrice->delete();

        return response()->json([
            'message' => 'Configuration price deleted successfully.'
        ]);
    }

    public function fetchPricesSearchIndex(Request $request)
    {
        $season_id = $this->fetchSeasonIdBySeasonTypeAndPackageId($request->season_type_id, $request->package_id);
        $date_type_id = $this->fetchDateTypeIdByDateTypeAndPackageId($request->date_type_id, $request->package_id);
        $prices = PackageConfiguration::where('package_id', $request->package_id)
            ->where('season_id', $season_id)
            ->where('date_type_id', $request->date_type_id)
            ->where('room_type_id', $date_type_id)
            ->with('prices')
            ->get();

        return response()->json($prices);
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
}
