<?php

namespace library\logic;
use library\service\goods\ProjectService;
use library\service\goods\SpuService;
use library\service\user\MemberBankService;
use library\service\user\OrderService;
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
     * @param $data {user_id,address_id,spu_id}
     */
    public function createOrder(array $data){
        $conn = $this->connection();
        $spuService = Container::get(SpuService::class);
        $spuObj = $spuService->get($data['spu_id']);
        if(empty($spuObj) || $spuObj['status']!=1){
            throw new BusinessException('暂未找到该商品');
        }
        elseif($spuObj['store_num']<1){
            throw new BusinessException('商品库存数量不足');
        }
        try{
            $projectService = Container::get(ProjectService::class);
            $projectObj = $projectService->getActiveProject();
            if(empty($projectObj)){
                throw new BusinessException('暂无找到进行中的项目');
            }
            $conn->beginTransaction();
            $orderService = Container::get(OrderService::class);
            $data['order_no'] = $orderService->getOrderNo();
            $data['project_id'] = $projectObj['project_id'];
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
    public function verifyOrder($order_id){

    }
}
