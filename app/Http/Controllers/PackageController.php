<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
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
use App\Models\SeasonType;
use App\Models\DateTypeRange;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    // ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort functionality
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $packages = $query
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
        DB::beginTransaction();

        try {
            $v = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon_photo' => 'nullable|image|max:2048',
                'display_price_adult' => 'nullable|numeric|min:0',
                'display_price_child' => 'nullable|numeric|min:0',
                'adult_surcharge' => 'nullable|numeric|min:0',
                'child_surcharge' => 'nullable|numeric|min:0',
                'package_days' => 'required|integer|min:1',
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
                $v['icon_photo'] = $request->file('icon_photo')->store('packages', 'public');
            }

            $v['uuid'] = Str::uuid()->toString();
            $v['package_min_days'] = $v['package_days'];
            $v['package_max_days'] = $v['package_days'];

            $package = Package::create($v);

            $seasonTypes = SeasonType::where('name', 'Default')->get();
            $dateTypes = DateType::whereIn('name', ['Default', 'Weekday', 'Weekend'])->get();

            if ($seasonTypes->isEmpty() || $dateTypes->isEmpty()) {
                throw new \Exception('Default season/date type not found');
            }

            $start = '1970-01-01';
            $end = '1970-01-02';

            $seasonTypes->each(fn($s) => Season::create([
                'season_type_id' => $s->id,
                'start_date' => $start,
                'end_date' => $end,
                'package_id' => $package->id,
            ]));

            $dateTypes->each(fn($d) => DateTypeRange::create([
                'date_type_id' => $d->id,
                'start_date' => $start,
                'end_date' => $end,
                'package_id' => $package->id,
            ]));

            $roomTypes = collect($v['room_types'])->map(fn($r) => RoomType::create([
                'name' => $r['name'],
                'description' => $r['description'] ?? null,
                'max_occupancy' => $r['max_occupancy'],
                'package_id' => $package->id,
            ]));

            $combinations = AppConstants::ADULT_CHILD_COMBINATIONS;
            $baseKey = AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE;
            $surKey = AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE;

            foreach ($roomTypes as $room) {
                foreach ($seasonTypes as $season) {
                    foreach ($dateTypes as $date) {
                        $prices = [];

                        foreach ($combinations as $c) {
                            $k = "{$c['adults']}_a_{$c['children']}_c";
                            $prices[$baseKey]["{$k}_a"] = $v['display_price_adult'];
                            $prices[$baseKey]["{$k}_c"] = $v['display_price_child'];
                            $prices[$surKey]["{$k}_a"] = $v['adult_surcharge'];
                            $prices[$surKey]["{$k}_c"] = $v['child_surcharge'];
                        }

                        PackageConfiguration::create([
                            'package_id' => $package->id,
                            'room_type_id' => $room->id,
                            'season_type_id' => $season->id,
                            'date_type_id' => $date->id,
                            'configuration_prices' => json_encode($prices),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('packages.index')->with('success', 'Package created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create package failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }


    public function show(Package $package, Request $request)
    {
        // Set up pagination for each section
        $roomTypes = $package->loadRoomTypes()
            ->latest()
            ->paginate(10);

        $defaultSeasonTypeId = SeasonType::where('name', 'Default')->value('id');
        $seasons = Season::with('type')
            ->where('package_id', $package->id)
            ->whereHas('type', function ($query) use ($defaultSeasonTypeId) {
                $query->where('id', '!=', $defaultSeasonTypeId);
            })
            ->latest()
            ->paginate(10);

        $defaultTypeRange = DateType::whereIn('name', ['Default', 'Weekday', 'Weekend'])->pluck('id');
        $dateTypeRanges = DateTypeRange::with('dateType')
            ->where('package_id', $package->id)
            ->whereHas('dateType', function ($query) use ($defaultTypeRange) {
                $query->whereNotIn('id', $defaultTypeRange);
            })
            ->latest()
            ->paginate(10);

        $seasonTypes = SeasonType::whereNot('name', 'Default')->get()->toArray();
        $dateTypes = DateType::whereNotIn('name', ['Default', 'Weekday', 'Weekend'])->get()->toArray();

        $priceConfigSeasonChoice = SeasonType::whereHas('seasons', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->get();

        $priceConfigDateTypeChoice = DateType::whereHas('ranges', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->get();

        return Inertia::render('Packages/Show', [
            'pkg' => $package->setRelation('load_room_types', $roomTypes),
            'seasons' => $seasons,
            'seasonTypes' => $seasonTypes,
            'dateTypeRanges' => $dateTypeRanges,
            'dateTypes' => $dateTypes,
            'priceConfigSeasonChoice' => $priceConfigSeasonChoice,
            'priceConfigDateTypeChoice' => $priceConfigDateTypeChoice
        ]);
    }

    public function getRoomTypes(Package $package, Request $request)
    {
        $roomTypes = $package->loadRoomTypes()
            ->latest()
            ->paginate(10, ['*'], 'page');

        return response()->json($roomTypes);
    }

    public function getSeasons(Request $request, Package $package)
    {
        $defaultSeasonTypeId = SeasonType::where('name', 'Default')->value('id');
        $seasons = Season::with('type')
            ->where('package_id', $package->id)
            ->where('season_type_id', '!=', $defaultSeasonTypeId)
            ->latest()
            ->paginate(10, ['*'], 'page');

        return response()->json($seasons);
    }

    public function getDateTypeRanges(Request $request, Package $package)
    {
        $defaultDateTypeId = DateType::whereIn('name', ['Default', 'Weekday', 'Weekend'])->pluck('id');
        $dateTypeRanges = DateTypeRange::with('dateType')
            ->where('package_id', $package->id)
            ->whereNotIn('date_type_id', $defaultDateTypeId)
            ->latest()
            ->paginate(10, ['*'], 'page');

        return response()->json($dateTypeRanges);
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

    public function duplicateForm(Package $package)
    {
        $package->load(['loadRoomTypes']);

        $data = $package->toArray();
        $data['name'] = $this->generateUniqueCopyName($package->name);
        $data['uuid'] = null;
        $data['room_types'] = collect($data['load_room_types'])->unique('name')->values();

        return Inertia::render('Packages/Duplicate', [
            'package' => $data,
            'originalPackage' => $package,
        ]);
    }

    private function generateUniqueCopyName($originalName)
    {
        // Normalize: Remove " (Copy)" or " (Copy X)" at the end
        $baseName = preg_replace('/\s\(Copy(?:\s\d+)?\)$/', '', $originalName);

        // Escape for LIKE
        $escapedBase = str_replace(['%', '_'], ['\%', '\_'], $baseName);
        $searchTerm = $escapedBase . '%';

        // Fetch all similar names once
        $similarNames = Package::where('name', 'like', $searchTerm)->pluck('name')->toArray();

        // If "(Copy)" not used yet
        $firstCopy = "{$baseName} (Copy)";
        if (!in_array($firstCopy, $similarNames)) {
            return $firstCopy;
        }

        // Find next available number
        $i = 2;
        while (in_array("{$baseName} (Copy {$i})", $similarNames)) {
            $i++;
        }

        return "{$baseName} (Copy {$i})";
    }

    public function duplicate(Request $request, Package $package)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'display_price_adult' => 'nullable|numeric|min:0',
                'display_price_child' => 'nullable|numeric|min:0',
                'package_min_days' => 'required|integer|min:1',
                'package_max_days' => 'required|integer|min:1|gte:package_min_days',
                'terms_and_conditions' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'package_start_date' => 'required|date',
                'package_end_date' => 'nullable|date|after:package_start_date',
            ]);

            // Create new package with validated data
            $newPackage = new Package($validated);
            $newPackage->uuid = Str::uuid();
            $newPackage->save();

            // Step 1: Create new records and build mappings
            $roomTypeMap = [];
            foreach ($package->roomTypes->unique('name') as $roomType) {
                $newRoomType = $roomType->replicate();
                $newRoomType->package_id = $newPackage->id;
                $newRoomType->save();
                $roomTypeMap[$roomType->id] = $newRoomType->id;
            }

            $seasonMap = [];
            foreach ($package->seasons as $season) {
                $newSeason = $season->replicate();
                $newSeason->package_id = $newPackage->id;
                $newSeason->save();
                $seasonMap[$season->id] = $newSeason->id;
            }

            $dateTypeRangeMap = [];
            foreach ($package->dateTypeRanges as $dateTypeRange) {
                $newDateTypeRange = $dateTypeRange->replicate();
                $newDateTypeRange->package_id = $newPackage->id;
                $newDateTypeRange->save();
                $dateTypeRangeMap[$dateTypeRange->id] = $newDateTypeRange->id;
            }

            // package configuration
            foreach ($package->configurations as $config) {
                $newConfig = $config->replicate();
                $newConfig->package_id = $newPackage->id;
                $newConfig->room_type_id = $roomTypeMap[$config->room_type_id];
                $newConfig->save();
            }

            DB::commit();

            return redirect()->route('packages.index')
                ->with('success', 'Package duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Package duplication failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Failed to duplicate package: ' . $e->getMessage());
        }
    }
}
