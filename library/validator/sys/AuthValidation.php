<?php

namespace library\validator\sys;
use support\extend\Validator;

class AuthValidation extends Validator{

    /**
     * 验证登陆方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingLogin($data){
        $this->setRules([
            'account' => 'required',
            'password'=> 'required|string',
        ]);
        return $this->checkValidate($data);
    }

    protected function checkingRegister($data){
        $this->setRules([
            'account' => 'required|email',
            'password'=> 'required|string|min:6|max:30',
        ]);
        return $this->checkValidate($data);
    }
}
