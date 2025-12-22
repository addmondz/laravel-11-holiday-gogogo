<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Season;
use App\Models\SeasonType;
use App\Services\CreatePriceConfigurationsService;
use App\Services\EnsurePriceConfigService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SeasonController extends Controller
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    protected EnsurePriceConfigService $ensurePriceConfigService;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService, EnsurePriceConfigService $ensurePriceConfigService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
        $this->ensurePriceConfigService = $ensurePriceConfigService;
    }

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

    // public function store(Request $request)
    // {
    //     $packageId = request()->package_id;

    //     $validated = $request->validate([
    //         'season_type_id' => 'required|exists:season_types,id',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after:start_date',
    //         'package_id' => 'required|exists:packages,id'
    //     ]);

    //     // Check for overlapping dates
    //     if (Season::hasOverlappingDates($validated['start_date'], $validated['end_date'], $validated['package_id'])) {
    //         $season = Season::where('package_id', $validated['package_id'])
    //             ->where('start_date', '<=', $validated['end_date'])
    //             ->where('end_date', '>=', $validated['start_date'])
    //             ->first();

    //         return back()->withErrors([
    //             'date_range' => 'This date range overlaps with an existing season for this package in the date range of ' . Carbon::parse($season->start_date)->format('d-m-Y') . ' to ' . Carbon::parse($season->end_date)->format('d-m-Y')
    //         ]);
    //     }

    //     $season = Season::create($validated);

    //     $this->ensurePriceConfigService->syncPriceConfigurationsBySeasonsAndDateTypes($packageId, [$season->id], []);

    //     // If the request has a return_to_package parameter, redirect back to the package page
    //     if (request()->has('return_to_package')) {
    //         return redirect()->route('packages.show', $packageId)
    //             ->with('success', 'Season deleted successfully.');
    //     }

    //     return back()->with('success', 'Season created successfully.');
    // }

    public function storeBulk(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => [],
            'validation_errors' => [],
        ];

        $validationErrors = [];

        if (!is_array($request->seasons) || empty($request->seasons)) {
            $validationErrors[] = 'At least one season is required';
        }

        if (!$request->package_id) {
            $validationErrors[] = 'Package ID is required';
        }

        // Validate each season entry
        foreach ($request->seasons ?? [] as $index => $seasonData) {
            $errors = [];

            if (empty($seasonData['season_type_id']) || !SeasonType::find($seasonData['season_type_id'])) {
                $errors[] = 'Season type is required and must exist';
            }

            if (empty($seasonData['start_date']) || !strtotime($seasonData['start_date'])) {
                $errors[] = 'Start date is required and must be a valid date';
            }

            if (empty($seasonData['end_date']) || !strtotime($seasonData['end_date'])) {
                $errors[] = 'End date is required and must be a valid date';
            }

            if (
                !empty($seasonData['start_date']) && !empty($seasonData['end_date']) &&
                strtotime($seasonData['end_date']) < strtotime($seasonData['start_date'])
            ) {
                $errors[] = 'End date must be the same as or after the start date';
            }

            if ($errors) {
                $results['validation_errors'][] = [
                    'index' => $index,
                    'data' => $seasonData,
                    'errors' => $errors
                ];
            }
        }

        // Return validation errors early
        if (!empty($validationErrors) || !empty($results['validation_errors'])) {
            $results['errors'] = array_merge($validationErrors, $results['validation_errors']);
            return response()->json($results, 200);
        }

        // Process each season
        foreach ($request->seasons as $index => $seasonData) {
            try {
                // Check for overlapping dates
                $message = $this->getSeasonOverlapMessage(
                    $seasonData['start_date'],
                    $seasonData['end_date'],
                    $request->package_id
                );

                if ($message) {
                    $results['errors'][] = [
                        'index' => $index,
                        'data' => $seasonData,
                        'message' => $message,
                    ];
                    continue;
                }

                // Create season
                $season = Season::create([
                    'season_type_id' => $seasonData['season_type_id'],
                    'start_date'     => $seasonData['start_date'],
                    'end_date'       => $seasonData['end_date'],
                    'package_id'     => $request->package_id,
                ]);

                // Generate price config
                $this->ensurePriceConfigService->syncPriceConfigurationsBySeasonsAndDateTypes($request->package_id, [$seasonData['season_type_id']], []);

                $results['success'][] = [
                    'index' => $index,
                    'data' => $seasonData,
                    'season_id' => $season->id,
                    'message' => 'Season created successfully',
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $seasonData,
                    'message' => 'Failed to create season: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json($results, 200);
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

    public function destroyBulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:seasons,id',
            'package_id' => 'required|exists:packages,id'
        ]);

        try {
            $deletedCount = Season::whereIn('id', $validated['ids'])
                ->where('package_id', $validated['package_id'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} season(s) deleted successfully",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk delete seasons error', [
                'error' => $e->getMessage(),
                'ids' => $validated['ids']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete seasons: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getSeasonOverlapMessage($startDate, $endDate, $packageId)
    {
        $existing = Season::where('package_id', $packageId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->first();

        return $existing
            ? 'This date range overlaps with an existing season for this package in the date range of ' .
            Carbon::parse($existing->start_date)->format('d-m-Y') . ' to ' .
            Carbon::parse($existing->end_date)->format('d-m-Y')
            : null;
    }
}
