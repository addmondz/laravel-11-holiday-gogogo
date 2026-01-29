<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAddOn;
use App\Services\GenerateBookingUid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|exists:packages,id',
                'rooms' => 'required|array|min:1',
                'rooms.*.room_type_id' => 'required|exists:room_types,id',
                'rooms.*.adults' => 'required|integer|min:1|max:4',
                'rooms.*.children' => 'required|integer|min:0|max:4',
                'rooms.*.infants' => 'required|integer|min:0|max:4',
                'rooms.*.children_dob' => 'array',
                'rooms.*.children_dob.*' => 'required|integer|min:1900|max:' . date('Y'),
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'booking_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'booking_ic' => 'string|max:20|nullable',
                'total_price' => 'required|numeric|min:0',
                'special_remarks' => 'nullable|string',
                'add_ons' => 'nullable|array',
                'add_ons.*.id' => 'required|exists:package_add_ons,id',
                'add_ons.*.room_number' => 'nullable|integer|min:1',
                'add_ons.*.adults' => 'required|integer|min:0',
                'add_ons.*.children' => 'required|integer|min:0',
                'add_ons.*.infants' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate children_dob array size matches children count
            foreach ($request->rooms as $index => $room) {
                if ($room['children'] > 0) {
                    $dobCount = count($room['children_dob'] ?? []);
                    if ($dobCount !== $room['children']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Room " . ($index + 1) . ": Date of birth required for all {$room['children']} children (received {$dobCount})"
                        ], 422);
                    }
                }
            }

            DB::beginTransaction();

            try {
                $totalAdults = 0;
                $totalChildren = 0;
                $totalInfants = 0;
                foreach ($request->rooms as $room) {
                    $totalAdults += $room['adults'];
                    $totalChildren += $room['children'];
                    $totalInfants += $room['infants'];
                }

                // Create the booking
                $booking = Booking::create([
                    'package_id' => $request->package_id,
                    'booking_name' => $request->booking_name,
                    'phone_number' => $request->phone_number,
                    'booking_ic' => $request->booking_ic,
                    'booking_email' => $request->booking_email,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_price' => floor($request->total_price),
                    'special_remarks' => $request->special_remarks,
                    // 'status' => 'pending',
                    'adults' => $totalAdults,
                    'children' => $totalChildren,
                    'infants' => $totalInfants,
                    'uuid' => (new GenerateBookingUid())->execute()
                ]);

                // Create booking rooms
                foreach ($request->rooms as $room) {
                    $bookingRoom = $booking->rooms()->create([
                        'room_type_id' => $room['room_type_id'],
                        'adults' => $room['adults'],
                        'children' => $room['children'],
                        'infants' => $room['infants']
                    ]);

                    // Create booking children with DOB
                    if (!empty($room['children_dob'])) {
                        foreach ($room['children_dob'] as $index => $dob) {
                            $bookingRoom->children()->create([
                                'child_number' => $index + 1,
                                'birth_year' => $dob
                            ]);
                        }
                    }
                }

                // Create booking add-ons
                if (!empty($request->add_ons)) {
                    foreach ($request->add_ons as $addOn) {
                        $booking->addOns()->create([
                            'package_add_on_id' => $addOn['id'],
                            'room_number' => $addOn['room_number'] ?? null,
                            'adults' => $addOn['adults'] ?? 0,
                            'children' => $addOn['children'] ?? 0,
                            'infants' => $addOn['infants'] ?? 0
                        ]);
                    }
                }

                DB::commit();

                $booking = Booking::with(['rooms.roomType', 'addOns.packageAddOn'])->find($booking->id);

                return response()->json([
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'booking' => $booking
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
