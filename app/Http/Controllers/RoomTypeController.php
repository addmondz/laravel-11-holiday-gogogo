<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('RoomTypes/Index', [
            'roomTypes' => RoomType::with('package')->latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('RoomTypes/Create', [
            'packages' => Package::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_occupancy' => 'required|integer|min:1',
            'package_id' => 'required|exists:packages,id'
        ]);

        RoomType::create($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        if ($request->has('return_to_package')) {
            return redirect()->route('packages.show', $validated['package_id'])
                ->with('success', 'Room type created successfully.');
        }

        return redirect()->route('room-types.index')
            ->with('success', 'Room type created successfully.');
    }

    public function show(RoomType $roomType)
    {
        return Inertia::render('RoomTypes/Show', [
            'roomType' => $roomType->load(['configurations', 'package'])
        ]);
    }

    public function edit(RoomType $roomType)
    {
        return Inertia::render('RoomTypes/Edit', [
            'roomType' => $roomType,
            'packages' => Package::where('is_active', true)->get()
        ]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_occupancy' => 'required|integer|min:1',
            'package_id' => 'required|exists:packages,id'
        ]);

        $roomType->update($validated);

        // If the request has a return_to_package parameter, redirect back to the package page
        if ($request->has('return_to_package')) {
            return redirect()->route('packages.show', $validated['package_id'])
                ->with('success', 'Room type updated successfully.');
        }

        return redirect()->route('room-types.index')
            ->with('success', 'Room type updated successfully.');
    }

    public function destroy(RoomType $roomType)
    {
        $packageId = $roomType->package_id;
        $roomType->delete();

        // If the request has a return_to_package parameter, redirect back to the package page
        if (request()->has('return_to_package')) {
            return redirect()->route('packages.show', $packageId)
                ->with('success', 'Room type deleted successfully.');
        }

        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }
}
