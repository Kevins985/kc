<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberExtendModel;

class MemberExtendService extends Service
{
    public function __construct(MemberExtendModel $model)
    {
        $this->model = $model;
    }

    public function getMemberExtendList(array $user_ids){
        $rows = $this->fetchAll(['user_id'=>['in',$user_ids]]);
        $data = [];
        foreach($rows as $v){
            $data[$v['user_id']] = $v;
        }
        return $data;
    }
}
