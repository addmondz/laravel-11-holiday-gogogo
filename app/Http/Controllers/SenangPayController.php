<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\SenangPayApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SenangPayController extends Controller
{
    public function handleReturn(Request $request)
    {
        $requestData = $request->all();
        Log::channel('senangpay')->info('handleReturn', $requestData);

        // start testing remove later
        // Mock SenangPay response
        // $mockJson = '{"status_id":"0","order_id":"10","transaction_id":"1750438825000316388","msg":"The_payment_was_declined._Please_contact_your_bank._Thank_you._","hash":"6f94c182585db9f64eb3aca65ef938b837bd31fe1cff88234dc9065c0f127091"}';

        // Decode JSON to array to simulate $request->all()
        // $requestData = json_decode($mockJson, true);
        // end testing remove later

        $result = $this->processPaymentResponse($requestData);

        Log::info('handleReturn - result', $result);

        $route = $result['success'] ? 'payments.success' : 'payments.failed';

        return redirect()->route($route, ['transaction_id' => $result['transaction_id'] ?? 0]);
    }

    public function handleCallback(Request $request)
    {
        $requestData = $request->all();
        Log::channel('senangpay')->info('handleCallback', $requestData);

        $this->processPaymentResponse($requestData);
        return response('OK', 200);
    }

    /**
     * Initiate a SenangPay payment for a booking
     */
    public function initiatePayment(Request $request, $bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            if ($booking->payment_status === 'paid') {
                return response()->json(['success' => false, 'message' => 'Booking is already paid'], 400);
            }

            $merchant_id = config('senangpay.merchant_id');
            $secret_key = config('senangpay.secret_key');
            $base_url = config('senangpay.base_url');
            $is_sandbox = config('senangpay.sandbox');
            $package_hash = $booking->package->uuid;

            $detail = "Payment for booking {$booking->uuid}. Package: {$package_hash}";
            $amount = round((float) $booking->total_price, 2);
            $order_id = (string) $booking->id;
            $customer_name = $booking->booking_name;
            $customer_email = $booking->email ?? 'customer@example.com';
            $customer_contact = $booking->phone_number;

            Log::channel('senangpay')->info('SENANGPAY_SANDBOX: ' . config('senangpay.sandbox'));
            // Log inputs
            Log::channel('senangpay')->info('--- SenangPay Hash Generation Inputs ---' . json_encode([
                'detail' => $detail,
                'amount' => $amount,
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_contact' => $customer_contact,
                'secret_key (partial)' => substr($secret_key, 0, 5) . '***',
            ], JSON_PRETTY_PRINT));

            // Generate hash
            $hash_input = $secret_key . urldecode($detail) . urldecode($amount) . urldecode($order_id);

            Log::channel('senangpay')->info('Hash input string:', ['hash_input' => $hash_input]);

            if ($is_sandbox) {
                $hash = md5($hash_input);
            } else {
                $hash = hash_hmac('sha256', $hash_input, $secret_key);
            }

            Log::channel('senangpay')->info('Generated hash:', ['hash' => $hash]);

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

            Log::channel('senangpay')->info('Final payment URL:', ['url' => $payment_url]);

            return response()->json([
                'success' => true,
                'payment_url' => $payment_url,
                'order_id' => $order_id
            ]);
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Payment initiation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment initiation failed'], 500);
        }
    }

    private function processPaymentResponse(array $data)
    {
        // Log the request
        $this->logApiRequest($data);

        try {
            // Extract required fields
            $statusId = $data['status_id'] ?? '';
            $orderId = $data['order_id'] ?? '';
            $transactionId = $data['transaction_id'] ?? '';
            $message = $data['msg'] ?? '';
            $hash = $data['hash'] ?? '';

            // Basic validation
            $missingFields = [];
            if ($statusId === null || $statusId === '') $missingFields[] = 'status_id';
            if ($orderId === null || $orderId === '') $missingFields[] = 'order_id';
            if ($transactionId === null || $transactionId === '') $missingFields[] = 'transaction_id';
            if ($hash === null || $hash === '') $missingFields[] = 'hash';
            if (count($missingFields) > 0) {
                return ['success' => false, 'transaction_id' => 0, 'message' => 'Missing required fields: ' . implode(', ', $missingFields)];
            }

            // Verify hash
            if (!$this->verifyHash($data)) {
                return ['success' => false, 'transaction_id' => 0, 'message' => 'Hash verification failed'];
            }

            // Find booking by order_id
            $booking = Booking::where('id', $orderId)->first();
            if (!$booking) {
                return ['success' => false, 'transaction_id' => 0, 'message' => 'Booking not found'];
            }

            // Create or find transaction
            $transaction = $booking->transactions()->latest()->whereNotIn('status', ['completed', 'failed'])->first();
            if (!$transaction) {
                // Create new transaction if none exists
                $transaction = Transaction::create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price,
                    'status' => 'pending',
                    'payment_method' => 'senangpay',
                    'order_id' => $orderId,
                ]);
            }

            $isSuccess = $statusId === '1';
            $transaction->update([
                'status' => $isSuccess ? 'completed' : 'failed',
                'status_id' => $statusId,
                'message' => $message,
                'transaction_id' => $transactionId,
                'processed_at' => now(),
            ]);

            // Update booking if successful
            if ($isSuccess && $transaction->booking) {
                $transaction->booking->update(['payment_status' => 'paid']);
            }

            return [
                'success' => $isSuccess,
                'transaction_id' => $transaction->id,
                'message' => $message
            ];
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Payment processing failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => 0];
        }
    }

    private function verifyHash(array $data)
    {
        $secretKey = config('senangpay.secret_key');
        $isSandbox = config('senangpay.sandbox');

        $hashInput = $secretKey .
            ($data['status_id'] ?? '') .
            ($data['order_id'] ?? '') .
            ($data['transaction_id'] ?? '') .
            ($data['msg'] ?? '');

        $expectedHash = $isSandbox ? md5($hashInput) : hash_hmac('sha256', $hashInput, $secretKey);

        return hash_equals($expectedHash, $data['hash'] ?? '');
    }

    private function logApiRequest(array $data)
    {
        try {
            SenangPayApiLog::create([
                'log_type' => 'payment_response',
                'status_id' => $data['status_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'msg' => $data['msg'] ?? null,
                'hash' => $data['hash'] ?? null,
                'raw_payload' => $data,
                'is_processed' => true,
            ]);
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Failed to log API request', ['error' => $e->getMessage()]);
        }
    }
}
