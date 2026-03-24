<?php

declare(strict_types=1);

return [
    'access_key' => env('RAPYD_ACCESS_KEY', ''),
    'secret_key' => env('RAPYD_SECRET_KEY', ''),
    'sandbox' => env('RAPYD_SANDBOX', true),
    'base_url' => [
        'sandbox' => 'https://sandboxapi.rapyd.net',
        'production' => 'https://api.rapyd.net',
    ],
    'webhook' => [
        'path' => env('RAPYD_WEBHOOK_PATH', '/rapyd/webhook'),
        'tolerance' => env('RAPYD_WEBHOOK_TOLERANCE', 60),
        'middleware' => [],
    ],
    'timeout' => env('RAPYD_TIMEOUT', 30),
    'retry' => [
        'times' => env('RAPYD_RETRY_TIMES', 3),
        'sleep' => env('RAPYD_RETRY_SLEEP', 100),
    ],
];
