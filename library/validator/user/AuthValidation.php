<?php

namespace library\validator\user;
use support\extend\Validator;

class AuthValidation extends Validator{

    /**
     * 验证登陆方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingLogin($data){
        $this->setRules([
            'account' => 'required|string',
            'password'=> 'required|string',
        ]);
        $this->setAttributes([
            'account'=>'账号',
            'password'=>'密码',
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证注册方法
     */
    protected function checkingRegister($data){
        $this->setRules([
            'account' => 'required|string',
            'password'=> 'required|string|min:6|max:30',
            'invitationCode' => 'required|string',
            'nickname' => 'required|string',
            'mobile' => 'required|mobile',
            'vcode'=>'required|string',
        ]);
        $this->setAttributes([
            'account'=>'账号',
            'password'=>'密码',
            'invitationCode' => '邀请码',
            'nickname' => '姓名',
            'mobile' => '手机号码',
            'vcode' => '验证码',
        ]);
        return $this->checkValidate($data);
    }


    /**
     * 验证注册方法
     */
    protected function checkingResetPwd($data){
        $this->setRules([
            'account' => 'required|string',
            'vcode'=>'required|string',
            'password'=> 'required|string|min:6|max:30',
        ]);
        $this->setAttributes([
            'account'=>'账号',
            'vcode' => '验证码',
            'password'=>'密码',
        ]);
        return $this->checkValidate($data);
    }
}
