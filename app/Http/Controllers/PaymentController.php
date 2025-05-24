<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
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

    public function handlePayment(Request $request, $uuid)
    {
        $booking = Booking::where('uuid', $uuid)->first();
        
        // Validate request
        $validated = $request->validate([
            'payment_method' => 'nullable|string|in:credit_card,bank_transfer',
            'status' => 'nullable|in:success,failed',
            'transaction_id' => 'required|exists:transactions,id',
            'is_simulation' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($validated['transaction_id']);

            // Handle payment based on whether it's a simulation or real payment
            if ($validated['is_simulation'] ?? false) {
                // This is a simulation
                $transaction->update([
                    'status' => $validated['status'],
                    'payment_method' => 'simulation',
                    'transaction_id' => 'SIM_' . time(),
                    'payment_details' => json_encode([
                        'payment_time' => now(),
                        'payment_method' => 'simulation',
                        'simulation_status' => $validated['status']
                    ])
                ]);
            } else {
                // This is a real payment
                // Here you would integrate with your payment gateway
                $paymentSuccess = $this->processPaymentWithGateway($transaction);

                if (!$paymentSuccess) {
                    throw new \Exception('Payment processing failed');
                }

                $transaction->update([
                    'status' => 'paid',
                    'payment_method' => $validated['payment_method'] ?? 'credit_card',
                    'transaction_id' => 'TRANS_' . time(), // Replace with actual transaction ID from payment gateway
                    'payment_details' => json_encode([
                        'payment_time' => now(),
                        'payment_method' => $validated['payment_method'] ?? 'credit_card'
                    ])
                ]);
            }

            // Update booking payment status if payment was successful
            if ($transaction->status === 'paid' || $transaction->status === 'success') {
                $booking->update(['payment_status' => 'paid']);
            }

            DB::commit();

            // Return appropriate response based on payment type
            if ($validated['is_simulation'] ?? false) {
                return response()->json([
                    'success' => true,
                    'transaction' => $transaction
                ]);
            } else {
                return redirect()->route('bookings.payment.success', $booking);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($validated['is_simulation'] ?? false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment simulation failed: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->route('bookings.payment.failed', $booking)
                    ->with('error', 'Payment failed: ' . $e->getMessage());
            }
        }
    }

    public function success($uuid)
    {
        $booking = Booking::where('uuid', $uuid)->first();
        return Inertia::render('Payments/Success', [
            'booking' => $booking
        ]);
    }

    public function failed($uuid)
    {
        $booking = Booking::where('uuid', $uuid)->first();
        return Inertia::render('Payments/Failed', [
            'booking' => $booking
        ]);
    }

    private function processPaymentWithGateway($transaction)
    {
        // This is a placeholder for payment gateway integration
        // In a real application, you would integrate with Stripe, PayPal, etc.
        return true;
    }
}
