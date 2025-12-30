<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Models\RoomType;
use App\Models\Package;
use App\Models\PackageConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Services\CreatePriceConfigurationsService;
use App\Services\EnsurePriceConfigService;

class RoomTypeController extends Controller
{
    protected CreatePriceConfigurationsService $priceConfigurationService;
    protected EnsurePriceConfigService $ensurePriceConfigService;
    public function __construct(CreatePriceConfigurationsService $priceConfigurationService, EnsurePriceConfigService $ensurePriceConfigService)
    {
        $this->priceConfigurationService = $priceConfigurationService;
        $this->ensurePriceConfigService = $ensurePriceConfigService;
    }

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
            'bed_desc' => 'nullable|string',
            'max_occupancy' => 'required|integer|min:1',
            'max_adults' => 'nullable|integer|min:1',
            'max_children' => 'nullable|integer|min:1',
            'max_infants' => 'nullable|integer|min:1',
            'package_id' => 'required|exists:packages,id',
            'images.*' => 'nullable|image|max:2048',
            'disabled_pax_combinations' => 'nullable',
            'default_show_surcharge' => 'nullable|boolean',
        ]);

        // Convert string '1'/'0' to boolean for default_show_surcharge
        $validated['default_show_surcharge'] = filter_var($request->input('default_show_surcharge'), FILTER_VALIDATE_BOOLEAN);

        // Handle disabled_pax_combinations
        $disabledPax = $validated['disabled_pax_combinations'] ?? null;
        
        // Handle different input formats
        if (is_array($disabledPax)) {
            // Already an array (from FormData array notation)
            $disabledPax = array_values(array_filter($disabledPax, fn($item) => !empty(trim($item))));
        } elseif (is_string($disabledPax)) {
            // Comma-separated string
            if (trim($disabledPax) === '') {
                $disabledPax = [];
            } else {
                $disabledPax = array_values(array_filter(
                    array_map('trim', explode(',', $disabledPax))
                ));
            }
        } else {
            // null or other - convert to empty array
            $disabledPax = [];
        }
        
        $validated['disabled_pax_combinations'] = $disabledPax;

        // Convert empty strings to null for max_pax fields
        $validated['max_adults'] = !empty($validated['max_adults']) ? (int)$validated['max_adults'] : null;
        $validated['max_children'] = !empty($validated['max_children']) ? (int)$validated['max_children'] : null;
        $validated['max_infants'] = !empty($validated['max_infants']) ? (int)$validated['max_infants'] : null;

        // Handle image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('room-types', 'public');
            }
        }
        $validated['images'] = $images;

        $roomType = RoomType::create($validated);
        $this->ensurePriceConfigService->syncPriceConfigurationsBySeasonsAndDateTypes($validated['package_id'], [], []);

        // Clean price configurations for disabled combinations
        try {
            $stats = $this->priceConfigurationService->cleanPriceConfigurationsByMaxPax($roomType);
        } catch (\Exception $e) {
            Log::error('Failed to clean price configurations after room type creation', [
                'room_type_id' => $roomType->id,
                'error' => $e->getMessage()
            ]);
            // Don't fail the creation if cleaning fails, just log it
        }

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
        $newRoomPax = (int) $request->max_occupancy;
        $prev_disabled_pax_combinations = $roomType->disabled_pax_combinations;

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'bed_desc' => 'nullable|string',
                'max_occupancy' => 'required|integer|min:1',
                'max_adults' => 'nullable|integer|min:1',
                'max_children' => 'nullable|integer|min:1',
                'max_infants' => 'nullable|integer|min:1',
                'package_id' => 'required|exists:packages,id',
                'images.*' => 'nullable|image|max:2048',
                'delete_images' => 'nullable|array',
                'delete_images.*' => 'string',
                'disabled_pax_combinations' => 'nullable',
                'default_show_surcharge' => 'nullable|boolean',
            ]);

            // Convert string '1'/'0' to boolean for default_show_surcharge
            $validated['default_show_surcharge'] = filter_var($request->input('default_show_surcharge'), FILTER_VALIDATE_BOOLEAN);

            $disabledPax = $validated['disabled_pax_combinations'] ?? null;
            
            // Handle different input formats
            if (is_array($disabledPax)) {
                // Already an array (from FormData array notation)
                $disabledPax = array_values(array_filter($disabledPax, fn($item) => !empty(trim($item))));
            } elseif (is_string($disabledPax)) {
                // Comma-separated string
                if (trim($disabledPax) === '') {
                    $disabledPax = [];
                } else {
                    $disabledPax = array_values(array_filter(
                        array_map('trim', explode(',', $disabledPax))
                    ));
                }
            } else {
                // null or other - convert to empty array
                $disabledPax = [];
            }
            
            $validated['disabled_pax_combinations'] = $disabledPax;

            // Handle image deletions
            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imagePath) {
                    if (in_array($imagePath, $roomType->images ?? []) && !in_array($imagePath, AppConstants::TESTING_IMAGES)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
                $currentImages = array_diff($roomType->images ?? [], $request->delete_images);
            } else {
                $currentImages = $roomType->images ?? [];
            }

            // Log::info('Current images', ['images' => $currentImages]);

            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $currentImages[] = $image->store('room-types', 'public');
                }
            }

            $validated['images'] = array_values($currentImages); // Reindex array

            // Convert empty strings to null for max_pax fields
            $validated['max_adults'] = $validated['max_adults'] ?? null;
            $validated['max_children'] = $validated['max_children'] ?? null;
            $validated['max_infants'] = $validated['max_infants'] ?? null;

            // Check if max_pax fields are being updated
            // $maxPaxChanged = false;
            // if (isset($validated['max_adults']) && $roomType->max_adults != $validated['max_adults']) {
            //     $maxPaxChanged = true;
            // }
            // if (isset($validated['max_children']) && $roomType->max_children != $validated['max_children']) {
            //     $maxPaxChanged = true;
            // }
            // if (isset($validated['max_infants']) && $roomType->max_infants != $validated['max_infants']) {
            //     $maxPaxChanged = true;
            // }

            $roomType->update($validated);

            Log::info('prev and current disabled_pax_combinations are different: ' . ($prev_disabled_pax_combinations !== $validated['disabled_pax_combinations'] ? 'true' : 'false'));

            // Update price configurations when max_occupancy changes
            $this->priceConfigurationService->updateConfigsToPaxAndFill($roomType->id, $newRoomPax);

            // Clean price configurations if max_pax limits were updated or disabled_pax_combinations changed
            // if ($maxPaxChanged || ($prev_disabled_pax_combinations !== $validated['disabled_pax_combinations'])) {
            try {
                $stats = $this->priceConfigurationService->cleanPriceConfigurationsByMaxPax($roomType);
                Log::info('Cleaned price configurations after max_pax update', [
                    'room_type_id' => $roomType->id,
                    'stats' => $stats
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to clean price configurations after max_pax update', [
                    'room_type_id' => $roomType->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the update if cleaning fails, just log it
            }
            // }

            // If the request has a return_to_package parameter, redirect back to the package page
            if ($request->has('return_to_package')) {
                return redirect()->route('packages.show', $validated['package_id'])
                    ->with('success', 'Room type updated successfully.');
            }

            return redirect()->route('room-types.index')
                ->with('success', 'Room type updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function duplicate(Request $request, RoomType $roomType)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'bed_desc' => 'nullable|string',
                'max_occupancy' => 'required|integer|min:1',
                'max_adults' => 'nullable|integer|min:1',
                'max_children' => 'nullable|integer|min:1',
                'max_infants' => 'nullable|integer|min:1',
                'package_id' => 'required|exists:packages,id',
                'images.*' => 'nullable|image|max:2048',
                'existing_images' => 'nullable|array',
                'existing_images.*' => 'string'
            ]);

            // Convert empty strings to null for max_pax fields
            $validated['max_adults'] = $validated['max_adults'] ?? null;
            $validated['max_children'] = $validated['max_children'] ?? null;
            $validated['max_infants'] = $validated['max_infants'] ?? null;
            
            // Start with existing images that should be copied
            $duplicatedImages = [];
            if ($request->has('existing_images') && is_array($request->existing_images)) {
                foreach ($request->existing_images as $imagePath) {
                    // Verify the image exists in the original room type
                    if (in_array($imagePath, $roomType->images ?? [])) {
                        if (Storage::disk('public')->exists($imagePath)) {
                            // Generate new path for the copied image
                            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
                            $newPath = 'room-types/' . Str::uuid() . '.' . $extension;
                            // Copy the image to the new path
                            Storage::disk('public')->copy($imagePath, $newPath);
                            $duplicatedImages[] = $newPath;
                        }
                    }
                }
            }
            
            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $duplicatedImages[] = $image->store('room-types', 'public');
                }
            }
            
            // Create new room type with form data
            $newRoomType = RoomType::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'bed_desc' => $validated['bed_desc'] ?? null,
                'max_occupancy' => $validated['max_occupancy'],
                'max_adults' => $validated['max_adults'] ?? null,
                'max_children' => $validated['max_children'] ?? null,
                'max_infants' => $validated['max_infants'] ?? null,
                'package_id' => $validated['package_id'],
                'images' => $duplicatedImages,
            ]);
            
            // Sync price configurations
            $this->ensurePriceConfigService->syncPriceConfigurationsBySeasonsAndDateTypes($validated['package_id'], [], []);
            
            // If the request has a return_to_package parameter, return JSON response
            if ($request->has('return_to_package')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Room type duplicated successfully.',
                    'room_type' => $newRoomType
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Room type duplicated successfully.',
                'room_type' => $newRoomType
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed on duplicate:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Duplicate room type failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate room type: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function generateUniqueCopyName($baseName, $existingNames)
    {
        // Check if "baseName (Copy)" already exists
        $copyName = $baseName . ' (Copy)';
        if (!in_array($copyName, $existingNames)) {
            return $copyName;
        }
        
        // Find the highest copy number
        $maxCopy = 0;
        foreach ($existingNames as $name) {
            if (preg_match('/\s\(Copy\s(\d+)\)$/', $name, $matches)) {
                $copyNum = (int)$matches[1];
                $maxCopy = max($maxCopy, $copyNum);
            } elseif (preg_match('/\s\(Copy\)$/', $name)) {
                $maxCopy = max($maxCopy, 1);
            }
        }
        
        return $baseName . ' (Copy ' . ($maxCopy + 1) . ')';
    }

    public function destroy(RoomType $roomType)
    {
        // Delete all associated images
        if ($roomType->images) {
            foreach ($roomType->images as $imagePath) {
                if (in_array($imagePath, AppConstants::TESTING_IMAGES)) {
                    continue;
                }
                Storage::disk('public')->delete($imagePath);
            }
        }

        $packageId = $roomType->package_id;
        $roomType->delete();

        PackageConfiguration::where('room_type_id', $roomType->id)->delete();

        // If the request has a return_to_package parameter, redirect back to the package page
        if (request()->has('return_to_package')) {
            return redirect()->route('packages.show', $packageId)
                ->with('success', 'Room type deleted successfully.');
        }

        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }
}
