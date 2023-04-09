<?php

namespace app\api\middleware;

use Carbon\Carbon;
use support\extend\Middleware;
use support\extend\Request;

class CorsMiddleware extends Middleware
{
    public function process(Request $request, callable $next)
    {
        $response = $request->method() == 'OPTIONS' ? response('') : $next($request);
        $response->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin,Token'
        ]);
        return $response;
    }
}