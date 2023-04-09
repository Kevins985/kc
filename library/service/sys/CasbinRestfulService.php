<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\CasbinRestfulModel;

class CasbinRestfulService extends Service
{
    public function __construct(CasbinRestfulModel $model)
    {
        $this->model = $model;
    }
}
