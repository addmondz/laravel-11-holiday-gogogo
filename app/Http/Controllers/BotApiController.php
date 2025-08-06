<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Http\Controllers\Api\TravelCalculatorController;
use App\Models\ActivityLog;
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
        $validator = Validator::make($request->all(), [
            'package_name' => 'required|string|max:255',
            'travel_month' => 'required|string|max:255',
            'travel_year' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->returnBotApiResponse(false, 'Validation failed', $validator->errors(), 400, $request, 'fetchRoomTypesByPackageName');
        }

        try {
            $package = Package::with(['roomTypes.dateBlockers', 'seasons'])
                ->where('name', $request->package_name)
                ->first();

            if (!$package) {
                return $this->returnBotApiResponse(false, 'Package not found', null, 404, $request, 'fetchRoomTypesByPackageName');
            }

            $travelMonth = (int) $request->travel_month;
            $travelYear = (int) $request->travel_year;
            $packageMinDays = $package->package_min_days;

            $startOfMonth = Carbon::create($travelYear, $travelMonth, 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $today = Carbon::today();

            $spiResponse = [
                'success' => true,
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'uid' => $package->uuid,
                        'booking_page_url' => route('quotation.with-hash', $package->uuid),
                        'images' => collect($package->images)->map(fn($image) => url('/images') . '/' . $image)->values(),
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
                        'room_types' => $package->roomTypes
                            ->groupBy('id')
                            ->map(function ($groupedRoomTypes) use ($startOfMonth, $endOfMonth, $today, $packageMinDays, $package) {
                                $roomType = $groupedRoomTypes->first();

                                // Check if current month range is blocked
                                $travelRangeStart = $startOfMonth->copy();
                                $travelRangeEnd = $travelRangeStart->copy()->addDays($packageMinDays - 1);

                                foreach ($roomType->dateBlockers as $blocker) {
                                    if (
                                        $travelRangeStart->between($blocker->start_date, $blocker->end_date) ||
                                        $travelRangeEnd->between($blocker->start_date, $blocker->end_date) ||
                                        ($blocker->start_date->lt($travelRangeStart) && $blocker->end_date->gt($travelRangeEnd))
                                    ) {
                                        return null;
                                    }
                                }

                                // Calculate available_booking_start_dates in this month & after today
                                $bookingStartDates = [];
                                foreach ($package->seasons as $season) {
                                    $cursor = Carbon::parse($season->start_date)->copy();
                                    $seasonEnd = Carbon::parse($season->end_date);

                                    while ($cursor->lte($seasonEnd)) {
                                        $cursorEnd = $cursor->copy()->addDays($packageMinDays - 1);

                                        if (
                                            $cursor->between($startOfMonth, $endOfMonth) &&
                                            $cursor->gt($today) &&
                                            $cursorEnd->lte($seasonEnd)
                                        ) {
                                            $overlapsBlocker = $roomType->dateBlockers->contains(function ($blocker) use ($cursor, $cursorEnd) {
                                                return $cursor->between($blocker->start_date, $blocker->end_date) ||
                                                    $cursorEnd->between($blocker->start_date, $blocker->end_date) ||
                                                    ($blocker->start_date->lt($cursor) && $blocker->end_date->gt($cursorEnd));
                                            });

                                            if (!$overlapsBlocker) {
                                                $bookingStartDates[] = $cursor->format('Y-m-d');
                                            }
                                        }

                                        $cursor->addDay();
                                    }
                                }

                                return [
                                    'id' => $roomType->id,
                                    'name' => $roomType->name,
                                    'description' => $roomType->description,
                                    'max_occupancy' => $roomType->max_occupancy,
                                    'images' => collect($roomType->images)->map(fn($image) => url('/images') . '/' . $image)->values(),
                                    'date_blockers' => $roomType->dateBlockers->map(fn($blocker) => [
                                        'start_date' => $blocker->start_date->format('Y-m-d'),
                                        'end_date' => $blocker->end_date->format('Y-m-d'),
                                    ]),
                                    'available_booking_start_dates' => $bookingStartDates,
                                ];
                            })
                            ->filter()
                            ->values(),
                    ]
                ]
            ];

            return $this->returnBotApiResponse(true, 'Success', $spiResponse, 200, $request, 'fetchRoomTypesByPackageName');
        } catch (\Exception $e) {
            return $this->returnBotApiResponse(false, 'Server error', $e->getMessage(), 500, $request, 'fetchRoomTypesByPackageName');
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
                'adults' => $room['adults'] ?? 0,
                'children' => $room['children'] ?? 0,
                'infants' => $room['infants'] ?? 0,
            ];
        })->toArray();

        try {
            $package = Package::find($request->package_id);
            $endDate = Carbon::parse($request->travel_date_start)->addDays($package->package_min_days);

            $spiResponse = app(TravelCalculatorController::class)->calculatePriceByParams(
                $request->package_id,
                $rooms,
                $request->travel_date_start,
                $endDate,
                true
            );

            return $this->returnBotApiResponse(true, 'Success', $spiResponse, 200, $request, 'fetchQuotation');
        } catch (\Exception $e) {
            return $this->returnBotApiResponse(false, 'Calculation error', $e->getMessage(), 500, $request, 'fetchQuotation');
        }
    }

    public function returnBotApiResponse($success, $message, $data = null, $statusCode = 200, $request, $apiName)
    {
        ActivityLog::create([
            'user_email' => $request->user()->email ?? 'guest',
            'action' => 'api response',
            'subject' => $apiName,
            'description' => $message,
            'request_body' => json_encode($request->all(), JSON_UNESCAPED_UNICODE),
            'api_response' => is_array($data) || is_object($data)
                ? json_encode($data, JSON_UNESCAPED_UNICODE)
                : $data,
        ]);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
