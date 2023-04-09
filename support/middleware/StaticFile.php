<?php

namespace support\middleware;

use support\extend\Middleware;
use support\extend\Request;
use support\extend\Response;

/**
 * Class StaticFile
 * @package app\middleware
 */
class StaticFile extends Middleware
{
    public function process(Request $request, callable $next)
    {
        // Access to files beginning with. Is prohibited
        if (strpos($request->path(), '/.') !== false) {
            return response('<h1>403 forbidden</h1>', 403);
        }
        /** @var Response $response */
        $response = $next($request);
        // Add cross domain HTTP header
        /*$response->withHeaders([
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]);*/
        return $response;
    }
}
