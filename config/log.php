<?php

return [
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/bussiness/bussiness.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'server' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/server/server.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'queue' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/queue/queue.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'crontab' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/crontab/crontab.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'process' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/process/process.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'websocket' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/websocket/websocket.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'message' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/message/message.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'upload' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/upload/upload.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
    'api' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path('logs/api/api.log'),
                    30, //$maxFiles
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [ null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
];
