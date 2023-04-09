<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\NoticeModel;

class NoticeService extends Service
{
    public function __construct(NoticeModel $model)
    {
        $this->model = $model;
    }
}
