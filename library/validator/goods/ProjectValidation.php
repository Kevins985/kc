<?php

namespace library\validator\goods;
use support\extend\Validator;

class ProjectValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'project_name' => 'required|string',
            'project_prefix' => 'required|string',
            'user_cnt'=>'required|numeric|gt:0',
            'user_id'=>'required|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);
        $this->setAttributes([
            'project_name' => '项目名称',
            'project_prefix'=> '前缀',
            'user_cnt'=>'项目成团人数',
            'user_id'=>'用户ID',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
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
            'project_name' => 'required|string',
            'project_prefix' => 'required|string',
            'user_cnt'=>'required|numeric|gt:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);
        $this->setAttributes([
            'project_name' => '项目名称',
            'project_prefix'=> '前缀',
            'user_cnt'=>'项目成团人数',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
        ]);
        return $this->checkValidate($data);
    }
}
