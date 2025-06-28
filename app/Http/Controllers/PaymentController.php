<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['booking.package', 'booking.rooms.roomType']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                        $bookingQuery->where('booking_name', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%")
                            ->orWhere('uuid', 'like', "%{$search}%");
                    })
                    ->orWhereHas('booking.package', function ($packageQuery) use ($search) {
                        $packageQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Date range filter
        $query->when(
            $request->filled('date_from'),
            fn($q) =>
            $q->whereDate('created_at', '>=', $request->date_from)
        )->when(
            $request->filled('date_to'),
            fn($q) =>
            $q->whereDate('created_at', '<=', $request->date_to)
        );

        // Sort functionality
        $sortField = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $transactions = $query->paginate(10)->withQueryString();

        // Get summary statistics
        $summary = [
            'total' => Transaction::count(),
            'completed' => Transaction::where('status', 'completed')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'total_amount' => Transaction::where('status', 'completed')->sum('amount'),
        ];

        return Inertia::render('Payments/Index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort', 'direction'])
        ]);
    }

    public function show($uuid)
    {
        $booking = Booking::where('uuid', $uuid)->first();
        // Retrieve or create the latest pending transaction for this booking
        $transaction = Transaction::where('booking_id', $booking->id)
            ->whereIn('status', ['in progress', 'pending'])
            ->latest()
            ->first();

        return Inertia::render('Payments/Show', [
            'booking' => $booking,
            'transaction' => $transaction
        ]);
    }

}
