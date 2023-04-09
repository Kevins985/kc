<?php

namespace library\validator\sys;
use support\extend\Validator;

class RoleValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'role_name' => 'required|string',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'role_name'=>'角色名称',
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
            'role_name' => 'required|string',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'role_name'=>'角色名称',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
