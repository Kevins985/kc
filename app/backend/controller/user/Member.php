<?php

namespace app\backend\controller\user;

use library\service\goods\ProjectService;
use library\service\user\LevelService;
use library\service\user\MemberExtendService;
use library\service\user\MemberService;
use library\service\user\MemberTeamService;
use library\service\user\TagsService;
use library\validator\user\MemberValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class Member extends Backend
{
    public function __construct(MemberService $service,MemberValidation $validation)
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
        if(!empty($params['nickname'])){
            $params['nickname'] = ['like',$params['nickname']];
        }
        if(!empty($params['mobile'])){
            $params['mobile'] = ['like',$params['mobile']];
        }
//        $params['is_robot'] = 0;
        if(!empty($params['invite_userid'])){
            $memberTeamService = Container::get(MemberTeamService::class);
            $invite_userids = $memberTeamService->pluck('user_id',['parent_id'=>$params['invite_userid']]);
            if(count($invite_userids)>0){
                $params['user_id'] = ['in',$invite_userids];
            }
            else{
                $params['user_id'] = 0;
            }
            unset($params['invite_userid']);
        }
        elseif(!empty($params['invite_code'])){
            $memberTeamService = Container::get(MemberTeamService::class);
            $invite_userids = $memberTeamService->pluck('user_id',['invite_code'=>$params['invite_code']]);
            if(count($invite_userids)>0){
                $params['user_id'] = ['in',$invite_userids];
            }
            else{
                $params['user_id'] = 0;
            }
            unset($params['invite_userid']);
        }
        if(!empty($this->loginUser['project_id'])){
            $projectService = Container::get(ProjectService::class);
            $projectObj = $projectService->get($this->loginUser['project_id']);
            if(!empty($projectObj)){
                $params['source'] = $projectObj['project_no'];
            }
            else{
                $params['user_id'] = 0;
            }
        }
        $data = $this->service->paginate('/backend/member/list',$params,['user_id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $levelService = Container::get(LevelService::class);
        $this->response->assign("levelList",$levelService->getSelectList());
        $member_ids = Data::toFlatArray($data->items(),'user_id');
        $teamMemberList = [];
        if(!empty($member_ids)){
            $teamService = Container::get(MemberTeamService::class);
            $teamMemberList = $teamService->getTeamListByIds($member_ids);
        }
        $this->response->assign("teamList",$teamMemberList);

        $user_ids = Data::toFlatArray($data->items(),'user_id');
        $memberExtend = [];
        if(!empty($user_ids)){
            $memberExtendService = Container::get(MemberExtendService::class);
            $memberExtend =$memberExtendService->getMemberExtendList($user_ids);
        }
        $this->response->assign('memberExtend',$memberExtend);
        return $this->response->layout('user/member/list');
    }

    /**
     * 获取用户的其他信息
     * @param Request $request
     */
    public function getMemberInfo(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if(!$request->isAjax() || empty($id)){
                throw new VerifyException('Exception request');
            }
            $memberObj = $this->service->get($id);
            $this->response->assign('member',$memberObj);
            $inviteList = $memberObj->teamList;
            $this->response->assign('inviteList',$inviteList);
            $memberList = [];
            $user_ids = Data::toFlatArray($inviteList,'user_id');
            if(!empty($user_ids)){
                $memberList = $this->service->getMemberList($user_ids);
            }
            $this->response->assign('memberList',$memberList);
            $this->response->assign('team',$memberObj->team);
            $addressList = $memberObj->addressList;
            $this->response->assign('addressList',$addressList);
            $extendObj = $memberObj->memberExtend;
            $this->response->assign('extend',$extendObj);
            return $this->response->view('user/member/_info');
        }
        catch (\Exception $e) {
            print_r($e->getTraceAsString());
            return $this->response->json(false,null,$e->getMessage());
        }
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
                $post['source'] = 'admin';
                $userObj = $this->service->createUser($post);
                if(empty($userObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $levelService = Container::get(LevelService::class);
        $this->response->assign("levelList",$levelService->getSelectList());
        $tagsService = Container::get(TagsService::class);
        $this->response->assign("tagsList",$tagsService->getCategoryTagsList(1));
        return $this->response->layout('user/member/add');
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
                $memberObj = $this->service->update($post['user_id'],$post);
                if(empty($memberObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else {
            $id = $this->getParams('id');
            $memberObj = $this->service->get($id);
            if(empty($memberObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $data = $memberObj->toArray();
            $this->response->assign("data",$data);
            $levelService = Container::get(LevelService::class);
            $this->response->assign("levelList",$levelService->getSelectList());
            $tagsService = Container::get(TagsService::class);
            $this->response->assign("tagsList",$tagsService->getCategoryTagsList(1));
            $this->response->addScriptAssign(['initData'=>$data]);
            return $this->response->layout('user/member/update');
        }
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->deleteMember($id);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 验证会员账号是否存在
     */
    public function checkAccountExists(Request $request)
    {
        try {
            $account = $this->getParams('account');
            if (!$request->isAjax() || empty($account)) {
                throw new \Exception('Exception request');
            }
            $memberObj = $this->service->fetch(['account'=>$account]);
            if(!empty($memberObj)){
                throw new \Exception("账号已经存在");
            }
            return $this->response->output(json_encode(['valid'=>true]));
        }
        catch (\Exception $e) {
            return $this->response->output(json_encode(['valid'=>false]));
        }
    }

    /**
     * 修改用户的密码
     * @param post{userid,new_pass,pass_type}
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
            $r_value = $this->service->modifyUsersPassword($ids, $post['new_pass'],$post['pass_type']);
            if (empty($r_value)) {
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, null, $e->getMessage());
        }
    }

    /**
     * 备注
     * @param Request $request
     */
    public function setRemark(Request $request){
        try {
            $post = $this->getPost(['id','remark']);
            if (empty($post) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->update($post['id'],['remark'=>$post['remark']]);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 获取用户所有的树形图
     */
    public function treeMember(Request $request){
        $this->response->addScriptAssign(['tree'=>1]);
        return $this->response->layout('user/member/tree');
    }

    public function getTreeMembers(Request $request)
    {
        try {
            $user_id = $this->getParams('user_id',0);
            $projectNumberService = Container::get(MemberTeamService::class);
            $data =$projectNumberService->queryTreeMembers($user_id);
            return $this->response->json(true,$data);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}