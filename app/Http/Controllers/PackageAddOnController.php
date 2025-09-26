<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageAddOn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageAddOnController extends Controller
{
    public function index(Request $request, Package $package)
    {
        $addOns = PackageAddOn::where('package_id', $package->id)
            ->latest()
            ->paginate(10);

        return response()->json($addOns);
    }

    public function store(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'adult_price' => 'nullable|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
            'infant_price' => 'nullable|numeric|min:0',
        ]);

        $validated['package_id'] = $package->id;
        $addOn = PackageAddOn::create($validated);

        return response()->json([
            'message' => 'Package add-on created successfully.',
            'addOn' => $addOn
        ]);
    }

    public function update(Request $request, PackageAddOn $packageAddOn)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'adult_price' => 'nullable|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
            'infant_price' => 'nullable|numeric|min:0',
        ]);

        $packageAddOn->update($validated);

        return response()->json([
            'message' => 'Package add-on updated successfully.',
            'addOn' => $packageAddOn
        ]);
    }

    public function destroy(PackageAddOn $packageAddOn)
    {
        $packageAddOn->delete();

        return response()->json([
            'message' => 'Package add-on deleted successfully.'
        ]);
    }
}