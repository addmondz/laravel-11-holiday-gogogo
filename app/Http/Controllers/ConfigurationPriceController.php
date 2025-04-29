<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationPrice;
use App\Models\Package;
use App\Models\Season;
use App\Models\DateType;
use App\Models\PackageConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ConfigurationPriceController extends Controller
{
    public function index()
    {
        return Inertia::render('ConfigurationPrices/Index', [
            'packages' => Package::all(),
            'seasons' => Season::with('type')->get(),
            'dateTypes' => DateType::all(),
            'roomTypes' => \App\Models\PackageConfiguration::distinct()->pluck('room_type'),
        ]);
    }

    public function create(Request $request)
    {
        $packageId = $request->query('package_id');
        $seasonId = $request->query('season_id');
        $dateTypeId = $request->query('date_type_id');
        $roomType = $request->query('room_type');

        return Inertia::render('ConfigurationPrices/Create', [
            'packages' => Package::all(),
            'seasons' => Season::with('type')->get(),
            'dateTypes' => DateType::all(),
            'roomTypes' => PackageConfiguration::distinct()->pluck('room_type'),
            'prefilled' => [
                'package_id' => $packageId,
                'season_id' => $seasonId,
                'date_type_id' => $dateTypeId,
                'room_type' => $roomType
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_id' => 'required|exists:seasons,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type' => 'required|string',
            'type' => 'required|string|in:base_charge,sur_charge,ext_charge',
            'prices' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Create or get package configuration
            $configuration = PackageConfiguration::firstOrCreate([
                'package_id' => $validated['package_id'],
                'season_id' => $validated['season_id'],
                'date_type_id' => $validated['date_type_id'],
                'room_type' => $validated['room_type']
            ]);

            // Create prices
            foreach ($validated['prices'] as $price) {
                if (!empty($price['adult_price']) || !empty($price['child_price'])) {
                    ConfigurationPrice::create([
                        'package_configuration_id' => $configuration->id,
                        'type' => $validated['type'],
                        'number_of_adults' => $price['number_of_adults'],
                        'number_of_children' => $price['number_of_children'],
                        'adult_price' => $price['adult_price'] ?? 0,
                        'child_price' => $price['child_price'] ?? 0
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Configuration prices created successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating configuration prices.',
                'error' => $e->getMessage()
            ], 500);
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
        $roomType = $request->query('room_type');

        // Find the configuration
        $configuration = PackageConfiguration::where('package_id', $packageId)
            ->where('season_id', $seasonId)
            ->where('date_type_id', $dateTypeId)
            ->where('room_type', $roomType)
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
            'roomTypes' => PackageConfiguration::distinct()->pluck('room_type'),
            'configuration' => $configuration,
            'prices' => $prices,
            'prefilled' => [
                'package_id' => $packageId,
                'season_id' => $seasonId,
                'date_type_id' => $dateTypeId,
                'room_type' => $roomType
            ]
        ]);
    }

    public function update(Request $request, ConfigurationPrice $configurationPrice)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_id' => 'required|exists:seasons,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type' => 'required|string',
            'type' => 'required|string|in:base_charge,sur_charge,ext_charge',
            'prices' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Update or create package configuration
            $configuration = PackageConfiguration::updateOrCreate(
                [
                    'package_id' => $validated['package_id'],
                    'season_id' => $validated['season_id'],
                    'date_type_id' => $validated['date_type_id'],
                    'room_type' => $validated['room_type']
                ],
                [
                    'package_id' => $validated['package_id'],
                    'season_id' => $validated['season_id'],
                    'date_type_id' => $validated['date_type_id'],
                    'room_type' => $validated['room_type']
                ]
            );

            // Delete existing prices for this configuration and type
            ConfigurationPrice::where('package_configuration_id', $configuration->id)
                ->where('type', $validated['type'])
                ->delete();

            // Create new prices
            foreach ($validated['prices'] as $price) {
                if (!empty($price['adult_price']) || !empty($price['child_price'])) {
                    ConfigurationPrice::create([
                        'package_configuration_id' => $configuration->id,
                        'type' => $validated['type'],
                        'number_of_adults' => $price['number_of_adults'],
                        'number_of_children' => $price['number_of_children'],
                        'adult_price' => $price['adult_price'] ?? 0,
                        'child_price' => $price['child_price'] ?? 0
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Configuration prices updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating configuration prices.',
                'error' => $e->getMessage()
            ], 500);
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
        $prices = PackageConfiguration::where('package_id', $request->package_configuration_id)
            ->where('season_id', $request->season_id)
            ->where('date_type_id', $request->date_type_id)
            ->where('room_type_id', $request->room_type_id)
            ->with('prices')
            ->get();

        return response()->json($prices);
    }
}
