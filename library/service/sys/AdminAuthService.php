<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\AdminAuthModel;

class AdminAuthService extends Service
{
    public function __construct(AdminAuthModel $model)
    {
        $this->model = $model;
    }
}
