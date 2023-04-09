<?php

namespace library\validator\sys;
use support\extend\Validator;

class MenuValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'menu_name' => 'required|string',
            'menu_type'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'menu_name'=>'菜单名称',
            'menu_type'=>'类型',
            'sort' => '排序',
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
            'menu_name' => 'required|string',
            'menu_type'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'menu_name'=>'菜单名称',
            'menu_type'=>'类型',
            'sort' => '排序',
        ]);
        return $this->checkValidate($data);
    }
}
