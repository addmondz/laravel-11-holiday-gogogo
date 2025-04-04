<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\PackageAddOn;
use Carbon\Carbon;

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
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $selectedAddOns = PackageAddOn::whereIn('id', $validated['add_on_ids'] ?? [])->get();

        $adults = $validated['adults'];
        $children = $validated['children'];

        // === Base Charge ===
        $pricePerAdult = (float) $package->display_price_adult;
        $pricePerChild = (float) $package->display_price_child;
        $baseAdultTotal = $pricePerAdult * $adults;
        $baseChildTotal = $pricePerChild * $children;
        $baseTotal = $baseAdultTotal + $baseChildTotal;

        // === Surcharge (Weekend: fixed per person) ===
        $travelDate = Carbon::parse($validated['travel_date']);
        $isWeekend = in_array($travelDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

        $surchargePerAdult = $isWeekend ? 20 : 0;
        $surchargePerChild = $isWeekend ? 10 : 0;
        $surchargeAdultTotal = $surchargePerAdult * $adults;
        $surchargeChildTotal = $surchargePerChild * $children;
        $surchargeTotal = $surchargeAdultTotal + $surchargeChildTotal;

        // === Extra Charges ===
        $extraPerAdult = 50;
        $extraPerChild = 20;
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
}
