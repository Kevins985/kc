<?php

namespace library\logic;
use library\service\goods\ProjectNumberService;
use library\service\goods\ProjectService;
use library\service\goods\SpuService;
use library\service\user\MemberBankService;
use library\service\user\OrderService;
use library\service\user\ProjectOrderService;
use library\service\user\RechargeOrderService;
use library\service\user\WithdrawOrderService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Logic;

class OrderLogic extends Logic
{
    /**
     * 创建充值订单
     * @param $data {user_id,money,descr}
     */
    public function createRechargeOrder($data){
        $dictLogic = Container::get(DictLogic::class);
        $rechargeConfig = $dictLogic->getDictConfigs('recharge');
        if($data['money']<$rechargeConfig['min_money']){
            throw new BusinessException('最小充值金额为'.$rechargeConfig['min_money']);
        }
        elseif($data['money']>$rechargeConfig['max_money']){
            throw new BusinessException('最大充值金额为'.$rechargeConfig['max_money']);
        }
        try{
            $rechargeOrderService = Container::get(RechargeOrderService::class);
            $data['order_no'] = $rechargeOrderService->getOrderNo();
            $rechargeOrderObj = $rechargeOrderService->create($data);
            return $rechargeOrderObj;
        }
        catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 创建提现订单
     * @param $data {user_id,bank_id,money,descr,type[wallet,invite]}
     */
    public function createWithdrawOrder($data){
        $conn = $this->connection();
        $dictLogic = Container::get(DictLogic::class);
        $withdrawConfig = $dictLogic->getDictConfigs('withdraw');
        if(empty($withdrawConfig) || $withdrawConfig['is_open']=="N"){
            throw new BusinessException('该时间暂不支持提现');
        }
        elseif($data['money']<$withdrawConfig['min_money']){
            throw new BusinessException('最小提现金额为'.$withdrawConfig['min_money']);
        }
        elseif($data['money']>$withdrawConfig['max_money']){
            throw new BusinessException('最大提现金额为'.$withdrawConfig['max_money']);
        }
        try{
            $conn->beginTransaction();
            $withdrawOrderService = Container::get(WithdrawOrderService::class);
            $data['order_no'] = $withdrawOrderService->getOrderNo();
            $withdrawOrderObj = $withdrawOrderService->create($data);
            $walletLogic = Container::get(WalletLogic::class);
            if(empty($data['bank_id'])){
                throw new BusinessException('银行卡ID暂未找到');
            }
            $bankService = Container::get(MemberBankService::class);
            $bankObj = $bankService->get($data['bank_id']);
            if(empty($bankObj) || $bankObj['user_id']!=$data['user_id']){
                throw new BusinessException('银行卡数据异常');
            }
            $walletMoney = $walletLogic->getUserWallet($data['user_id'],true);
            if($data['money']>$walletMoney){
                throw new BusinessException('钱包余额不足');
            }
            $walletLogic->addUserFrozen($withdrawOrderObj['user_id'],$withdrawOrderObj['money']);
            $conn->commit();
            return $withdrawOrderObj;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * 创建积分兑换礼品订单
     * @param $data {user_id,address_id,spu_id,file_url}
     */
    public function createOrder(array $data){
        $conn = $this->connection();
        try{
            $spuService = Container::get(SpuService::class);
            $spuService->selector(['spu_id'=>$data['spu_id']])->lockForUpdate();
            $spuObj = $spuService->get($data['spu_id']);
            if(empty($spuObj) || $spuObj['status']!=1){
                throw new BusinessException('暂未找到该商品');
            }
            elseif($spuObj['store_num']<1){
                throw new BusinessException('商品库存数量不足');
            }
            $orderService = Container::get(OrderService::class);
            $orderObj = $orderService->fetch(['user_id'=>$data['user_id'],'status'=>1]);
            if(!empty($orderObj)){
                throw new BusinessException('你已经报名参加过该项目了');
            }
            $conn->beginTransaction();
            $data['order_no'] = $orderService->getOrderNo();
            $data['project_id'] = 0;
            $data['qty'] = 1;
            $data['point'] = $spuObj['point'];
            $data['money'] = $spuObj['sell_price'];
            $orderObj = $orderService->create($data);
            $spuObj->update([
                'store_num'=>($spuService->raw('store_num-'.$orderObj['qty'])),
                'sales_cnt'=>($spuService->raw('sales_cnt+'.$orderObj['qty'])),
            ]);
            $conn->commit();
            return $orderObj;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * 审核订单
     */
    public function verifyOrder($order_id,$order_status='paid',$remark=''){
        $orderService = Container::get(OrderService::class);
        $projectService = Container::get(ProjectService::class);
        $projectOrderService = Container::get(ProjectOrderService::class);
        $orderObj = $orderService->get($order_id);
        if(empty($orderObj) || $orderObj['order_status']!='pending'){
            throw new BusinessException("操作异常，只能审核待处理的订单");
        }
        if($order_status!='paid'){
            return $orderObj->update([
                'order_status'=>'refused',
                'verify_time'=>time(),
                'remark'=>$remark,
                'status'=>2
            ]);
        }
        $memberTeam = $orderObj->memberTeam;
        $projectNumberObj = null;
        $projectObj = null;
        if(!empty($memberTeam) && !empty($memberTeam['parents_path'])){
            $projectOrderService = Container::get(ProjectOrderService::class);
            $parentArr = explode(',',$memberTeam['parents_path']);
            $projectOrderObj = $projectOrderService->fetch(['user_id'=>['in',$parentArr],'status'=>1]);
            if(!empty($projectOrderObj)){
                $projectNumberObj = $projectOrderObj->projectNumber;
                $projectObj = $projectOrderObj->project;
            }
            else{
                $projectOrderObj = $projectOrderService->fetch(['order_id'=>$order_id,'status'=>2]);
                if(!empty($projectOrderObj)){
                    $projectObj = $projectOrderObj->project;
                    $projectNumberObj = $projectObj->projectNumber()->where('status',1)->first();
                }
            }
            $parentOrderWhere = [
                'user_id'=>$memberTeam['parent_id'],
                'status'=>1
            ];
            if(!empty($projectObj)){
                $parentOrderWhere['project_id'] = $projectObj['project_id'];
            }
            $orderService->updateAll($parentOrderWhere,['invite_cnt'=>$orderService->raw('invite_cnt+1')]);
        }
        else{
            $projectObj = $projectService->get($orderObj['user_id'],'user_id');
            if(empty($projectObj)){
                $projectObj = $projectService->getActiveProject();
            }
            if(!empty($projectObj)){
                $projectNumberObj = $projectObj->projectNumber()->where('status',1)->first();
            }
        }
        if(empty($projectObj)){
            throw new BusinessException("暂未找到适合该用户的项目");
        }
        elseif(empty($projectNumberObj)){
            throw new BusinessException("暂未找到适合该用户的项目期");
        }
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $projectService->selector(['project_id'=>$projectObj['project_id']])->lockForUpdate();
            $projectUpdate = [
                'sales_cnt'=> $projectService->raw('sales_cnt+1'),
                'sales_money'=> $projectService->raw('sales_money+'.$orderObj['pay_money']),
            ];
            if(!$orderService->verifyUserBuyOrder($projectObj['project_id'],$orderObj['user_id'])){
                $projectUpdate['user_cnt'] = ($projectObj['user_cnt']+1);
            }
            //修改项目数据
            $projectObj->update($projectUpdate);
            //修改订单数据
            $orderObj->update([
                'project_id'=>$projectObj['project_id'],
                'project_sort'=>$projectObj['user_cnt'],
                'order_status'=>'paid',
                'pay_money'=>$orderObj['money'],
                'verify_time'=>time(),
            ]);
            //修改项目排期数据
            $projectNumberObj->update([
                'user_cnt'=>($projectNumberObj['user_cnt']+1),
            ]);
            $projectOrderData = [
                'order_id'=>$order_id,
                'project_id'=>$projectObj['project_id'],
                'project_number'=>$projectNumberObj['project_number'],
                'user_id'=>$orderObj['user_id'],
                'user_number'=>$projectNumberObj['user_cnt'],
                'order_status'=>'pending',
                'status'=>1,
            ];
            $projectOrderService->create($projectOrderData);
            if($projectNumberObj['user_cnt']==50){

            }
            elseif($projectNumberObj['user_cnt']>12){

            }
            $conn->commit();
            return $orderObj;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }
}
