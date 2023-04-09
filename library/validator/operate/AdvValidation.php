<?php

namespace library\validator\operate;
use support\extend\Validator;

class AdvValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'adv_name' => 'required|string',
            'adv_image' => 'required|string',
            'from_term' => 'required|string',
            'type_id'=> 'required|integer',
            'location_id'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'adv_name'=>'广告名称',
            'adv_image'=>'广告图片',
            'from_term'=>'投放终端',
            'type_id'=>'广告类型ID',
            'location_id'=>'广告位ID',
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
            'adv_name' => 'required|string',
            'adv_image' => 'required|string',
            'from_term' => 'required|string',
            'type_id'=> 'required|integer',
            'location_id'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'adv_name'=>'广告名称',
            'adv_image'=>'广告图片',
            'from_term'=>'投放终端',
            'type_id'=>'广告类型ID',
            'location_id'=>'广告位ID',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
