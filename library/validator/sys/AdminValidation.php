<?php

namespace library\validator\sys;
use support\extend\Validator;

class AdminValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'account' => 'required|string|min:6|max:30',
            'mobile'=> 'required|string',
            'email' => 'required|email',
            'realname' => 'required|string'
        ]);
        $this->setAttributes([
            'account'=>'用户账号',
            'mobile'=> '手机号',
            'email' => '邮箱',
            'realname' => '真实姓名'
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证修改
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingUpdate($data){
        $this->setRules([
            'mobile'=> 'required|string',
            'email' => 'required|email',
            'realname' => 'required|string'
        ]);
        $this->setAttributes([
            'mobile'=> '手机号',
            'email' => '邮箱',
            'realname' => '真实姓名'
        ]);
        return $this->checkValidate($data);
    }
}
