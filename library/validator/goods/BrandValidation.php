<?php

namespace library\validator\goods;
use support\extend\Validator;

class BrandValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'brand_name' => 'required|string',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'brand_name'=>'品牌名称',
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
            'brand_name' => 'required|string',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'brand_name'=>'品牌名称',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
