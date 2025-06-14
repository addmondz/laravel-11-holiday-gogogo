<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SenangPayController extends Controller
{

    public function form()
    {
        return view('senangpay.form');
    }

    public function process(Request $request)
    {
        $request->validate([
            'detail' => 'required|string',
            'amount' => 'required|numeric',
            'order_id' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $merchant_id = config('senangpay.merchant_id');
        $secret_key = config('senangpay.secret_key');

        $hash = hash_hmac(
            'sha256',
            $secret_key . urldecode($request->detail) . urldecode($request->amount) . urldecode($request->order_id),
            $secret_key
        );

        $senangpay_url = config('senangpay.base_url');

        return view('senangpay.redirect', [
            'merchant_id' => $merchant_id,
            'data' => $request->all(),
            'hash' => $hash,
            'senangpay_url' => $senangpay_url
        ]);
    }

    public function handleReturn(Request $request)
    {
        Log::info('handleReturn: ' . json_encode($request->all()));
        return 'Payment successful';

        // $status_id = $request->get('status_id');
        // $order_id = $request->get('order_id');
        // $msg = $request->get('msg');
        // $transaction_id = $request->get('transaction_id');
        // $hash = $request->get('hash');

        // $calculated_hash = hash_hmac(
        //     'sha256',
        //     $this->secret_key . urldecode($status_id) . urldecode($order_id) . urldecode($transaction_id) . urldecode($msg),
        //     $this->secret_key
        // );

        // if ($hash === $calculated_hash) {
        //     return $status_id == '1'
        //         ? '✅ Payment successful: ' . $msg
        //         : '❌ Payment failed: ' . $msg;
        // }

        // return '⚠️ Invalid hash received.';
    }

    public function handleCallback(Request $request)
    {
        Log::info('handleCallback: ' . json_encode($request->all()));
        // $status_id = $request->get('status_id');
        // $order_id = $request->get('order_id');
        // $msg = $request->get('msg');
        // $transaction_id = $request->get('transaction_id');
        // $hash = $request->get('hash');

        // $calculated_hash = hash_hmac(
        //     'sha256',
        //     $this->secret_key . urldecode($status_id) . urldecode($order_id) . urldecode($transaction_id) . urldecode($msg),
        //     $this->secret_key
        // );

        // if ($hash === $calculated_hash) {
        //     // ✅ Update DB record for $order_id
        //     // e.g. mark order as paid
        //     Log::info("Callback received: $order_id is now " . ($status_id == '1' ? 'PAID' : 'FAILED'));
        // } else {
        //     Log::warning("Callback hash mismatch for order $order_id");
        // }

        return response('OK', 200);
    }
}
