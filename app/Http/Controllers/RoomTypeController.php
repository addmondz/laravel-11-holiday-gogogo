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

        $roomType = RoomType::create($validated);
        $this->ensurePriceConfigService->syncPriceConfigurationsBySeasonsAndDateTypes($validated['package_id'], [], []);

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
        $currentRoomPax = $roomType->max_occupancy;
        $newRoomPax = (int) $request->max_occupancy;

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'bed_desc' => 'nullable|string',
                'max_occupancy' => 'required|integer|min:1',
                'package_id' => 'required|exists:packages,id',
                'images.*' => 'nullable|image|max:2048',
                'delete_images' => 'nullable|array',
                'delete_images.*' => 'string'
            ]);

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

            $roomType->update($validated);

            // if ($currentRoomPax != $newRoomPax) {
            //     $this->priceConfigurationService->updateConfigsToPaxAndFill($roomType->id, $newRoomPax);
            // }
            $this->priceConfigurationService->updateConfigsToPaxAndFill($roomType->id, $newRoomPax);

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
                'package_id' => 'required|exists:packages,id',
                'images.*' => 'nullable|image|max:2048',
                'existing_images' => 'nullable|array',
                'existing_images.*' => 'string'
            ]);
            
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
