<?php

namespace app\backend\controller\user;

use library\service\user\MemberBankService;
use library\service\user\MemberService;
use library\service\user\WithdrawOrderService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class WithdrawOrder extends Backend
{
    public function __construct(WithdrawOrderService $service)
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
        $data = $this->service->paginate('/backend/withdrawOrder/list',$params,['order_id'=>'desc']);
        if(!empty($params['order_status'])){
            unset($params['order_status']);
        }
        $countList = $this->service->getGroupAllStatusCnt($params);
        $this->response->assign('countList',$countList);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $memberList = [];
        $user_ids = Data::toFlatArray($data->items(),'user_id');
        if(!empty($user_ids)){
            $memberService = Container::get(MemberService::class);
            $memberList = $memberService->getMemberList($user_ids);
        }
        $this->response->assign('memberList',$memberList);
        return $this->response->layout('user/withdrawOrder/list');
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

    /**
     * 获取订单信息
     * @param Request $request
     */
    public function getOrderInfo(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if(!$request->isAjax() || empty($id)){
                throw new VerifyException('Exception request');
            }
            $orderObj = $this->service->get($id);
            $this->response->assign('orderObj',$orderObj);
            $memberService = Container::get(MemberService::class);
            $memberObj = $memberService->get($orderObj['user_id']);
            $this->response->assign('memberObj',$memberObj);
            $memberBankService = Container::get(MemberBankService::class);
            $bankObj = $memberBankService->get($orderObj['bank_id']);
            $this->response->assign('bankObj',$bankObj);
            return $this->response->view('user/withdrawOrder/_info');
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 审核订单
     */
    public function verifyOrder(Request $request)
    {
        try {
            $post = $this->getPost();
            if (empty($post) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            if(is_array($post['id'])){
                foreach ($post['id'] as $i){
                    $res = $this->service->verifyOrder($i,$post['status'],$request->getUserID(),$post['memo']);
                }
            }
            else{
                $res = $this->service->verifyOrder($post['id'],$post['status'],$request->getUserID(),$post['memo']);
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
     * 完成支付打款
     * @param Request $request
     * @params $post {order_id,pay_no,attachment,bank_id}
     */
    public function finishOrder(Request $request)
    {
        try {
            $id = $this->getPost('id');
            if (!$request->isAjax() || empty($id)) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->verifyFinishOrder($id);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}