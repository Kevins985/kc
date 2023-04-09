<?php

return [
    // 默认数据库
    'default' => 'mysql',
    // 各种数据库配置
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => env('MYSQL_HOST'),
            'port'        => env('MYSQL_PORT'),
            'database'    => env('MYSQL_DB'),
            'username'    => env('MYSQL_USER'),
            'password'    => env('MYSQL_PASS'),
            'unix_socket' => '',
            'charset'     => 'utf8',
            'collation'   => 'utf8_unicode_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => 'InnoDB',
        ]
    ],
];