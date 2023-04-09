<?php

return [
    'listen'               => env('APP_LISTEN'),
    'domain'               => env('APP_DOMAIN'),
    'transport'            => 'tcp',
    'context'              => [],
    'name'                 => 'http',
    'count'                => env('APP_PROCESS'),
    'api'                 => '',
    'group'                => '',
    'pid_file'             => runtime_path('http.pid'),
    'stdout_file'          => runtime_path('logs/stdout.log'),
    'log_file'             => runtime_path('logs/app.log') ,
    'max_request'          => env('APP_MAX_REQUEST'),
    'max_package_size'     => 20*1024*1024      //上传文件大小
];
