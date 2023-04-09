<?php

namespace library\validator\operate;
use support\extend\Validator;

class AdvTypeValidation extends Validator{

    
    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'type_name' => 'required|string',
            'type_code' => 'required|string',
            'from_term' => 'required|string',
            'limit_num'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'type_name'=>'广告类型名称',
            'type_code'=>'类型标识码',
            'from_term'=>'投放终端',
            'limit_num'=>'限制数量',
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
            'type_name' => 'required|string',
            'type_code' => 'required|string',
            'from_term' => 'required|string',
            'limit_num'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'type_name'=>'广告类型名称',
            'type_code'=>'类型标识码',
            'from_term'=>'投放终端',
            'limit_num'=>'限制数量',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
