<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\MenuModel;

class MenuService extends Service
{
    public function __construct(MenuModel $model)
    {
        $this->model = $model;
    }

    /**
     * 检测方法是否在数据库存在
     * @param string $url
     * @return boolean
     */
    public function checkUrlIsExists($url) {
        $result = $this->get(route_id($url),'route_id');
        return empty($result) ? false : true;
    }


}
