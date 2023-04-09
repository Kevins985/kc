<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\AdvModel;

class AdvService extends Service
{
    public function __construct(AdvModel $model)
    {
        $this->model = $model;
    }
}
