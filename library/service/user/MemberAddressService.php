<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberAddressModel;

class MemberAddressService extends Service
{
    public function __construct(MemberAddressModel $model)
    {
        $this->model = $model;
    }

    public function getDefaultAddress($user_id){
        return $this->fetch(['user_id'=>$user_id,'is_default'=>1]);
    }

    public function getUserAddress($user_id){
        return $this->fetch(['user_id'=>$user_id],['is_default'=>'desc']);
    }
}
