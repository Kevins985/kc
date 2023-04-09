<?php

namespace library\service\sys;

use support\Container;
use support\exception\VerifyException;
use support\extend\Service;
use library\model\sys\AdminModel;
use support\utils\Data;
use support\utils\Random;

class AdminService extends Service
{
    public function __construct(AdminModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取指定账号列表
     * @param array $admin_ids
     */
    public function getAdminList(array $admin_ids,$fields=[]){
        $rows = $this->fetchAll(['user_id'=>['in',$admin_ids]],[],$fields);
        return Data::toKeyArray($rows,'user_id');
    }

    /**
     * 创建用户
     * @param $data
     */
    public function createUser($data){
        $data['password'] = Data::hashPassword($data['password']);
        $adminObj = $this->create($data);
        if(!empty($adminObj) && !empty($data['role_id'])){
            $rbacService = Container::get(CasbinRbacService::class);
            $rbacService->setUserRole($adminObj['user_id'],$data['role_id']);
        }
        return $adminObj;
    }

    public function update($id, array $data)
    {
        $adminObj = $this->get($id);
        $old_role_id = $adminObj['role_id'];
        $adminObj->setAttributes($data);
        $res =$adminObj->save();
        if(!empty($res) && !empty($data['role_id']) && $old_role_id!=$data['role_id']){
            $rbacService = Container::get(CasbinRbacService::class);
            return $rbacService->setUserRole($id,$data['role_id']);
        }
        return $res;
    }

    /**
     * 根据用户名获取管理员用户对象
     * @param string $account
     * @param int $type
     * @return AdminModel
     */
    public function getUserByAccount(string $account){
        return $this->fetch(['account'=>$account]);;
    }

    /**
     * 重制用户密码
     * @param $username
     */
    public function resetUserPassword($username){
        $userObj = $this->get($username,'username');
        if(empty($userObj)){
            throw new VerifyException('User account does not exist');
        }
        elseif($userObj['status']!=1){
            throw new VerifyException('User account locked');
        }
        $new_password = Random::getPwdRandom(6);
        $passpwd= Data::hashPassword($new_password);
        $data = [
            'password'=>$passpwd,
            'pwd_modify_time'=>time(),
            'pwd_strong'=>0
        ];
        $res = $userObj->update($data);
        if(!empty($res)){
            return $new_password;
        }
        return false;
    }

    /**
     * 修改用户密码
     * @param int $userid  用户ID
     * @param string $new_password 新密码
     * @param string $old_password 旧密码
     * @return AdminModel
     */
    public function modifyPassword(int $userid,string $new_password,string $old_password = null,int $pwd_strong=0) {
        $userObj = $this->get($userid);
        if (empty($userObj)) {
            throw new VerifyException('User does not exist');
        }
        elseif(!empty($old_password) && !password_verify($old_password,$userObj->password)){
            throw new VerifyException('Old password input error');
        }
        $passpwd= Data::hashPassword($new_password);
        $data = [
            'password'=>$passpwd,
            'modify_pwd_time'=>time(),
        ];
        if(!empty($pwd_strong)){
            $data['pwd_strong'] =$pwd_strong;
        }
        return $userObj->update($data);
    }

    /**
     * 修改用户密码
     * @param array $ids 用户ID
     * @param string $password 密码
     * @return bool
     */
    public function modifyUsersPassword(array $ids,string $password){
        $rows = $this->fetchAll(['user_id'=>['in',$ids]]);
        foreach($rows as $obj){
            $passpwd = Data::hashPassword($password);
            $obj->update(['password'=>$passpwd,'modify_pwd_time'=>time()]);
        }
        return true;
    }

    /**
     * 保存用户权限
     * @param int $userid
     * @param array $menu_ids
     */
    public function saveAdminMenusGrant(int $userid,array $menu_ids){
        $adminObj = $this->get($userid);
        $roleService = Container::get(RoleService::class);
        $roleObj = $roleService->get($adminObj['role_id']);
        $role_menu_ids = [];
        if(!empty($roleObj['menu_ids'])){
            $role_menu_ids = json_decode($roleObj['menu_ids'],true);
        }
        $user_menu_ids = array_diff($menu_ids,$role_menu_ids);
        $res = $this->update($userid,['menu_ids'=>$user_menu_ids]);
        if($res){
            $menuService = Container::get(MenuService::class);
            $route_ids = $menuService->pluck('route_id',['menu_id'=>['in',$user_menu_ids]]);
            $route_ids = array_filter($route_ids);
            $rbacService = Container::get(CasbinRbacService::class);
            return $rbacService->saveUserGrant($userid,$route_ids);
        }
        return false;
    }
}
