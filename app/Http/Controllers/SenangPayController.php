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
        // $requestData = $request->all();
        // Log::channel('senangpay')->info('handleReturn', $requestData);

        // start testing remove later
        // Mock SenangPay response
        $mockJson = '{"status_id":"0","order_id":"10","transaction_id":"1750438825000316388","msg":"The_payment_was_declined._Please_contact_your_bank._Thank_you._","hash":"6f94c182585db9f64eb3aca65ef938b837bd31fe1cff88234dc9065c0f127091"}';

        // Decode JSON to array to simulate $request->all()
        $requestData = json_decode($mockJson, true);
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
     * Create a transaction for SenangPay payment
     */
    public static function createTransaction($bookingId, $amount, $paymentMethod = 'senangpay')
    {
        $transaction = Transaction::create([
            'booking_id' => $bookingId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
        ]);

        Log::channel('senangpay')->info('Transaction created for SenangPay', [
            'transaction_id' => $transaction->id,
            'booking_id' => $bookingId,
            'amount' => $amount
        ]);

        return $transaction;
    }

    /**
     * Generate a timestamp-based order ID for SenangPay
     */
    public static function generateOrderId()
    {
        return (string) (time() * 1000 + rand(0, 999)); // Millisecond timestamp + random
    }

    /**
     * Initiate a SenangPay payment for a booking
     */
    public function initiatePayment(Request $request, $bookingId)
    {
        try {
            $booking = \App\Models\Booking::findOrFail($bookingId);

            if ($booking->payment_status === 'paid') {
                return response()->json(['success' => false, 'message' => 'Booking is already paid'], 400);
            }

            $transaction = Transaction::create([
                'booking_id' => $booking->id,
                'amount' => $booking->total_price,
                'status' => 'pending',
                'payment_method' => 'senangpay',
            ]);

            $paymentUrl = $this->generatePaymentUrl($transaction, $booking);

            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Payment initiation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment initiation failed'], 500);
        }
    }

    /**
     * Generate SenangPay payment URL
     */
    private function generatePaymentUrl($transaction, $booking)
    {
        $merchantId = config('senangpay.merchant_id');
        $secretKey = config('senangpay.secret_key');
        $baseUrl = config('senangpay.base_url');
        $isSandbox = config('senangpay.sandbox');

        $orderId = $booking->id;
        $transaction->update(['order_id' => NULL]);

        $detail = "Payment for booking #{$booking->uuid} - {$booking->package->name}";
        $hashInput = $secretKey . urldecode($detail) . urldecode($transaction->amount) . urldecode($orderId);
        $hash = $isSandbox ? md5($hashInput) : hash_hmac('sha256', $hashInput, $secretKey);

        return "{$baseUrl}/{$merchantId}?" . http_build_query([
            'detail' => $detail,
            'amount' => $transaction->amount,
            'order_id' => $orderId,
            'name' => $booking->booking_name,
            'email' => 'customer@example.com',
            'phone' => $booking->phone_number,
            'hash' => $hash,
        ]);
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

            // Find and update transaction
            $booking = Booking::where('id', $orderId)->first();
            $transaction = $booking->transactions()->latest()->whereNotIn('status', ['completed', 'failed'])->first();
            if (!$transaction) {
                return ['success' => false, 'transaction_id' => 0, 'message' => 'Transaction not found'];
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
