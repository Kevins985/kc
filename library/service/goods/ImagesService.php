<?php

namespace library\service\goods;

use support\extend\Service;
use library\model\goods\ImagesModel;

class ImagesService extends Service
{
    public function __construct(ImagesModel $model)
    {
        $this->model = $model;
    }
}
