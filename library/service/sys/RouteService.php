<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\RouteModel;

class RouteService extends Service
{
    public function __construct(RouteModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选择的权限
     * @return array
     */
    public function getSelectList($module=null){
        $rows = $this->fetchAll(['module'=>$module])->toArray();
        $data = [];
        foreach($rows as $v){
            $key = $v['module'].'/'.$v['controller'].'/'.$v['action'];
            $data[$key] = $v;
        }
        return $data;
    }

    /**
     * 检测方法是否在数据库存在
     * @param string $url
     * @return boolean
     */
    public function checkUrlIsExists($url) {
        $result = $this->get(route_id($url));
        return empty($result) ? false : true;
    }
}
