<?php

namespace app\backend\controller\user;

use library\logic\OrderLogic;
use library\service\goods\SpuService;
use library\service\user\MemberService;
use library\service\user\OrderService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class Order extends Backend
{
    public function __construct(OrderService $service,OrderLogic $logic)
    {
        $this->service = $service;
        $this->logic = $logic;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        if(!empty($params['account'])){
            $params['account'] = ['has','member',$params['account']];
        }
        elseif(!empty($params['project_number'])){
            $params['project_number'] = ['has','projectOrder',$params['project_number']];
        }
        $data = $this->service->paginate('/backend/order/list',$params,['order_id'=>'desc']);
        if(!empty($params['order_status'])){
            unset($params['order_status']);
        }
        if(!empty($params['account'])){
            unset($params['account']);
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
        $spuList = [];
        $spu_ids = Data::toFlatArray($data->items(),'spu_id');
        if(!empty($spu_ids)){
            $spuService = Container::get(SpuService::class);
            $spuList = $spuService->getGoodsList($spu_ids);
        }
        $this->response->assign('spuList',$spuList);
        return $this->response->layout('user/order/list');
    }

    /**
     * 发货
     * @param {order_id,tracking_name,tracking_number,tracking_url}
     */
    public function dispatched(Request $request)
    {
        try {
            if (!$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $post = $this->getPost();
            if (!empty($post)) {
                $pointOrderObj = $this->service->dispatched($post);
                if(empty($pointOrderObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            else{
                $id = $this->getParams('id');
                $orderObj = $this->service->get($id);
                if(empty($orderObj) || $orderObj['order_status']!='paid'){
                    throw new BusinessException('异常操作');
                }
                $spuService = Container::get(SpuService::class);
                $spuObj = $spuService->get($orderObj['spu_id']);
                if(empty($spuObj)){
                    throw new BusinessException('商品不存在');
                }
                $this->response->assign('data',$orderObj);
                return $this->response->view('user/order/_dispatched');
            }
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 删除
     */
    public function verifyOrder(Request $request)
    {
        try {
            $post = $this->getPost(['id','status','remark']);
            if (!empty($post['id'])) {
                foreach($post['id'] as $id){
                    $res = $this->logic->verifyOrder($id,$post['status'],$post['remark']);
                }
                return $this->response->json(true);
            }
            return $this->response->view('user/order/_verify');
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
            $orderObj = $this->service->get($id);
            $this->response->assign('data',$orderObj);
            $projectObj = $orderObj->project;
            $this->response->assign('project',$projectObj);
            $spuObj = $orderObj->spu;
            $this->response->assign('spu',$spuObj);
            $projectOrder = $orderObj->projectOrder;
            $this->response->assign('projectOrder',$projectOrder);
            $memberTeam = $orderObj->memberTeam;
            $this->response->assign('memberTeam',$memberTeam);
            return $this->response->view('user/order/_info');
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