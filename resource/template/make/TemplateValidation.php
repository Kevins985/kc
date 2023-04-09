<?php

namespace library\validator\module;
use support\extend\Validator;

class TemplateValidation extends Validator{
    
    /**
     * 验证参数参考类 (方法前加checking,后面是请求的具体方法名)
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingTmp($data){
        $this->setRules([
            'tmp_name' => 'required|string',
            'tmp_id'=> 'required|integer',
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:10'
        ]);
        $this->setAttributes([
            'tmp_name'=>'名称',
            'tmp_id'=>'父级ID',
            'email' => '邮箱',
            'password' => '密码'
        ]);
        return $this->checkValidate($data);
    }
    
    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){

        return true;
    }
    
    /**
     * 验证修改
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingUpdate($data){

        return true;
    }
}
