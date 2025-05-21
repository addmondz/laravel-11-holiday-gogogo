<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\SeasonType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

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
        $packageId = request()->package_id;

        $validated = $request->validate([
            'season_type_id' => 'required|exists:season_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'package_id' => 'required|exists:packages,id'
        ]);

        // Check for overlapping dates
        if (Season::hasOverlappingDates($validated['start_date'], $validated['end_date'], $validated['package_id'])) {
            $season = Season::where('package_id', $validated['package_id'])
                ->where('start_date', '<=', $validated['end_date'])
                ->where('end_date', '>=', $validated['start_date'])
                ->first();

            return back()->withErrors([
                'date_range' => 'This date range overlaps with an existing season for this package in the date range of ' . Carbon::parse($season->start_date)->format('d-m-Y') . ' to ' . Carbon::parse($season->end_date)->format('d-m-Y')
            ]);
        }

        Season::create($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        if (request()->has('return_to_package')) {
            return redirect()->route('packages.show', $packageId)
                ->with('success', 'Season deleted successfully.');
        }

        return back()->with('success', 'Season created successfully.');
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
            'end_date' => 'required|date|after:start_date'
        ]);

        // Check for overlapping dates, excluding the current season
        if (Season::hasOverlappingDates($validated['start_date'], $validated['end_date'], $season->package_id, $season->id)) {
            return back()->withErrors([
                'date_range' => 'This date range overlaps with an existing season for this package.'
            ]);
        }

        $season->update($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        return redirect()->route('packages.show', $request->package_id)
            ->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season)
    {
        $packageId = request()->package_id;
        $season->delete();

        // If the request has a return_to_package parameter, redirect back to the package page
        if (request()->has('return_to_package')) {
            return redirect()->route('packages.show', $packageId)
                ->with('success', 'Season deleted successfully.');
        }

        return redirect()->route('seasons.index')
            ->with('success', 'Season deleted successfully.');
    }
}
