<?php

namespace library\validator\sys;
use support\extend\Validator;

class CurrencyValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'currency_name' => 'required|string',
            'currency_code' => 'required|string',
            'currency_symbol' => 'required|string',
            'sort'=> 'required|integer'
        ]);
        $this->setAttributes([
            'currency_name'=>'货币名称',
            'currency_code'=>'货币代码',
            'currency_symbol'=>'货币符号',
            'sort'=>'排序',
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
            'currency_name' => 'required|string',
            'currency_code' => 'required|string',
            'currency_symbol' => 'required|string',
            'sort'=> 'required|integer'
        ]);
        $this->setAttributes([
            'currency_name'=>'货币名称',
            'currency_code'=>'货币代码',
            'currency_symbol'=>'货币符号',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
