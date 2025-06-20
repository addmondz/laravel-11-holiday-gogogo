<?php

namespace App\Http\Controllers;

use App\Constants\AppConstants;
use App\Models\Package;
use App\Models\PackageConfiguration;
use App\Models\DateBlocker;
use App\Models\SeasonType;
use App\Models\DateType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
            // Removed invalid eager loads
            $package = Package::with([
                'roomTypes',
                'dateBlockers',
            ])->where('name', $request->package_name)->first();

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            // Get date blockers for the next 12 months
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addMonths(12);

            $dateBlockers = DateBlocker::where('package_id', $package->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->get()
                ->map(function ($blocker) {
                    return [
                        'start_date' => $blocker->start_date->format('Y-m-d'),
                        'end_date' => $blocker->end_date->format('Y-m-d'),
                        'room_type_id' => $blocker->room_type_id,
                        'room_type_name' => $blocker->roomType?->name
                    ];
                });

            // Format room types with availability info
            $roomTypes = $package->roomTypes->map(function ($roomType) use ($package) {
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'description' => $roomType->description,
                    'max_occupancy' => $roomType->max_occupancy,
                    'images' => $roomType->images ?? [],
                    'available_dates' => $this->getAvailableDates($package->id, $roomType->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'package' => [
                        'id' => $package->id,
                        'name' => $package->name,
                        'description' => $package->description,
                        'location' => $package->location,
                        'min_days' => $package->package_min_days,
                        'max_days' => $package->package_max_days,
                        'child_max_age_desc' => $package->child_max_age_desc,
                        'infant_max_age_desc' => $package->infant_max_age_desc,
                        'display_prices' => [
                            'adult' => $package->display_price_adult,
                            'child' => $package->display_price_child,
                            'infant' => $package->display_price_infant,
                        ]
                    ],
                    'room_types' => $roomTypes,
                    'date_blockers' => $dateBlockers,
                    'season_types' => $package->uniqueSeasonTypes->pluck('name'),
                    'date_types' => $package->uniqueDateTypes->pluck('name'),
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
            'package_name' => 'required|string|max:255',
            'travel_date' => 'required|date|after:today',
            'adults' => 'required|integer|min:1|max:10',
            'children' => 'integer|min:0|max:10',
            'infants' => 'integer|min:0|max:10',
            'rooms' => 'required|array|min:1',
            'rooms.*.room_type_id' => 'required|integer|exists:room_types,id',
            'rooms.*.adults' => 'required|integer|min:1',
            'rooms.*.children' => 'integer|min:0',
            'rooms.*.infants' => 'integer|min:0',
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

            if (!$package) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package not found'
                ], 404);
            }

            // Check if travel date is blocked
            $travelDate = Carbon::parse($request->travel_date);
            $isDateBlocked = DateBlocker::where('package_id', $package->id)
                ->where('start_date', '<=', $travelDate)
                ->where('end_date', '>=', $travelDate)
                ->exists();

            if ($isDateBlocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected date is not available',
                    'suggested_dates' => $this->getSuggestedDates($package->id, $travelDate)
                ], 400);
            }

            // Calculate pricing for each room
            $roomQuotations = [];
            $grandTotal = 0;

            foreach ($request->rooms as $room) {
                $roomQuotation = $this->calculateRoomPrice(
                    $package->id,
                    $room['room_type_id'],
                    $travelDate,
                    $room['adults'],
                    $room['children'] ?? 0,
                    $room['infants'] ?? 0
                );

                $roomQuotations[] = $roomQuotation;
                $grandTotal += $roomQuotation['total_price'];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'package_name' => $package->name,
                    'travel_date' => $travelDate->format('Y-m-d'),
                    'total_pax' => [
                        'adults' => $request->adults,
                        'children' => $request->children ?? 0,
                        'infants' => $request->infants ?? 0,
                    ],
                    'rooms' => $roomQuotations,
                    'grand_total' => round($grandTotal, 2),
                    'currency' => 'MYR'
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
     * Get available dates for a room type (next 12 months excluding blockers)
     */
    private function getAvailableDates(int $packageId, int $roomTypeId): array
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(12);

        $blockedDates = DateBlocker::where('package_id', $packageId)
            ->where(function ($query) use ($roomTypeId) {
                $query->where('room_type_id', $roomTypeId)
                    ->orWhereNull('room_type_id');
            })
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->get();

        $availableDates = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $isBlocked = $blockedDates->contains(function ($blocker) use ($currentDate) {
                return $currentDate->between($blocker->start_date, $blocker->end_date);
            });

            if (!$isBlocked) {
                $availableDates[] = $currentDate->format('Y-m-d');
            }

            $currentDate->addDay();
        }

        return $availableDates;
    }

    /**
     * Calculate room price based on configuration
     */
    private function calculateRoomPrice(int $packageId, int $roomTypeId, Carbon $travelDate, int $adults, int $children, int $infants): array
    {
        // Determine season and date type based on travel date
        $seasonType = $this->getSeasonTypeForDate($packageId, $travelDate);
        $dateType = $this->getDateTypeForDate($packageId, $travelDate);

        // Get price configuration
        $configuration = PackageConfiguration::where('package_id', $packageId)
            ->where('room_type_id', $roomTypeId)
            ->where('season_type_id', $seasonType->id)
            ->where('date_type_id', $dateType->id)
            ->first();

        if (!$configuration || !$configuration->configuration_prices) {
            return [
                'room_type_id' => $roomTypeId,
                'base_price' => 0,
                'surcharge' => 0,
                'total_price' => 0,
                'error' => 'No pricing configuration found'
            ];
        }

        $prices = json_decode($configuration->configuration_prices, true);
        $keyPrefix = "{$adults}_a_{$children}_c_{$infants}_i";

        $basePrice = $prices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_a"] ?? 0;
        $childBasePrice = $prices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_c"] ?? 0;
        $infantBasePrice = $prices[AppConstants::CONFIGURATION_PRICE_TYPES_BASE_CHARGE]["{$keyPrefix}_i"] ?? 0;

        $surcharge = $prices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_a"] ?? 0;
        $childSurcharge = $prices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_c"] ?? 0;
        $infantSurcharge = $prices[AppConstants::CONFIGURATION_PRICE_TYPES_SUR_CHARGE]["{$keyPrefix}_i"] ?? 0;

        $totalBasePrice = ($basePrice * $adults) + ($childBasePrice * $children) + ($infantBasePrice * $infants);
        $totalSurcharge = ($surcharge * $adults) + ($childSurcharge * $children) + ($infantSurcharge * $infants);
        $totalPrice = $totalBasePrice + $totalSurcharge;

        return [
            'room_type_id' => $roomTypeId,
            'room_type_name' => $configuration->roomType->name,
            'season_type' => $seasonType->name,
            'date_type' => $dateType->name,
            'pricing_breakdown' => [
                'base_price' => [
                    'adults' => $basePrice * $adults,
                    'children' => $childBasePrice * $children,
                    'infants' => $infantBasePrice * $infants,
                    'total' => $totalBasePrice
                ],
                'surcharge' => [
                    'adults' => $surcharge * $adults,
                    'children' => $childSurcharge * $children,
                    'infants' => $infantSurcharge * $infants,
                    'total' => $totalSurcharge
                ]
            ],
            'total_price' => round($totalPrice, 2)
        ];
    }

    /**
     * Get season type for a specific date
     */
    private function getSeasonTypeForDate(int $packageId, Carbon $date): SeasonType
    {
        // This is a simplified implementation - you might need to adjust based on your season logic
        $season = SeasonType::whereHas('seasons', function ($query) use ($packageId, $date) {
            $query->where('package_id', $packageId)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date);
        })->first();

        return $season ?? SeasonType::first(); // Fallback to first season type
    }

    /**
     * Get date type for a specific date
     */
    private function getDateTypeForDate(int $packageId, Carbon $date): DateType
    {
        // This is a simplified implementation - you might need to adjust based on your date type logic
        $dateType = DateType::whereHas('dateTypeRanges', function ($query) use ($packageId, $date) {
            $query->where('package_id', $packageId)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date);
        })->first();

        return $dateType ?? DateType::first(); // Fallback to first date type
    }

    /**
     * Get suggested alternative dates when selected date is blocked
     */
    private function getSuggestedDates(int $packageId, Carbon $blockedDate): array
    {
        $suggestions = [];

        // Suggest dates before and after the blocked date
        for ($i = 1; $i <= 7; $i++) {
            $beforeDate = $blockedDate->copy()->subDays($i);
            $afterDate = $blockedDate->copy()->addDays($i);

            if ($beforeDate->isFuture()) {
                $isBeforeBlocked = DateBlocker::where('package_id', $packageId)
                    ->where('start_date', '<=', $beforeDate)
                    ->where('end_date', '>=', $beforeDate)
                    ->doesntExist();

                if ($isBeforeBlocked) {
                    $suggestions[] = $beforeDate->format('Y-m-d');
                }
            }

            $isAfterBlocked = DateBlocker::where('package_id', $packageId)
                ->where('start_date', '<=', $afterDate)
                ->where('end_date', '>=', $afterDate)
                ->doesntExist();

            if ($isAfterBlocked) {
                $suggestions[] = $afterDate->format('Y-m-d');
            }
        }

        return array_slice(array_unique($suggestions), 0, 5); // Return max 5 suggestions
    }
}
