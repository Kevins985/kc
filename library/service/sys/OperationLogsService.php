<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\OperationLogsModel;

class OperationLogsService extends Service
{
    public function __construct(OperationLogsModel $model)
    {
        $this->model = $model;
    }
}
