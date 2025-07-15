<?php

namespace App\Http\Controllers;

use App\Models\DateType;
use App\Models\DateTypeRange;
use App\Models\Package;
use App\Services\CreatePriceConfigurationsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DateTypeRangeController extends Controller
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
    }

    public function index()
    {
        $dateTypeRanges = DateTypeRange::with('dateType')
            ->latest()
            ->get();

        return Inertia::render('DateTypeRanges/Index', [
            'dateTypeRanges' => $dateTypeRanges
        ]);
    }

    public function create()
    {
        $dateTypes = DateType::all();

        return Inertia::render('DateTypeRanges/Create', [
            'dateTypes' => $dateTypes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_type_id' => 'required|exists:date_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'package_id' => 'required|exists:packages,id'
        ]);

        // Check for overlapping dates
        if (DateTypeRange::hasOverlappingDates($validated['start_date'], $validated['end_date'], $validated['package_id'])) {
            $dateTypeRange = DateTypeRange::where('package_id', $validated['package_id'])
                ->where('start_date', '<=', $validated['end_date'])
                ->where('end_date', '>=', $validated['start_date'])
                ->first();

            return back()->withErrors([
                'date_range' => 'This date range overlaps with an existing date type range for this package in the date range of ' . Carbon::parse($dateTypeRange->start_date)->format('d-m-Y') . ' to ' . Carbon::parse($dateTypeRange->end_date)->format('d-m-Y')
            ]);
        }

        DateTypeRange::create($validated);
        $dateType = DateType::find($validated['date_type_id']);
        $this->priceConfigurationService->createPriceConfigurationsService(Package::find($validated['package_id']), [], [], [$dateType], false);

        // If the request has a return_to_package parameter, redirect back to the package page
        if ($request->has('return_to_package')) {
            return redirect()->route('packages.show', $request->package_id)
                ->with('success', 'Date range created successfully.');
        }

        return redirect()->route('date-type-ranges.index')
            ->with('success', 'Date range created successfully.');
    }

    public function show(DateTypeRange $dateTypeRange)
    {
        return Inertia::render('DateTypeRanges/Show', [
            'dateTypeRange' => $dateTypeRange->load('dateType')
        ]);
    }

    public function edit(DateTypeRange $dateTypeRange)
    {
        $dateTypes = DateType::all();

        return Inertia::render('DateTypeRanges/Edit', [
            'dateTypeRange' => $dateTypeRange,
            'dateTypes' => $dateTypes
        ]);
    }

    public function update(Request $request, DateTypeRange $dateTypeRange)
    {
        $validated = $request->validate([
            'date_type_id' => 'required|exists:date_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $dateTypeRange->update($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        if ($request->has('return_to_package')) {
            return redirect()->route('packages.show', $request->package_id)
                ->with('success', 'Date range updated successfully.');
        }

        return redirect()->route('date-type-ranges.index')
            ->with('success', 'Date range updated successfully.');
    }

    public function destroy(DateTypeRange $dateTypeRange)
    {
        $packageId = request()->package_id;
        $dateTypeRange->delete();

        // If the request has a return_to_package parameter, redirect back to the package page
        if (request()->has('return_to_package')) {
            return redirect()->route('packages.show', $packageId)
                ->with('success', 'Date range deleted successfully.');
        }

        return redirect()->route('date-type-ranges.index')
            ->with('success', 'Date range deleted successfully.');
    }

    public function storeBulk(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => [],
            'validation_errors' => []
        ];

        // Manual validation to collect all errors
        $validationErrors = [];

        if (!$request->has('dateTypeRanges') || !is_array($request->dateTypeRanges) || empty($request->dateTypeRanges)) {
            $validationErrors[] = 'At least one date type range is required';
        }

        if (!$request->has('package_id') || !$request->package_id) {
            $validationErrors[] = 'Package ID is required';
        }

        // Validate each date type range individually
        if ($request->has('dateTypeRanges') && is_array($request->dateTypeRanges)) {
            foreach ($request->dateTypeRanges as $index => $dateTypeRangeData) {
                $dateTypeRangeErrors = [];

                // Validate date type
                if (empty($dateTypeRangeData['date_type_id'])) {
                    $dateTypeRangeErrors[] = 'Date type is required';
                } elseif (!DateType::find($dateTypeRangeData['date_type_id'])) {
                    $dateTypeRangeErrors[] = 'Selected date type does not exist';
                }

                // Validate start date
                if (empty($dateTypeRangeData['start_date'])) {
                    $dateTypeRangeErrors[] = 'Start date is required';
                } elseif (!strtotime($dateTypeRangeData['start_date'])) {
                    $dateTypeRangeErrors[] = 'Start date must be a valid date';
                }

                // Validate end date
                if (empty($dateTypeRangeData['end_date'])) {
                    $dateTypeRangeErrors[] = 'End date is required';
                } elseif (!strtotime($dateTypeRangeData['end_date'])) {
                    $dateTypeRangeErrors[] = 'End date must be a valid date';
                }

                // Validate date range
                if (
                    !empty($dateTypeRangeData['start_date']) && !empty($dateTypeRangeData['end_date']) &&
                    strtotime($dateTypeRangeData['start_date']) && strtotime($dateTypeRangeData['end_date'])
                ) {
                    if (strtotime($dateTypeRangeData['end_date']) <= strtotime($dateTypeRangeData['start_date'])) {
                        $dateTypeRangeErrors[] = 'End date must be after start date';
                    }
                }

                if (!empty($dateTypeRangeErrors)) {
                    $results['validation_errors'][] = [
                        'index' => $index,
                        'data' => $dateTypeRangeData,
                        'errors' => $dateTypeRangeErrors
                    ];
                }
            }
        }

        // If there are validation errors, return them but still with 200 status
        if (!empty($validationErrors) || !empty($results['validation_errors'])) {
            $results['errors'] = array_merge($validationErrors, $results['validation_errors']);
            return response()->json($results, 200);
        }

        // Process date type ranges if validation passes
        foreach ($request->dateTypeRanges as $index => $dateTypeRangeData) {
            try {
                // Check for overlapping dates
                if (DateTypeRange::hasOverlappingDates($dateTypeRangeData['start_date'], $dateTypeRangeData['end_date'], $request->package_id)) {
                    $existingDateTypeRange = DateTypeRange::where('package_id', $request->package_id)
                        ->where('start_date', '<=', $dateTypeRangeData['end_date'])
                        ->where('end_date', '>=', $dateTypeRangeData['start_date'])
                        ->first();

                    $results['errors'][] = [
                        'index' => $index,
                        'data' => $dateTypeRangeData,
                        'message' => 'This date range overlaps with an existing date type range for this package in the date range of ' .
                            Carbon::parse($existingDateTypeRange->start_date)->format('d-m-Y') . ' to ' .
                            Carbon::parse($existingDateTypeRange->end_date)->format('d-m-Y')
                    ];
                    continue;
                }

                // Create the date type range
                $dateTypeRange = DateTypeRange::create([
                    'date_type_id' => $dateTypeRangeData['date_type_id'],
                    'start_date' => $dateTypeRangeData['start_date'],
                    'end_date' => $dateTypeRangeData['end_date'],
                    'package_id' => $request->package_id
                ]);

                // Create price configurations
                $dateType = DateType::find($dateTypeRangeData['date_type_id']);
                $this->priceConfigurationService->createPriceConfigurationsService(
                    Package::find($request->package_id),
                    [],
                    [],
                    [$dateType],
                    false
                );

                $results['success'][] = [
                    'index' => $index,
                    'data' => $dateTypeRangeData,
                    'date_type_range_id' => $dateTypeRange->id,
                    'message' => 'Date type range created successfully'
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $dateTypeRangeData,
                    'message' => 'Failed to create date type range: ' . $e->getMessage()
                ];
            }
        }

        // Always return 200 status, even with errors
        return response()->json($results, 200);
    }
}
