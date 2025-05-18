<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use Carbon\WeekDay;

class TravelCalculatorController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'add_on_ids' => 'array',
            'add_on_ids.*' => 'exists:package_add_ons,id',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'travel_date' => 'required|date',
            'room_type' => 'required|string',
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $selectedAddOns = PackageAddOn::whereIn('id', $validated['add_on_ids'] ?? [])->get();
        $season = Season::where('start_date', '<=', $validated['travel_date'])
            ->where('end_date', '>=', $validated['travel_date'])
            ->orderBy('priority')
            ->first();
        $travelDate = Carbon::parse($validated['travel_date']);
        $isWeekend = $travelDate->isWeekend();
        $dateType = $isWeekend ? 'weekend' : 'weekday';
        $opposite = [
            'weekend' => 'weekday',
            'weekday' => 'weekend'
        ];
        $oppositeDateType = $opposite[$dateType];
        $dateTypeId = DateType::where('name', 'LIKE', "%{$dateType}%")->first()->id ?? null;
        $oppositeDateTypeId = DateType::where('name', 'LIKE', "%{$oppositeDateType}%")->first()->id ?? null;

        $adults = $validated['adults'];
        $children = $validated['children'];
        $roomType = $validated['room_type'];

        $packageConfig = PackageConfiguration::where([
            'package_id' => $package->id,
            'season_id' => $season->id ?? 0,
            'date_type_id' => $dateTypeId,
            'room_type' => $roomType
        ])->first();

        // fallback to weekday if weekend package not found
        if ($isWeekend && (!$packageConfig || $packageConfig->prices()->where('type', 'base_charge')->count() === 0)) {
            $packageConfig = PackageConfiguration::where([
                'package_id' => $package->id,
                'season_id' => $season->id ?? 0,
                'date_type_id' => $oppositeDateTypeId,
                'room_type' => $validated['room_type']
            ])->first();
        }

        if (!$packageConfig) {
            return response()->json([
                'success' => true,
                'message' => 'Package with selected option not found.'
            ]);
        }

        // === Base Charge ===
        $basePackageConfigPrice = $packageConfig->prices()->where('type', 'base_charge')->where('number_of_adults', $adults)->where('number_of_children', $children)->first();
        $pricePerAdult = (float) $basePackageConfigPrice->adult_price;
        $pricePerChild = (float) $basePackageConfigPrice->child_price;
        $baseAdultTotal = $pricePerAdult * $adults;
        $baseChildTotal = $pricePerChild * $children;
        $baseTotal = $baseAdultTotal + $baseChildTotal;

        // === Surcharge (Weekend: fixed per person) ===
        $surDateTypeIds = DateType::where('name', 'LIKE', '%sur%')->pluck('id')->toArray();
        $surDateTypeIds = DateTypeRange::where('start_date', '<=', $validated['travel_date'])
            ->where('end_date', '>=', $validated['travel_date'])
            ->whereIn('date_type_id', $surDateTypeIds)
            ->orderBy('start_date')
            ->pluck('date_type_id')
            ->toArray();

        $surPackageConfig = null;
        if ($surDateTypeIds) {
            $surPackageConfig = PackageConfiguration::where([
                'package_id' => $package->id,
                'season_id' => $season->id ?? 0,
                'room_type' => $validated['room_type']
            ])->whereIn('date_type_id', $surDateTypeIds)
                ->orderByRaw('FIELD(date_type_id, ' . implode(',', $surDateTypeIds) . ')')
                ->first();
        }

        $surPackageConfigPrice = $surPackageConfig ? $surPackageConfig->prices()->where('type', 'sur_charge')->where('number_of_adults', $adults)->where('number_of_children', $children)->first() : null;

        $surchargePerAdult = (float) ($surPackageConfigPrice->adult_price ?? 0);
        $surchargePerChild = (float) ($surPackageConfigPrice->child_price ?? 0);
        $surchargeAdultTotal = $surchargePerAdult * $adults;
        $surchargeChildTotal = $surchargePerChild * $children;
        $surchargeTotal = $surchargeAdultTotal + $surchargeChildTotal;

        // === Add-ons ===
        $userAddOns = [];
        $userAddOnsTotal = 0;

        foreach ($selectedAddOns as $addon) {
            $adultPrice = (float) $addon->adult_price;
            $childPrice = (float) $addon->child_price;
            $adultTotal = $adultPrice * $adults;
            $childTotal = $childPrice * $children;
            $total = $adultTotal + $childTotal;

            $userAddOns[] = [
                'name' => $addon->name,
                'adult_price' => number_format($adultPrice, 2, '.', ''),
                'adult_qty' => $adults,
                'adult_total' => number_format($adultTotal, 2, '.', ''),
                'child_price' => number_format($childPrice, 2, '.', ''),
                'child_qty' => $children,
                'child_total' => number_format($childTotal, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
            ];

            $userAddOnsTotal += $total;
        }

        $total = $baseTotal + $surchargeTotal + $userAddOnsTotal;

        return response()->json([
            'success' => true,
            'currency' => 'MYR',
            'season' => $season ? $season->name : 'Default Season',
            'season_type' => $season ? $season->type->name : 'Default',
            'date_type' => $dateType,
            'is_weekend' => $isWeekend,
            'breakdown' => [
                'base_charge' => [
                    'per_adult' => number_format($pricePerAdult, 2, '.', ''),
                    'adult_qty' => $adults,
                    'adult_total' => number_format($baseAdultTotal, 2, '.', ''),
                    'per_child' => number_format($pricePerChild, 2, '.', ''),
                    'child_qty' => $children,
                    'child_total' => number_format($baseChildTotal, 2, '.', ''),
                    'total' => number_format($baseTotal, 2, '.', ''),
                ],
                'surcharge' => [
                    'per_adult' => number_format($surchargePerAdult, 2, '.', ''),
                    'adult_qty' => $adults,
                    'adult_total' => number_format($surchargeAdultTotal, 2, '.', ''),
                    'per_child' => number_format($surchargePerChild, 2, '.', ''),
                    'child_qty' => $children,
                    'child_total' => number_format($surchargeChildTotal, 2, '.', ''),
                    'total' => number_format($surchargeTotal, 2, '.', ''),
                ],
                'add_ons' => [
                    'items' => $userAddOns,
                    'total' => number_format($userAddOnsTotal, 2, '.', ''),
                ],
            ],
            'total' => number_format($total, 2, '.', ''),
        ]);
    }

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

        $roomTypes = RoomType::whereIn(
            'id',
            PackageConfiguration::where('package_id', $package->id)
                ->distinct()
                ->pluck('room_type_id')
        )->get();

        return response()->json([
            'success' => true,
            'package' => $package,
            'room_types' => $roomTypes,
        ]);
    }

    public function packageCalculatePrice(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'room_type' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1|max:4',
            'children' => 'required|integer|min:0|max:3'
        ]);

        try {
            $package = Package::findOrFail($validated['package_id']);
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $adults = $validated['adults'];
            $children = $validated['children'];
            $nights = [];

            // Calculate prices for each night
            $currentDate = $startDate->copy();
            while ($currentDate->lt($endDate)) {
                // Get the season for this night
                $season = Season::where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->orderBy('priority')
                    ->first();

                // If no season found, get the default season
                if (!$season) {
                    $season = Season::whereHas('type', function ($query) {
                        $query->where('name', 'Default');
                    })->first();
                }

                // Determine if it's a weekend
                $isWeekend = $currentDate->isWeekend();
                $dateType = $isWeekend ? 'weekend' : 'weekday';
                $opposite = [
                    'weekend' => 'weekday',
                    'weekday' => 'weekend'
                ];
                $oppositeDateType = $opposite[$dateType];
                
                // Get date type IDs with fallback to default
                $dateTypeId = DateType::where('name', 'LIKE', "%{$dateType}%")->first()->id ?? null;
                $oppositeDateTypeId = DateType::where('name', 'LIKE', "%{$oppositeDateType}%")->first()->id ?? null;
                $defaultDateTypeId = DateType::where('name', 'LIKE', '%default%')->first()->id ?? null;

                // Get package configuration with multiple fallback attempts
                $packageConfig = PackageConfiguration::where([
                    'package_id' => $package->id,
                    'season_id' => $season->id ?? 0,
                    'date_type_id' => $dateTypeId,
                    'room_type_id' => $validated['room_type']
                ])->first();

                // Fallback sequence
                if (!$packageConfig || $packageConfig->prices()->where('type', 'base_charge')->count() === 0) {
                    $packageConfig = PackageConfiguration::where([
                        'package_id' => $package->id,
                        'season_id' => $season->id ?? 0,
                        'date_type_id' => $oppositeDateTypeId,
                        'room_type_id' => $validated['room_type']
                    ])->first();
                }

                if (!$packageConfig || $packageConfig->prices()->where('type', 'base_charge')->count() === 0) {
                    $packageConfig = PackageConfiguration::where([
                        'package_id' => $package->id,
                        'season_id' => $season->id ?? 0,
                        'date_type_id' => $defaultDateTypeId,
                        'room_type_id' => $validated['room_type']
                    ])->first();
                }

                if (!$packageConfig || $packageConfig->prices()->where('type', 'base_charge')->count() === 0) {
                    $packageConfig = PackageConfiguration::where([
                        'package_id' => $package->id,
                        'date_type_id' => $dateTypeId,
                        'room_type_id' => $validated['room_type']
                    ])->first();
                }

                if (!$packageConfig || $packageConfig->prices()->where('type', 'base_charge')->count() === 0) {
                    $packageConfig = PackageConfiguration::where([
                        'package_id' => $package->id,
                        'date_type_id' => $defaultDateTypeId,
                        'room_type_id' => $validated['room_type']
                    ])->first();
                }

                if (!$packageConfig) {
                    return response()->json([
                        'success' => false,
                        'message' => "Package configuration not found for {$currentDate->format('Y-m-d')}. Please contact support.",
                        'breakdown' => null,
                        'total' => 0
                    ]);
                }

                // Get base charge price
                $basePackageConfigPrice = $packageConfig->prices()
                    ->where('type', 'base_charge')
                    ->where('number_of_adults', $adults)
                    ->where('number_of_children', $children)
                    ->first();

                if (!$basePackageConfigPrice) {
                    return response()->json([
                        'success' => false,
                        'message' => "Price configuration not found for {$currentDate->format('Y-m-d')} with the selected number of guests.",
                        'breakdown' => null,
                        'total' => 0
                    ]);
                }

                // Calculate base charge
                $pricePerAdult = (float) $basePackageConfigPrice->adult_price;
                $pricePerChild = (float) $basePackageConfigPrice->child_price;
                $baseAdultTotal = $pricePerAdult * $adults;
                $baseChildTotal = $pricePerChild * $children;
                $baseTotal = $baseAdultTotal + $baseChildTotal;

                // Get surcharge if applicable
                $surDateTypeIds = DateType::where('name', 'LIKE', '%sur%')->pluck('id')->toArray();
                $surDateTypeIds = DateTypeRange::where('start_date', '<=', $currentDate)
                    ->where('end_date', '>=', $currentDate)
                    ->whereIn('date_type_id', $surDateTypeIds)
                    ->orderBy('start_date')
                    ->pluck('date_type_id')
                    ->toArray();

                $surPackageConfig = null;
                if ($surDateTypeIds) {
                    $surPackageConfig = PackageConfiguration::where([
                        'package_id' => $package->id,
                        'season_id' => $season->id ?? 0,
                        'room_type_id' => $validated['room_type']
                    ])->whereIn('date_type_id', $surDateTypeIds)
                        ->orderByRaw('FIELD(date_type_id, ' . implode(',', $surDateTypeIds) . ')')
                        ->first();
                }

                $surPackageConfigPrice = $surPackageConfig ? $surPackageConfig->prices()
                    ->where('type', 'sur_charge')
                    ->where('number_of_adults', $adults)
                    ->where('number_of_children', $children)
                    ->first() : null;

                $surchargePerAdult = (float) ($surPackageConfigPrice->adult_price ?? 0);
                $surchargePerChild = (float) ($surPackageConfigPrice->child_price ?? 0);
                $surchargeAdultTotal = $surchargePerAdult * $adults;
                $surchargeChildTotal = $surchargePerChild * $children;
                $surchargeTotal = $surchargeAdultTotal + $surchargeChildTotal;

                // Calculate add-ons for this night
                $nightAddOns = [];
                $nightAddOnsTotal = 0;
                $addOns = PackageAddOn::where('package_id', $package->id)->get();

                foreach ($addOns as $addon) {
                    $adultPrice = (float) $addon->adult_price;
                    $childPrice = (float) $addon->child_price;
                    $adultTotal = $adultPrice * $adults;
                    $childTotal = $childPrice * $children;
                    $total = $adultTotal + $childTotal;

                    $nightAddOns[] = [
                        'name' => $addon->name,
                        'adult_price' => number_format($adultPrice, 2, '.', ''),
                        'adult_qty' => $adults,
                        'adult_total' => number_format($adultTotal, 2, '.', ''),
                        'child_price' => number_format($childPrice, 2, '.', ''),
                        'child_qty' => $children,
                        'child_total' => number_format($childTotal, 2, '.', ''),
                        'total' => number_format($total, 2, '.', ''),
                    ];

                    $nightAddOnsTotal += $total;
                }

                $nightTotal = $baseTotal + $surchargeTotal + $nightAddOnsTotal;

                // Store night details
                $nights[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'season' => $season ? $season->name : 'Default Season',
                    'season_type' => $season ? $season->type->name : 'Default',
                    'date_type' => $dateType,
                    'is_weekend' => $isWeekend,
                    'base_charge' => [
                        'adult' => [
                            'price' => number_format($pricePerAdult, 2, '.', ''),
                            'quantity' => $adults,
                            'total' => number_format($baseAdultTotal, 2, '.', ''),
                        ],
                        'child' => [
                            'price' => number_format($pricePerChild, 2, '.', ''),
                            'quantity' => $children,
                            'total' => number_format($baseChildTotal, 2, '.', ''),
                        ],
                        'total' => number_format($baseTotal, 2, '.', ''),
                    ],
                    'surcharge' => [
                        'adult' => [
                            'price' => number_format($surchargePerAdult, 2, '.', ''),
                            'quantity' => $adults,
                            'total' => number_format($surchargeAdultTotal, 2, '.', ''),
                        ],
                        'child' => [
                            'price' => number_format($surchargePerChild, 2, '.', ''),
                            'quantity' => $children,
                            'total' => number_format($surchargeChildTotal, 2, '.', ''),
                        ],
                        'total' => number_format($surchargeTotal, 2, '.', ''),
                    ],
                    'add_ons' => $nightAddOns,
                    'total' => number_format($nightTotal, 2, '.', ''),
                ];

                $currentDate->addDay();
            }

            // Calculate totals across all nights
            $totalBaseChargeAdult = array_sum(array_map(function($night) {
                return floatval($night['base_charge']['adult']['total']);
            }, $nights));
            $totalBaseChargeChild = array_sum(array_map(function($night) {
                return floatval($night['base_charge']['child']['total']);
            }, $nights));
            $totalBaseCharge = $totalBaseChargeAdult + $totalBaseChargeChild;

            $totalSurchargeAdult = array_sum(array_map(function($night) {
                return floatval($night['surcharge']['adult']['total']);
            }, $nights));
            $totalSurchargeChild = array_sum(array_map(function($night) {
                return floatval($night['surcharge']['child']['total']);
            }, $nights));
            $totalSurcharge = $totalSurchargeAdult + $totalSurchargeChild;

            $totalAddOnsAdult = array_sum(array_map(function($night) {
                return array_sum(array_map(function($addon) {
                    return floatval($addon['adult_total']);
                }, $night['add_ons']));
            }, $nights));
            $totalAddOnsChild = array_sum(array_map(function($night) {
                return array_sum(array_map(function($addon) {
                    return floatval($addon['child_total']);
                }, $night['add_ons']));
            }, $nights));
            $totalAddOns = $totalAddOnsAdult + $totalAddOnsChild;

            $grandTotal = array_sum(array_map(function($night) {
                return floatval($night['total']);
            }, $nights));

            return response()->json([
                'success' => true,
                'currency' => 'MYR',
                'nights' => $nights,
                'summary' => [
                    'total_nights' => count($nights),
                    'base_charges' => [
                        'adult' => [
                            'price_per_night' => number_format($nights[0]['base_charge']['adult']['price'], 2, '.', ''),
                            'quantity' => $adults,
                            'total' => number_format($totalBaseChargeAdult, 2, '.', ''),
                        ],
                        'child' => [
                            'price_per_night' => number_format($nights[0]['base_charge']['child']['price'], 2, '.', ''),
                            'quantity' => $children,
                            'total' => number_format($totalBaseChargeChild, 2, '.', ''),
                        ],
                        'total' => number_format($totalBaseCharge, 2, '.', ''),
                    ],
                    'surcharges' => [
                        'adult' => [
                            'price_per_night' => number_format($nights[0]['surcharge']['adult']['price'], 2, '.', ''),
                            'quantity' => $adults,
                            'total' => number_format($totalSurchargeAdult, 2, '.', ''),
                        ],
                        'child' => [
                            'price_per_night' => number_format($nights[0]['surcharge']['child']['price'], 2, '.', ''),
                            'quantity' => $children,
                            'total' => number_format($totalSurchargeChild, 2, '.', ''),
                        ],
                        'total' => number_format($totalSurcharge, 2, '.', ''),
                    ],
                    'add_ons' => [
                        'adult' => [
                            'total' => number_format($totalAddOnsAdult, 2, '.', ''),
                        ],
                        'child' => [
                            'total' => number_format($totalAddOnsChild, 2, '.', ''),
                        ],
                        'total' => number_format($totalAddOns, 2, '.', ''),
                    ],
                    'grand_total' => number_format($grandTotal, 2, '.', ''),
                ],
                'total' => number_format($grandTotal, 2, '.', ''),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while calculating the price: ' . $e->getMessage(),
                'breakdown' => null,
                'total' => 0
            ], 500);
        }
    }
}
