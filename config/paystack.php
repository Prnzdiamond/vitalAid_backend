<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paystack Keys
    |--------------------------------------------------------------------------
    |
    | The Paystack publishable key and secret key. You can get these from
    | your Paystack dashboard.
    |
    */
    'publicKey' => env('PAYSTACK_PUBLIC_KEY', ''),
    'secretKey' => env('PAYSTACK_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Paystack Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL for Paystack API requests.
    |
    */
    'baseUrl' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This secret is used to verify that webhooks are actually coming from Paystack.
    |
    */
    'webhookSecret' => env('PAYSTACK_WEBHOOK_SECRET', ''),
];
