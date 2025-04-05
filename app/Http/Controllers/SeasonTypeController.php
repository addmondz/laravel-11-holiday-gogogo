<?php

namespace App\Http\Controllers;

use App\Models\SeasonType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeasonTypeController extends Controller
{
    public function index()
    {
        $seasonTypes = SeasonType::with('seasons')
            ->latest()
            ->get();

        return Inertia::render('SeasonTypes/Index', [
            'seasonTypes' => $seasonTypes
        ]);
    }

    public function create()
    {
        return Inertia::render('SeasonTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:season_types',
        ]);

        SeasonType::create($validated);

        return redirect()->route('season-types.index')
            ->with('success', 'Season type created successfully.');
    }

    public function show(SeasonType $seasonType)
    {
        return Inertia::render('SeasonTypes/Show', [
            'seasonType' => $seasonType->load('seasons')
        ]);
    }

    public function edit(SeasonType $seasonType)
    {
        return Inertia::render('SeasonTypes/Edit', [
            'seasonType' => $seasonType
        ]);
    }

    public function update(Request $request, SeasonType $seasonType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:season_types,name,' . $seasonType->id,
        ]);

        $seasonType->update($validated);

        return redirect()->route('season-types.index')
            ->with('success', 'Season type updated successfully.');
    }

    public function destroy(SeasonType $seasonType)
    {
        $seasonType->delete();

        return redirect()->route('season-types.index')
            ->with('success', 'Season type deleted successfully.');
    }
}
