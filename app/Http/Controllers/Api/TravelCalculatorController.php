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

        // === Extra Charges ===
        $extPackageConfig = PackageConfiguration::where([
            'package_id' => $package->id,
            'season_id' => $season->id ?? 0,
            'date_type_id' => $dateTypeId,
            'room_type' => $validated['room_type']
        ])->first();

        // fallback to weekday if weekend package not found
        if ($isWeekend && (!$extPackageConfig || $extPackageConfig->prices()->where('type', 'ext_charge')->count() === 0)) {
            $extPackageConfig = PackageConfiguration::where([
                'package_id' => $package->id,
                'season_id' => $season->id ?? 0,
                'date_type_id' => $oppositeDateTypeId,
                'room_type' => $validated['room_type']
            ])->first();
        }

        $extPackageConfigPrice = $extPackageConfig->prices()->where('type', 'ext_charge')->where('number_of_adults', $adults)->where('number_of_children', $children)->first();
        $extraPerAdult = (float) ($extPackageConfigPrice->adult_price ?? 0);
        $extraPerChild = (float) ($extPackageConfigPrice->child_price ?? 0);
        $extraAdultTotal = $extraPerAdult * $adults;
        $extraChildTotal = $extraPerChild * $children;
        $extraTotal = $extraAdultTotal + $extraChildTotal;

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

        $total = $baseTotal + $surchargeTotal + $extraTotal + $userAddOnsTotal;

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
                'extra_charges' => [
                    'per_adult' => number_format($extraPerAdult, 2, '.', ''),
                    'adult_qty' => $adults,
                    'adult_total' => number_format($extraAdultTotal, 2, '.', ''),
                    'per_child' => number_format($extraPerChild, 2, '.', ''),
                    'child_qty' => $children,
                    'child_total' => number_format($extraChildTotal, 2, '.', ''),
                    'total' => number_format($extraTotal, 2, '.', ''),
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

        $package = Package::findOrFail($validated['package_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $numberOfNights = $startDate->diffInDays($endDate);
        $adults = $validated['adults'];
        $children = $validated['children'];

        // Get the season for the travel dates
        $season = Season::where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $startDate)
            ->orderBy('priority')
            ->first();

        // If no season found, get the default season
        if (!$season) {
            $season = Season::whereHas('type', function ($query) {
                $query->where('name', 'Default');
            })->first();
        }

        // Determine if it's a weekend
        $isWeekend = $startDate->isWeekend();
        $dateType = $isWeekend ? 'weekend' : 'weekday';
        $opposite = [
            'weekend' => 'weekday',
            'weekday' => 'weekend'
        ];
        $oppositeDateType = $opposite[$dateType];
        $dateTypeId = DateType::where('name', 'LIKE', "%{$dateType}%")->first()->id ?? null;
        $oppositeDateTypeId = DateType::where('name', 'LIKE', "%{$oppositeDateType}%")->first()->id ?? null;

        // Get package configuration
        $packageConfig = PackageConfiguration::where([
            'package_id' => $package->id,
            'season_id' => $season->id ?? 0,
            'date_type_id' => $dateTypeId,
            'room_type_id' => $validated['room_type']
        ])->first();

        // Fallback to weekday if weekend package not found
        if ($isWeekend && (!$packageConfig || $packageConfig->prices()->where('type', 'base_charge')->count() === 0)) {
            $packageConfig = PackageConfiguration::where([
                'package_id' => $package->id,
                'season_id' => $season->id ?? 0,
                'date_type_id' => $oppositeDateTypeId,
                'room_type_id' => $validated['room_type']
            ])->first();
        }

        if (!$packageConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Package configuration not found for the selected dates and room type.'
            ]);
        }

        // === Base Charge ===
        $basePackageConfigPrice = $packageConfig->prices()->where('type', 'base_charge')
            ->where('number_of_adults', $adults)
            ->where('number_of_children', $children)
            ->first();

        if (!$basePackageConfigPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Price configuration not found for the selected number of guests.'
            ]);
        }

        $pricePerAdult = (float) $basePackageConfigPrice->adult_price;
        $pricePerChild = (float) $basePackageConfigPrice->child_price;
        $baseAdultTotal = $pricePerAdult * $adults * $numberOfNights;
        $baseChildTotal = $pricePerChild * $children * $numberOfNights;
        $baseTotal = $baseAdultTotal + $baseChildTotal;

        // === Surcharge (Weekend: fixed per person) ===
        $surDateTypeIds = DateType::where('name', 'LIKE', '%sur%')->pluck('id')->toArray();
        $surDateTypeIds = DateTypeRange::where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $startDate)
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
        $surchargeAdultTotal = $surchargePerAdult * $adults * $numberOfNights;
        $surchargeChildTotal = $surchargePerChild * $children * $numberOfNights;
        $surchargeTotal = $surchargeAdultTotal + $surchargeChildTotal;

        // === Extra Charges ===
        $extPackageConfig = PackageConfiguration::where([
            'package_id' => $package->id,
            'season_id' => $season->id ?? 0,
            'date_type_id' => $dateTypeId,
            'room_type_id' => $validated['room_type']
        ])->first();

        // Fallback to weekday if weekend package not found
        if ($isWeekend && (!$extPackageConfig || $extPackageConfig->prices()->where('type', 'ext_charge')->count() === 0)) {
            $extPackageConfig = PackageConfiguration::where([
                'package_id' => $package->id,
                'season_id' => $season->id ?? 0,
                'date_type_id' => $oppositeDateTypeId,
                'room_type_id' => $validated['room_type']
            ])->first();
        }

        $extPackageConfigPrice = $extPackageConfig->prices()
            ->where('type', 'ext_charge')
            ->where('number_of_adults', $adults)
            ->where('number_of_children', $children)
            ->first();

        $extraPerAdult = (float) ($extPackageConfigPrice->adult_price ?? 0);
        $extraPerChild = (float) ($extPackageConfigPrice->child_price ?? 0);
        $extraAdultTotal = $extraPerAdult * $adults * $numberOfNights;
        $extraChildTotal = $extraPerChild * $children * $numberOfNights;
        $extraTotal = $extraAdultTotal + $extraChildTotal;

        // === Add-ons ===
        $userAddOns = [];
        $userAddOnsTotal = 0;

        // Get all add-ons for this package
        $addOns = PackageAddOn::where('package_id', $package->id)->get();

        foreach ($addOns as $addon) {
            $adultPrice = (float) $addon->adult_price;
            $childPrice = (float) $addon->child_price;
            $adultTotal = $adultPrice * $adults * $numberOfNights;
            $childTotal = $childPrice * $children * $numberOfNights;
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

        $total = $baseTotal + $surchargeTotal + $extraTotal + $userAddOnsTotal;

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
                'extra_charges' => [
                    'per_adult' => number_format($extraPerAdult, 2, '.', ''),
                    'adult_qty' => $adults,
                    'adult_total' => number_format($extraAdultTotal, 2, '.', ''),
                    'per_child' => number_format($extraPerChild, 2, '.', ''),
                    'child_qty' => $children,
                    'child_total' => number_format($extraChildTotal, 2, '.', ''),
                    'total' => number_format($extraTotal, 2, '.', ''),
                ],
                'add_ons' => [
                    'items' => $userAddOns,
                    'total' => number_format($userAddOnsTotal, 2, '.', ''),
                ],
            ],
            'total' => number_format($total, 2, '.', ''),
        ]);
    }
}
