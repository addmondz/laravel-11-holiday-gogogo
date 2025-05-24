<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function show(Booking $booking)
    {
        // Get the latest pending transaction for this booking
        $transaction = Transaction::where('booking_id', $booking->id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        return Inertia::render('Payments/Show', [
            'booking' => $booking,
            'transaction' => $transaction
        ]);
    }

    public function process(Request $request, Booking $booking)
    {
        // Validate request
        $request->validate([
            'payment_method' => 'required|string|in:credit_card,bank_transfer',
        ]);

        try {
            DB::beginTransaction();

            // Create transaction record
            $transaction = Transaction::create([
                'booking_id' => $booking->id,
                'payment_method' => $request->payment_method,
                'amount' => $booking->total_price,
                'status' => 'pending'
            ]);

            // Here you would integrate with your payment gateway
            // For example, with Stripe, PayPal, etc.
            $paymentSuccess = $this->processPaymentWithGateway($transaction);

            if ($paymentSuccess) {
                $transaction->update([
                    'status' => 'paid',
                    'transaction_id' => 'TRANS_' . time(), // Replace with actual transaction ID from payment gateway
                    'payment_details' => json_encode([
                        'payment_time' => now(),
                        'payment_method' => $request->payment_method
                    ])
                ]);

                // Update booking payment status
                $booking->update(['payment_status' => 'paid']);

                DB::commit();

                return redirect()->route('bookings.payment.success', $booking);
            } else {
                throw new \Exception('Payment processing failed');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('bookings.payment.failed', $booking)
                ->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    public function simulate(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:success,failed',
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        $transaction = Transaction::findOrFail($validated['transaction_id']);
        $transaction->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'transaction' => $transaction
        ]);
    }

    public function success(Booking $booking)
    {
        return Inertia::render('Payments/Success', [
            'booking' => $booking
        ]);
    }

    public function failed(Booking $booking)
    {
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