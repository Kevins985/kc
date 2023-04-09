<?php

namespace app\backend\controller\sys;

use library\service\sys\RoleService;
use library\validator\sys\RoleValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class Role extends Backend
{
    public function __construct(RoleService $service,RoleValidation $validation)
    {
        $this->service = $service;
        $this->validation = $validation;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/role/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $parent_ids = Data::toFlatArray($data->items(),'parent_id');
        $parentNames = $this->service->getRoleNameByIds($parent_ids);
        $this->response->assign('parentNames',$parentNames);
        return $this->response->layout('sys/role/list');
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
                $roleObj = $this->service->create($post);
                if(empty($roleObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $roles = $this->service->getSelectList(null,'tree');
        $this->response->assign('roles',$roles);
        return $this->response->layout('sys/role/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['role_id'])) {
                    throw new VerifyException('Exception request');
                }
                $roleObj = $this->service->update($post['role_id'],$post);
                if(empty($roleObj)){
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
            $roleObj = $this->service->get($id);
            if(empty($roleObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$roleObj);
            $roles = $this->service->getSelectList(null,'tree');
            $this->response->assign('roles',$roles);
            $this->response->addScriptAssign(['initData'=>$roleObj->toArray()]);
            return $this->response->layout('sys/role/update');
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
     * 设置角色的权限
     */
    public function setMenus(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['role_id'])) {
                    throw new VerifyException('Exception request');
                }
                $r_value = $this->service->saveRoleMenus($post['role_id'],$post['menu_ids']);
                return $this->response->json($r_value);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else {
            $id = $this->getParams('id',0);
            $roleObj = $this->service->get($id);
            if(empty($roleObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            return $this->response->layout('sys/role/set_menus',['data'=>$roleObj]);
        }
    }
}