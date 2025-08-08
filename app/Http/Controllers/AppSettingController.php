<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class AppSettingController extends Controller
{
    public function index(): Response
    {
        $sstConfiguration = AppSetting::getSstConfiguration();
        
        return Inertia::render('Settings/Index', [
            'sstConfiguration' => $sstConfiguration
        ]);
    }

    /**
     * Get SST configuration
     */
    public function getSstConfiguration(): JsonResponse
    {
        $sstConfiguration = AppSetting::getSstConfiguration();
        
        return response()->json([
            'success' => true,
            'data' => $sstConfiguration
        ]);
    }

    /**
     * Update SST configuration
     */
    public function updateSstConfiguration(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|boolean',
            'sst_percent' => 'required|numeric|min:0|max:100'
        ]);

        try {
            $sstConfiguration = AppSetting::updateSstConfiguration([
                'status' => $request->status,
                'sst_percent' => $request->sst_percent
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SST configuration updated successfully',
                'data' => $sstConfiguration->value
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update SST configuration: ' . $e->getMessage()
            ], 500);
        }
    }
}
