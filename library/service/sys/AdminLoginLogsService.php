<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\AdminLoginLogsModel;

class AdminLoginLogsService extends Service
{
    public function __construct(AdminLoginLogsModel $model)
    {
        $this->model = $model;
    }
}
