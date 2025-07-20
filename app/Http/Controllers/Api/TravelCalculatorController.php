<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DateBlocker;
use App\Models\DateType;
use App\Models\DateTypeRange;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\PackageAddOn;
use App\Models\PackageConfiguration;
use App\Models\RoomType;
use App\Models\Season;
use App\Models\SeasonType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Validator;

class TravelCalculatorController extends Controller
{
    public $enabledDefaultSeasonAndDateType;
    public function __construct()
    {
        $this->enabledDefaultSeasonAndDateType = env('ENABLED_DEFAULT_SEASON_AND_DATE_TYPE', false);
    }

    // public function calculate(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'package_id' => 'required|exists:packages,id',
    //             'rooms' => 'required|array|min:1',
    //             'rooms.*.room_type' => 'required|exists:room_types,id',
    //             'rooms.*.adults' => 'required|integer|min:1|max:4',
    //             'rooms.*.children' => 'required|integer|min:0|max:4',
    //             'start_date' => 'required|date',
    //             'end_date' => 'required|date|after:start_date',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation failed',
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $package = Package::with(['roomTypes', 'seasons', 'dateTypeRanges'])->findOrFail($request->package_id);
    //         $startDate = Carbon::parse($request->start_date);
    //         $endDate = Carbon::parse($request->end_date);
    //         $duration = $startDate->diffInDays($endDate);

    //         // Validate duration against package max days
    //         if ($duration > $package->package_max_days) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Duration cannot exceed {$package->package_max_days} days"
    //             ], 422);
    //         }

    //         // Validate package date range
    //         $packageStartDate = Carbon::parse($package->package_start_date);
    //         $packageEndDate = Carbon::parse($package->package_end_date);
    //         if ($startDate < $packageStartDate || $endDate > $packageEndDate) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Booking dates must be within package date range ({$packageStartDate->format('d M Y')} to {$packageEndDate->format('d M Y')})"
    //             ], 422);
    //         }

    //         // Determine season and date type
    //         $season = $package->seasons->first(function ($season) use ($startDate) {
    //             return $startDate->between($season->season_start_date, $season->season_end_date);
    //         });

    //         $dateTypeRange = $package->dateTypeRanges->first(function ($range) use ($startDate) {
    //             return $startDate->between($range->date_type_start_date, $range->date_type_end_date);
    //         });

    //         $isWeekend = $startDate->isWeekend();

    //         // Calculate base charges for each room
    //         $totalBaseCharge = 0;
    //         $roomBreakdowns = [];
    //         $nightlyBreakdown = [];

    //         foreach ($request->rooms as $room) {
    //             $roomType = $package->roomTypes->firstWhere('id', $room['room_type']);
    //             if (!$roomType) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => "Invalid room type selected"
    //                 ], 422);
    //             }

    //             // Validate total guests per room
    //             if ($room['adults'] + $room['children'] > 4) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => "Maximum 4 guests per room"
    //                 ], 422);
    //             }

    //             // Get price configuration for this room type and guest combination
    //             $priceConfig = $roomType->priceConfigurations()
    //                 ->where('adults', $room['adults'])
    //                 ->where('children', $room['children'])
    //                 ->first();

    //             if (!$priceConfig) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => "No price configuration found for {$roomType->name} with {$room['adults']} adults and {$room['children']} children"
    //                 ], 422);
    //             }

    //             $baseCharge = $priceConfig->base_charge;
    //             $surcharge = 0;

    //             // Apply season surcharge if applicable
    //             if ($season) {
    //                 $surcharge += $baseCharge * ($season->season_surcharge_percentage / 100);
    //             }

    //             // Apply date type surcharge if applicable
    //             if ($dateTypeRange) {
    //                 $surcharge += $baseCharge * ($dateTypeRange->date_type_surcharge_percentage / 100);
    //             }

    //             // Apply weekend surcharge if applicable
    //             if ($isWeekend) {
    //                 $surcharge += $baseCharge * ($package->weekend_surcharge_percentage / 100);
    //             }

    //             $roomTotal = $baseCharge + $surcharge;
    //             $totalBaseCharge += $roomTotal;

    //             // Add to room breakdowns
    //             $roomBreakdowns[] = [
    //                 'room_type' => $roomType->name,
    //                 'adults' => $room['adults'],
    //                 'children' => $room['children'],
    //                 'base_charge' => $baseCharge,
    //                 'surcharge' => $surcharge,
    //                 'total' => $roomTotal
    //             ];

