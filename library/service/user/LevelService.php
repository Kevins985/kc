<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\LevelModel;

class LevelService extends Service
{
    public function __construct(LevelModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取会员等级
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll([],['grade'=>'asc'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['grade']] = $v;
        }
        return $data;
    }
}
