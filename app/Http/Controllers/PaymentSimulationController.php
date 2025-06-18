<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentSimulationController extends Controller
{
    public function show(Transaction $transaction)
    {
        // Load the transaction with its relationships
        $transaction->load('booking');

        return Inertia::render('Payments/PaymentSimulation', [
            'transaction' => $transaction
        ]);
    }

    public function showTestPayment()
    {
        return Inertia::render('Payments/TestPayment');
    }


    public function createTestPaymentTransaction(Request $request)
    {
        $merchant_id = config('senangpay.merchant_id'); // e.g., '839174991356979'
        $secret_key = config('senangpay.secret_key');   // e.g., 'SK-H2wRDRAd5oO0BlwDsBfG'
        $base_url = config('senangpay.base_url');       // e.g., 'https://sandbox.senangpay.my/payment'

        // Input values
        $detail = $request->detail;
        $amount = $request->amount;
        $order_id = $request->order_id;
        $customer_name = $request->customer_name;
        $customer_email = $request->customer_email;
        $customer_contact = $request->customer_contact;

        Log::info('SENANGPAY_SANDBOX: ' . config('senangpay.sandbox'));
        // Log inputs
        Log::info('--- SenangPay Hash Generation Inputs ---' . json_encode([
            'detail' => $detail,
            'amount' => $amount,
            'order_id' => $order_id,
            'secret_key (partial)' => substr($secret_key, 0, 5) . '***',
        ], JSON_PRETTY_PRINT));

        // Generate hash
        $hash_input = $secret_key . urldecode($detail) . urldecode($amount) . urldecode($order_id);

        Log::info('Hash input string:', ['hash_input' => $hash_input]);

        $hash = hash_hmac('sha256', $hash_input, $secret_key);

        Log::info('Generated hash:', ['hash' => $hash]);

        // Build URL
        $payment_url = "{$base_url}/{$merchant_id}?" . http_build_query([
            'detail' => $detail,
            'amount' => $amount,
            'order_id' => $order_id,
            'name' => $customer_name,
            'email' => $customer_email,
            'phone' => $customer_contact,
            'hash' => $hash,
        ]);

        Log::info('Final payment URL:', ['url' => $payment_url]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment URL generated.',
            'url' => $payment_url,
            'hash' => $hash,
        ]);
    }
}
