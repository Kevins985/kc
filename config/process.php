<?php

$process = [];
$config = explode(',',env('APP_PROCESS_LIST'));
if(in_array('file_monitor',$config)){
    // File update detection and automatic reload
    $process['file_monitor'] = [
        'handler'     => app\process\FileMonitor::class,
        'reloadable'  => false,
        'constructor' => [
            // Monitor these directories
            'monitor_dir' => [
                app_path(),
                config_path(),
                base_path('library'),
                base_path('process'),
                base_path('support'),
                base_path('resource'),
                base_path('.env'),
            ],
            // Files with these suffixes will be monitored
            'monitor_extensions' => [
                'php', 'html', 'htm', 'env'
            ]
        ]
    ];
}
if(in_array('crontab_task',$config)){
    $process['crontab_task'] = [
        'handler'  => app\process\CrontabTask::class,
    ];
}
if(in_array('channel_server',$config)){
    $process['channel_server'] = [
        'handler' => app\process\ChannelServer::class,
        'listen'  => 'frame://0.0.0.0:2206',
        'reloadable' => false,
        'count' => 1,   // 必须是1
    ];
}
if(in_array('task',$config)){
    $process['task'] = [
        'handler' => app\process\Task::class
    ];
}
if(in_array('redis_consumer',$config)){
    $process['redis_consumer'] = [
        'handler'     => Webman\RedisQueue\Process\Consumer::class,
        'count'       => 1, // 进程数
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path('queue/redis')
        ]
    ];
}
if(in_array('websocket',$config)){
    $process['websocket'] = [
        // 这里指定进程类
        'handler' => app\process\Websocket::class,
        'listen'  => env('SOCKET_LISTEN'),
        'socket_url'=> env('SOCKET_URL'),
        'uidConnections'=>[],
        // 进程数 （可选，默认1）
        'count'   => env('SOCKET_PROCESS'),
        // 进程运行用户 （可选，默认当前用户）
        'api'    => '',
        // 进程运行用户组 （可选，默认当前用户组）
        'group'   => '',
        // 当前进程是否支持reload （可选，默认true）
        'reloadable' => true,
        // 是否开启reusePort （可选，此选项需要php>=7.0，默认为true）
        'reusePort'  => true,
        // transport (可选，当需要开启ssl时设置为ssl，默认为tcp)
        'transport'  => 'ssl',
        // context （可选，当transport为是ssl时，需要传递证书路径）
        'context'    => [
            'ssl' => [
                'local_pk'    => env('SOCKET_SSL_PK'),
                'local_cert'  => env('SOCKET_SSL_CERT'),
                'verify_peer' => false
            ]
        ],
        // 进程类构造函数参数，这里为 process\Pusher::class 类的构造函数参数 （可选）
        'constructor' => [],
    ];
}
return $process;
