<?php

namespace App\Http\Controllers;

use App\Models\PackageConfiguration;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageConfigurationController extends Controller
{
    public function index()
    {
        $configurations = PackageConfiguration::with(['package', 'season', 'dateType', 'roomType', 'season.type'])
            ->latest()
            ->get();

        return Inertia::render('PackageConfigurations/Index', [
            'configurations' => $configurations
        ]);
    }

    public function create()
    {
        return Inertia::render('PackageConfigurations/Create', [
            'packages' => \App\Models\Package::all(),
            'seasons' => \App\Models\Season::with('type')->get(),
            'dateTypes' => \App\Models\DateType::all(),
            'roomTypes' => RoomType::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_id' => 'required|exists:seasons,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type_id' => 'required|exists:room_types,id'
        ]);

        PackageConfiguration::create($validated);

        return response()->json([
            'message' => 'Package configuration created successfully.'
        ]);
    }

    public function show(PackageConfiguration $packageConfiguration)
    {
        $packageConfiguration->load(['package', 'season', 'dateType', 'roomType', 'prices', 'season.type']);

        return Inertia::render('PackageConfigurations/Show', [
            'configuration' => $packageConfiguration
        ]);
    }

    public function edit(PackageConfiguration $packageConfiguration)
    {
        return Inertia::render('PackageConfigurations/Edit', [
            'configuration' => $packageConfiguration,
            'packages' => \App\Models\Package::all(),
            'seasons' => \App\Models\Season::all(),
            'dateTypes' => \App\Models\DateType::all(),
            'roomTypes' => RoomType::where('is_active', true)->get()
        ]);
    }

    public function update(Request $request, PackageConfiguration $packageConfiguration)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'season_id' => 'required|exists:seasons,id',
            'date_type_id' => 'required|exists:date_types,id',
            'room_type_id' => 'required|exists:room_types,id'
        ]);

        $packageConfiguration->update($validated);

        return response()->json([
            'message' => 'Package configuration updated successfully.'
        ]);
    }

    public function destroy(PackageConfiguration $packageConfiguration)
    {
        $packageConfiguration->delete();

        return response()->json([
            'message' => 'Package configuration deleted successfully.'
        ]);
    }
}
