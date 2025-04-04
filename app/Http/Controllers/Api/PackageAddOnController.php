<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageAddOn;
use Illuminate\Http\Request;

class PackageAddOnController extends Controller
{
    public function index()
    {
        $addOns = PackageAddOn::with('package')
            ->latest()
            ->get();

        return response()->json($addOns);
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

        $addOn = PackageAddOn::create($validated);

        return response()->json($addOn, 201);
    }

    public function show(PackageAddOn $packageAddOn)
    {
        return response()->json($packageAddOn->load('package'));
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

        return response()->json($packageAddOn);
    }

    public function destroy(PackageAddOn $packageAddOn)
    {
        $packageAddOn->delete();

        return response()->noContent();
    }
}
