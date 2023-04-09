<?php

namespace library\validator\user;
use support\extend\Validator;

class MemberValidation extends Validator{

    /**
     * 验证修改密码
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingModifyPassword($data){
        $this->setRules([
            'old_pass' => 'old_pass|string',
            'new_pass'=> 'new_pass|string',
        ]);
        $this->setAttributes([
            'old_pass'=>'旧密码',
            'new_pass'=>'新密码'
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 提交实名验证
     * @param $data
     */
    public function checkingSubmitRealnameAuth($data){
        $this->setRules([
            'real_name' => 'required|string',
            'card_id'=> 'required|isIdCard',
//            'front_pic'=> 'required|string',
//            'reverse_pic'=> 'required|string',
        ]);
        $this->setAttributes([
            'real_name'=>'真实姓名',
            'card_id'=>'身份证',
//            'front_pic'=> '身份证正面照',
//            'reverse_pic'=> '身份证反面照',
        ]);
        return $this->checkValidate($data);
    }
}
