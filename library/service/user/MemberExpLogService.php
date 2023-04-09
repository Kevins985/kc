<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberExpLogModel;

class MemberExpLogService extends Service
{
    public function __construct(MemberExpLogModel $model)
    {
        $this->model = $model;
    }
}
