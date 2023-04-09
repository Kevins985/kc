<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberProfitLogModel;

class MemberProfitLogService extends Service
{
    public function __construct(MemberProfitLogModel $model)
    {
        $this->model = $model;
    }
}
