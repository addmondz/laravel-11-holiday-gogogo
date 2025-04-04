<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    public function index()
    {
        return Inertia::render('Packages/Index', [
            'packages' => Package::with(['addOns', 'configurations'])->latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Packages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon_photo' => 'nullable|image|max:2048',
            'display_price_adult' => 'nullable|numeric|min:0',
            'display_price_child' => 'nullable|numeric|min:0',
            'package_min_days' => 'required|integer|min:1',
            'package_max_days' => 'required|integer|min:1',
            'terms_and_conditions' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'package_start_date' => 'required|date',
            'package_end_date' => 'nullable|date|after:package_start_date',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('icon_photo')) {
            $validated['icon_photo'] = $request->file('icon_photo')->store('packages', 'public');
        }

        Package::create($validated);

        return redirect()->route('packages.index')
            ->with('success', 'Package created successfully.');
    }

    public function show(Package $package)
    {
        return Inertia::render('Packages/Show', [
            'pkg' => $package->load(['addOns', 'configurations'])
        ]);
    }

    public function edit(Package $package)
    {
        return Inertia::render('Packages/Edit', [
            'package' => $package
        ]);
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon_photo' => 'nullable|image|max:2048',
            'display_price_adult' => 'nullable|numeric|min:0',
            'display_price_child' => 'nullable|numeric|min:0',
            'package_min_days' => 'required|integer|min:1',
            'package_max_days' => 'required|integer|min:1',
            'terms_and_conditions' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'package_start_date' => 'required|date',
            'package_end_date' => 'nullable|date|after:package_start_date',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('icon_photo')) {
            // Delete old photo if exists
            if ($package->icon_photo) {
                Storage::disk('public')->delete($package->icon_photo);
            }
            $validated['icon_photo'] = $request->file('icon_photo')->store('packages', 'public');
        }

        $package->update($validated);

        return redirect()->route('packages.index')
            ->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        // Delete icon photo if exists
        if ($package->icon_photo) {
            Storage::disk('public')->delete($package->icon_photo);
        }

        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', 'Package deleted successfully.');
    }
}
