<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MessageModel;

class MessageService extends Service
{
    public function __construct(MessageModel $model)
    {
        $this->model = $model;
    }
}
