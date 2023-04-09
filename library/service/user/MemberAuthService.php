<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberAuthModel;

class MemberAuthService extends Service
{
    public function __construct(MemberAuthModel $model)
    {
        $this->model = $model;
    }
}
