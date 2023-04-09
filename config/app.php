<?php
/**
 * This file is part of cli.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    'app_key' => 'base64:N721v3Gt2I58Hb7oiU7a70PQ+i8ekPWRqaI+JSnM1wa=',
    'access_exp' => 8640000,
    'refresh_exp' => 25920000,
    'debug' => env('APP_DEBUG',false),
    'default_timezone' => 'Asia/Shanghai',
    'is_open_cache'=>true,
    "url_expire"=>10,
    "sign_private_key"=> "projectApi",
    'validation_sign'=>[
        'backend'=>false,
        'api'=>true
    ],
    'operation_log'=>[
        'backend'=>true,
        'api'=>true
    ],
    'view_suffix'=>'html',
    'view_options'=>[
//        'cache' => runtime_path("views"),
        'autoescape'=>false,
        'debug'=>true
    ],
    'limit' => [
        'enable' => true,
        'limit' => 30, // 请求次数
        'window_time' => 10, // 窗口时间，单位：秒
        'status' => 429,  // HTTP 状态码
        'body' => [  // 响应信息
            'status'=>0,
            'code' => 0,
            'msg' => 'Too many requests, please try again later!',
            'data' => null
        ]
    ]
];
