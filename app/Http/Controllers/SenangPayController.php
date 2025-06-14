<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Transaction;

class SenangPayController extends Controller
{
    public function handleReturn(Request $request)
    {
        $expectedHash = hash_hmac(
            'sha256',
            config('senangpay.secret_key') .
                $request->status_id .
                $request->order_id .
                $request->transaction_id .
                $request->msg,
            config('senangpay.secret_key')
        );

        if ($expectedHash !== $request->hash) {
            abort(403, 'Hash mismatch. Data may be tampered.');
        }

        $booking = Booking::where('uuid', $request->order_id)->first();
        if (!$booking) return redirect('/')->with('error', 'Booking not found');

        // Update transaction and booking status here...

        return redirect()->route(
            $request->status_id == 1 ? 'api.payment.success' : 'api.payment.failed',
            $booking->uuid
        );
    }

    public function handleCallback(Request $request)
    {
        // 1. Validate hash
        $expectedHash = hash_hmac(
            'sha256',
            config('senangpay.secret_key') .
                $request->status_id .
                $request->order_id .
                $request->transaction_id .
                $request->msg,
            config('senangpay.secret_key')
        );

        if ($expectedHash !== $request->hash) {
            return response('Hash mismatch', 403);
        }

        // 2. Find booking
        $booking = Booking::where('uuid', $request->order_id)->first();
        if (!$booking) return response('Booking not found', 404);

        // 3. Update transaction
        $transaction = Transaction::where('booking_id', $booking->id)->latest()->first();
        $status = $request->status_id == '1' ? 'paid' : 'failed';

        $transaction->update([
            'status' => $status,
            'transaction_id' => $request->transaction_id,
            'payment_method' => 'senangpay',
            'payment_details' => json_encode($request->all())
        ]);

        // 4. Update booking
        $booking->update(['payment_status' => $status]);

        return response('OK');
    }
}
