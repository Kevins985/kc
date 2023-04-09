<?php

namespace library\validator\operate;
use support\extend\Validator;

class HelpValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'title' => 'required|string',
            'content' => 'required|string',
            'category_id'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'title'=>'标题',
            'content'=>'内容',
            'category_id'=>'分类ID',
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
            'title' => 'required|string',
            'content' => 'required|string',
            'category_id'=> 'required|integer',
            'sort'=> 'required|integer',
        ]);
        $this->setAttributes([
            'title'=>'标题',
            'content'=>'内容',
            'category_id'=>'分类ID',
            'sort'=>'排序',
        ]);
        return $this->checkValidate($data);
    }
}
