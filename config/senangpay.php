<?php

return [
    'merchant_id' => env('SENANGPAY_MERCHANT_ID'),
    'secret_key' => env('SENANGPAY_SECRET_KEY'),
    'sandbox' => env('SENANGPAY_SANDBOX', true),
    'hash_algorithm' => env('SENANGPAY_HASH_ALGORITHM', 'sha256'),
    'base_url' => env('SENANGPAY_SANDBOX', true)
        ? 'https://sandbox.senangpay.my/payment'
        : 'https://app.senangpay.my/payment',
];
