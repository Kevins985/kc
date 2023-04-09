<?php

namespace library\service\sys;

use support\Container;
use support\extend\Service;
use library\model\sys\RoleModel;
use support\utils\Data;

class RoleService extends Service
{
    public function __construct(RoleModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可用的角色
     * @param null $ids
     * @param string $cell
     * @return array
     */
    public function getSelectList($parent_id=null,$type=null){
        $params = [];
        if(!is_null($parent_id)){
            $params['parent_id'] = $parent_id;
        }
        $rows = $this->fetchAll($params,[],['role_id','role_name','parent_id as pid'])->toArray();
        if($type=='tree'){
            Data::$zoomAry = [];
            return Data::getArrayZoomList($rows,'role_name','role_id');
        }
        else{
            $data = [];
            foreach($rows as $v){
                $data[$v['role_id']] = $v;
            }
            return $data;
        }
    }

    /**
     * 根据ID获取所有的名称
     * @param $role_ids
     */
    public function getRoleNameByIds(array $role_ids){
        $data = $this->fetchAll(['role_id'=>['in',$role_ids]],[],['role_id','role_name'])->toArray();
        return Data::toKVArray($data,'role_id','role_name');
    }

    /**
     * 保存角色全县
     * @param int $role_id
     * @param array $menu_ids
     */
    public function saveRoleMenus(int $role_id,array $menu_ids){
        $res = $this->update($role_id,['menu_ids'=>json_encode($menu_ids)]);
        if($res){
            $menuService = Container::get(MenuService::class);
            $route_ids = $menuService->pluck('route_id',['menu_id'=>['in',$menu_ids]]);
            $route_ids = array_filter($route_ids);
            $rbacService = Container::get(CasbinRbacService::class);
            return $rbacService->saveRoleGrant($role_id,$route_ids);
        }
        return false;
    }
}
