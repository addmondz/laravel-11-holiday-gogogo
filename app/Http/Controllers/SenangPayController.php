<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SenangPayController extends Controller
{
    private $merchant_id = '839174991356979';
    private $secret_key = 'SK-H2wRDRAd5oO0BlwDsBfG';

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

        $hash = hash_hmac(
            'sha256',
            $this->secret_key . urldecode($request->detail) . urldecode($request->amount) . urldecode($request->order_id),
            $this->secret_key
        );

        return view('senangpay.redirect', [
            'merchant_id' => $this->merchant_id,
            'data' => $request->all(),
            'hash' => $hash
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
