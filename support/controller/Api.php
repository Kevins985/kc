<?php

namespace support\controller;

use support\extend\Controller;
use support\extend\Request;

/**
 * 平台访问模式controller 继承
 */
class Api extends Controller{

    /**
     * 初始化数据
     */
    public function beforeAction(Request $request)
    {
        try{
            $request->verifyIpBlacklist();
            $this->request = $request;
            $this->response->setRequest($request);
            $this->loginUser = getTokenUser('user',$request->getUserToken());
            if(!empty($this->validation)){
                $this->request->setValidation($this->validation);
            }
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage(),$e->getCode());
        }
    }

    public function afterAction(Request $request)
    {

    }
}
