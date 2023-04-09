<?php

namespace app\api\controller;

use library\logic\OrderLogic;
use library\service\goods\SpuService;
use library\service\user\OrderService;
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
            $params['user_id'] = $request->getUserID();
            $orderService = Container::get(OrderService::class);
            $data = $orderService->paginateData($params,['order_id'=>'desc']);
            $spu_ids = Data::toFlatArray($data['data'],'spu_id');
            if(!empty($spu_ids)){
                $spuService = Container::get(SpuService::class);
                $goodsList = $spuService->getGoodsList($spu_ids);
                foreach($data['data'] as $k=>$v){
                    $goods = $goodsList[$v['spu_id']];
                    $goods['image'] = upload_md5_url($goods['image']);
                    $data['data'][$k]['goods'] = $goods;
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
            $post = $this->getPost();
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
}