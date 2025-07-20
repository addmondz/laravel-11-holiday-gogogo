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
        $dateTypeRanges = DateTypeRange::with('dateType')->latest()->get();

        return Inertia::render('DateTypeRanges/Index', [
            'dateTypeRanges' => $dateTypeRanges
        ]);
    }

    public function create()
    {
        return Inertia::render('DateTypeRanges/Create', [
            'dateTypes' => DateType::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_type_id' => 'required|exists:date_types,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'package_id'   => 'required|exists:packages,id'
        ], [
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
        ]);

        if ($message = $this->getOverlapMessage($validated['start_date'], $validated['end_date'], $validated['package_id'])) {
            return back()->withErrors(['date_range' => $message]);
        }

        DateTypeRange::create($validated);

        $dateType = DateType::find($validated['date_type_id']);
        $this->priceConfigurationService->createPriceConfigurationsService(
            Package::find($validated['package_id']),
            [],
            [],
            [$dateType],
            false
        );

        return $request->has('return_to_package')
            ? redirect()->route('packages.show', $request->package_id)->with('success', 'Date range created successfully.')
            : redirect()->route('date-type-ranges.index')->with('success', 'Date range created successfully.');
    }

    public function show(DateTypeRange $dateTypeRange)
    {
        return Inertia::render('DateTypeRanges/Show', [
            'dateTypeRange' => $dateTypeRange->load('dateType')
        ]);
    }

    public function edit(DateTypeRange $dateTypeRange)
    {
        return Inertia::render('DateTypeRanges/Edit', [
            'dateTypeRange' => $dateTypeRange,
            'dateTypes' => DateType::all()
        ]);
    }

    public function update(Request $request, DateTypeRange $dateTypeRange)
    {
        $validated = $request->validate([
            'date_type_id' => 'required|exists:date_types,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
        ]);

        // Check for overlap excluding current record
        $existing = DateTypeRange::where('package_id', $dateTypeRange->package_id)
            ->where('id', '!=', $dateTypeRange->id)
            ->where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->first();

        if ($existing) {
            $message = 'This date range overlaps with an existing date type range for this package in the date range of ' .
                Carbon::parse($existing->start_date)->format('d-m-Y') . ' to ' .
                Carbon::parse($existing->end_date)->format('d-m-Y');

            return back()->withErrors(['date_range' => $message]);
        }

        $dateTypeRange->update($validated);

        return $request->has('return_to_package')
            ? redirect()->route('packages.show', $request->package_id)->with('success', 'Date range updated successfully.')
            : redirect()->route('date-type-ranges.index')->with('success', 'Date range updated successfully.');
    }

    public function destroy(DateTypeRange $dateTypeRange)
    {
        $packageId = request()->package_id;
        $dateTypeRange->delete();

        return request()->has('return_to_package')
            ? redirect()->route('packages.show', $packageId)->with('success', 'Date range deleted successfully.')
            : redirect()->route('date-type-ranges.index')->with('success', 'Date range deleted successfully.');
    }

    public function storeBulk(Request $request)
    {
        $results = [
            'success' => [],
            'errors' => [],
            'validation_errors' => [],
        ];

        if (!$request->has('dateTypeRanges') || !is_array($request->dateTypeRanges) || empty($request->dateTypeRanges)) {
            $results['errors'][] = 'At least one date type range is required';
            return response()->json($results, 200);
        }

        if (!$request->package_id) {
            $results['errors'][] = 'Package ID is required';
            return response()->json($results, 200);
        }

        foreach ($request->dateTypeRanges as $index => $range) {
            $errors = [];

            if (empty($range['date_type_id']) || !DateType::find($range['date_type_id'])) {
                $errors[] = 'Date type is invalid or missing';
            }

            if (empty($range['start_date']) || !strtotime($range['start_date'])) {
                $errors[] = 'Start date is invalid or missing';
            }

            if (empty($range['end_date']) || !strtotime($range['end_date'])) {
                $errors[] = 'End date is invalid or missing';
            }

            if (!empty($range['start_date']) && !empty($range['end_date'])) {
                if (strtotime($range['end_date']) < strtotime($range['start_date'])) {
                    $errors[] = 'End date must be the same as or after the start date';
                }
            }

            if ($errors) {
                $results['validation_errors'][] = [
                    'index' => $index,
                    'data' => $range,
                    'errors' => $errors
                ];
                continue;
            }

            // Overlap check
            $message = $this->getOverlapMessage($range['start_date'], $range['end_date'], $request->package_id);
            if ($message) {
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $range,
                    'message' => $message
                ];
                continue;
            }

            try {
                $dateTypeRange = DateTypeRange::create([
                    'date_type_id' => $range['date_type_id'],
                    'start_date' => $range['start_date'],
                    'end_date' => $range['end_date'],
                    'package_id' => $request->package_id
                ]);

                $dateType = DateType::find($range['date_type_id']);
                $this->priceConfigurationService->createPriceConfigurationsService(
                    Package::find($request->package_id),
                    [],
                    [],
                    [$dateType],
                    false
                );

                $results['success'][] = [
                    'index' => $index,
                    'data' => $range,
                    'date_type_range_id' => $dateTypeRange->id,
                    'message' => 'Date type range created successfully'
                ];
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $range,
                    'message' => 'Failed to create date type range: ' . $e->getMessage()
                ];
            }
        }

        return response()->json($results, 200);
    }

    // 🔁 Reusable method to format overlap message
    private function getOverlapMessage($startDate, $endDate, $packageId)
    {
        $existing = DateTypeRange::where('package_id', $packageId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->first();

        return $existing
            ? 'This date range overlaps with an existing date type range for this package in the date range of ' .
            Carbon::parse($existing->start_date)->format('d-m-Y') . ' to ' .
            Carbon::parse($existing->end_date)->format('d-m-Y')
            : null;
    }
}
