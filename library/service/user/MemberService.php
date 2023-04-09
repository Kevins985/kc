<?php

namespace library\service\user;

use library\logic\DictLogic;
use library\service\sys\FlowNumbersService;
use support\Container;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Service;
use library\model\user\MemberModel;
use support\utils\Data;
use support\utils\Random;
use Webman\Event\Event;

class MemberService extends Service
{
    public function __construct(MemberModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取商品编号
     * @param string $suffix
     * @return mixed
     */
    public function getUserNo($suffix=''){
        $flowNumberServer = Container::get(FlowNumbersService::class);
        $user_no = $flowNumberServer->getFlowOrderNo($this->model->getTable(),$suffix);
        $userObj = $this->get($user_no,'user_no');
        if(empty($userObj)){
            return $user_no;
        }
        return $this->getUserNo();
    }

    /**
     * 获取指定客户列表
     * @param array $user_ids
     */
    public function getMemberList(array $user_ids,$fields=[]){
        $rows = $this->fetchAll(['user_id'=>['in',$user_ids]],[],$fields);
        return Data::toKeyArray($rows,'user_id');
    }

    /**
     * 创建会员信息
     * @param array $data {account,type,password,nickname,invitationCode,source}
     *
     */
    public function createUser($data){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $data['user_no'] = $this->getUserNo();
            $data['password'] = Data::hashPassword($data['password']);
            $parentMemberTeamObj = null;
            $dictLogic = Container::get(DictLogic::class);
            $commissionConfig=$dictLogic->getDictConfigs('commission');
            $memberTeamService = Container::get(MemberTeamService::class);
            $parent_id = 0;
            if(!empty($data['invitationCode']) && $commissionConfig['is_open']=='Y') {
                $parentMemberTeamObj = $memberTeamService->get($data['invitationCode'], 'invite_code');
                if(empty($parentMemberTeamObj)){
                    throw new BusinessException('用户邀请码不存在');
                }
                $parent_id = $parentMemberTeamObj['user_id'];
            }
            if(isset($data['type']) && $data['type']=='email'){
                $data['email'] = $data['account'];
            }
            else{
                $data['mobile'] = $data['account'];
            }
            $memberObj = $this->create($data);
            $memberExtendService = Container::get(MemberExtendService::class);
            $memberExtendService->create(['user_id'=>$memberObj['user_id']]);
            $mermber = $memberObj->toArray();
            if($commissionConfig['is_open']=='Y'){
                $parents_path = $memberObj['user_id'];
                if(!empty($parentMemberTeamObj)){
                    $commission_level = $commissionConfig['level_num'];
                    $commission_level = 100;
                    $parentsArr = explode(',',$parentMemberTeamObj['parents_path']);
                    if(count($parentsArr)>$commission_level){
                        array_shift($parentsArr);
                        $parentsArr[] = $memberObj['user_id'];
                        $parents_path = implode(',',$parentsArr);
                    }
                    else{
                        $parents_path = $parentMemberTeamObj['parents_path'].','.$memberObj['user_id'];
                    }
                }
                $memberTeamObj = $memberTeamService->create([
                    'user_id'=>$memberObj['user_id'],
                    'account'=>$memberObj['account'],
                    'invite_code'=>$memberTeamService->getInviteCode(),
                    'parent_id'=>$parent_id,
                    'parents_path'=>$parents_path
                ]);
                $mermber['member_team'] = $memberTeamObj->toArray();
            }
            $conn->commit();
            Event::emit('user.register',$mermber);
            return $memberObj;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * 根据用户名获取管理员用户对象
     * @param string $account
     * @param int $type
     * @return MemberModel
     */
    public function getUserByAccount(string $account){
        return $this->get($account,'account');
    }

    /**
     * 重制用户密码
     * @param $username
     */
    public function resetUserPassword($username){
        $userObj = $this->get($username,'username');
        if(empty($userObj)){
            throw new VerifyException('用户账号不存在');
        }
        elseif($userObj['status']!=1){
            throw new VerifyException('账号已锁定');
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
     * @return MemberModel
     */
    public function modifyPassword(int $userid,string $new_password,string $old_password = null,int $pwd_strong=0) {
        $userObj = $this->get($userid);
        if (empty($userObj)) {
            throw new VerifyException('用户不存在');
        }
        elseif(!empty($old_password) && !password_verify($old_password,$userObj->password)){
            throw new VerifyException('旧密码错误');
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
     * 修改用户支付密码
     * @param int $userid  用户ID
     * @param string $new_password 新密码
     * @param string $old_password 旧密码
     * @return MemberModel
     */
    public function modifyPayPassword(int $userid,string $new_password,string $old_password = null,int $pwd_strong=0) {
        $userObj = $this->get($userid);
        if (empty($userObj)) {
            throw new VerifyException('用户不存在');
        }
        elseif(!empty($old_password) && !password_verify($old_password,$userObj->pay_password)){
            throw new VerifyException('旧密码错误');
        }
        $passpwd= Data::hashPassword($new_password);
        return $userObj->update(['pay_password'=>$passpwd]);
    }

    /**
     * 修改用户密码
     * @param array $ids 用户ID
     * @param string $password 密码
     * @return bool
     */
    public function modifyUsersPassword(array $ids,string $password,$pass_type='login'){
        $rows = $this->fetchAll(['user_id'=>['in',$ids]]);
        foreach($rows as $obj){
            $passpwd = Data::hashPassword($password);
            if($pass_type=='pay'){
                $obj->update(['pay_password'=>$passpwd]);
            }
            else{
                $obj->update(['password'=>$passpwd,'modify_pwd_time'=>time()]);
            }
        }
        return true;
    }

    /**
     * 删除会员信息
     */
    public function deleteMember($id){
        $ids = explode(',',$id);
        $extendService = Container::get(MemberExtendService::class);
        if(count($ids)>1){
            $res = $this->batchDelete($ids);
            $extendService->batchDelete($ids);
        }
        else{
            $res = $this->delete($id);
            $extendService->delete($id);
        }
        return $res;
    }
}
