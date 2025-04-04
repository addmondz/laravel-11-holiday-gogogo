<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageAddOn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageAddOnController extends Controller
{
    public function index()
    {
        $addOns = PackageAddOn::with('package')
            ->latest()
            ->get();

        return Inertia::render('PackageAddOns/Index', [
            'addOns' => $addOns
        ]);
    }

    public function create()
    {
        $packages = Package::all();

        return Inertia::render('PackageAddOns/Create', [
            'packages' => $packages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'adult_price' => 'nullable|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
        ]);

        PackageAddOn::create($validated);

        return redirect()->route('package-add-ons.index')
            ->with('success', 'Package add-on created successfully.');
    }

    public function show(PackageAddOn $packageAddOn)
    {
        return Inertia::render('PackageAddOns/Show', [
            'addOn' => $packageAddOn->load('package')
        ]);
    }

    public function edit(PackageAddOn $packageAddOn)
    {
        $packages = Package::all();

        return Inertia::render('PackageAddOns/Edit', [
            'addOn' => $packageAddOn,
            'packages' => $packages
        ]);
    }

    public function update(Request $request, PackageAddOn $packageAddOn)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'adult_price' => 'nullable|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
        ]);

        $packageAddOn->update($validated);

        return redirect()->route('package-add-ons.index')
            ->with('success', 'Package add-on updated successfully.');
    }

    public function destroy(PackageAddOn $packageAddOn)
    {
        $packageAddOn->delete();

        return redirect()->route('package-add-ons.index')
            ->with('success', 'Package add-on deleted successfully.');
    }
}
