<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\MakeLogsModel;

class MakeLogsService extends Service
{
    public function __construct(MakeLogsModel $model)
    {
        $this->model = $model;
    }
}
