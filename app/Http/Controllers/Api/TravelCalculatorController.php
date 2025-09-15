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
    
            // 1) Date blockers per room
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
    
                if (!empty($blockedDates = array_unique($blockedDates))) {
                    throw new \Exception('Sorry, the selected dates and room type combination are not available. Please try to select another room type or contact us for more information.');
                }
            }
    
            $allNights = [];
            $roomBreakdowns = [];
    
            foreach ($rooms as $roomIndex => $room) {
                $roomTypeId = $room['room_type'];
                $adults     = (int)($room['adults']   ?? 0);
                $children   = (int)($room['children'] ?? 0);
                $infants    = (int)($room['infants']  ?? 0);
    
                $roomType   = RoomType::find($roomTypeId);
                $totalGuests = $adults + $children + $infants;
    
                if ($totalGuests > $roomType->max_occupancy) {
                    throw new \Exception('The selected room type id `' . $roomTypeId . '` and name `' . $roomType->name . '` has a maximum capacity of `' . $roomType->max_occupancy . '` guests. Please select a different room type.');
                }
    
                // First-night base lump sum (computed from per-guest a1..aN / c1..cN / i1..iN)
                $firstNightBase = [
                    'adult'      => 0.0,   // average per adult (for UI)
                    'child'      => 0.0,   // average per child (for UI)
                    'infant'     => 0.0,   // average per infant (for UI)
                    'total'      => 0.0,   // exact lump-sum base
                    'adult_list' => [],    // per-guest actual list
                    'child_list' => [],
                    'infant_list'=> [],
                    '_captured'  => false,
                ];
    
                $roomNightsRaw = [];
                $loopDate = $startDate->copy();
    
                while ($loopDate->lt($endDate)) {
                    // 2) Resolve date type (range -> fallback weekday/weekend with package override)
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
                            $fallbackType = in_array($loopDate->dayOfWeek, (array)$weekendDays, true) ? 'weekend' : 'weekday';
                        }
                        $dateType = DateType::where('name', 'LIKE', "%$fallbackType%")->first();
                        if (!$dateType) {
                            Log::error("Default Date type not found for {$loopDate->format('Y-m-d')} for package {$packageId}");
                            throw new \Exception("Date type not found for {$loopDate->format('Y-m-d')}");
                        }
                    }
    
                    // 3) Resolve season
                    $season = Season::where('start_date', '<=', $loopDate)
                        ->where('end_date', '>=', $loopDate)
                        ->where('package_id', $packageId)
                        ->first();
    
                    if ($season) {
                        $seasonType = $season->type;
                    } elseif ($this->enabledDefaultSeasonAndDateType) {
                        $seasonType = SeasonType::where('name', 'Default')->first();
                        if (!$seasonType) {
                            Log::error('Default Season type not found for ' . $loopDate->format('Y-m-d') . ' for package ' . $packageId);
                            throw new \Exception("season type not found for " . $loopDate->format('Y-m-d') . " for package " . $packageId);
                        }
                    } else {
                        throw new \Exception("The selected dates and room type combination are not available. Please contact us for more information.");
                    }
    
                    // 4) Find package config
                    $packageConfig = PackageConfiguration::where([
                        'package_id'     => $packageId,
                        'season_type_id' => $seasonType->id,
                        'date_type_id'   => $dateType->id,
                        'room_type_id'   => $roomTypeId
                    ])->first();
    
                    if (!$packageConfig) {
                        Log::error("Package configuration not found for {$loopDate->format('Y-m-d')} (pkg {$packageId})");
                        return response()->json([
                            'success'  => false,
                            'message'  => "Package configuration not found for {$loopDate->format('Y-m-d')}",
                            'breakdown'=> null,
                            'total'    => 0
                        ]);
                    }
    
                    // 5) Decode prices (support new & legacy)
                    $comboKey = "{$adults}_a_{$children}_c_{$infants}_i";
                    $decoded  = is_array($packageConfig->configuration_prices) ? $packageConfig->configuration_prices : json_decode($packageConfig->configuration_prices ?? '[]', true);
    
                    // Normalize payload
                    if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0]) && (isset($decoded[0]['base']) || isset($decoded[0]['surch']))) {
                        $payload = $decoded[0]; // new format with outer array
                    } elseif (is_array($decoded) && (isset($decoded['base']) || isset($decoded['surch']))) {
                        $payload = $decoded;    // new format without outer array
                    } elseif (is_array($decoded) && (isset($decoded['b']) || isset($decoded['s']))) {
                        $payload = ['base' => $decoded['b'] ?? [], 'surch' => $decoded['s'] ?? []]; // legacy
                    } else {
                        $payload = ['base' => [], 'surch' => []];
                    }
    
                    $baseEntry  = $payload['base'][$comboKey]  ?? null;
                    $surchEntry = $payload['surch'][$comboKey] ?? null;
    
                    // Legacy fallback suffix keys
                    if (!$baseEntry && (isset($payload['base'][$comboKey . '_a']) || isset($payload['base'][$comboKey . '_c']) || isset($payload['base'][$comboKey . '_i']))) {
                        $baseEntry = [
                            'a1' => (float)($payload['base'][$comboKey . '_a'] ?? 0),
                            'c1' => (float)($payload['base'][$comboKey . '_c'] ?? 0),
                            'i1' => (float)($payload['base'][$comboKey . '_i'] ?? 0),
                        ];
                    }
                    if (!$surchEntry && (isset($payload['surch'][$comboKey . '_a']) || isset($payload['surch'][$comboKey . '_c']) || isset($payload['surch'][$comboKey . '_i']))) {
                        $surchEntry = [
                            'a' => (float)($payload['surch'][$comboKey . '_a'] ?? 0),
                            'c' => (float)($payload['surch'][$comboKey . '_c'] ?? 0),
                            'i' => (float)($payload['surch'][$comboKey . '_i'] ?? 0),
                        ];
                    }
    
                    if (!$baseEntry || !$surchEntry) {
                        Log::error("Prices not found for combo {$comboKey} in configuration {$packageConfig->id}");
                        return response()->json([
                            'success'  => false,
                            'message'  => "Prices not found for the selected guests/room combination.",
                            'breakdown'=> null,
                            'total'    => 0
                        ]);
                    }
    
                    // 6) Build per-guest base arrays (a1..aN / c1..cN / i1..iN), exact first-night lump sum
                    $mkList = static function(array $entry, string $prefix, int $count): array {
                        $out = [];
                        for ($i = 1; $i <= $count; $i++) {
                            $out[] = (float)($entry[$prefix . $i] ?? 0);
                        }
                        return $out;
                    };
    
                    $adultBaseList  = $mkList($baseEntry, 'a', $adults);
                    $childBaseList  = $mkList($baseEntry, 'c', $children);
                    $infantBaseList = $mkList($baseEntry, 'i', $infants);
    
                    $adultBaseTotal  = array_sum($adultBaseList);
                    $childBaseTotal  = array_sum($childBaseList);
                    $infantBaseTotal = array_sum($infantBaseList);
                    $lumpSumBase     = round($adultBaseTotal + $childBaseTotal + $infantBaseTotal, 2);
    
                    // Per-night surcharge (flat per pax type)
                    $surAdult  = (float)($surchEntry['a'] ?? 0);
                    $surChild  = (float)($surchEntry['c'] ?? 0);
                    $surInfant = (float)($surchEntry['i'] ?? 0);
    
                    // Capture first-night base once for this room
                    if (!$firstNightBase['_captured']) {
                        // store averages for UI (price × qty) compatibility
                        $firstNightBase['adult']       = $adults   > 0 ? round($adultBaseTotal  / $adults,   2) : 0.0;
                        $firstNightBase['child']       = $children > 0 ? round($childBaseTotal  / $children, 2) : 0.0;
                        $firstNightBase['infant']      = $infants  > 0 ? round($infantBaseTotal / $infants,  2) : 0.0;
                        $firstNightBase['total']       = $lumpSumBase;
                        $firstNightBase['adult_list']  = $adultBaseList;
                        $firstNightBase['child_list']  = $childBaseList;
                        $firstNightBase['infant_list'] = $infantBaseList;
                        $firstNightBase['_captured']   = true;
                    }
    
                    // Build night row (base totals will be split later; surcharge is nightly)
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
    
                        'base_charge' => [
                            // keep avg "price" for UI compatibility + expose per_guest list (exact)
                            'adult'  => [
                                'price'     => $firstNightBase['adult'],
                                'quantity'  => $adults,
                                'per_guest' => $firstNightBase['adult_list'],  // [a1,a2,...]
                                'total'     => 0.0, // filled after split
                            ],
                            'child'  => [
                                'price'     => $firstNightBase['child'],
                                'quantity'  => $children,
                                'per_guest' => $firstNightBase['child_list'],  // [c1,c2,...]
                                'total'     => 0.0,
                            ],
                            'infant' => [
                                'price'     => $firstNightBase['infant'],
                                'quantity'  => $infants,
                                'per_guest' => $firstNightBase['infant_list'], // [i1,i2,...]
                                'total'     => 0.0,
                            ],
                            'total'  => 0.0,
                        ],
    
                        'surcharge' => [
                            'adult'  => ['price' => $surAdult,  'quantity' => $adults,   'total' => round($surAdult  * $adults,   2)],
                            'child'  => ['price' => $surChild,  'quantity' => $children, 'total' => round($surChild  * $children, 2)],
                            'infant' => ['price' => $surInfant, 'quantity' => $infants,  'total' => round($surInfant * $infants,  2)],
                            'total'  => round(($surAdult * $adults) + ($surChild * $children) + ($surInfant * $infants), 2),
                        ],
    
                        'total' => 0.0,
                    ];
    
                    $roomNightsRaw[] = $nightData;
                    $loopDate->addDay();
                }
    
                // 7) Split first-night lump-sum base evenly across the nights (for display only)
                $nightsCount = max(count($roomNightsRaw), 1);
    
                $baseAdultTotal  = round($firstNightBase['adult']  * $adults,   2);
                $baseChildTotal  = round($firstNightBase['child']  * $children, 2);
                $baseInfantTotal = round($firstNightBase['infant'] * $infants,  2);
    
                // Use exact lists to recompute component totals (avoid tiny rounding drift)
                if (!empty($firstNightBase['adult_list']))  $baseAdultTotal  = round(array_sum($firstNightBase['adult_list']), 2);
                if (!empty($firstNightBase['child_list']))  $baseChildTotal  = round(array_sum($firstNightBase['child_list']), 2);
                if (!empty($firstNightBase['infant_list'])) $baseInfantTotal = round(array_sum($firstNightBase['infant_list']), 2);
    
                $evenAdult  = round($baseAdultTotal  / $nightsCount, 2);
                $evenChild  = round($baseChildTotal  / $nightsCount, 2);
                $evenInfant = round($baseInfantTotal / $nightsCount, 2);
    
                $adultRem   = round($baseAdultTotal  - ($evenAdult  * $nightsCount), 2);
                $childRem   = round($baseChildTotal  - ($evenChild  * $nightsCount), 2);
                $infantRem  = round($baseInfantTotal - ($evenInfant * $nightsCount), 2);
    
                $roomNights = [];
                foreach ($roomNightsRaw as $idx => $night) {
                    $isLast = ($idx === $nightsCount - 1);
    
                    $nightAdult  = $evenAdult  + ($isLast ? $adultRem  : 0.0);
                    $nightChild  = $evenChild  + ($isLast ? $childRem  : 0.0);
                    $nightInfant = $evenInfant + ($isLast ? $infantRem : 0.0);
    
                    $night['base_charge']['adult']['total']  = $nightAdult;
                    $night['base_charge']['child']['total']  = $nightChild;
                    $night['base_charge']['infant']['total'] = $nightInfant;
                    $night['base_charge']['total'] = round($nightAdult + $nightChild + $nightInfant, 2);
    
                    $night['total'] = round($night['base_charge']['total'] + ($night['surcharge']['total'] ?? 0), 2);
    
                    $roomNights[] = $night;
                    $allNights[]  = $night; // global AFTER split
                }
    
                // 8) Room summary
                $sum = fn($key) => array_sum(array_map(fn($n) => floatval(data_get($n, $key)), $roomNights));
    
                $roomBreakdowns[] = [
                    'room_type_name' => $roomType->name,
                    'room_type'      => $roomTypeId,
                    'adults'         => $adults,
                    'children'       => $children,
                    'infants'        => $infants,
                    'nights'         => $roomNights,
                    'summary'        => [
                        'base_charges' => [
                            'adult'  => [
                                'price_per_package' => $firstNightBase['adult'],        // avg for UI
                                'per_guest'         => $firstNightBase['adult_list'],   // exact list
                                'quantity'          => $adults,
                                'total'             => $sum('base_charge.adult.total'),
                            ],
                            'child'  => [
                                'price_per_package' => $firstNightBase['child'],
                                'per_guest'         => $firstNightBase['child_list'],
                                'quantity'          => $children,
                                'total'             => $sum('base_charge.child.total'),
                            ],
                            'infant' => [
                                'price_per_package' => $firstNightBase['infant'],
                                'per_guest'         => $firstNightBase['infant_list'],
                                'quantity'          => $infants,
                                'total'             => $sum('base_charge.infant.total'),
                            ],
                            'total' => $sum('base_charge.total'),
                        ],
                        'surcharges' => [
                            'adult'  => [
                                'price_per_night' => $roomNights[0]['surcharge']['adult']['price'] ?? 0,
                                'quantity'        => $adults,
                                'total'           => $sum('surcharge.adult.total')
                            ],
                            'child'  => [
                                'price_per_night' => $roomNights[0]['surcharge']['child']['price'] ?? 0,
                                'quantity'        => $children,
                                'total'           => $sum('surcharge.child.total')
                            ],
                            'infant' => [
                                'price_per_night' => $roomNights[0]['surcharge']['infant']['price'] ?? 0,
                                'quantity'        => $infants,
                                'total'           => $sum('surcharge.infant.total')
                            ],
                            'total' => $sum('surcharge.total'),
                        ],
                        'total' => $sum('total'),
                    ]
                ];
            }
    
            // 9) Overall summary + guest breakdown (use exact per-guest base)
            $sum = fn($key) => array_sum(array_map(fn($n) => floatval(data_get($n, $key)), $allNights));
    
            $guestBreakdown = [];
            $totalAdults = 0;
            $totalChildren = 0;
            $totalInfants = 0;
    
            foreach ($roomBreakdowns as $rIdx => $room) {
                $adults   = (int)$room['adults'];
                $children = (int)$room['children'];
                $infants  = (int)$room['infants'];
    
                $totalAdults   += $adults;
                $totalChildren += $children;
                $totalInfants  += $infants;
    
                $nightsCount = max(count($room['nights']), 1);
    
                $adultList  = $room['summary']['base_charges']['adult']['per_guest']  ?? [];
                $childList  = $room['summary']['base_charges']['child']['per_guest']  ?? [];
                $infantList = $room['summary']['base_charges']['infant']['per_guest'] ?? [];
    
                $surAdult  = $room['nights'][0]['surcharge']['adult']['price']  ?? 0;
                $surChild  = $room['nights'][0]['surcharge']['child']['price']  ?? 0;
                $surInfant = $room['nights'][0]['surcharge']['infant']['price'] ?? 0;
    
                for ($i = 1; $i <= $adults; $i++) {
                    $base = (float)($adultList[$i - 1] ?? 0);
                    $key  = "adult_{$rIdx}_{$i}";
                    $guestBreakdown[$key] = [
                        'room_number'    => $rIdx + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type'     => 'adult',
                        'guest_number'   => $i,
                        'nights'         => $nightsCount,
                        'base_charge'    => [
                            'price_per_package' => $base,
                            'total'             => $base,
                        ],
                        'surcharge'      => [
                            'price_per_night' => $surAdult,
                            'total'           => round($surAdult * $nightsCount, 2),
                        ],
                        'total'          => round($base + ($surAdult * $nightsCount), 2),
                    ];
                }
                for ($i = 1; $i <= $children; $i++) {
                    $base = (float)($childList[$i - 1] ?? 0);
                    $key  = "child_{$rIdx}_{$i}";
                    $guestBreakdown[$key] = [
                        'room_number'    => $rIdx + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type'     => 'child',
                        'guest_number'   => $i,
                        'nights'         => $nightsCount,
                        'base_charge'    => [
                            'price_per_package' => $base,
                            'total'             => $base,
                        ],
                        'surcharge'      => [
                            'price_per_night' => $surChild,
                            'total'           => round($surChild * $nightsCount, 2),
                        ],
                        'total'          => round($base + ($surChild * $nightsCount), 2),
                    ];
                }
                for ($i = 1; $i <= $infants; $i++) {
                    $base = (float)($infantList[$i - 1] ?? 0);
                    $key  = "infant_{$rIdx}_{$i}";
                    $guestBreakdown[$key] = [
                        'room_number'    => $rIdx + 1,
                        'room_type_name' => $room['room_type_name'],
                        'guest_type'     => 'infant',
                        'guest_number'   => $i,
                        'nights'         => $nightsCount,
                        'base_charge'    => [
                            'price_per_package' => $base,
                            'total'             => $base,
                        ],
                        'surcharge'      => [
                            'price_per_night' => $surInfant,
                            'total'           => round($surInfant * $nightsCount, 2),
                        ],
                        'total'          => round($base + ($surInfant * $nightsCount), 2),
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
                'success'          => true,
                'currency'         => 'MYR',
                'nights'           => $allNights,
                'rooms'            => $roomBreakdowns,
                'guest_breakdown'  => $guestBreakdown,
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
                'total'             => $total,
                'total_without_sst' => $totalWithoutSst,
                'sst'               => $sst,
            ]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'the selected dates and room type combination are not available') && $isFromBot) {
                $suggestedDates = $this->getSuggestedDates($packageId, $startDate);
                return response()->json([
                    'success'         => false,
                    'message'         => 'The selected dates and room type combination are not available. Below are some alternative dates that you can try.',
                    'suggested_dates' => $suggestedDates
                ], 400);
            } elseif (
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
