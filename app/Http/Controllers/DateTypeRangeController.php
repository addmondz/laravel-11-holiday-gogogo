<?php

namespace App\Http\Controllers;

use App\Models\DateType;
use App\Models\DateTypeRange;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DateTypeRangeController extends Controller
{
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

        DateTypeRange::create($validated);

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
}
