<?php

namespace library\validator\sys;
use support\extend\Validator;

class CountryValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'name' => 'required|string',
            'name_en' => 'required|string',
            'code' => 'required|string',
            'sort'=> 'required|integer'
        ]);
        $this->setAttributes([
            'name'=>'国家名称',
            'name_en'=>'国家英文名称',
            'code'=>'二字码',
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
            'name' => 'required|string',
            'name_en' => 'required|string',
            'code' => 'required|string',
            'sort'=> 'required|integer'
        ]);
        $this->setAttributes([
            'name'=>'国家名称',
            'name_en'=>'国家英文名称',
            'code'=>'二字码',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
