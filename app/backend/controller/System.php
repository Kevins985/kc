<?php

namespace app\backend\controller;

use library\logic\DictLogic;
use support\controller\Backend;
use support\extend\Request;

class System extends Backend
{
    /**
     * @Inject
     * @var DictLogic
     */
    private $dictLogic;

    /**
     * 网站设置
     */
    public function web(Request $request)
    {
        $data = $this->dictLogic->getDictListForType(0);
        $this->response->assign('data',$data);
        return $this->response->layout('system/web');
    }

    /**
     * 分佣设置
     */
    public function commission(Request $request)
    {
        $data = $this->dictLogic->getDictListForType(1);
        $this->response->assign('data',$data);
        return $this->response->layout('system/web');
    }
}