<?php

namespace app\api\controller;

use library\logic\AuthLogic;
use library\service\user\MemberService;
use library\validator\user\AuthValidation;
use support\Container;
use support\controller\Api;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

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
        $request->session()->set('captcha', strtolower($captcha->getCheckCode()));
        return $this->response->output($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 登陆接口
     */
    public function login(Request $request)
    {
        try{
            $request->verifyRequestData();
            $data = $this->getPost(['account','vcode','num_code','password']);
            $this->logic->setUserGuard('user');
            $this->logic->setClientType('wap');
            $res = $this->logic->login($data['account'],$data['password'],$data['vcode']);
            if (empty($res)) {
                throw new VerifyException("登陆失败");
            }
            return $this->response->json(true,['token'=>$res], "登陆成功");
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 注册接口
     * @param $post {account,type,password,nickname,invitationCode}
     */
    public function register(Request $request)
    {
        try{
            $request->verifyRequestData();
            $post = $this->getPost(['account','mobile','email','type','password','nickname','invitationCode','vcode']);
            $this->logic->setUserGuard('user');
            $account = $post['type']=='email'?$post['email']:$post['mobile'];
            if(!verifyCodeMsg($account,$post['vcode'],$post['type'])) {
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
     * 发送验证码
     * @param Request $request
     */
    public function sendMsgCode(Request $request){
        try{
            $type = $this->getPost('type','mobile');
            $account = $this->getPost('account');
            $num_code = $this->getPost('num_code');
            $from = $this->getPost('from','register');
            $memberService = Container::get(MemberService::class);
            $accountObj = $memberService->getUserByAccount($account);
            if($from!='register'){
                if(empty($accountObj)){
                    throw new BusinessException('该账号不存在');
                }
                elseif($from=='findPwd'){
                    $account = $type=='email'?$accountObj['email']:$accountObj['mobile'];
                }
            }
//            elseif(!empty($accountObj)){
//                throw new BusinessException('该账号已经存在');
//            }
//            if($type=='mobile' && !empty($num_code) && $num_code!='86'){
//                $account = $num_code.$account;
//            }
            sendCodeMsg($account,$type);
            return $this->response->json(true);
        }
        catch (\Throwable $e){
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 重置用户密码
     * @param Request $request
     */
    public function resetPwd(Request $request){
        try{
            $post = $this->getPost(['account','type','num_code','vcode','password']);
            $memberService = Container::get(MemberService::class);
            $accountObj = $memberService->getUserByAccount($post['account']);
            if(empty($accountObj)){
                throw new BusinessException('该账号不存在');
            }
            $account = $post['type']=='email'?$accountObj['email']:$accountObj['mobile'];
            if(!verifyCodeMsg($account,$post['vcode'],$post['type'])) {
                throw new VerifyException("输入的验证码不正确");
            }
            $memberObj = $memberService->modifyPassword($accountObj['user_id'],$post['password']);
            if(empty($memberObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
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