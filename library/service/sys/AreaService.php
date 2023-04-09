<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\AreaModel;

class AreaService extends Service
{
    public function __construct(AreaModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取下级地址列表
     * @param $parent_id
     */
    public function getAreaList($parent_id=0){
        return $this->fetchAll(['parent_id'=>$parent_id,'status'=>1],[],['id','name','level']);
    }
}
