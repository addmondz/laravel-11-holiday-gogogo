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
use App\Services\GeneratePackageUid;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public $enabledDefaultSeasonAndDateType;
    public function __construct()
    {
        $this->enabledDefaultSeasonAndDateType = env('ENABLED_DEFAULT_SEASON_AND_DATE_TYPE', false);
    }

    public function index(Request $request)
    {
        $query = Package::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    // ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Date range filtering - Find packages available within the given date range
        if ($request->filled('dateFrom') && $request->filled('dateTo')) {
            $dateFrom = $request->dateFrom;
            $dateTo = $request->dateTo;

            // Find packages that are available during the specified date range
            // A package is available if:
            // 1. Package start date is before or equal to the user's end date AND
            // 2. Package end date is after or equal to the user's start date
            $query->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where('package_start_date', '<=', $dateTo)
                    ->where('package_end_date', '>=', $dateFrom);
            });
        } elseif ($request->filled('dateFrom')) {
            // If only start date is provided, find packages that end after or on the start date
            $query->where('package_end_date', '>=', $request->dateFrom);
        } elseif ($request->filled('dateTo')) {
            // If only end date is provided, find packages that start before or on the end date
            $query->where('package_start_date', '<=', $request->dateTo);
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
            'filters' => $request->only(['search', 'status', 'sort', 'direction', 'dateFrom', 'dateTo'])
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
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('packages', 'name')->whereNull('deleted_at'),
                ],
                'description' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpeg,png,gif',
                    'max:10240', // 10MB max per image
                ],
                'child_max_age_desc' => 'nullable|string',
                'infant_max_age_desc' => 'nullable|string',
                'display_price_adult' => 'required|numeric|min:0',
                'display_price_child' => 'numeric|min:0',
                'display_price_infant' => 'numeric|min:0',
                'adult_surcharge' => 'numeric|min:0',
                'child_surcharge' => 'numeric|min:0',
                'infant_surcharge' => 'numeric|min:0',
                'package_days' => 'required|integer|min:1',
                'terms_and_conditions' => 'nullable|string',
                'location' => 'required|string|max:255',
                'package_start_date' => 'required|date',
                'package_end_date' => 'required|date|after:package_start_date',
                'weekend_days' => 'nullable|array',
                'weekend_days.*' => 'integer|min:0|max:6',
                'room_types' => 'required|array|min:1',
                'room_types.*.name' => 'required|string|max:255',
                'room_types.*.max_occupancy' => 'required|integer|min:1',
                'room_types.*.description' => 'nullable|string',
                'room_types.*.images' => 'nullable|array',
                'room_types.*.images.*' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpeg,png,gif',
                    'max:10240', // 10MB max per image
                ],
            ], [
                'child_max_age_desc.string' => 'Child age description must be a string',
                'infant_max_age_desc.string' => 'Infant age description must be a string',
                'name.required' => 'Package name is required',
                'name.max' => 'Package name cannot exceed 255 characters',
                'images.array' => 'Images must be provided as a list',
                'images.*.required' => 'Image file is required',
                'images.*.file' => 'The uploaded file is invalid',
                'images.*.image' => 'The file must be an image (JPG, PNG, or GIF)',
                'images.*.mimes' => 'Only JPG, PNG, and GIF images are allowed',
                'images.*.max' => 'Each image cannot exceed 10MB',
                'display_price_adult.required' => 'Adult base price is required',
                'display_price_adult.numeric' => 'Adult base price must be a number',
                'display_price_adult.min' => 'Adult base price cannot be negative',
                'display_price_child.required' => 'Child base price is required',
                'display_price_child.numeric' => 'Child base price must be a number',
                'display_price_child.min' => 'Child base price cannot be negative',
                'display_price_infant.required' => 'Infant base price is required',
                'display_price_infant.numeric' => 'Infant base price must be a number',
                'display_price_infant.min' => 'Infant base price cannot be negative',
                'adult_surcharge.required' => 'Adult surcharge is required',
                'adult_surcharge.numeric' => 'Adult surcharge must be a number',
                'adult_surcharge.min' => 'Adult surcharge cannot be negative',
                'child_surcharge.required' => 'Child surcharge is required',
                'child_surcharge.numeric' => 'Child surcharge must be a number',
                'child_surcharge.min' => 'Child surcharge cannot be negative',
                'infant_surcharge.required' => 'Infant surcharge is required',
                'infant_surcharge.numeric' => 'Infant surcharge must be a number',
                'infant_surcharge.min' => 'Infant surcharge cannot be negative',
                'package_days.required' => 'Package duration is required',
                'package_days.integer' => 'Package duration must be a whole number',
                'package_days.min' => 'Package duration must be at least 1 night',
                'location.required' => 'Location is required',
                'location.max' => 'Location cannot exceed 255 characters',
                'package_start_date.required' => 'Start date is required',
                'package_start_date.date' => 'Invalid start date format',
                'package_end_date.required' => 'End date is required',
                'package_end_date.date' => 'Invalid end date format',
                'package_end_date.after' => 'End date must be after start date',
                'weekend_days.array' => 'Weekend days must be provided as a list',
                'weekend_days.*.integer' => 'Weekend day must be a whole number',
                'weekend_days.*.min' => 'Weekend day must be between 0 and 6',
                'weekend_days.*.max' => 'Weekend day must be between 0 and 6',
                'room_types.required' => 'At least one room type is required',
                'room_types.array' => 'Room types must be provided as a list',
                'room_types.min' => 'At least one room type is required',
                'room_types.*.name.required' => 'Room type name is required',
                'room_types.*.name.max' => 'Room type name cannot exceed 255 characters',
                'room_types.*.max_occupancy.required' => 'Maximum occupancy is required',
                'room_types.*.max_occupancy.integer' => 'Maximum occupancy must be a whole number',
                'room_types.*.max_occupancy.min' => 'Maximum occupancy must be at least 1',
                'room_types.*.images.array' => 'Room type images must be provided as a list',
                'room_types.*.images.*.required' => 'Room type image file is required',
                'room_types.*.images.*.file' => 'The uploaded file is invalid',
                'room_types.*.images.*.image' => 'The file must be an image (JPG, PNG, or GIF)',
                'room_types.*.images.*.mimes' => 'Only JPG, PNG, and GIF images are allowed',
                'room_types.*.images.*.max' => 'Each image cannot exceed 10MB',
            ]);

            $v = $validator->validated();

            // Handle package image uploads
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('packages', 'public');
                }
            }
            $v['images'] = $images;

            $v['uuid'] = (new GeneratePackageUid())->execute($v['name']);
            $v['package_min_days'] = $v['package_days'];
            $v['package_max_days'] = $v['package_days'];
            $v['weekend_days'] = $v['weekend_days'] ?? [0, 6]; // Default to Saturday and Sunday

            $package = Package::create($v);

            if ($this->enabledDefaultSeasonAndDateType) {
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
            }

            // Handle room types with their images
            $roomTypes = collect($request->room_types)->map(function ($roomTypeData, $roomTypeIndex) use ($package, $request) {
                $roomTypeImages = [];
                if ($request->hasFile("room_types.{$roomTypeIndex}.images")) {
                    foreach ($request->file("room_types.{$roomTypeIndex}.images") as $image) {
                        $roomTypeImages[] = $image->store('room-types', 'public');
                    }
                }

                return RoomType::create([
                    'name' => $roomTypeData['name'],
                    'description' => $roomTypeData['description'] ?? null,
                    'max_occupancy' => $roomTypeData['max_occupancy'],
                    'package_id' => $package->id,
                    'images' => $roomTypeImages
                ]);
            });

            $combinations = AppConstants::ADULT_CHILD_COMBINATIONS;
            $baseKey = AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE;
            $surKey = AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE;

            foreach ($roomTypes as $room) {
                if ($this->enabledDefaultSeasonAndDateType) {
                    foreach ($seasonTypes as $season) {
                        foreach ($dateTypes as $date) {
                            $prices = [];

                            foreach ($combinations as $c) {
                                $k = "{$c['adults']}_a_{$c['children']}_c_{$c['infants']}_i";
                                $prices[$baseKey]["{$k}_a"] = $v['display_price_adult'];
                                $prices[$baseKey]["{$k}_c"] = $v['display_price_child'];
                                $prices[$baseKey]["{$k}_i"] = $v['display_price_infant'];
                                $prices[$surKey]["{$k}_a"] = $v['adult_surcharge'];
                                $prices[$surKey]["{$k}_c"] = $v['child_surcharge'];
                                $prices[$surKey]["{$k}_i"] = $v['infant_surcharge'];
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
            }

            DB::commit();
            return redirect()->route('packages.index')->with('success', 'Package created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Return validation errors to the frontend
            Log::error('Package creation validation error: ' . $e->getMessage());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create package failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create package: ' . $e->getMessage()]);
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
        $allSeasonTypes = SeasonType::all()->toArray();
        $dateTypes = DateType::whereNotIn('name', ['Default', 'Weekday', 'Weekend'])->get()->toArray();
        $allDateTypes = DateType::all()->toArray();

        $priceConfigSeasonChoice = SeasonType::whereHas('seasons', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->get();

        $priceConfigDateTypeChoice = DateType::whereHas('ranges', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->get();

        $packageUniqueRoomTypes = $package->loadRoomTypes()->get()->pluck('name', 'id');

        $assignedSeasonTypes = SeasonType::whereHas('seasons', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->orWhere('name', 'Default')->get();

        $assignedDateTypes = DateType::whereHas('ranges', function ($query) use ($package) {
            $query->where('package_id', $package->id);
        })->orWhereIn('name', ['Default', 'Weekday', 'Weekend'])->get();

        return Inertia::render('Packages/Show', [
            'pkg' => $package->setRelation('load_room_types', $roomTypes),
            'seasons' => $seasons,
            'seasonTypes' => $seasonTypes,
            'dateTypeRanges' => $dateTypeRanges,
            'dateTypes' => $dateTypes,
            'priceConfigSeasonChoice' => $priceConfigSeasonChoice,
            'priceConfigDateTypeChoice' => $priceConfigDateTypeChoice,
            'packageUniqueRoomTypes' => $packageUniqueRoomTypes,
            'allSeasonTypes' => $allSeasonTypes,
            'allDateTypes' => $allDateTypes,
            'assignedSeasonTypes' => $assignedSeasonTypes,
            'assignedDateTypes' => $assignedDateTypes
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
        $weekendDays = json_decode($request->weekend_days, true);
        if (is_array($weekendDays)) {
            sort($weekendDays);
        }

        $request->merge([
            'weekend_days' => $weekendDays
        ]);

        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('packages', 'name')
                        ->ignore($package->id)
                        ->whereNull('deleted_at'),
                ],
                'description' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpeg,png,gif',
                    'max:10240', // 10MB max per image
                ],
                'display_price_adult' => 'nullable|numeric|min:0',
                'display_price_child' => 'nullable|min:0',
                'package_min_days' => 'required|integer|min:1',
                'package_max_days' => 'required|integer|min:1',
                'terms_and_conditions' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'package_start_date' => 'required|date',
                'package_end_date' => 'nullable|date|after:package_start_date',
                'weekend_days' => 'nullable|array',
                'weekend_days.*' => 'integer|min:0|max:6',
                'delete_images' => 'nullable|array',
                'delete_images.*' => 'string'
            ], [
                'name.required' => 'Package name is required',
                'name.max' => 'Package name cannot exceed 255 characters',
                'images.array' => 'Images must be provided as a list',
                'images.*.required' => 'Image file is required',
                'images.*.file' => 'The uploaded file is invalid',
                'images.*.image' => 'The file must be an image (JPG, PNG, or GIF)',
                'images.*.mimes' => 'Only JPG, PNG, and GIF images are allowed',
                'images.*.max' => 'Each image cannot exceed 10MB',
                'package_min_days.required' => 'Minimum package duration is required',
                'package_min_days.integer' => 'Minimum package duration must be a whole number',
                'package_min_days.min' => 'Minimum package duration must be at least 1 night',
                'package_max_days.required' => 'Maximum package duration is required',
                'package_max_days.integer' => 'Maximum package duration must be a whole number',
                'package_max_days.min' => 'Maximum package duration must be at least 1 night',
                'package_start_date.required' => 'Start date is required',
                'package_start_date.date' => 'Invalid start date format',
                'package_end_date.date' => 'Invalid end date format',
                'package_end_date.after' => 'End date must be after start date',
                'weekend_days.array' => 'Weekend days must be provided as a list',
                'weekend_days.*.integer' => 'Weekend day must be a whole number',
                'weekend_days.*.min' => 'Weekend day must be between 0 and 6',
                'weekend_days.*.max' => 'Weekend day must be between 0 and 6'
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                if ($errors->has('images.*')) {
                    $imageErrors = collect($errors->get('images.*'))->map(function ($error, $key) {
                        $index = explode('.', $key)[1];
                        return "Image " . ($index + 1) . ": " . $error[0];
                    })->toArray();

                    $errors->forget('images.*');
                    foreach ($imageErrors as $error) {
                        $errors->add('images', $error);
                    }
                }

                // 🔁 Return JSON if it's an Axios/XHR request
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $errors,
                    ], 422); // ✅ This makes Vue run .catch()
                }

                return back()->withErrors($errors)->withInput(); // fallback for web
            }

            $validated = $validator->validated();

            // Handle image deletions
            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imagePath) {
                    if (in_array($imagePath, $package->images ?? [])) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
                $currentImages = array_diff($package->images ?? [], $request->delete_images);
            } else {
                $currentImages = $package->images ?? [];
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    // Additional validation for image dimensions if needed
                    $imageInfo = getimagesize($image->getRealPath());
                    if ($imageInfo === false) {
                        throw new \Exception('Invalid image file: Unable to read image dimensions');
                    }

                    $currentImages[] = $image->store('packages', 'public');
                }
            }

            $validated['images'] = array_values($currentImages); // Reindex array

            $package->update($validated);

            DB::commit();
            return redirect()->route('packages.show', $package->id)
                ->with('success', 'Package updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update package failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update package: ' . $e->getMessage()]);
        }
    }

    public function destroy(Package $package)
    {
        // Delete all package images
        if ($package->images) {
            foreach ($package->images as $image) {
                Storage::disk('public')->delete($image);
            }
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
                'name' => 'required|string|max:255|unique:packages,name',
                'description' => 'nullable|string',
                'display_price_adult' => 'nullable|numeric|min:0',
                'display_price_child' => 'nullable|numeric|min:0',
                'package_min_days' => 'required|integer|min:1',
                'package_max_days' => 'required|integer|min:1|gte:package_min_days',
                'terms_and_conditions' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'package_start_date' => 'required|date',
                'package_end_date' => 'nullable|date|after:package_start_date',
                'weekend_days' => 'nullable|array',
                'weekend_days.*' => 'integer|min:0|max:6',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
                'room_types' => 'required|array|min:1',
                'room_types.*.name' => 'required|string|max:255',
                'room_types.*.max_occupancy' => 'required|integer|min:1',
                'room_types.*.description' => 'nullable|string',
            ], [
                'name.required' => 'Package name is required',
                'name.max' => 'Package name cannot exceed 255 characters',
                'name.unique' => 'A package with this name already exists',
                'package_min_days.required' => 'Package minimum days is required',
                'package_min_days.integer' => 'Package minimum days must be a whole number',
                'package_min_days.min' => 'Package minimum days must be at least 1',
                'package_max_days.required' => 'Package maximum days is required',
                'package_max_days.integer' => 'Package maximum days must be a whole number',
                'package_max_days.min' => 'Package maximum days must be at least 1',
                'package_max_days.gte' => 'Package maximum days must be greater than or equal to minimum days',
                'package_start_date.required' => 'Start date is required',
                'package_start_date.date' => 'Invalid start date format',
                'package_end_date.date' => 'Invalid end date format',
                'package_end_date.after' => 'End date must be after start date',
                'weekend_days.array' => 'Weekend days must be provided as a list',
                'weekend_days.*.integer' => 'Weekend day must be a whole number',
                'weekend_days.*.min' => 'Weekend day must be between 0 and 6',
                'weekend_days.*.max' => 'Weekend day must be between 0 and 6',
                'images.*.image' => 'The file must be an image (JPG, PNG, or GIF)',
                'images.*.mimes' => 'Only JPG, PNG, and GIF images are allowed',
                'images.*.max' => 'Each image cannot exceed 10MB',
                'room_types.required' => 'At least one room type is required',
                'room_types.array' => 'Room types must be provided as a list',
                'room_types.min' => 'At least one room type is required',
                'room_types.*.name.required' => 'Room type name is required',
                'room_types.*.name.max' => 'Room type name cannot exceed 255 characters',
                'room_types.*.max_occupancy.required' => 'Maximum occupancy is required',
                'room_types.*.max_occupancy.integer' => 'Maximum occupancy must be a whole number',
                'room_types.*.max_occupancy.min' => 'Maximum occupancy must be at least 1',
            ]);

            // Create new package with validated data
            $newPackage = new Package($validated);
            $newPackage->uuid = (new GeneratePackageUid())->execute($newPackage->name);
            $newPackage->weekend_days = $validated['weekend_days'] ?? [0, 6]; // Default to Saturday and Sunday

            // Handle image uploads and copying
            $images = [];

            // Copy existing images if they exist
            if ($package->images) {
                foreach ($package->images as $imagePath) {
                    if (Storage::disk('public')->exists($imagePath)) {
                        // Generate new path for the copied image
                        $newPath = 'packages/' . Str::uuid() . '_' . basename($imagePath);
                        // Copy the image to the new path
                        Storage::disk('public')->copy($imagePath, $newPath);
                        $images[] = $newPath;
                    }
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('packages', 'public');
                    $images[] = $path;
                }
            }

            $newPackage->images = $images;
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

            // date blockers
            foreach ($package->dateBlockers as $dateBlocker) {
                $newDateBlocker = $dateBlocker->replicate();
                $newDateBlocker->package_id = $newPackage->id;
                $newDateBlocker->save();
            }

            DB::commit();

            return redirect()->route('packages.show', $newPackage->id)
                ->with('success', 'Package duplicated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Return validation errors to the frontend
            Log::error('Package duplication validation error: ' . $e->getMessage());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Package duplication failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
