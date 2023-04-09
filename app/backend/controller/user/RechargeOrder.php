<?php

namespace app\backend\controller\user;

use library\service\sys\AdminService;
use library\service\user\MemberService;
use library\service\user\RechargeOrderService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class RechargeOrder extends Backend
{
    public function __construct(RechargeOrderService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     * 订单状态(unpaid,pending,paid,refunded,closed)
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        if(!empty($params['user_no'])){
            $params['user_no'] = ['has','Member',$params['user_no']];
        }
        $data = $this->service->paginate('/backend/rechargeOrder/list',$params,['order_id'=>'desc']);
        if(!empty($params['order_status'])){
            unset($params['order_status']);
        }
        $countList = $this->service->getGroupAllStatusCnt($params);
        $this->response->assign('countList',$countList);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $memberList = [];
        $user_ids = Data::toFlatArray($data->items(),'user_id');
        $admin_ids = Data::toFlatArray($data->items(),'admin_id');
        if(!empty($user_ids)){
            $memberService = Container::get(MemberService::class);
            $memberList = $memberService->getMemberList($user_ids);
        }
        $adminList = [];
        if(!empty($admin_ids)){
            $adminService = Container::get(AdminService::class);
            $adminList = $adminService->getAdminList($admin_ids);
        }
        $this->response->assign('memberList',$memberList);
        $this->response->assign('adminList',$adminList);
        return $this->response->layout('user/rechargeOrder/list');
    }

    /**
     * 充值审核
     */
    public function verify(Request $request)
    {
        try {
            $post = $this->getPost();
            if(!empty($post)){
                $res = $this->service->verify($post['id'],$post['status'],$post['descr'],$request->getUserID());
                if(empty($res)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            else{
                $id = $this->getParams('id');
                $data = $this->service->get($id);
                return $this->response->view('user/rechargeOrder/_verify',['data'=>$data]);
            }
        }
        catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 获取订单信息
     */
    public function getOrderInfo(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if(!$request->isAjax() || empty($id)){
                throw new VerifyException('Exception request');
            }
            $rechargeOrderObj = $this->service->get($id);
            $this->response->assign('data',$rechargeOrderObj);
            $adminObj = null;
            if(!empty($rechargeOrderObj['admin_id'])){
                $adminService =Container::get(AdminService::class);
                $adminObj = $adminService->get($rechargeOrderObj['admin_id']);
            }
            $this->response->assign('adminObj',$adminObj);
            return $this->response->view('user/rechargeOrder/_info');
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
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