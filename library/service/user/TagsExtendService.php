<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\TagsExtendModel;

class TagsExtendService extends Service
{
    public function __construct(TagsExtendModel $model)
    {
        $this->model = $model;
    }
}
