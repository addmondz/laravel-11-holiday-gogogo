<?php

namespace App\Http\Controllers\Api;

use App\Constants\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'amount' => 'required|numeric|min:0',
                'status' => 'required|string'
            ]);

            $booking = Booking::find($validated['booking_id']);
            if ($booking->status >= ApprovalStatus::PAYMENT_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking already paid'
                ], 400);
            }

            $transaction = Transaction::create([
                'booking_id' => $validated['booking_id'],
                'amount' => $validated['amount'],
                'status' => $validated['status'],
                'payment_method' => 'stripe', // default payment method
            ]);

            return response()->json([
                'success' => true,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPaymentHistory(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $status = $request->get('status'); // optional filter by status
            
            $query = Transaction::with(['booking.package', 'booking.rooms.roomType'])
                ->orderBy('created_at', 'desc');
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $transactions = $query->limit($limit)->get();
            
            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
