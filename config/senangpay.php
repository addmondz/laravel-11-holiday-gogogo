<?php

return [
    'merchant_id' => env('SENANGPAY_MERCHANT_ID'),
    'secret_key' => env('SENANGPAY_SECRET_KEY'),
    'sandbox' => env('SENANGPAY_SANDBOX', true),
    'base_url' => env('SENANGPAY_SANDBOX', true)
        ? 'https://sandbox.senangpay.my/payment/'
        : 'https://app.senangpay.my/payment/',
];
