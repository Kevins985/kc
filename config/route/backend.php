<?php

use support\extend\Route;

//数据库维护的路由数据
$routes = getRouteList("backend",true);
foreach ($routes as $v){
    Route::add($v['methods'],$v['route_url'],[$v['class'],$v['action']])->middleware($v['middleware']);
}

