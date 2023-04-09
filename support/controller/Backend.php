<?php

namespace support\controller;

use support\extend\Controller;
use support\extend\Request;
use support\utils\Http;

/**
 * 平台访问模式controller 继承
 */
class Backend extends Controller
{

    /**
     * 初始化数据
     */
    public function beforeAction(Request $request)
    {
        try {
            $this->request = $request;
            $this->response->setRequest($request);
            locale($request->getLanguage());
            $this->response->layout->setLayout('layout');
            $this->loginUser = getTokenUser('admin',$request->getUserToken());
            $this->initScriptData();
            if (!empty($this->validation)) {
                $this->request->setValidation($this->validation);
            }
            if(!empty($this->loginUser)){
                $this->response->layout->setLoginUser($this->loginUser);
            }
        }
        catch (\Exception $e) {
            if ($request->isAjax()) {
                return $this->response->json(false, [], $e->getMessage(), $e->getCode());
            }
            else {
                return redirect($request->getLoginUrl());
            }
        }
    }

    /**
     * 加载初始化JS
     */
    private function initScriptData()
    {
        if (!empty($this->loginUser)) {
            $scriptAssign['uuid'] = $this->loginUser['user_id'];
            $scriptAssign['username'] = $this->loginUser['account'];
        }
        $scriptAssign['m'] = $this->request->app;
        $scriptAssign['c'] = $this->request->getControllerName();
        $scriptAssign['a'] = $this->request->action;
        $socket_url = config('process.websocket.socket_url');
        if(!empty($socket_url)){
            $scriptAssign['socket_url'] = $socket_url;
        }
        $this->response->assign('controller',$scriptAssign['c']);
        $this->response->assign('action',$scriptAssign['a']);
        $this->response->assign('cmenu',$this->request->getCurrentMenu());
        $this->response->addScriptAssign($scriptAssign);
    }

    /**
     * 跳转错误页面
     * @param string $msg
     */
    protected function redirectErrorUrl(string $msg=null){
        if(!$this->request->isAjax()){
            Http::getInstance($this->request)->setRefererUrl($this->request->uri());
        }
        $this->request->setErrorMsg($msg);
        return redirect("/backend/error");
    }
}
