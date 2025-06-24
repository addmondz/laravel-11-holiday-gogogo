<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['package', 'roomType']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('booking_ic', 'like', "%{$search}%")
                    ->orWhere('booking_email', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        // Sort functionality
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $bookings = $query->paginate(10)->withQueryString();

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'sort', 'direction'])
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['package', 'rooms', 'transactions', 'rooms.roomType']);

        return Inertia::render('Bookings/Show', [
            'booking' => $booking
        ]);
    }

    public function edit(Booking $booking)
    {
        $booking->load(['package', 'roomType']);

        return Inertia::render('Bookings/Edit', [
            'booking' => $booking
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'booking_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'booking_ic' => 'required|string|max:50',
            'special_remarks' => 'nullable|string',
            'booking_email' => 'required|email|max:255'
        ]);

        try {
            $booking->update([
                'booking_name' => $validated['booking_name'],
                'phone_number' => $validated['phone_number'],
                'booking_ic' => $validated['booking_ic'],
                'special_remarks' => $validated['special_remarks']
            ]);

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update booking: ' . $e->getMessage());
        }
    }
}
