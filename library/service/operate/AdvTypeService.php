<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\AdvTypeModel;

class AdvTypeService extends Service
{
    public function __construct(AdvTypeModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选择的广告类型
     * @return array
     */
    public function getSelectList($from_term=null){
        $rows = $this->fetchAll(['from_term'=>$from_term],['sort'=>'desc'],['type_id','type_name','type_code'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['type_id']] = $v;
        }
        return $data;
    }
}
