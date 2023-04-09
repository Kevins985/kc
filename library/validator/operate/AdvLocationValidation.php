<?php

namespace library\validator\operate;
use support\extend\Validator;

class AdvLocationValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'location_name' => 'required|string',
            'location_code' => 'required|string',
            'from_term' => 'required|string',
            'limit_num'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'location_name'=>'广告位名称',
            'location_code'=>'广告位标识码',
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
            'location_name' => 'required|string',
            'location_code' => 'required|string',
            'from_term' => 'required|string',
            'limit_num'=> 'required|integer',
            'width'=> 'required|integer',
            'height'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'location_name'=>'广告位名称',
            'location_code'=>'广告位标识码',
            'from_term'=>'投放终端',
            'limit_num'=>'限制数量',
            'width'=>'宽度',
            'height'=>'高度',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
