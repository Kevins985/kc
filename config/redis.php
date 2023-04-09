<?php

return [
    'default' => [
        'host'     => env('REDIS_HOST'),
        'password' => env('REDIS_PASS'),
        'port'     => env('REDIS_PORT'),
        'database' => 0,
    ],
    'cache' => [
        'host'     => env('REDIS_HOST'),
        'password' => env('REDIS_PASS'),
        'port'     => env('REDIS_PORT'),
        'database' => 1,
    ]
];
