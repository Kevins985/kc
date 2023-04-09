<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\AdvLocationModel;

class AdvLocationService extends Service
{
    public function __construct(AdvLocationModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选择的广告位
     * @return array
     */
    public function getSelectList($from_term=null){
        $rows = $this->fetchAll(['from_term'=>$from_term],['sort'=>'desc'],['location_id','location_name','location_code'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['location_id']] = $v;
        }
        return $data;
    }
}
