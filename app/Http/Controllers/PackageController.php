<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\RoomType;
use App\Models\Season;
use App\Models\DateType;
use App\Models\PackageConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort functionality
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $packages = $query->with(['addOns', 'configurations', 'loadRoomTypes'])
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
            'filters' => $request->only(['search', 'status', 'sort', 'direction'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Packages/Create');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

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
                'room_types' => 'required|array|min:1',
                'room_types.*.name' => 'required|string|max:255',
                'room_types.*.max_occupancy' => 'required|integer|min:1',
                'room_types.*.description' => 'nullable|string',
            ]);

            if ($request->hasFile('icon_photo')) {
                $validated['icon_photo'] = $request->file('icon_photo')->store('packages', 'public');
            }

            // Create the package
            $package = Package::create($validated);

            // Get default season and date type
            $defaultSeason = Season::first();
            $defaultDateType = DateType::first();

            if (!$defaultSeason || !$defaultDateType) {
                throw new \Exception('Default season or date type not found. Please create them first.');
            }

            // Create room types and configurations
            foreach ($request->room_types as $roomTypeData) {
                // Create room type
                $roomType = RoomType::create([
                    'name' => $roomTypeData['name'],
                    'description' => $roomTypeData['description'],
                    'max_occupancy' => $roomTypeData['max_occupancy'],
                    'package_id' => $package->id
                ]);

                // Create package configuration
                PackageConfiguration::create([
                    'package_id' => $package->id,
                    'room_type_id' => $roomType->id,
                    'season_id' => $defaultSeason->id,
                    'date_type_id' => $defaultDateType->id
                ]);
            }

            DB::commit();

            return redirect()->route('packages.index')
                ->with('success', 'Package created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Package creation failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }

    public function show(Package $package)
    {
        $roomTypes = $package->loadRoomTypes()->latest()->paginate(10);

        return Inertia::render('Packages/Show', [
            'pkg' => $package->load([
                'configurations',
                'configurations.roomType',
                'configurations.season',
                'configurations.season.type',
                'configurations.dateType',
                'configurations.dateType.ranges'
            ])->setRelation('load_room_types', $roomTypes),
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

        $package->addOns()->delete();
        $package->configurations()->delete();
        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', 'Package deleted successfully.');
    }
}
