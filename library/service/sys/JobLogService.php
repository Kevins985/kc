<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\JobLogModel;

class JobLogService extends Service
{
    public function __construct(JobLogModel $model)
    {
        $this->model = $model;
    }
}
