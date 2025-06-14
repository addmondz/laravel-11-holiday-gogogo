<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class PaymentController extends Controller
{
    public function show($uuid)
    {
        $booking = Booking::where('uuid', $uuid)->firstOrFail();
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
        $booking = Booking::where('uuid', $uuid)->firstOrFail();

        // Create or get pending transaction
        $transaction = Transaction::firstOrCreate(
            ['booking_id' => $booking->id, 'status' => 'pending'],
            ['amount' => $booking->total_price]
        );

        // Generate senangPay URL
        $detail = 'Booking #' . $booking->id;
        $amount = number_format($transaction->amount, 2, '.', '');
        $order_id = $booking->uuid;
        $secret = config('senangpay.secret_key');
        $merchant_id = config('senangpay.merchant_id');

        $hash = hash('sha256', $secret . $detail . $amount . $order_id);

        $url = config('senangpay.base_url') . "{$merchant_id}?detail=" . urlencode($detail) .
            "&amount={$amount}&order_id={$order_id}&hash={$hash}";

        // ✅ Return the URL to be used by frontend for redirection
        return response()->json([
            'success' => true,
            'redirect_url' => $url
        ]);
    }

    public function handleSenangPayCallback(Request $request)
    {
        $booking = Booking::where('uuid', $request->order_id)->first();

        if (!$booking) return response('Booking not found', 404);

        $transaction = Transaction::where('booking_id', $booking->id)->latest()->first();

        $statusText = $request->status == '1' ? 'paid' : 'failed';

        $transaction->update([
            'status' => $statusText,
            'payment_method' => 'senangpay',
            'transaction_id' => $request->transaction_id ?? null,
            'payment_details' => json_encode($request->all())
        ]);

        $booking->update([
            'payment_status' => $statusText
        ]);

        return response('OK');
    }

    public function success($uuid)
    {
        $booking = Booking::where('uuid', $uuid)->firstOrFail();
        return Inertia::render('Payments/Success', [
            'bookingUuid' => $booking->uuid,
            'packageUuid' => $booking->package->uuid,
        ]);
    }

    public function failed($uuid)
    {
        $booking = Booking::where('uuid', $uuid)->firstOrFail();
        return Inertia::render('Payments/Failed', [
            'booking' => $booking
        ]);
    }
}
