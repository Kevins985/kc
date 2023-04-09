<?php

namespace library\service\sys;

use support\Container;
use support\extend\Service;
use library\model\sys\CasbinRbacModel;

class CasbinRbacService extends Service
{
    public function __construct(CasbinRbacModel $model)
    {
        $this->model = $model;
    }

    /**
     * 设置角色权限
     * @param $user_id
     * @param $route_ids
     * @return bool
     */
    public function saveUserGrant($user_id,$route_ids){
        $this->deleteAll(['ptype'=>'p','v0'=>'user'.$user_id]);
        $casbinList = [];
        $routeService = Container::get(RouteService::class);
        foreach($route_ids as $id){
            $grantObj = $routeService->get($id);
            if(!empty($grantObj)){
                $casbinList[] = ['ptype'=>'p','v0'=>('user'.$user_id),'v1'=>$id,'v2'=>$grantObj['method'],'created_time'=>time()];
            }
        }
        $res = $this->insert($casbinList);
        if($res){
            $cmd = 'cd '.base_path().' && php server.php reload -d';
            exec($cmd,$output,$result);
        }
        return $res;
    }

    /**
     * 设置角色权限
     * @param $role_id
     * @param $route_ids
     * @return bool
     */
    public function saveRoleGrant($role_id,$route_ids){
        $this->deleteAll(['ptype'=>'p','v0'=>'role'.$role_id]);
        $casbinList = [];
        $routeService = Container::get(RouteService::class);
        foreach($route_ids as $id){
            $grantObj = $routeService->get($id);
            if(!empty($grantObj)){
                $casbinList[] = ['ptype'=>'p','v0'=>('role'.$role_id),'v1'=>$id,'v2'=>$grantObj['method'],'created_time'=>time()];
            }
        }
        $res = $this->insert($casbinList);
        if($res){
            $cmd = 'cd '.base_path().' && php server.php reload -d';
            exec($cmd,$output,$result);
        }
        return $res;
    }

    /**
     * 设置用户角色
     * @param $userid
     * @param $role_id
     */
    public function setUserRole($userid,$role_id){
        $user = 'user'.$userid;
        $role = 'role'.$role_id;
        $this->deleteAll(['ptype'=>'g','v0'=>'user'.$userid]);
        $res = $this->create(['ptype'=>'g','v0'=>$user,'v1'=>$role]);
        if($res){
            $cmd = 'cd '.base_path().' && php server.php reload -d';
            exec($cmd,$output,$result);
        }
        return $res;
    }
}
