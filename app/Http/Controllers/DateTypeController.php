<?php

namespace App\Http\Controllers;

use App\Models\DateType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DateTypeController extends Controller
{
    public function index()
    {
        $dateTypes = DateType::with('ranges')
            ->latest()
            ->get();

        return Inertia::render('DateTypes/Index', [
            'dateTypes' => $dateTypes
        ]);
    }

    public function create()
    {
        return Inertia::render('DateTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:date_types'
        ]);

        DateType::create($validated);

        return redirect()->route('date-types.index')
            ->with('success', 'Date type created successfully.');
    }

    public function show(DateType $dateType)
    {
        return Inertia::render('DateTypes/Show', [
            'dateType' => $dateType->load('ranges')
        ]);
    }

    public function edit(DateType $dateType)
    {
        return Inertia::render('DateTypes/Edit', [
            'dateType' => $dateType
        ]);
    }

    public function update(Request $request, DateType $dateType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:date_types,name,' . $dateType->id
        ]);

        $dateType->update($validated);

        return redirect()->route('date-types.index')
            ->with('success', 'Date type updated successfully.');
    }

    public function destroy(DateType $dateType)
    {
        $dateType->delete();

        return redirect()->route('date-types.index')
            ->with('success', 'Date type deleted successfully.');
    }
}
