<?php

namespace App\Http\Controllers;

use App\Models\SeasonType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeasonTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = SeasonType::whereNot('name', 'Default');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort functionality
        $sortField = $request->get('sort', 'created_at');
        // $sortDirection = $request->get('direction', 'desc');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $seasonTypes = $query->with('seasons')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('SeasonTypes/Index', [
            'seasonTypes' => $seasonTypes,
            'filters' => $request->only(['search', 'sort', 'direction'])
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
