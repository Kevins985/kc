<?php

namespace support\middleware;

use support\extend\Middleware;
use support\extend\Request;
use support\extend\Response;
use support\extend\RateLimiter;

class LimitVisit extends Middleware
{

    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $next)
    {
        if ($result = RateLimiter::traffic()) {
            return new Response($result['status'], [
                'Content-Type' => 'application/json',
                'X-Rate-Limit-Limit' => $result['limit'],
                'X-Rate-Limit-Remaining' => $result['remaining'],
                'X-Rate-Limit-Reset' => $result['reset']
            ], json_encode($result['body']));
        }
        return $next($request);
    }
}