<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DateType;
use App\Models\DateTypeRange;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\PackageAddOn;
use App\Models\PackageConfiguration;
use App\Models\Season;
use App\Models\SeasonType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            'room_type' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1|max:4',
            'children' => 'required|integer|min:0|max:3'
        ]);

        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $adults = $validated['adults'];
            $children = $validated['children'];
            $packageId = $validated['package_id'];
            $roomTypeId = $validated['room_type'];

            $nights = [];

            for ($date = $startDate->copy(); $date->lt($endDate); $date->addDay()) {
                $dateTypeRange = DateTypeRange::where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('package_id', $packageId)
                    ->first();

                if (!$dateTypeRange) {
                    $fallbackType = $date->isWeekend() ? 'weekend' : 'weekday';
                    $dateType = DateType::where('name', 'LIKE', "%$fallbackType%")->first();
                }
                else{
                    $dateType = $dateTypeRange->dateType;
                }

                if (!$dateType) {
                    throw new \Exception('Date type not found for ' . $date->format('Y-m-d'));
                }

                $season = Season::where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('package_id', $packageId)
                    ->first();

                if (!$season) {
                    $seasonType = SeasonType::where('name', 'Default')->first();
                }
                else{
                    $seasonType = $season->type;
                }

                if (!$seasonType) {
                    throw new \Exception('Season type not found for ' . $date->format('Y-m-d'));
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

                $keyAdult = "{$adults}_a_{$children}_c_a";
                $keyChild = "{$adults}_a_{$children}_c_c";

                $baseAdult = $prices['b'][$keyAdult] ?? 0;
                $baseChild = $prices['b'][$keyChild] ?? 0;
                $surAdult = $prices['s'][$keyAdult] ?? 0;
                $surChild = $prices['s'][$keyChild] ?? 0;

                $baseTotal = $baseAdult * $adults + $baseChild * $children;
                $surTotal = $surAdult * $adults + $surChild * $children;
                $nightTotal = $baseTotal + $surTotal;

                $nights[] = [
                    'date' => $date->format('Y-m-d'),
                    'season' => $seasonType->name,
                    'season_type' => $seasonType->name,
                    'date_type' => $dateType->name,
                    'is_weekend' => $date->isWeekend(),
                    'base_charge' => [
                        'adult' => ['price' => $baseAdult, 'quantity' => $adults, 'total' => $baseAdult * $adults],
                        'child' => ['price' => $baseChild, 'quantity' => $children, 'total' => $baseChild * $children],
                        'total' => $baseTotal,
                    ],
                    'surcharge' => [
                        'adult' => ['price' => $surAdult, 'quantity' => $adults, 'total' => $surAdult * $adults],
                        'child' => ['price' => $surChild, 'quantity' => $children, 'total' => $surChild * $children],
                        'total' => $surTotal,
                    ],
                    'total' => $nightTotal,
                ];
            }

            $sum = fn($key) => array_sum(array_map(fn($night) => floatval(data_get($night, $key)), $nights));

            return response()->json([
                'success' => true,
                'currency' => 'MYR',
                'nights' => $nights,
                'summary' => [
                    'total_nights' => count($nights),
                    'base_charges' => [
                        'adult' => [
                            'price_per_night' => $nights[0]['base_charge']['adult']['price'],
                            'quantity' => $adults,
                            'total' => $sum('base_charge.adult.total')
                        ],
                        'child' => [
                            'price_per_night' => $nights[0]['base_charge']['child']['price'],
                            'quantity' => $children,
                            'total' => $sum('base_charge.child.total')
                        ],
                        'total' => $sum('base_charge.total'),
                    ],
                    'surcharges' => [
                        'adult' => [
                            'price_per_night' => $nights[0]['surcharge']['adult']['price'],
                            'quantity' => $adults,
                            'total' => $sum('surcharge.adult.total')
                        ],
                        'child' => [
                            'price_per_night' => $nights[0]['surcharge']['child']['price'],
                            'quantity' => $children,
                            'total' => $sum('surcharge.child.total')
                        ],
                        'total' => $sum('surcharge.total'),
                    ],
                    'grand_total' => $sum('total'),
                ],
                'total' => $sum('total'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'breakdown' => null,
                'total' => 0
            ], 500);
        }
    }
}