    //             // Add to nightly breakdown
    //             $nightlyBreakdown[] = [
    //                 'room_type' => $roomType->name,
    //                 'adults' => $room['adults'],
    //                 'children' => $room['children'],
    //                 'base_charge' => $baseCharge,
    //                 'surcharge' => $surcharge,
    //                 'total' => $roomTotal
    //             ];
    //         }

    //         // Calculate add-ons if any
    //         $addOns = [];
    //         $addOnTotal = 0;
    //         if ($request->has('add_on_ids')) {
    //             $addOns = PackageAddOn::whereIn('id', $request->add_on_ids)->get();
    //             $addOnTotal = $addOns->sum('price') * $duration;
    //         }

    //         $grandTotal = $totalBaseCharge + $addOnTotal;

    //         return response()->json([
    //             'success' => true,
    //             'total' => $grandTotal,
    //             'breakdown' => [
    //                 'rooms' => $roomBreakdowns,
    //                 'nightly_breakdown' => $nightlyBreakdown,
    //                 'add_ons' => $addOns->map(function ($addOn) use ($duration) {
    //                     return [
    //                         'name' => $addOn->name,
    //                         'price' => $addOn->price,
    //                         'total' => $addOn->price * $duration
    //                     ];
    //                 }),
    //                 'add_on_total' => $addOnTotal
    //             ],
    //             'season_type' => $season ? $season->season_name : null,
    //             'date_type' => $dateTypeRange ? $dateTypeRange->date_type_name : null,
    //             'is_weekend' => $isWeekend,
    //             'duration' => $duration
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getResources(Request $request)
    {
        $packages = Package::all();
        $addOns = PackageAddOn::all();

        return response()->json([
            'packages' => $packages,
            'addOns' => $addOns,
        ]);
    }

    public function getRoomTypes(Request $request, $packageId)
    {
        $roomTypes = PackageConfiguration::where('package_id', $packageId)
            ->distinct()
            ->pluck('room_type');


        return response()->json($roomTypes);
    }

    public function fetchPackageByUuid(Request $request)
    {
        $package = Package::where('uuid', $request->uuid)->firstOrFail();

        if (!$request->booking_uuid) {
            $package->loadRoomTypes = $package->loadRoomTypes->filter(function ($roomType) {
                return $roomType->configurations?->count() > 0;
            });
        }

        return response()->json([
            'success' => true,
            'package' => $package,
            'room_types' => $package->loadRoomTypes,
        ]);
    }

    public function packageCalculatePrice(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'rooms' => 'required|array|min:1',
            'rooms.*.room_type' => 'required|exists:room_types,id',
            'rooms.*.adults' => 'required|integer|min:1|max:4',
            'rooms.*.children' => 'required|integer|min:0|max:4',
            'rooms.*.infants' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        return $this->calculatePriceByParams($validated['package_id'], $validated['rooms'], $validated['start_date'], $validated['end_date']);
    }

    public function getSuggestedDates(int $packageId, Carbon $blockedDate): array
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

