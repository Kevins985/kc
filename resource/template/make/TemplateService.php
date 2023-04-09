<?php

namespace library\service\module;

use support\extend\Service;
use library\model\module\TemplateModel;

class TemplateService extends Service
{
    public function __construct(TemplateModel $model)
    {
        $this->model = $model;
    }
}
