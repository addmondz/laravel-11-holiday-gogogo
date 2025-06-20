<?php

namespace App\Http\Controllers;

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
        Log::channel('senangpay')->info('handleReturn: ' . json_encode($requestData));
        
        // Check if we have valid data
        if (empty($requestData) || !isset($requestData['order_id'])) {
            Log::channel('senangpay')->warning('Invalid or empty return data received', $requestData);
            // Redirect to a generic error page or home page
            return redirect()->route('welcome')->with('error', 'Invalid payment response received.');
        }
        
        // Log the return request
        $this->logApiRequest('return', $requestData);
        
        // Process the payment response
        $result = $this->processPaymentResponse($requestData);
        
        if ($result['success']) {
            // Redirect to success page
            return redirect()->route('payments.success', ['transaction_id' => $result['transaction_id']]);
        } else {
            // Redirect to failed page
            return redirect()->route('payments.failed', ['transaction_id' => $result['transaction_id'] ?? 0]);
        }
    }

    public function handleCallback(Request $request)
    {
        $requestData = $request->all();
        Log::channel('senangpay')->info('handleCallback: ' . json_encode($requestData));
        
        // Check if we have valid data
        if (empty($requestData) || !isset($requestData['order_id'])) {
            Log::channel('senangpay')->warning('Invalid or empty callback data received', $requestData);
            // For callbacks, we still return OK to SenangPay even if data is invalid
            return response('OK', 200);
        }
        
        // Log the callback request
        $this->logApiRequest('callback', $requestData);
        
        // Process the payment response
        $result = $this->processPaymentResponse($requestData);
        
        // For callbacks, we always return OK to SenangPay
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
            
            // Check if booking is already paid
            if ($booking->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already paid'
                ], 400);
            }
            
            // Create transaction
            $transaction = self::createTransaction(
                bookingId: $booking->id,
                amount: $booking->total_price,
                paymentMethod: 'senangpay'
            );
            
            // Generate SenangPay payment URL
            $paymentUrl = $this->generatePaymentUrl($transaction, $booking);
            
            Log::channel('senangpay')->info('Payment initiated', [
                'booking_id' => $booking->id,
                'transaction_id' => $transaction->id,
                'amount' => $booking->total_price,
                'payment_url' => $paymentUrl
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment URL generated successfully',
                'payment_url' => $paymentUrl,
                'transaction' => $transaction
            ]);
            
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Failed to initiate payment: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage()
            ], 500);
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
        
        // Generate order ID for this transaction
        $orderId = self::generateOrderId();
        
        // Update transaction with order ID
        $transaction->update(['order_id' => $orderId]);
        
        // Prepare payment data
        $detail = "Payment for booking #{$booking->uuid} - {$booking->package->name}";
        $amount = $isSandbox ? $transaction->amount : $transaction->amount;
        $customerName = $booking->booking_name;
        $customerEmail = 'customer@example.com'; // You might want to add email to booking
        $customerContact = $booking->phone_number;
        
        // Generate hash
        $hashInput = $secretKey . urldecode($detail) . urldecode($amount) . urldecode($orderId);
        
        if ($isSandbox) {
            $hash = md5($hashInput);
        } else {
            $hash = hash_hmac('sha256', $hashInput, $secretKey);
        }
        
        // Build payment URL
        $paymentUrl = "{$baseUrl}/{$merchantId}?" . http_build_query([
            'detail' => $detail,
            'amount' => $amount,
            'order_id' => $orderId,
            'name' => $customerName,
            'email' => $customerEmail,
            'phone' => $customerContact,
            'hash' => $hash,
        ]);
        
        return $paymentUrl;
    }
    
    private function processPaymentResponse(array $data)
    {
        try {
            // Log the incoming data for debugging
            Log::channel('senangpay')->info('Processing payment response', $data);
            
            // Extract data from SenangPay response
            $statusId = $data['status_id'] ?? null;
            $orderId = $data['order_id'] ?? null;
            $transactionId = $data['transaction_id'] ?? null;
            $message = $data['msg'] ?? null;
            $hash = $data['hash'] ?? null;
            
            // Log extracted values for debugging
            Log::channel('senangpay')->info('Extracted payment data', [
                'status_id' => $statusId,
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'message' => $message,
                'hash' => $hash ? 'present' : 'missing'
            ]);
            
            // Validate required fields
            if (!$statusId || !$orderId || !$transactionId || !$hash) {
                $missingFields = [];
                if (!$statusId) $missingFields[] = 'status_id';
                if (!$orderId) $missingFields[] = 'order_id';
                if (!$transactionId) $missingFields[] = 'transaction_id';
                if (!$hash) $missingFields[] = 'hash';
                
                Log::channel('senangpay')->error('Missing required fields in payment response', [
                    'missing_fields' => $missingFields,
                    'received_data' => $data
                ]);
                return ['success' => false, 'error' => 'Missing required fields: ' . implode(', ', $missingFields)];
            }
            
            // Verify hash
            if (!$this->verifyHash($data)) {
                Log::channel('senangpay')->error('Hash verification failed', $data);
                return ['success' => false, 'error' => 'Hash verification failed'];
            }
            
            // Find the transaction by order_id
            $transaction = Transaction::where('order_id', $orderId)->first();
            
            if (!$transaction) {
                Log::channel('senangpay')->error('Transaction not found for order_id: ' . $orderId, [
                    'order_id' => $orderId,
                    'available_transactions' => Transaction::whereNotNull('order_id')->pluck('order_id')->toArray()
                ]);
                return ['success' => false, 'error' => 'Transaction not found for order_id: ' . $orderId];
            }
            
            // Update transaction status
            $isSuccess = $statusId === '1';
            $transaction->status = $isSuccess ? 'completed' : 'failed';
            $transaction->status_id = $statusId;
            $transaction->message = $message;
            $transaction->transaction_id = $transactionId; // SenangPay transaction ID
            $transaction->processed_at = now();
            $transaction->save();
            
            // Update booking payment status if transaction is successful
            if ($isSuccess && $transaction->booking) {
                $transaction->booking->payment_status = 'paid';
                $transaction->booking->save();
                
                Log::channel('senangpay')->info('Booking payment status updated to paid', [
                    'booking_id' => $transaction->booking->id,
                    'transaction_id' => $transaction->id
                ]);
            }
            
            // Mark the API log as processed
            $this->markApiLogAsProcessed($data);
            
            Log::channel('senangpay')->info('Payment processed successfully', [
                'order_id' => $orderId,
                'status' => $isSuccess ? 'success' : 'failed',
                'transaction_id' => $transaction->id,
                'booking_id' => $transaction->booking_id
            ]);
            
            return [
                'success' => $isSuccess,
                'transaction_id' => $transaction->id,
                'message' => $message
            ];
            
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Error processing payment response: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function verifyHash(array $data)
    {
        $secretKey = config('senangpay.secret_key');
        $isSandbox = config('senangpay.sandbox');
        
        // Extract required fields for hash verification
        $statusId = $data['status_id'] ?? '';
        $orderId = $data['order_id'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';
        $message = $data['msg'] ?? '';
        $receivedHash = $data['hash'] ?? '';
        
        // Create hash input string (same format as SenangPay documentation)
        $hashInput = $secretKey . $statusId . $orderId . $transactionId . $message;
        
        // Generate hash based on environment
        if ($isSandbox) {
            $expectedHash = md5($hashInput);
        } else {
            $expectedHash = hash_hmac('sha256', $hashInput, $secretKey);
        }
        
        Log::channel('senangpay')->info('Hash verification', [
            'received_hash' => $receivedHash,
            'expected_hash' => $expectedHash,
            'hash_input' => $hashInput,
            'is_sandbox' => $isSandbox
        ]);
        
        return hash_equals($expectedHash, $receivedHash);
    }
    
    private function logApiRequest(string $type, array $data)
    {
        try {
            SenangPayApiLog::create([
                'log_type' => $type,
                'status_id' => $data['status_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'msg' => $data['msg'] ?? null,
                'hash' => $data['hash'] ?? null,
                'raw_payload' => $data,
                'is_processed' => false,
            ]);
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Failed to log API request: ' . $e->getMessage(), [
                'type' => $type,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function markApiLogAsProcessed(array $data)
    {
        try {
            $log = SenangPayApiLog::where('order_id', $data['order_id'])
                ->where('transaction_id', $data['transaction_id'])
                ->where('is_processed', false)
                ->first();
                
            if ($log) {
                $log->update(['is_processed' => true]);
                Log::channel('senangpay')->info('API log marked as processed', [
                    'log_id' => $log->id,
                    'order_id' => $data['order_id']
                ]);
            } else {
                Log::channel('senangpay')->warning('No unprocessed API log found to mark', [
                    'order_id' => $data['order_id'] ?? 'missing',
                    'transaction_id' => $data['transaction_id'] ?? 'missing'
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('senangpay')->error('Failed to mark API log as processed: ' . $e->getMessage(), [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
        }
    }
}

