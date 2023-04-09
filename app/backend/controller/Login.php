<?php

namespace app\backend\controller;

use library\logic\AuthLogic;
use library\validator\sys\AuthValidation;
use support\Container;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\controller\Backend;

/**
 * Class Login
 * @package app\backend\controller
 */
class Login extends Backend
{
    public function __construct(AuthLogic $logic,AuthValidation $validation)
    {
        $this->logic = $logic;
        $this->validation = $validation;
    }

    /**
     * 输出验证码图像
     */
    public function captcha(Request $request)
    {
        $captcha = new \support\utils\Captcha(75,26);
        $img_content = $captcha->getImageContent();
        $request->session()->set('captcha', strtolower($captcha->getCheckCode()));
        return $this->response->output($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 登陆页面
     */
    public function index(Request $request)
    {
        if (!empty($this->loginUser)) {
            return redirect($request->getLoginUrl("success"));
        }
        $this->response->layout->setLayout("login");
        $captcha = getRouteUrl("/backend/login/captcha");
        return $this->response->layout('login/index', ['captcha' => $captcha]);
    }

    /**
     * 登陆提交
     */
    public function submit(Request $request)
    {
        try {
            if (!$request->isAjax()) {
                throw new VerifyException("Exception request");
            }
            if (!empty($this->loginUser)) {
                $res = ['url'=>$request->getLoginUrl("success")];
                return $this->response->json(true, $res, "登陆成功");
            }
            $this->request->verifyRequestData('login');
            $data = $this->getPost(['account','captcha','password']);
            $request->verifyIpWhiteList($data['account']);
            $this->logic->setUserGuard('admin');
            $this->logic->setClientType('web');
            $res = $this->logic->login($data["account"], $data["password"],$data["captcha"]);
            if (empty($res)) {
                throw new VerifyException("登陆失败");
            }
//            if($data['is_remember']){
//                $this->response->cookie('Token',$res->token,time()+$res['expire']);
//            }
            $response = [
                'token'=>$res,
                'url'=>$request->getLoginUrl("success")
            ];
            return $this->response->json(true, $response, "登陆成功");

        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage(), $e->getCode());
        }
    }

    /**
     * 退出登陆
     */
    public function logout(Request $request)
    {
        if (!empty($this->loginUser)) {
            $this->logic->setUserGuard('admin');
            $this->logic->logout($request->getUserToken());
        }
        return $this->response->redirect($request->getLoginUrl());
    }

    /**
     * 刷新token
     */
    public function refreshToken(Request $request)
    {
        try {
            if (empty($this->loginUser)) {
                throw new BusinessException("暂无该权限");
            }
            $this->logic->setUserGuard('admin');
            $res = $this->logic->refreshUserToken($this->loginUser->getUserID());
            if (empty($res)) {
                throw new BusinessException("操作失败");
            }
            return $this->response->json(true, $res);

        } catch (BusinessException $e) {
            return $this->response->json(false, [], $e->getMessage(), $e->getCode());
        }
    }
}