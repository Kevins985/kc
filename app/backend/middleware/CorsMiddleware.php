<?php

namespace app\backend\middleware;

use support\extend\Middleware;
use support\extend\Request;
use support\extend\Response;

class CorsMiddleware extends Middleware
{
    public function process(Request $request, callable $next)
    {
        $response = $request->method() == 'OPTIONS' ? response('') : $next($request);
        $response->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin'
        ]);
        return $response;
    }
}