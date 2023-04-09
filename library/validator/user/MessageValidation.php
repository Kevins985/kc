<?php

namespace library\validator\user;
use support\extend\Validator;

class MessageValidation extends Validator{

    /**
     * 发布消息
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAddMessage($data){
        $this->setRules([
            'message_type' => 'required|integer',
            'user_id' => 'required|integer',
            'identity' => 'required|integer',
            'content'=> 'required|string',
        ]);
        $this->setAttributes([
            'message_type'=>'消息类型',
            'user_id'=>'用户ID',
            'identity'=>'用户身份',
            'content'=>'内容',
        ]);
        return $this->checkValidate($data);
    }
}
