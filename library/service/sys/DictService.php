<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\DictModel;

class DictService extends Service
{
    public function __construct(DictModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取字典列表
     * @param null $dict_type
     * @param null $status
     * @return array
     */
    public function getSelectList($dict_type=null,$status=null){
        $rows = $this->fetchAll(['dict_type'=>$dict_type,'status'=>$status],['sort'=>'asc'],['dict_id','dict_name','dict_code']);
        return $rows->toArray();
    }
}
