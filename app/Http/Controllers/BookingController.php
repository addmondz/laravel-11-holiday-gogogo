<?php

namespace App\Http\Controllers;

use App\Constants\ApprovalStatus;
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

        // Status filtering
        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status;
            $query->where('status', $status);
        }

        // Date range filtering - Find bookings for specific travel dates
        if ($request->filled('dateFrom') && $request->filled('dateTo')) {
            $dateFrom = $request->dateFrom;
            $dateTo = $request->dateTo;
            
            // Find bookings where travel dates overlap with the specified range
            $query->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where('start_date', '<=', $dateTo)
                  ->where('end_date', '>=', $dateFrom);
            });
        } elseif ($request->filled('dateFrom')) {
            // If only start date is provided, find bookings that end after or on the start date
            $query->where('end_date', '>=', $request->dateFrom);
        } elseif ($request->filled('dateTo')) {
            // If only end date is provided, find bookings that start before or on the end date
            $query->where('start_date', '<=', $request->dateTo);
        }

        // Sort functionality
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $bookings = $query->paginate(10)->withQueryString();

        // Calculate summary statistics based on filtered results
        $filteredQuery = Booking::with(['package', 'roomType']);

        // Apply the same filters to summary calculation
        if ($request->has('search')) {
            $search = $request->search;
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('booking_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('booking_ic', 'like', "%{$search}%")
                    ->orWhere('booking_email', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('dateFrom') && $request->filled('dateTo')) {
            $dateFrom = $request->dateFrom;
            $dateTo = $request->dateTo;
            
            $filteredQuery->where(function ($q) use ($dateFrom, $dateTo) {
                $q->where('start_date', '<=', $dateTo)
                  ->where('end_date', '>=', $dateFrom);
            });
        } elseif ($request->filled('dateFrom')) {
            $filteredQuery->where('end_date', '>=', $request->dateFrom);
        } elseif ($request->filled('dateTo')) {
            $filteredQuery->where('start_date', '<=', $request->dateTo);
        }

        $summary = [
            'total' => $filteredQuery->count(),
            'pending' => (clone $filteredQuery)->where('status', 0)->count(),
            'approved' => (clone $filteredQuery)->where('status', 1)->count(),
            'rejected' => (clone $filteredQuery)->where('status', 2)->count(),
            'payment_completed' => (clone $filteredQuery)->where('status', 3)->count(),
            'refunded' => (clone $filteredQuery)->where('status', 4)->count(),
        ];

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status', 'sort', 'direction', 'dateFrom', 'dateTo']),
            'summary' => $summary
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['package', 'rooms', 'transactions', 'rooms.roomType', 'approver']);

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
            'booking_ic' => 'string|max:50',
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

    public function approve(Request $request, Booking $booking)
    {
        try {
            $booking->update([
                'approval_status' => 'approved',
                'approval_by' => $request->user()->id,
                'approval_date' => now(),
                'status' => ApprovalStatus::APPROVED
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking approved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve booking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        try {
            $booking->update([
                'approval_status' => 'rejected',
                'approval_by' => $request->user()->id,
                'approval_date' => now(),
                'status' => ApprovalStatus::REJECTED
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking rejected successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
