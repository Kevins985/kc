<?php
namespace support\middleware;

use support\extend\Middleware;
use support\extend\Request;

class Cors extends Middleware
{
    public function process(Request $request, callable $next)
    {
        $response = $request->method() == 'OPTIONS' ? response('') : $next($request);
        $response->withHeaders([
            'Access-Control-Allow-Origin' => $request->host(),
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Max-Age' => '3600',
            'Set-Cookie'=> 'HttpOnly;Secure;SameSite=None',
//            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin,Access-Control-Allow-Headers,Access-Token',
            'Access-Control-Allow-Credentials' => 'true'  //若要返回cookie、携带seesion等信息则将此项设置我true
        ]);
        return $response;
    }
}