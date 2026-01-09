<?php

return [
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    'timeout' => env('PAYSTACK_TIMEOUT', 10),
    'retry' => [
        'times' => env('PAYSTACK_RETRY_TIMES', 2),
        'sleep_ms' => env('PAYSTACK_RETRY_SLEEP_MS', 250),
    ],
];
