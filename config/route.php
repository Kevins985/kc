<?php

use support\extend\Route;

foreach (config('autoload.routes', []) as $file) {
    include_once $file;
}

Route::disableDefaultRoute(); //关闭路由

