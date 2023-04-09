<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\JobGroupModel;

class JobGroupService extends Service
{
    public function __construct(JobGroupModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选的任务分类
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll([],['sort'=>'desc'],['group_id','group_name'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['group_id']] = $v;
        }
        return $data;
    }
}
