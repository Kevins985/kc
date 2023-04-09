<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberLoginLogsModel;

class MemberLoginLogsService extends Service
{
    public function __construct(MemberLoginLogsModel $model)
    {
        $this->model = $model;
    }
}
