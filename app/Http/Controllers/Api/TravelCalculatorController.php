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
use App\Services\SstCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Validator;

class TravelCalculatorController extends Controller
{
    public $enabledDefaultSeasonAndDateType;
    protected SstCalculationService $sstCalculationService;

    public function __construct(SstCalculationService $sstCalculationService)
    {
        $this->enabledDefaultSeasonAndDateType = env('ENABLED_DEFAULT_SEASON_AND_DATE_TYPE', false);
        $this->sstCalculationService = $sstCalculationService;
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
            $endDate   = Carbon::parse($endDate);

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

            foreach ($rooms as $room) {
                $roomTypeId = $room['room_type'];
                $adults     = (int)($room['adults']   ?? 0);
                $children   = (int)($room['children'] ?? 0);
                $infants    = (int)($room['infants']  ?? 0);

                $roomType   = RoomType::find($roomTypeId);
                $totalGuests = $adults + $children + $infants;

                if ($totalGuests > $roomType->max_occupancy) {
                    throw new \Exception('The selected room type id `' . $roomTypeId . '` and name `' . $roomType->name . '` has a maximum capacity of `' . $roomType->max_occupancy . '` guests. Please select a different room type.');
                }

                // Capture first-night base rates (lump sum per package/room)
                $firstNightBase = [
                    'adult'  => null,
                    'child'  => null,
                    'infant' => null,
                    'total'  => 0.0,
                    '_captured' => false,
                ];

                $roomNightsRaw = [];
                $loopDate = $startDate->copy();

                while ($loopDate->lt($endDate)) {
                    // Resolve date type (range -> fallback weekday/weekend + package weekend_days override)
                    $dateTypeRange = DateTypeRange::where('start_date', '<=', $loopDate)
                        ->where('end_date', '>=', $loopDate)
                        ->where('package_id', $packageId)
                        ->first();

                    if ($dateTypeRange) {
                        $dateType = $dateTypeRange->dateType;
                    } else {
                        $fallbackType = $loopDate->isWeekend() ? 'weekend' : 'weekday';
                        if (!empty($package->weekend_days)) {
                            $weekendDays = is_array($package->weekend_days) ? $package->weekend_days : json_decode($package->weekend_days, true);
                            $dayOfWeek = $loopDate->dayOfWeek; // 0..6
                            $fallbackType = in_array($dayOfWeek, $weekendDays, true) ? 'weekend' : 'weekday';
                        }
                        $dateType = DateType::where('name', 'LIKE', "%$fallbackType%")->first();
                        if (!$dateType) {
                            Log::error("Default Date type not found for {$loopDate->format('Y-m-d')} for package {$packageId}");
                            throw new \Exception("Date type not found for {$loopDate->format('Y-m-d')}");
                        }
                    }

                    // Resolve season
                    $season = Season::where('start_date', '<=', $loopDate)
                        ->where('end_date', '>=', $loopDate)
                        ->where('package_id', $packageId)
                        ->first();

                    if ($season) {
                        $seasonType = $season->type;
                    } else if ($this->enabledDefaultSeasonAndDateType) {
                        $seasonType = SeasonType::where('name', 'Default')->first();
                        if (!$seasonType) {
                            Log::error('Default Season type not found for ' . $loopDate->format('Y-m-d') . ' for package ' . $packageId);
                            throw new \Exception("season type not found for " . $loopDate->format('Y-m-d') . " for package " . $packageId);
                        }
                    } else {
                        throw new \Exception("The selected dates and room type combination are not available. Please contact us for more information.");
                    }

                    Log::info(json_encode([
                        'package_id'        => $packageId,
                        'season_type_id'    => $seasonType->id,
                        'season_type_name'  => $seasonType->name,
                        'date_type_id'      => $dateType->id,
                        'date_type_name'    => $dateType->name,
                        'room_type_id'      => $roomTypeId,
                        'room_type_name'    => $roomType->name,
                    ], JSON_PRETTY_PRINT));

                    $packageConfig = PackageConfiguration::where([
                        'package_id'     => $packageId,
                        'season_type_id' => $seasonType->id,
                        'date_type_id'   => $dateType->id,
                        'room_type_id'   => $roomTypeId
                    ])->first();

                    if (!$packageConfig) {
                        Log::error('Package configuration not found for ' . $loopDate->format('Y-m-d') . ' for package ' . $packageId);
                        return response()->json([
                            'success'  => false,
                            'message'  => "Package configuration not found for {$loopDate->format('Y-m-d')}",
                            'breakdown' => null,
                            'total'    => 0
                        ]);
                    }

                    $prices = json_decode($packageConfig->configuration_prices, true) ?? [];

                    $keyAdult = "{$adults}_a_{$children}_c_{$infants}_i_a";
                    $keyChild = "{$adults}_a_{$children}_c_{$infants}_i_c";
                    $keyInfant = "{$adults}_a_{$children}_c_{$infants}_i_i";

                    $baseAdult = (float)($prices['b'][$keyAdult] ?? 0);
                    $baseChild = (float)($prices['b'][$keyChild] ?? 0);
                    $baseInfant = (float)($prices['b'][$keyInfant] ?? 0);

                    $surAdult  = (float)($prices['s'][$keyAdult] ?? 0);
                    $surChild  = (float)($prices['s'][$keyChild] ?? 0);
                    $surInfant = (float)($prices['s'][$keyInfant] ?? 0);

                    // Capture first-night base once
                    if (!$firstNightBase['_captured']) {
                        $firstNightBase['adult']  = $baseAdult;
                        $firstNightBase['child']  = $baseChild;
                        $firstNightBase['infant'] = $baseInfant;
                        $firstNightBase['total']  = $baseAdult * $adults + $baseChild * $children + $baseInfant * $infants;
                        $firstNightBase['_captured'] = true;
                    }

                    // Build raw night (base will be split later)
                    $nightData = [
                        'date'        => $loopDate->format('Y-m-d'),
                        'season'      => $seasonType->name,
                        'season_type' => $seasonType->name,
                        'date_type'   => $dateType->name,
                        'is_weekend'  => $loopDate->isWeekend(),
                        'room_type'   => $roomTypeId,
                        'adults'      => $adults,
                        'children'    => $children,
                        'infants'     => $infants,

                        // placeholder base (will be replaced by split)
                        'base_charge' => [
                            'adult'  => ['price' => $firstNightBase['adult'],  'quantity' => $adults,   'total' => 0.0],
                            'child'  => ['price' => $firstNightBase['child'],  'quantity' => $children, 'total' => 0.0],
                            'infant' => ['price' => $firstNightBase['infant'], 'quantity' => $infants,  'total' => 0.0],
                            'total'  => 0.0,
                            // 'applied_as_lump_sum' => false,
                        ],

                        // nightly surcharge
                        'surcharge' => [
                            'adult'  => ['price' => $surAdult,  'quantity' => $adults,   'total' => $surAdult  * $adults],
                            'child'  => ['price' => $surChild,  'quantity' => $children, 'total' => $surChild  * $children],
                            'infant' => ['price' => $surInfant, 'quantity' => $infants,  'total' => $surInfant * $infants],
                            'total'  => ($surAdult * $adults) + ($surChild * $children) + ($surInfant * $infants),
                        ],

                        'total' => 0.0, // recomputed after split
                    ];

                    $roomNightsRaw[] = $nightData;
                    $loopDate->addDay();
                }

                // ---- Split first-night base evenly across nights (remainder to last night) ----
                $nightsCount = max(count($roomNightsRaw), 1);

                $baseAdultTotal  = (float)(($firstNightBase['adult']  ?? 0) * $adults);
                $baseChildTotal  = (float)(($firstNightBase['child']  ?? 0) * $children);
                $baseInfantTotal = (float)(($firstNightBase['infant'] ?? 0) * $infants);

                $evenAdult  = round($baseAdultTotal  / $nightsCount, 2);
                $evenChild  = round($baseChildTotal  / $nightsCount, 2);
                $evenInfant = round($baseInfantTotal / $nightsCount, 2);

                $adultRemainder  = round($baseAdultTotal  - ($evenAdult  * $nightsCount), 2);
                $childRemainder  = round($baseChildTotal  - ($evenChild  * $nightsCount), 2);
                $infantRemainder = round($baseInfantTotal - ($evenInfant * $nightsCount), 2);

                $roomNights = [];
                foreach ($roomNightsRaw as $idx => $night) {
                    $isLast = ($idx === $nightsCount - 1);

                    $nightAdult  = $evenAdult  + ($isLast ? $adultRemainder  : 0.0);
                    $nightChild  = $evenChild  + ($isLast ? $childRemainder  : 0.0);
                    $nightInfant = $evenInfant + ($isLast ? $infantRemainder : 0.0);

                    $night['base_charge'] = [
                        'adult'  => ['price' => $firstNightBase['adult'] ?? 0,  'quantity' => $adults,   'total' => $nightAdult],
                        'child'  => ['price' => $firstNightBase['child'] ?? 0,  'quantity' => $children, 'total' => $nightChild],
                        'infant' => ['price' => $firstNightBase['infant'] ?? 0, 'quantity' => $infants,  'total' => $nightInfant],
                        'total'  => round($nightAdult + $nightChild + $nightInfant, 2),
                        // 'applied_as_lump_sum' => false,
                    ];

                    $night['total'] = round($night['base_charge']['total'] + ($night['surcharge']['total'] ?? 0), 2);

                    $roomNights[] = $night;
                    $allNights[]  = $night; // add to global AFTER split so UI shows per-night split
                }

                // Room summary
                $sum = fn($key) => array_sum(array_map(fn($night) => floatval(data_get($night, $key)), $roomNights));

                $roomBreakdowns[] = [
                    'room_type_name' => $roomType->name,
                    'room_type'      => $roomTypeId,
                    'adults'         => $adults,
                    'children'       => $children,
                    'infants'        => $infants,
                    'nights'         => $roomNights,
                    'summary'        => [
                        // Base totals equal first-night lump sum; now represented as split across nights
                        'base_charges' => [
                            'adult'  => [
                                'price_per_package' => $firstNightBase['adult'],
                                'quantity'          => $adults,
                                'total'             => $sum('base_charge.adult.total'),
                            ],
                            'child'  => [
                                'price_per_package' => $firstNightBase['child'],
                                'quantity'          => $children,
                                'total'             => $sum('base_charge.child.total'),
                            ],
                            'infant' => [
                                'price_per_package' => $firstNightBase['infant'],
                                'quantity'          => $infants,
                                'total'             => $sum('base_charge.infant.total'),
                            ],
                            'total' => $sum('base_charge.total'),
                        ],
                        'surcharges' => [
                            'adult'  => [
                                'price_per_night' => $roomNights[0]['surcharge']['adult']['price'],
                                'quantity'        => $adults,
                                'total'           => $sum('surcharge.adult.total')
                            ],
                            'child'  => [
                                'price_per_night' => $roomNights[0]['surcharge']['child']['price'],
                                'quantity'        => $children,
                                'total'           => $sum('surcharge.child.total')
                            ],
                            'infant' => [
                                'price_per_night' => $roomNights[0]['surcharge']['infant']['price'],
                                'quantity'        => $infants,
                                'total'           => $sum('surcharge.infant.total')
                            ],
                            'total' => $sum('surcharge.total'),
                        ],
                        'total' => $sum('total'),
                    ]
                ];
            }

            // Overall summary
            $sum = fn($key) => array_sum(array_map(fn($night) => floatval(data_get($night, $key)), $allNights));

            // Guest breakdown (base = per package once per guest; surcharge = per-night × nights)
            $guestBreakdown = [];
            $totalAdults = 0;
            $totalChildren = 0;
            $totalInfants = 0;

            foreach ($roomBreakdowns as $roomIndex => $room) {
                $adults   = $room['adults'];
                $children = $room['children'];
                $infants  = $room['infants'];

                $totalAdults   += $adults;
                $totalChildren += $children;
                $totalInfants  += $infants;

                $nightsCount = max(count($room['nights']), 1);

                $firstNightBaseAdult  = $room['summary']['base_charges']['adult']['price_per_package']  ?? 0;
                $firstNightBaseChild  = $room['summary']['base_charges']['child']['price_per_package']  ?? 0;
                $firstNightBaseInfant = $room['summary']['base_charges']['infant']['price_per_package'] ?? 0;

                $surAdult  = $room['nights'][0]['surcharge']['adult']['price']  ?? 0;
                $surChild  = $room['nights'][0]['surcharge']['child']['price']  ?? 0;
                $surInfant = $room['nights'][0]['surcharge']['infant']['price'] ?? 0;

                for ($i = 1; $i <= $adults; $i++) {
                    $key = "adult_{$roomIndex}_{$i}";
                    $guestBreakdown[$key] = [
                        'room_number'    => $roomIndex + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type'     => 'adult',
                        'guest_number'   => $i,
                        'nights'         => $nightsCount,
                        'base_charge'    => [
                            'price_per_package' => $firstNightBaseAdult,
                            'total'             => $firstNightBaseAdult,
                        ],
                        'surcharge'      => [
                            'price_per_night' => $surAdult,
                            'total'           => $surAdult * $nightsCount,
                        ],
                        'total'          => $firstNightBaseAdult + ($surAdult * $nightsCount),
                    ];
                }
                for ($i = 1; $i <= $children; $i++) {
                    $key = "child_{$roomIndex}_{$i}";
                    $guestBreakdown[$key] = [
                        'room_number'    => $roomIndex + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type'     => 'child',
                        'guest_number'   => $i,
                        'nights'         => $nightsCount,
                        'base_charge'    => [
                            'price_per_package' => $firstNightBaseChild,
                            'total'             => $firstNightBaseChild,
                        ],
                        'surcharge'      => [
                            'price_per_night' => $surChild,
                            'total'           => $surChild * $nightsCount,
                        ],
                        'total'          => $firstNightBaseChild + ($surChild * $nightsCount),
                    ];
                }
                for ($i = 1; $i <= $infants; $i++) {
                    $key = "infant_{$roomIndex}_{$i}";
                    $guestBreakdown[$key] = [
                        'room_number'    => $roomIndex + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type'     => 'infant',
                        'guest_number'   => $i,
                        'nights'         => $nightsCount,
                        'base_charge'    => [
                            'price_per_package' => $firstNightBaseInfant,
                            'total'             => $firstNightBaseInfant,
                        ],
                        'surcharge'      => [
                            'price_per_night' => $surInfant,
                            'total'           => $surInfant * $nightsCount,
                        ],
                        'total'          => $firstNightBaseInfant + ($surInfant * $nightsCount),
                    ];
                }
            }

            $sst = 0;
            if ($package->sst_enable) {
                $sst = $this->sstCalculationService->calculateSst($sum('total'));
            }

            $totalWithoutSst = $sum('total');
            $total = $totalWithoutSst + $sst;

            return response()->json([
                'success'         => true,
                'currency'        => 'MYR',
                'nights'          => $allNights,
                'rooms'           => $roomBreakdowns,
                'guest_breakdown' => $guestBreakdown,
                'summary' => [
                    'total_nights'   => count($allNights) / max(count($rooms), 1),
                    'total_adults'   => $totalAdults,
                    'total_children' => $totalChildren,
                    'total_infants'  => $totalInfants,
                    'base_charges' => [
                        'adult'  => ['total' => $sum('base_charge.adult.total')],
                        'child'  => ['total' => $sum('base_charge.child.total')],
                        'infant' => ['total' => $sum('base_charge.infant.total')],
                        'total'  => $sum('base_charge.total'),
                    ],
                    'surcharges' => [
                        'adult'  => ['total' => $sum('surcharge.adult.total')],
                        'child'  => ['total' => $sum('surcharge.child.total')],
                        'infant' => ['total' => $sum('surcharge.infant.total')],
                        'total'  => $sum('surcharge.total'),
                    ],
                    'grand_total' => $sum('total'),
                ],
                'total'            => $total,
                'total_without_sst' => $totalWithoutSst,
                'sst'              => $sst,
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'the selected dates and room type combination are not available') && $isFromBot) {
                $suggestedDates = $this->getSuggestedDates($packageId, $startDate);
                return response()->json([
                    'success'         => false,
                    'message'         => 'The selected dates and room type combination are not available. Below are some alternative dates that you can try.',
                    'suggested_dates' => $suggestedDates
                ], 400);
            } else if (
                isset($roomTypeId, $roomType, $totalGuests) &&
                str_contains($e->getMessage(), 'The selected room type id `' . $roomTypeId . '` and name `' . $roomType->name . '` has a maximum capacity of `' . $roomType->max_occupancy . '` guests. Please select a different room type.')
            ) {
                return response()->json([
                    'success'           => false,
                    'message'           => $e->getMessage(),
                    'max_occupancy'     => $roomType->max_occupancy,
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
