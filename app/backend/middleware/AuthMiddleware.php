<?php

namespace app\backend\middleware;

use Carbon\Carbon;
use support\exception\AuthorizeException;
use support\extend\Middleware;
use support\extend\Request;
use support\extend\Response;

class AuthMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function process(Request $request, callable $next)
    {
        try{
            $this->verifyRequestSign($request);
            $request->verifyRequestData();
            $this->verifyUserGrant($request,'rbac');
            if($request->method()!='GET'){
                $this->writeRequestLog($request);
            }
            return $next($request);
        }
        catch (AuthorizeException $e){
            if($request->isAjax()){
                return $next($request)->json(false,[],'token已失效,请重新登陆!',$e->getCode());
            }
            else{
                $request->setErrorMsg($e->getMessage());
                return redirect("/backend/error");
            }
        }
        catch (\Exception $e){
            $request->setErrorMsg($e->getMessage());
            if($request->isAjax()){
                return $next($request)->json(false,[],$e->getMessage(),$e->getCode());
            }
            else{
                if($e->getCode()==403){
                    return redirect("/backend/error");
                }
                return redirect($request->getLoginUrl());
            }
        }
    }
}