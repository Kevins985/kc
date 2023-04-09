<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\ProjectOrderModel;

class ProjectOrderService extends Service
{
    public function __construct(ProjectOrderModel $model)
    {
        $this->model = $model;
    }
}
