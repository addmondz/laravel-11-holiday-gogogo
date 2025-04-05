<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\SeasonType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::with('type')
            ->latest()
            ->get();

        return Inertia::render('Seasons/Index', [
            'seasons' => $seasons
        ]);
    }

    public function create()
    {
        $seasonTypes = SeasonType::all();

        return Inertia::render('Seasons/Create', [
            'seasonTypes' => $seasonTypes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'season_type_id' => 'required|exists:season_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'priority' => 'required|integer|min:1'
        ]);

        Season::create($validated);

        return redirect()->route('seasons.index')
            ->with('success', 'Season created successfully.');
    }

    public function show(Season $season)
    {
        return Inertia::render('Seasons/Show', [
            'season' => $season->load('type')
        ]);
    }

    public function edit(Season $season)
    {
        $seasonTypes = SeasonType::all();

        return Inertia::render('Seasons/Edit', [
            'season' => $season,
            'seasonTypes' => $seasonTypes
        ]);
    }

    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'season_type_id' => 'required|exists:season_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'priority' => 'required|integer|min:1'
        ]);

        $season->update($validated);

        return redirect()->route('seasons.index')
            ->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season)
    {
        $season->delete();

        return redirect()->route('seasons.index')
            ->with('success', 'Season deleted successfully.');
    }
}
