<?php

namespace library\validator\sys;
use support\extend\Validator;

/**
 * 模型验证类
 */
class SystemValidation extends Validator{
    
    /**
     * 获取随机验证码验证参数
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingGetRandomStr($data){
        $this->setRules([
            'type' => 'required|integer',
            'length'=> 'required|integer'
        ]);
        $this->setAttributes([
            'type'=>'类型',
            'length'=>'长度',
        ]);
        return $this->checkValidate($data);
    }

}
