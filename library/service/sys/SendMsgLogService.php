<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\SendMsgLogModel;

class SendMsgLogService extends Service
{
    public function __construct(SendMsgLogModel $model)
    {
        $this->model = $model;
    }
}
