<?php

namespace app\backend\controller\user;

use library\service\user\MemberService;
use library\service\user\MemberTeamService;
use library\service\user\RealAuthService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class RealAuth extends Backend
{
    public function __construct(RealAuthService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        if(!empty($params['user_no'])){
            $params['user_no'] = ['has','Member',$params['user_no']];
        }
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
        $data = $this->service->paginate('/backend/realAuth/list',$params,['id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $memberList = [];
        $user_ids = Data::toFlatArray($data->items(),'user_id');
        if(!empty($user_ids)){
            $memberService = Container::get(MemberService::class);
            $memberList = $memberService->getMemberList($user_ids);
        }
        $this->response->assign('memberList',$memberList);
        return $this->response->layout('user/realAuth/list');
    }

    /**
     * 实名验证
     */
    public function verify(Request $request)
    {
        try {
            $post = $this->getPost();
            if(!empty($post)){
                if(is_array($post['id'])){
                    foreach ($post['id'] as $i){
                        $res = $this->service->verify($i,$post['status'],$post['descr']);
                    }
                }
                else{
                    $res = $this->service->verify($post['id'],$post['status'],$post['descr']);
                }
                if(empty($res)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            else{
                $id = $this->getParams('id');
                $data = $this->service->get($id);
                return $this->response->view('user/realAuth/_verify',['data'=>$data]);
            }
        }
        catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
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
}