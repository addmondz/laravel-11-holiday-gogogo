<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Transaction;

class SenangPayController extends Controller
{
    public function handleReturn(Request $request)
    {
        // You can redirect to your success/fail pages based on $request->status
        $booking = Booking::where('uuid', $request->order_id)->first();

        return redirect()->route(
            $request->status == '1' ? 'api.payment.success' : 'api.payment.failed',
            $booking->uuid
        );
    }

    public function handleCallback(Request $request)
    {
        $booking = Booking::where('uuid', $request->order_id)->first();
        if (!$booking) return response('Booking not found', 404);

        $transaction = Transaction::where('booking_id', $booking->id)->latest()->first();

        $status = $request->status == '1' ? 'paid' : 'failed';

        $transaction->update([
            'status' => $status,
            'transaction_id' => $request->transaction_id ?? null,
            'payment_method' => 'senangpay',
            'payment_details' => json_encode($request->all())
        ]);

        $booking->update(['payment_status' => $status]);

        return response('OK');
    }
}
