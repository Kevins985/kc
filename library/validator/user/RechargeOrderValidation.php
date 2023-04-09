<?php

namespace library\validator\user;
use support\extend\Validator;

class RechargeOrderValidation extends Validator{


    /**
     * 验证创建理财订单类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingCreate($data){
        $this->setRules([
            'money'=> 'required|numeric',
            'image_url'=> 'required|string',
        ]);
        $this->setAttributes([
            'money'=>'金额',
            'image_url'=>'支付凭证',
        ]);
        return $this->checkValidate($data);
    }
}
