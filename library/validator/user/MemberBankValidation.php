<?php

namespace library\validator\user;
use support\extend\Validator;

class MemberBankValidation extends Validator{
    
    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'bank_type_id' => 'required|integer',
            'bank_card'=> 'required|string',
            'real_name' => 'required|string',
        ]);
        $this->setAttributes([
            'bank_type_id'=>'银行类型ID',
            'bank_card'=>'银行卡号',
            'real_name' => '持卡姓名',
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
            'bank_type_id' => 'required|integer',
            'bank_card'=> 'required|isBankCard',
            'real_name' => 'required|string',
        ]);
        $this->setAttributes([
            'bank_type_id'=>'银行类型ID',
            'bank_card'=>'银行卡号',
            'real_name' => '持卡姓名',
        ]);
        return $this->checkValidate($data);
    }
}
