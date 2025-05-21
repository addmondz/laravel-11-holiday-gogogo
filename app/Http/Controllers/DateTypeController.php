<?php

namespace App\Http\Controllers;

use App\Models\DateType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DateTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = DateType::whereNot('name', 'Default');

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
        $dateTypes = $query->with('ranges')
            ->paginate(10)
            ->withQueryString();

        $data = [
            'dateTypes' => $dateTypes,
            'filters' => $request->only(['search', 'sort', 'direction'])
        ];

        if ($request->has('package_id')) {
            $data['package_id'] = $request->package_id;
        }

        return Inertia::render('DateTypes/Index', $data);
    }

    public function create(Request $request)
    {
        return Inertia::render('DateTypes/Create', [
            'packageId' => $request->package_id
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:date_types'
        ]);

        DateType::create($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        if ($request->has('return_to_package')) {
            return redirect()->route('packages.show', $request->package_id)
                ->with('success', 'Date type created successfully.');
        }

        return redirect()->route('date-types.index')
            ->with('success', 'Date type created successfully.');
    }

    public function show(DateType $dateType)
    {
        return Inertia::render('DateTypes/Show', [
            'dateType' => $dateType->load('ranges')
        ]);
    }

    public function edit(DateType $dateType, Request $request)
    {
        return Inertia::render('DateTypes/Edit', [
            'dateType' => $dateType,
            'packageId' => $request->package_id
        ]);
    }

    public function update(Request $request, DateType $dateType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:date_types,name,' . $dateType->id
        ]);

        $dateType->update($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        return redirect()->route('packages.show', $request->package_id)
            ->with('success', 'Date type updated successfully.');
    }

    public function destroy(DateType $dateType)
    {
        $packageId = request()->package_id;
        $dateType->delete();

        // If the request has a return_to_package parameter, redirect back to the package page
        if (request()->has('return_to_package')) {
            return redirect()->route('packages.show', $packageId)
                ->with('success', 'Date type deleted successfully.');
        }

        return redirect()->route('date-types.index')
            ->with('success', 'Date type deleted successfully.');
    }
}
