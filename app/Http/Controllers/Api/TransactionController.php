<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
