<?php

namespace library\validator\user;
use support\extend\Validator;

class MemberAddressValidation extends Validator{
    
    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'name' => 'required|string',
            'mobile' => 'required|string',
            'country' => 'required|string',
            'local' => 'required|string',
            'address' => 'required|string'
        ]);
        $this->setAttributes([
            'name' => '收件人姓名',
            'mobile' => '手机号',
            'country' => '国家',
            'local' => '地区名称',
            'address' => '详细地址'
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
            'name' => 'required|string',
            'mobile' => 'required|string',
            'country' => 'required|string',
            'local' => 'required|string',
            'address' => 'required|string'
        ]);
        $this->setAttributes([
            'name' => '收件人姓名',
            'mobile' => '手机号',
            'country' => '国家',
            'local' => '地区名称',
            'address' => '详细地址'
        ]);
        return $this->checkValidate($data);
    }
}
