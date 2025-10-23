<?php

namespace App\Http\Controllers;

use App\Constants\ApprovalStatus;
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
        // $mockJson = '{"status_id":"1","order_id":"25","transaction_id":"1761139218000108459","msg":"Payment_was_successful","hash":"88574f33079c3c09a29d6d1d312fc306fe00fd8329ed90624ba3c1d304b4992b"}';
        // $mockJson = '{"status_id":"0","order_id":"25","transaction_id":"1761139928000113639","msg":"Your_payment_was_declined._Please_check_with_your_bank._Thank_you.","hash":"56d7bc1bf4e3bedd26ee551c6543bd95ba8d64f810b606c5473ba0979cf2d53b"}';
        // $requestData = json_decode($mockJson, true);
        // end testing remove later

        $result = $this->processPaymentResponse($requestData);

        Log::info('handleReturn - result', $result);

        // Get the booking to find the package UUID
        $booking = null;
        if ($result['transaction_id'] && $result['transaction_id'] !== 0) {
            $transaction = Transaction::find($result['transaction_id']);
            if ($transaction) {
                $booking = $transaction->booking;
            }
        }

        // Redirect to quotation page with payment status
        $redirectParams = [
            'uuid' => $booking?->package?->uuid ?? request()->get('package_uuid'),
            'booking' => $booking?->uuid ?? request()->get('booking_uuid'),
            'payment_status' => $result['success'] ? 'success' : 'failed',
            'transaction_id' => $result['transaction_id'] ?? 0
        ];

        if (!$result['success']) {
            $redirectParams['error'] = $result['message'] ?? 'Payment processing failed';
            $redirectParams['error'] = str_replace('_', ' ', rtrim($redirectParams['error'], '_'));
        }

        return redirect()->route('quotation.with-hash', $redirectParams);
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
            $booking = Booking::where('uuid', $bookingId)->first();

            if ($booking->status >= ApprovalStatus::PAYMENT_COMPLETED) {
                return response()->json(['success' => false, 'message' => 'Booking is already paid'], 400);
            }

            $merchant_id = config('senangpay.merchant_id');
            $secret_key = config('senangpay.secret_key');
            $base_url = config('senangpay.base_url');
            $is_sandbox = config('senangpay.sandbox');
            $hash_algorithm = config('senangpay.hash_algorithm');
            $package_hash = $booking->package->uuid;

            $detail = "Payment for booking {$booking->uuid}. Package: {$package_hash}";
            $amount = number_format((float) $booking->total_price, 2, '.', '');
            $order_id = (string) $booking->id;
            $customer_name = $booking->booking_name;
            $customer_email = $booking->email ?? 'customer@example.com';
            $customer_contact = $booking->phone_number;

            // Generate hash: hash_hmac('sha256', secret_key + detail + amount + order_id, secret_key)
            if ($hash_algorithm == 'md5') {
                $hash = md5($secret_key . $detail . $amount . $order_id);
            } else {
                $hash_input = $secret_key . $detail . $amount . $order_id;
                $hash = hash_hmac('sha256', $hash_input, $secret_key);
            }

            // Build payment URL (SenangPay expects POST)
            $payment_url = "{$base_url}/{$merchant_id}";

            // Log::channel('senangpay')->info('Payment URL', ['payment_url' => $payment_url]);

            $transaction = new Transaction();
            $transaction->booking_id = $booking->id;
            $transaction->payment_method = 'senangpay';
            $transaction->amount = $amount;
            $transaction->status = 'pending';
            $transaction->order_id = $order_id;
            $transaction->save();

            $apiResponse = [
                'success' => true,
                'payment_url' => $payment_url,
                'order_id' => $order_id,
                'payment_data' => [
                    'detail' => $detail,
                    'amount' => $amount,
                    'order_id' => $order_id,
                    'name' => $customer_name,
                    'email' => $customer_email,
                    'phone' => $customer_contact,
                    'hash' => $hash,
                ]
            ];

            // Log::channel('senangpay')->info('Payment initiation response: '. json_encode($apiResponse, JSON_PRETTY_PRINT));

            return response()->json($apiResponse);
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Payment initiation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Payment initiation failed'], 500);
        }
    }

    private function processPaymentResponse(array $data)
    {
        // Log the request
        $senangPayApiLogId = $this->logApiRequest($data);

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

            $isSuccess = ($statusId === '1') ? true : false;

            // check if already have transaction
            $transaction = Transaction::where('transaction_id', $transactionId)->first();
            if ($transaction) {
                $transaction->update([
                    'status' => $statusId === '1' ? 'completed' : 'failed',
                    'status_id' => $statusId,
                    'message' => $message,
                    'transaction_id' => $transactionId,
                    'processed_at' => now(),
                    'senang_pay_api_log_id' => $senangPayApiLogId,
                ]);
            } else {
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
                        'senang_pay_api_log_id' => $senangPayApiLogId,
                    ]);
                }

                $transaction->update([
                    'status' => $isSuccess ? 'completed' : 'failed',
                    'status_id' => $statusId,
                    'message' => $message,
                    'transaction_id' => $transactionId,
                    'processed_at' => now(),
                ]);

                // Update booking if successful
                if ($isSuccess && $transaction->booking) {
                    $transaction->booking->update(['status' => ApprovalStatus::PAYMENT_COMPLETED]);
                }
            }

            return [
                'success' => $isSuccess,
                'transaction_id' => $transaction->id,
                'message' => $message
            ];
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Payment processing failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => 0, 'error' => $e->getMessage()];
        }
    }

    private function verifyHash(array $data)
    {
        $secretKey = config('senangpay.secret_key');
        $hashAlgorithm = config('senangpay.hash_algorithm');

        $hashInput = $secretKey .
            ($data['status_id'] ?? '') .
            ($data['order_id'] ?? '') .
            ($data['transaction_id'] ?? '') .
            ($data['msg'] ?? '');

        if ($hashAlgorithm === 'md5') {
            $expectedHash = md5($hashInput);
        } else {
            $expectedHash = hash_hmac('sha256', $hashInput, $secretKey);
        }

        return hash_equals($expectedHash, $data['hash'] ?? '');
    }

    private function logApiRequest(array $data)
    {
        try {
            $senangPayApiLog = SenangPayApiLog::create([
                'log_type' => 'payment_response',
                'status_id' => $data['status_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'msg' => $data['msg'] ?? null,
                'hash' => $data['hash'] ?? null,
                'raw_payload' => $data,
                'is_processed' => true,
            ]);

            return $senangPayApiLog->id;
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Failed to log API request', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
