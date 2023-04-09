<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberTeamModel;
use support\utils\Data;
use support\utils\Random;

class MemberTeamService extends Service
{
    public function __construct(MemberTeamModel $model)
    {
        $this->model = $model;
    }

    public function getInviteCode(){
        $str = Random::getRandStr(8);
        $inviteObj = $this->get($str,'invite_code');
        if(!empty($inviteObj)){
            return $this->getInviteCode();
        }
        return $str;
    }

    /**
     * 获取用的的级别
     * @param array $user_ids
     */
    public function getUserInviteLevel(array $user_ids){
        $rows = $this->fetchAll(['user_id'=>['in',$user_ids]],[],['user_id','invite_cnt'])->toArray();
        return Data::toKVArray($rows,'user_id','invite_cnt');
    }

    /**
     * 获取用的邀请列表
     * @param array $user_ids
     */
    public function getTeamListByIds(array $user_ids){
        $rows = $this->fetchAll(['user_id'=>['in',$user_ids]])->toArray();
        return Data::toKVArray($rows,'user_id');
    }
}
