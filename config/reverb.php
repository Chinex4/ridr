<?php

return [

    'servers' => [
        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port' => env('REVERB_SERVER_PORT', 8080),
            'hostname' => env('REVERB_HOST'),

            'options' => [
                // keep this an array (default)
                'tls' => [],
            ],

            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10000),

            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'connection' => env('REVERB_SCALING_CONNECTION', 'redis'),
            ],
        ],
    ],

    'apps' => [
        'provider' => 'config',

        'apps' => [
            [
                'key' => env('REVERB_APP_KEY', 'my-app-key'),
                'secret' => env('REVERB_APP_SECRET', 'my-app-secret'),
                'app_id' => env('REVERB_APP_ID', 'my-app-id'),

                'allowed_origins' => ['*'],
            ],
        ],
    ],

];
