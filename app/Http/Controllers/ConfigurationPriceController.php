<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationPrice;
use App\Models\Package;
use App\Models\Season;
use App\Models\DateType;
use App\Models\PackageConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConfigurationPriceController extends Controller
{
    public function index()
    {
        return Inertia::render('ConfigurationPrices/Index', [
            'packages' => Package::all(),
            'seasons' => Season::with('type')->get(),
            'dateTypes' => DateType::all(),
            'roomTypes' => \App\Models\PackageConfiguration::distinct()->pluck('room_type'),
            'prices' => ConfigurationPrice::with(['configuration'])->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('ConfigurationPrices/Create', [
            'configurations' => \App\Models\PackageConfiguration::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_configuration_id' => 'required|exists:package_configurations,id',
            'type' => 'required|string|max:255',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'required|integer|min:0',
            'adult_price' => 'required|numeric|min:0',
            'child_price' => 'required|numeric|min:0'
        ]);

        ConfigurationPrice::create($validated);

        return response()->json([
            'message' => 'Configuration price created successfully.'
        ]);
    }

    public function show(ConfigurationPrice $configurationPrice)
    {
        $configurationPrice->load(['configuration']);

        return Inertia::render('ConfigurationPrices/Show', [
            'price' => $configurationPrice
        ]);
    }

    public function edit(ConfigurationPrice $configurationPrice)
    {
        return Inertia::render('ConfigurationPrices/Edit', [
            'price' => $configurationPrice,
            'configurations' => \App\Models\PackageConfiguration::all()
        ]);
    }

    public function update(Request $request, ConfigurationPrice $configurationPrice)
    {
        $validated = $request->validate([
            'package_configuration_id' => 'required|exists:package_configurations,id',
            'type' => 'required|string|max:255',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'required|integer|min:0',
            'adult_price' => 'required|numeric|min:0',
            'child_price' => 'required|numeric|min:0'
        ]);

        $configurationPrice->update($validated);

        return response()->json([
            'message' => 'Configuration price updated successfully.'
        ]);
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
            ->where('room_type', $request->room_type)
            ->with('prices')
            ->get();

        return response()->json($prices);
    }
}
