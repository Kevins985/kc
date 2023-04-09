<?php

namespace library\validator\goods;
use support\extend\Validator;

class SpuValidation extends Validator{

    
    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'title' => 'required|string',
            'image_url'=> 'required|string',
            'market_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
        ]);
        $this->setAttributes([
            'title' => '商品名称',
            'image_url'=> '商品图片',
            'market_price' => '市场价格',
            'sell_price' => '销售价格',
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
            'title' => 'required|string',
            'image_url'=> 'required|string',
            'market_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
        ]);
        $this->setAttributes([
            'title' => '商品名称',
            'image_url'=> '商品图片',
            'market_price' => '市场价格',
            'sell_price' => '销售价格',
        ]);
        return $this->checkValidate($data);
    }
}
