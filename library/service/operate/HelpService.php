<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\HelpModel;

class HelpService extends Service
{
    public function __construct(HelpModel $model)
    {
        $this->model = $model;
    }
}
