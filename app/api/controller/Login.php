<?php

namespace app\api\controller;

use library\logic\AuthLogic;
use library\validator\user\AuthValidation;
use support\controller\Api;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Login extends Api
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
        $request->session()->set('vcode', strtolower($captcha->getCheckCode()));
        return $this->response->output($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 登陆接口
     */
    public function login(Request $request)
    {
        try{
            $this->request->verifyRequestData();
            $account = $this->getPost('account');
            $password = $this->getPost('password');
            $vcode = $this->getPost('vcode');
            $this->logic->setUserGuard('user');
            if(!empty($vcode) && strtolower($vcode) !== $request->session()->get('vcode')) {
                throw new VerifyException("输入的验证码不正确");
            }
            $res = $this->logic->login($account,$password,'wap');
            if (empty($res)) {
                throw new VerifyException("登陆失败");
            }
            return $this->response->json(true,['token'=>$res->token], "登陆成功");
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 注册接口
     * @param $post {account,password,nickname,invitationCode}
     */
    public function register(Request $request)
    {
        try{
            $this->request->verifyRequestData();
            $post = $this->getPost();
            $this->logic->setUserGuard('user');
            if(!empty($vcode) && strtolower($vcode) !== $request->session()->get('vcode')) {
                throw new VerifyException("输入的验证码不正确");
            }
            $res = $this->logic->register($post);
            if (empty($res)) {
                throw new VerifyException("注册失败");
            }
            return $this->response->json(true,[], "注册成功");
        }
        catch (\Exception $e){
            $msg = $e->getMessage();
            if($msg=='username already exists'){
                $msg = '该账号已经存在';
            }
            return $this->response->json(false,[],$msg);
        }
    }

    /**
     * 退出登陆接口
     */
    public function logout(Request $request)
    {
        try {
            $this->logic->setUserGuard('user');
            $res = $this->logic->logout($request->getUserToken());
            if (empty($res)) {
                throw new VerifyException("退出登陆失败");
            }
            return $this->response->json(true);
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
        }
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
            $this->logic->setUserGuard('user');
            $res = $this->logic->refreshUserToken($request->getUserToken());
            if (empty($res)) {
                throw new BusinessException("操作失败");
            }
            return $this->response->json(true, $res);

        } catch (BusinessException $e) {
            return $this->response->json(false, [], $e->getMessage(), $e->getCode());
        }
    }
}