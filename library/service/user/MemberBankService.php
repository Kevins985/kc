<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberBankModel;

class MemberBankService extends Service
{
    public function __construct(MemberBankModel $model)
    {
        $this->model = $model;
    }
}
