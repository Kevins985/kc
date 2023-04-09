<?php

namespace library\validator\sys;
use support\extend\Validator;

class UploadFilesValidation extends Validator{

    /**
     * 验证文件上传
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingFile($data){
        $this->setRules([
            'uuid' => 'required|string',
            'type'=> 'required|string'
        ]);
        $this->setAttributes([
            'type'=>'类型',
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证CURL上传
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingCurl($data){
        $this->setRules([
            'uuid' => 'required|string',
            'type'=> 'required|string'
        ]);
        $this->setAttributes([
            'type'=>'类型',
        ]);
        return $this->checkValidate($data);
    }
}
