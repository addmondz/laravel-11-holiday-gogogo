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
        ]);

        DateTypeRange::create($validated);

        return redirect()->route('date-type-ranges.index')
            ->with('success', 'Date type range created successfully.');
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

        return redirect()->route('date-type-ranges.index')
            ->with('success', 'Date type range updated successfully.');
    }

    public function destroy(DateTypeRange $dateTypeRange)
    {
        $dateTypeRange->delete();

        return redirect()->route('date-type-ranges.index')
            ->with('success', 'Date type range deleted successfully.');
    }
}
