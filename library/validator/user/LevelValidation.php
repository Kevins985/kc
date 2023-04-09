<?php

namespace library\validator\user;
use support\extend\Validator;

class LevelValidation extends Validator{

    /**
     * 验证添加方法类
     * @param array $data 请求数据
     * @return boolean
     */
    protected function checkingAdd($data){
        $this->setRules([
            'level_name' => 'required|string',
            'grade'=> 'required|int',
            'discount'=> 'required|int',
            'exp_num' => 'required|int',
        ]);
        $this->setAttributes([
            'level_name'=>'会员等级名称',
            'grade' => '级别',
            'discount' => '折扣率',
            'exp_num' => '经验值',
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
            'level_name' => 'required|string',
            'grade'=> 'required|int',
            'discount'=> 'required|int',
            'exp_num' => 'required|int',
        ]);
        $this->setAttributes([
            'level_name'=>'会员等级名称',
            'grade' => '级别',
            'discount' => '折扣率',
            'exp_num' => '经验值',
        ]);
        return $this->checkValidate($data);
    }
}
