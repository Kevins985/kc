<?php

namespace library\validator\sys;
use support\extend\Validator;

class DictValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'dict_name' => 'required|string',
            'dict_code'=> 'required|string',
            'dict_type' => 'required|integer',
            'sort' => 'required|integer'
        ]);
        $this->setAttributes([
            'dict_name' => '字典名称',
            'dict_code'=> '字典标识码',
            'dict_type' => '字典类型',
            'sort' => '排序值'
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
            'dict_id' => 'required|integer',
            'dict_name' => 'required|string',
            'dict_code'=> 'required|string',
            'dict_type' => 'required|integer',
            'sort' => 'required|integer'
        ]);
        $this->setAttributes([
            'dict_name' => '字典名称',
            'dict_code'=> '字典标识码',
            'dict_type' => '字典类型',
            'sort' => '排序值'
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证配置
     * @param type $data
     */
    protected function checkingSetting($data){
        $this->setRules([
            'dict_code' => 'required|string',
            'list' => 'required|array|min:1',
        ]);
        $this->setAttributes([
            'dict_code' => '字典标识码',
            'list'=> '字典数据',
        ]);
        return $this->checkValidate($data);
    }

    /**
     * 验证删除
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingDelete($data){
        $this->setRules([
            'id' => 'required|string'
        ]);
        return $this->checkValidate($data);
    }
}
