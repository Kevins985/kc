<?php

return [
    'type'    => 'redis', //file or redis or redis_cluster
    'handler' => Webman\RedisSessionHandler::class,
    'config' => [
        'file' => [
            'save_path' => runtime_path('sessions'),
        ],
        'redis' => [
            'host'      => env('REDIS_HOST'),
            'port'      => env('REDIS_PORT'),
            'auth'      => env('REDIS_PASS'),
            'database'  => 3,
            'timeout'   => 5,
            'prefix'    => 'sid_',
        ]
    ],
    'token_lifetime'=>3600*24*30,
    'gc_maxlifetime'=>3600*24*30,
    'cookie_lifetime'=>3600*24*30,
    'session_name' => 'SID',
];
