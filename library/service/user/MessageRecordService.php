<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MessageRecordModel;

class MessageRecordService extends Service
{
    public function __construct(MessageRecordModel $model)
    {
        $this->model = $model;
    }
}
