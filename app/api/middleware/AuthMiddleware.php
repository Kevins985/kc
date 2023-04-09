<?php

namespace app\api\middleware;

use Carbon\Carbon;
use support\extend\Middleware;
use support\extend\Request;

class AuthMiddleware extends Middleware
{

    public function process(Request $request, callable $next)
    {
        try{
            $this->verifyRequestSign($request);
            $request->verifyRequestData();
            $this->verifyUserGrant($request,'restful');
            $this->writeRequestLog($request);
            return $next($request);
        }
        catch (\Throwable $e){
            return json([
                'status' => 0,
                'data' => [],
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ]);
        }
    }
}