        return array_slice($suggestions, 0, 5); // Return max 5 suggestions
    }

    public function calculatePriceByParams($packageId, $rooms, $startDate, $endDate, $isFromBot = false)
    {
        $package = Package::find($packageId);
        try {
            $startDate = Carbon::parse($startDate);

            // Check date blockers for each room
            foreach ($rooms as $room) {
                $dateBlockers = DateBlocker::where('package_id', $packageId)
                    ->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate)
                    ->where('room_type_id', $room['room_type'])
                    ->get();

                $blockedDates = [];
                foreach ($dateBlockers as $blocker) {
                    $period = CarbonPeriod::create($blocker->start_date, $blocker->end_date);
                    foreach ($period as $date) {
                        $blockedDates[] = $date->toDateString();
                    }
                }

                $blockedDates = array_unique($blockedDates);
                if (!empty($blockedDates)) {
                    throw new \Exception('Sorry, the selected dates and room type combination are not available. Please try to select another room type or contact us for more information.');
                }
            }

            $allNights = [];
            $roomBreakdowns = [];

            // Calculate prices for each room
            foreach ($rooms as $room) {
                $roomTypeId = $room['room_type'];
                $adults = $room['adults'];
                $children = $room['children'];
                $infants = $room['infants'];
                $roomNights = [];
                $roomType = RoomType::find($roomTypeId);
                $totalGuests = $adults + $children + $infants;

                if ($totalGuests > $roomType->max_occupancy) {
                    throw new \Exception('The selected room type id `' . $roomTypeId . '` and name `' . $roomType->name . '` has a maximum capacity of `' . $roomType->max_occupancy . '` guests. Please select a different room type.');
                }

                for ($date = $startDate->copy(); $date->lt($endDate); $date->addDay()) {
                    $dateTypeRange = DateTypeRange::where('start_date', '<=', $date)
                        ->where('end_date', '>=', $date)
                        ->where('package_id', $packageId)
                        ->first();

                    if ($dateTypeRange) {
                        $dateType = $dateTypeRange->dateType;
                    } else {

                        $fallbackType = $date->isWeekend() ? 'weekend' : 'weekday';

                        if (!empty($package->weekend_days)) {
                            $weekendDays = is_array($package->weekend_days) ? $package->weekend_days : json_decode($package->weekend_days, true);
                            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

                            if (in_array($dayOfWeek, $weekendDays)) {
                                $fallbackType = 'weekend';
                            } else {
                                $fallbackType = 'weekday';
                            }
                        }

                        $dateType = DateType::where('name', 'LIKE', "%$fallbackType%")->first();

                        if (!$dateType) {
                            Log::error("Default Date type not found for {$date->format('Y-m-d')} for package {$packageId}");
                            throw new \Exception("Date type not found for {$date->format('Y-m-d')}");
                        }
                    }

                    $season = Season::where('start_date', '<=', $date)
                        ->where('end_date', '>=', $date)
                        ->where('package_id', $packageId)
                        ->first();

                    if ($season) {
                        $seasonType = $season->type;
                    } else if ($this->enabledDefaultSeasonAndDateType) {
                        $seasonType = SeasonType::where('name', 'Default')->first();

                        if (!$seasonType) {
                            Log::error('Default Season type not found for ' . $date->format('Y-m-d') . ' for package ' . $packageId);
                            throw new \Exception("season type not found for " . $date->format('Y-m-d') . " for package " . $packageId);
                        }
                    } else {
                        // throw new \Exception('An expected error occurred. Please contact admin. Season type not found for ' . $date->format('Y-m-d'));
                        // Log::error('Season type not found for ' . $date->format('Y-m-d') . ' for package ' . $packageId);
                        throw new \Exception("The selected dates and room type combination are not available. Please contact us for more information.");
                    }

                    $packageConfig = PackageConfiguration::where([
                        'package_id' => $packageId,
                        'season_type_id' => $seasonType->id,
                        'date_type_id' => $dateType->id,
                        'room_type_id' => $roomTypeId
                    ])->first();

                    if (!$packageConfig) {
                        return response()->json([
                            'success' => false,
                            'message' => "Package configuration not found for {$date->format('Y-m-d')}",
                            'breakdown' => null,
                            'total' => 0
                        ]);
                    }

                    $prices = json_decode($packageConfig->configuration_prices, true) ?? [];

                    $keyAdult = "{$adults}_a_{$children}_c_{$infants}_i_a";
                    $keyChild = "{$adults}_a_{$children}_c_{$infants}_i_c";
                    $keyInfant = "{$adults}_a_{$children}_c_{$infants}_i_i";

                    $baseAdult = $prices['b'][$keyAdult] ?? 0;
                    $baseChild = $prices['b'][$keyChild] ?? 0;
                    $baseInfant = $prices['b'][$keyInfant] ?? 0;
                    $surAdult = $prices['s'][$keyAdult] ?? 0;
                    $surChild = $prices['s'][$keyChild] ?? 0;
                    $surInfant = $prices['s'][$keyInfant] ?? 0;

                    $baseTotal = $baseAdult * $adults + $baseChild * $children + $baseInfant * $infants;
                    $surTotal = $surAdult * $adults + $surChild * $children + $surInfant * $infants;
                    $nightTotal = $baseTotal + $surTotal;

                    $nightData = [
                        'date' => $date->format('Y-m-d'),
                        'season' => $seasonType->name,
                        'season_type' => $seasonType->name,
                        'date_type' => $dateType->name,
                        'is_weekend' => $date->isWeekend(),
                        'room_type' => $roomTypeId,
                        'adults' => $adults,
                        'children' => $children,
                        'infants' => $infants,
                        'base_charge' => [
                            'adult' => ['price' => $baseAdult, 'quantity' => $adults, 'total' => $baseAdult * $adults],
                            'child' => ['price' => $baseChild, 'quantity' => $children, 'total' => $baseChild * $children],
                            'infant' => ['price' => $baseInfant, 'quantity' => $infants, 'total' => $baseInfant * $infants],
                            'total' => $baseTotal,
                        ],
                        'surcharge' => [
                            'adult' => ['price' => $surAdult, 'quantity' => $adults, 'total' => $surAdult * $adults],
                            'child' => ['price' => $surChild, 'quantity' => $children, 'total' => $surChild * $children],
                            'infant' => ['price' => $surInfant, 'quantity' => $infants, 'total' => $surInfant * $infants],
                            'total' => $surTotal,
                        ],
                        'total' => $nightTotal,
                    ];

                    $roomNights[] = $nightData;
                    $allNights[] = $nightData;
                }

                // Calculate room summary
                $sum = fn($key) => array_sum(array_map(fn($night) => floatval(data_get($night, $key)), $roomNights));

                $roomBreakdowns[] = [
                    'room_type_name' => RoomType::find($roomTypeId)->name,
                    'room_type' => $roomTypeId,
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                    'nights' => $roomNights,
                    'summary' => [
                        'base_charges' => [
                            'adult' => [
                                'price_per_night' => $roomNights[0]['base_charge']['adult']['price'],
                                'quantity' => $adults,
                                'total' => $sum('base_charge.adult.total')
                            ],
                            'child' => [
                                'price_per_night' => $roomNights[0]['base_charge']['child']['price'],
                                'quantity' => $children,
                                'total' => $sum('base_charge.child.total')
                            ],
                            'infant' => [
                                'price_per_night' => $roomNights[0]['base_charge']['infant']['price'],
                                'quantity' => $infants,
                                'total' => $sum('base_charge.infant.total')
                            ],
                            'total' => $sum('base_charge.total'),
                        ],
                        'surcharges' => [
                            'adult' => [
                                'price_per_night' => $roomNights[0]['surcharge']['adult']['price'],
                                'quantity' => $adults,
                                'total' => $sum('surcharge.adult.total')
                            ],
                            'child' => [
                                'price_per_night' => $roomNights[0]['surcharge']['child']['price'],
                                'quantity' => $children,
                                'total' => $sum('surcharge.child.total')
                            ],
                            'infant' => [
                                'price_per_night' => $roomNights[0]['surcharge']['infant']['price'],
                                'quantity' => $infants,
                                'total' => $sum('surcharge.infant.total')
                            ],
                            'total' => $sum('surcharge.total'),
                        ],
                        'total' => $sum('total'),
                    ]
                ];
            }

            // Calculate overall summary
            $sum = fn($key) => array_sum(array_map(fn($night) => floatval(data_get($night, $key)), $allNights));

            // Calculate guest breakdown - individual guest pricing for all types
            $guestBreakdown = [];
            $totalAdults = 0;
            $totalChildren = 0;
            $totalInfants = 0;

            foreach ($roomBreakdowns as $roomIndex => $room) {
                $adults = $room['adults'];
                $children = $room['children'];
                $infants = $room['infants'];
                $totalAdults += $adults;
                $totalChildren += $children;
                $totalInfants += $infants;

                // Add adults
                for ($adultIndex = 1; $adultIndex <= $adults; $adultIndex++) {
                    $adultKey = "adult_{$roomIndex}_{$adultIndex}";

                    // Calculate per-adult pricing for this room
                    $adultBaseChargePerNight = $room['nights'][0]['base_charge']['adult']['price'] ?? 0;
                    $adultSurchargePerNight = $room['nights'][0]['surcharge']['adult']['price'] ?? 0;
                    $nightsCount = count($room['nights']);

                    $guestBreakdown[$adultKey] = [
                        'room_number' => $roomIndex + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type' => 'adult',
                        'guest_number' => $adultIndex,
                        'nights' => $nightsCount,
                        'base_charge' => [
                            'price_per_night' => $adultBaseChargePerNight,
                            'total' => $adultBaseChargePerNight * $nightsCount
                        ],
                        'surcharge' => [
                            'price_per_night' => $adultSurchargePerNight,
                            'total' => $adultSurchargePerNight * $nightsCount
                        ],
                        'total' => ($adultBaseChargePerNight + $adultSurchargePerNight) * $nightsCount
                    ];
                }

                // Add children
                for ($childIndex = 1; $childIndex <= $children; $childIndex++) {
                    $childKey = "child_{$roomIndex}_{$childIndex}";

                    // Calculate per-child pricing for this room
                    $childBaseChargePerNight = $room['nights'][0]['base_charge']['child']['price'] ?? 0;
                    $childSurchargePerNight = $room['nights'][0]['surcharge']['child']['price'] ?? 0;
                    $nightsCount = count($room['nights']);

                    $guestBreakdown[$childKey] = [
                        'room_number' => $roomIndex + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type' => 'child',
                        'guest_number' => $childIndex,
                        'nights' => $nightsCount,
                        'base_charge' => [
                            'price_per_night' => $childBaseChargePerNight,
                            'total' => $childBaseChargePerNight * $nightsCount
                        ],
                        'surcharge' => [
                            'price_per_night' => $childSurchargePerNight,
                            'total' => $childSurchargePerNight * $nightsCount
                        ],
                        'total' => ($childBaseChargePerNight + $childSurchargePerNight) * $nightsCount
                    ];
                }

                // Add infants
                for ($infantIndex = 1; $infantIndex <= $infants; $infantIndex++) {
                    $infantKey = "infant_{$roomIndex}_{$infantIndex}";

                    // Calculate per-infant pricing for this room
                    $infantBaseChargePerNight = $room['nights'][0]['base_charge']['infant']['price'] ?? 0;
                    $infantSurchargePerNight = $room['nights'][0]['surcharge']['infant']['price'] ?? 0;
                    $nightsCount = count($room['nights']);

                    $guestBreakdown[$infantKey] = [
                        'room_number' => $roomIndex + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type' => 'infant',
                        'guest_number' => $infantIndex,
                        'nights' => $nightsCount,
                        'base_charge' => [
                            'price_per_night' => $infantBaseChargePerNight,
                            'total' => $infantBaseChargePerNight * $nightsCount
                        ],
                        'surcharge' => [
                            'price_per_night' => $infantSurchargePerNight,
                            'total' => $infantSurchargePerNight * $nightsCount
                        ],
                        'total' => ($infantBaseChargePerNight + $infantSurchargePerNight) * $nightsCount
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'currency' => 'MYR',
                'nights' => $allNights,
                'rooms' => $roomBreakdowns,
                'guest_breakdown' => $guestBreakdown,
                'summary' => [
                    'total_nights' => count($allNights) / count($rooms), // Average nights per room
                    'total_adults' => $totalAdults,
                    'total_children' => $totalChildren,
                    'total_infants' => $totalInfants,
                    'base_charges' => [
                        'adult' => [
                            'total' => $sum('base_charge.adult.total')
                        ],
                        'child' => [
                            'total' => $sum('base_charge.child.total')
                        ],
                        'infant' => [
                            'total' => $sum('base_charge.infant.total')
                        ],
                        'total' => $sum('base_charge.total'),
                    ],
                    'surcharges' => [
                        'adult' => [
                            'total' => $sum('surcharge.adult.total')
                        ],
                        'child' => [
                            'total' => $sum('surcharge.child.total')
                        ],
                        'infant' => [
                            'total' => $sum('surcharge.infant.total')
                        ],
                        'total' => $sum('surcharge.total'),
                    ],
                    'grand_total' => $sum('total'),
                ],
                'total' => $sum('total'),
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'the selected dates and room type combination are not available') && $isFromBot) {
                // suggest alternative dates
                $suggestedDates = $this->getSuggestedDates($packageId, $startDate);
                return response()->json([
                    'success' => false,
                    'message' => 'The selected dates and room type combination are not available. Below are some alternative dates that you can try.',
                    'suggested_dates' => $suggestedDates
                ], 400);
            } else if (str_contains($e->getMessage(), 'The selected room type id `' . $roomTypeId . '` and name `' . $roomType->name . '` has a maximum capacity of `' . $roomType->max_occupancy . '` guests. Please select a different room type.')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'max_occupancy' => $roomType->max_occupancy,
                    'current_occupancy' => $totalGuests,
                ], 400);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
