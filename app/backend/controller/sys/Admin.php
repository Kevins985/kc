<?php

namespace app\backend\controller\sys;

use library\logic\AuthLogic;
use library\service\sys\AdminService;
use library\service\sys\RoleService;
use library\validator\sys\AdminValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class Admin extends Backend
{

    /**
     * @Inject
     * @var RoleService
     */
    private $roleService;

    public function __construct(AdminService $service,AdminValidation $validation,AuthLogic $logic)
    {
        $this->service = $service;
        $this->validation = $validation;
        $this->logic = $logic;
    }

    /**
     * 验证账号是否存在
     */
    public function checkAccountExists(Request $request)
    {
        try {
            $account = $this->getParams('account');
            if (!$request->isAjax() || empty($account)) {
                throw new \Exception('Exception request');
            }
            if($this->loginUser->getAccount()!=$account){
                $adminObj = $this->service->getUserByAccount($account);
                if(!empty($adminObj)){
                    throw new \Exception("账号已经存在");
                }
            }
            return $this->response->output(json_encode(['valid'=>true]));
        } catch (\Exception $e) {
            return $this->response->output(json_encode(['valid'=>false]));
        }
    }

    /**
     * 分配权限
     * @params $post {userid,menu_ids}
     */
    public function setUserGrant(Request $request)
    {
        $post = $this->getPost();
        if(!empty($post) && $request->isAjax()){
            try{
                $this->addRequestLock($post);
                $r_value = $this->service->saveAdminMenusGrant($post['userid'],$post['menu_ids']);
                return $this->response->json($r_value);
            }
            catch (\Exception $e) {
                $this->deleteRequestLock($post,$e->getMessage());
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $id = $this->getParams('id',0);
        $adminObj = $this->service->get($id);
        if(empty($adminObj)){
            return $this->redirectErrorUrl('Exception request');
        }
        $this->response->assign('admin',$adminObj);
        return $this->response->layout('sys/admin/user_grant');
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/admin/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $roles = $this->roleService->getSelectList(null,'tree');
        $this->response->assign('roles',$roles);
        $role_ids = Data::toFlatArray($data->items(),'role_id');
        $roleNames = $this->roleService->getRoleNameByIds($role_ids);
        $this->response->assign('roleNames',$roleNames);
        return $this->response->layout('sys/admin/list');
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
                $userObj = $this->logic->register($post);
                if(empty($userObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e){
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $roles = $this->roleService->getSelectList(null,'tree');
        return $this->response->layout('sys/admin/add',['roles'=>$roles]);
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['user_id'])) {
                    throw new VerifyException('Exception request');
                }
                $adminObj = $this->service->update($post['user_id'],$post);
                if(empty($adminObj)){
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
            $adminObj = $this->service->get($id);
            if(empty($adminObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $roles = $this->roleService->getSelectList(null,'tree');
            $this->response->assign("roles",$roles);
            $this->response->assign("data",$adminObj);
            $this->response->addScriptAssign(['initData'=>$adminObj->toArray()]);
            return $this->response->layout('sys/admin/update');
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
            return $this->response->json($res);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 修改用户的密码
     */
    public function modifyUserPwd(Request $request)
    {
        try {
            $post = $this->getPost();
            if (empty($post) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $ids = explode(',',$post['userid']);
            if(empty($ids)){
                throw new VerifyException('未找到用户ID');
            }
            $r_value = $this->service->modifyUsersPassword($ids, $post['new_pass']);
            if (empty($r_value)) {
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, null, $e->getMessage());
        }
    }

    /**
     * 用户信息
     */
    public function userInfo(Request $request)
    {
        $type = $this->getParams('type');
        $data = $this->loginUser->toArray();
        $this->response->addScriptAssign(['initData'=>$data]);
        return $this->response->layout('sys/admin/userinfo',['data'=>$data,'type'=>$type]);
    }

    /**
     * 保存用户信息
     */
    public function saveUserinfo(Request $request)
    {
        try {
            $post = $this->getPost();
            if (empty($post) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $adminObj = $this->service->update($request->getUserID(),$post);
            if(empty($adminObj)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 修改自己的密码
     */
    public function modifyPassword(Request $request)
    {
        try {
            $post = $this->getPost();
            if (empty($post) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            if (isset($post['userid'])) {
                $id = $post['userid'];
                $old_pass = null;
            } else {
                $id = $request->getUserID();
                $old_pass = $post['old_pass'];
            }
            $r_value = $this->service->modifyPassword($id, $post['new_pass'], $old_pass);
            if (empty($r_value)) {
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, null, $e->getMessage());
        }
    }

}