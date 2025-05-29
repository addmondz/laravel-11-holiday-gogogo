<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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
            'package_id' => 'required|exists:packages,id',
            'images.*' => 'nullable|image|max:2048'
        ]);

        // Handle image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('room-types', 'public');
            }
        }
        $validated['images'] = $images;

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
            'package_id' => 'required|exists:packages,id',
            'images.*' => 'nullable|image|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'string'
        ]);

        // Handle image deletions
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $imagePath) {
                if (in_array($imagePath, $roomType->images ?? [])) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
            $currentImages = array_diff($roomType->images ?? [], $request->delete_images);
        } else {
            $currentImages = $roomType->images ?? [];
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $currentImages[] = $image->store('room-types', 'public');
            }
        }

        $validated['images'] = array_values($currentImages); // Reindex array

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
        // Delete all associated images
        if ($roomType->images) {
            foreach ($roomType->images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }

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
