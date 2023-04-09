<?php

namespace app\backend\controller\sys;

use library\logic\MenusLogic;
use library\logic\ReflectionLogic;
use library\service\sys\MenuService;
use library\service\sys\RoleService;
use library\service\sys\AdminService;
use library\validator\sys\MenuValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Menu extends Backend
{
    public function __construct(MenuService $service,MenuValidation $validation,MenusLogic $logic)
    {
        $this->service = $service;
        $this->validation = $validation;
        $this->logic = $logic;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/menu/list',$params,['menu_type'=>'asc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $menusList = $this->logic->getParentSelectMenus(3);
        $this->response->assign('menusList',$menusList);
        $menuTypes = $this->logic->getMenuTypes();
        $this->response->assign('menuTypes',$menuTypes);
        return $this->response->layout('sys/menu/list');
    }

    /**
     * 添加
     */
    public function add(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax()) {
                    throw new VerifyException('Exception request');
                }
                $menuObj = $this->logic->create($post);
                if(empty($menuObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $menusList = $this->logic->getParentSelectMenus(0);
        $this->response->assign('menusList',$menusList);
        $menuTypes = $this->logic->getMenuTypes();
        $this->response->assign('menuTypes',$menuTypes);
        return $this->response->layout('sys/menu/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['menu_id'])) {
                    throw new VerifyException('Exception request');
                }
                $menuObj = $this->service->update($post['menu_id'],$post);
                if(empty($menuObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else {
            $id = $this->getParams('id',0);
            $menuObj = $this->service->get($id);
            if(empty($menuObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $menusList = $this->logic->getParentSelectMenus($menuObj['menu_type']);
            $this->response->assign('menusList',$menusList);
            $menuTypes = $this->logic->getMenuTypes();
            $this->response->assign('menuTypes',$menuTypes);
            $this->response->assign("data",$menuObj);
            $this->response->addScriptAssign(['initData'=>$menuObj->toArray()]);
            return $this->response->layout('sys/menu/update');
        }
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        try {
            $id = $this->getParams('id',0);
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $ids = explode(',',$id);
            if(count($ids)>1){
                $res = $this->service->batchDelete($ids);
            }
            else{
                $res = $this->service->delete($id);
            }
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 获取树级菜单
     */
    public function getTreeMenus(Request $request)
    {
        try {
            $role_id = $this->getParams('role_id');
            $userid = $this->getParams('userid');
            if (!$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $select_menu_ids = [];
            $all_menu = $this->logic->queryTreeMenus();
            $my_menu_ids = $this->logic->getUserMenusIds($request->getUserID());
            if(!empty($userid)){
                $adminService = Container::get(AdminService::class);
                $adminObj = $adminService->get($userid);
                if (empty($adminObj)) {
                    throw new VerifyException('用户不存在');
                }
                $role_id = $adminObj['role_id'];
                if(!empty($adminObj['menu_ids'])){
                    $select_menu_ids = json_decode($adminObj['menu_ids'],true);
                }
            }
            if(!empty($role_id)){
                $roleService = Container::get(RoleService::class);
                $roleObj = $roleService->get($role_id);
                if (empty($roleObj)) {
                    throw new VerifyException('角色不存在');
                }
                if(!empty($roleObj['menu_ids'])){
                    $select_menu_ids = array_merge($select_menu_ids,json_decode($roleObj['menu_ids'],true));
                    $select_menu_ids = array_unique($select_menu_ids);
                }
            }
            $menu = [];
            foreach ($all_menu as $key => $v) {
                if($request->checkIsAdmin() || in_array($v['id'],$my_menu_ids)){
                    if (!empty($select_menu_ids) && in_array($v['id'], $select_menu_ids)) {
                        $v['checked'] = true;
                    }
                    $menu[] =$v;
                }
            }
            return $this->response->json(true,$menu);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取菜单图标
     */
    public function getMenusStyle(Request $request)
    {
        $type = $this->getParams('type');
        if($type=='icon'){
            return $this->response->view('sys/menu/_icon');
        }
        return $this->response->view('sys/menu/_button');
    }

    /**
     * 获取子级菜单
     */
    public function getChildList(Request $request)
    {
        try {
            $menu_type = $this->getParams('menu_type');
            if (!$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $options = ['menu_id'=>0,'menu_name'=>'顶级目录','pid'=>0,'level'=>0];
            if($menu_type != 0){
                $options = ['menu_id'=>'','menu_name'=>'请选择','pid'=>0,'level'=>0];
                $rows = $this->logic->getParentSelectMenus($menu_type);
                if(empty($rows)){
                    throw new VerifyException('暂无数据');
                }
                array_unshift($rows,$options);
            }
            else{
                $rows = [$options];
            }
            return $this->response->json(true, $rows);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取URL的功能列表
     */
    public function getUrlMenus(Request $request)
    {
        try {
            $module = $this->getParams('module');
            $controller = $this->getParams('controller');
            if (!$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $list = [];
            if(empty($module)){
                $list = ['backend'];
            }
            elseif (empty($controller)) {
                $reflectionLogic = Container::get(ReflectionLogic::class);
                $rows = $reflectionLogic->getAppControllerList($module);
                foreach($rows as $v){
                    $list[] = $v['name'];
                }
            }
            else {
                $list = $this->logic->getNotInDatabaseActions($module,$controller);
            }
            if (empty($list)) {
                throw new \Exception('暂无数据');
            }
            return $this->response->json(true,$list);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}