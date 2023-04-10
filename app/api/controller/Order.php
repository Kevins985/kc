<?php

namespace app\api\controller;

use library\logic\OrderLogic;
use library\service\goods\SpuService;
use library\service\user\OrderService;
use library\service\user\ProjectOrderService;
use library\validator\user\OrderValidation;
use support\Container;
use support\controller\Api;
use support\exception\BusinessException;
use support\extend\Request;
use support\utils\Data;

class Order extends Api
{
    public function __construct(OrderLogic $logic,OrderValidation $validation)
    {
        $this->logic = $logic;
        $this->validation = $validation;
    }

    /**
     * 提交提现订单
     * @param $param {user_id,bank_id,money,descr,type[wallet,invite]}
     */
    public function createWithdrawOrder(Request $request)
    {
        try {
            $post = $this->getPost();
            $post['user_id'] = $request->getUserID();
            $withdrawOrderObj = $this->logic->createWithdrawOrder($post);
            if(empty($withdrawOrderObj)){
                throw new BusinessException('添加失败');
            }
            return $this->response->json(true,$withdrawOrderObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 提交充值订单
     * @param $param {user_id,money,payment[wallet\weixin\alipay],descr}
     */
    public function createRechargeOrder(Request $request)
    {
        try {
            $post = $this->getPost();
            $post['user_id'] = $request->getUserID();
            $rechargeOrderObj = $this->logic->createRechargeOrder($post);
            if(empty($rechargeOrderObj)){
                throw new BusinessException('添加失败');
            }
            return $this->response->json(true,$rechargeOrderObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 商品订单
     */
    public function goodsOrders(Request $request)
    {
        try{
            $params['page'] = $this->getParams('page',1);
            $params['status'] = $this->getParams('status');
            $params['user_id'] = $request->getUserID();
            $orderService = Container::get(OrderService::class);
            $data = $orderService->paginateData($params,['order_id'=>'desc']);
            $spu_ids = Data::toFlatArray($data['data'],'spu_id');
            if(!empty($spu_ids)){
                $spuService = Container::get(SpuService::class);
                $goodsList = $spuService->getGoodsList($spu_ids);
                foreach($data['data'] as $k=>$v){
                    $data['data'][$k]['goods'] = $goodsList[$v['spu_id']];
                }
            }
            return $this->response->json(true,$data);
        }
        catch (\Exception $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 提交积分订单
     * @param {address_id,spu_id}
     */
    public function createGoodsOrder(Request $request)
    {
        try {
            $post = $this->getPost(['spu_id','file_url','payment']);
            $post['user_id'] = $request->getUserID();
            $orderObj = $this->logic->createOrder($post);
            if(empty($orderObj)){
                throw new BusinessException('添加失败');
            }
            return $this->response->json(true,$orderObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 接收礼品订单
     */
    public function receiptGoodsOrder(Request $request,int $id)
    {
        try {
            $orderService = Container::get(OrderService::class);
            $orderObj = $orderService->get($id);
            if(empty($orderObj) || $orderObj['user_id']!=$request->getUserID()){
                throw new BusinessException('异常请求');
            }
            $res = $orderObj->update(['order_status'=>'completed']);
            if(empty($res)){
                throw new BusinessException('操作失败');
            }
            return $this->response->json(true,$orderObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取本期项目
     */
    public function getCurrencyProject(Request $request){
        try{
            $projectOrderService = Container::get(ProjectOrderService::class);
            $where = [
                'user_id'=>$request->getUserID(),
                'status'=>1
            ];
            $projectOrderObj = $projectOrderService->fetch($where,['id'=>'desc']);
            if(empty($projectOrderObj)){
                throw new BusinessException("暂无活动数据");
            }
            $data = $projectOrderObj->toArray();
            $orderObj = $projectOrderObj->order;
            $data['invite_cnt'] = $orderObj['invite_cnt'];
            $data['point'] = $orderObj['point'];
            $data['progress'] = 2;
            return $this->response->json(true,$data);
        }
        catch (\Exception $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取往期项目
     * @param Request $request
     */
    public function getProjectNumber(Request $request){
        try{
            $params['page'] = $this->getParams('page',1);
//            $params['status'] = $this->getParams('status');
            $params['user_id'] = $request->getUserID();
            $projectOrderService = Container::get(ProjectOrderService::class);
            $data = $projectOrderService->paginateData($params,['id'=>'desc']);
            $order_ids = Data::toFlatArray($data['data'],'order_id');
            if(!empty($order_ids)){
                $orderService = Container::get(OrderService::class);
                $orderList = $orderService->getOrderList($order_ids);
                foreach($data['data'] as $k=>$v){
                    $data['data'][$k]['invite_cnt'] = $orderList[$v['order_id']]['invite_cnt']??0;
                }
            }
            return $this->response->json(true,$data);
        }
        catch (\Exception $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}