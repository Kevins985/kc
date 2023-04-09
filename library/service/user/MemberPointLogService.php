<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberPointLogModel;

class MemberPointLogService extends Service
{
    public function __construct(MemberPointLogModel $model)
    {
        $this->model = $model;
    }
}
