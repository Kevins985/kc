<?php

return [
    'default' => [
        'host' => env('REDIS_QUEUE'),
        'options' => [
            'auth'=> env('REDIS_PASS'),     // 密码，可选参数
            'db' => 5,      // 数据库
            'max_attempts'  => 3, // 消费失败后，重试次数
            'retry_seconds' => 5, // 重试间隔，单位秒
        ]
    ]
];