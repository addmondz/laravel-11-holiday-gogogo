<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Http\Controllers\Api\TravelCalculatorController;
use App\Models\Package;
use App\Models\PackageConfiguration;
use App\Models\DateBlocker;
use App\Models\SeasonType;
use App\Models\DateType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Validator;
use App\Models\DateTypeRange;
use App\Models\Season;
use App\Models\RoomType;

class BotApiController extends Controller
{
    /**
     * Fetch available room types and package information
     */
    public function fetchRoomTypesByPackageName(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'package_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {

            $package = Package::where('name', $request->package_name)->first();

            // Removed invalid eager loads
            $package = Package::with([
                'roomTypes',
            ])->where('name', $request->package_name)->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'uid' => $package->uuid,
                        'booking_page_url' => route('quotation.with-hash', $package->uuid),
                        'images' => collect($package->images)->map(function ($image) {
                            return url('/images') . '/' . $image;
                        })->values(),
                        'package_id' => $package->package_id,
                        'name' => $package->name,
                        'description' => $package->description,
                        'location' => $package->location,
                        'package_nights' => $package->package_min_days,
                        'package_start_date' => $package->package_start_date,
                        'package_end_date' => $package->package_end_date,
                        'child_max_age_desc' => $package->child_max_age_desc ?? '',
                        'infant_max_age_desc' => $package->infant_max_age_desc ?? '',
                        'package_display_price' => $package->display_price_adult,
                        'room_types' => $package->loadRoomTypes->map(function ($roomType) {
                            return [
                                'id' => $roomType->id,
                                'name' => $roomType->name,
                                'description' => $roomType->description,
                                'max_occupancy' => $roomType->max_occupancy,
                                'images' => collect($roomType->images)->map(function ($image) {
                                    return url('/images') . '/' . $image;
                                })->values(),
                                'date_blockers' => $roomType->dateBlockers->map(function ($dateBlocker) {
                                    return [
                                        'start_date' => $dateBlocker->start_date->format('Y-m-d'),
                                        'end_date' => $dateBlocker->end_date->format('Y-m-d'),
                                    ];
                                })
                            ];
                        }),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch package quotation with pricing
     */
    public function fetchQuotation(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer|exists:packages,id',
            'travel_date_start' => 'required|date|after:today',
            'rooms' => 'required|array|min:1',
            'rooms.*.room_type_id' => 'required|integer|exists:room_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $rooms = collect($request->rooms)->map(function ($room) {
            return [
                'room_type' => $room['room_type_id'],
                'adults' => $room['adults'],
                'children' => $room['children'],
                'infants' => $room['infants'],
            ];
        })->toArray();

        $package = Package::find($request->package_id);
        $endDate = Carbon::parse($request->travel_date_start)->addDays($package->package_min_days);

        return app(TravelCalculatorController::class)->calculatePriceByParams($request->package_id, $rooms, $request->travel_date_start, $endDate, true);
    }
}
