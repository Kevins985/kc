<?php

use support\extend\Route;
use app\api\middleware\CorsMiddleware;

//数据库维护的路由数据
$routes = getRouteList("api",true);
foreach ($routes as $v){
    $middleware = $v['middleware'];
    $middleware[] = CorsMiddleware::class;
    Route::add($v['methods'],$v['route_url'],[$v['class'],$v['action']])->middleware($middleware);
}





