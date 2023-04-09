<?php

namespace app\backend\controller;

use support\controller\Backend;
use support\extend\Request;
use support\utils\Http;

class Error extends Backend
{
    /**
     * 错误页面
     */
    public function index(Request $request)
    {
        if(empty($this->loginUser)){
            $url = $this->request->getLoginUrl();
            return $this->response->redirect($url);
        }
        $msg = $request->getErrorMsg();
        $refer = Http::getInstance($request)->getRefererUrl();
        return $this->response->layout("error/index",['msg'=>$msg,'refer_url'=>$refer]);
    }
}