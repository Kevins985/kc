<?php

namespace library\validator\user;
use support\extend\Validator;

class OrderValidation extends Validator{


    /**
     * 验证创建理财订单类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingCreateGoodsOrder($data){
        $this->setRules([
            'spu_id' => 'required|integer',
        ]);
        $this->setAttributes([
            'spu_id'=>'产品ID',
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证创建理财订单类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingCreateWithdrawOrder($data){
        $this->setRules([
            'money'=> 'required|numeric',
        ]);
        $this->setAttributes([
            'money'=>'金额',
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证创建理财订单类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingCreateRechargeOrder($data){
        $this->setRules([
            'money'=> 'required|numeric',
        ]);
        $this->setAttributes([
            'money'=>'金额',
        ]);
        return $this->checkValidate($data);
    }
}
