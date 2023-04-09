<?php

use library\model\sys\CasbinRestfulModel;
use library\model\sys\CasbinRbacModel;

//https://casbin.org/docs/zh-CN/adapters
return [
    'restful' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => config_path("casbin/restful-model.conf"), // 权限规则模型配置文件
            'config_text' => '',
        ],
        'adapter' => [
            'type' => 'model', // model or adapter
            'class' => CasbinRestfulModel::class,
        ],
    ],
    // 可以配置多个权限model
    'rbac' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => config_path("casbin/rbac-model.conf"), // 权限规则模型配置文件
            'config_text' => '',
        ],
        'adapter' => [
            'type' => 'model', // model or adapter
            'class' => CasbinRbacModel::class,
        ],
    ],
];