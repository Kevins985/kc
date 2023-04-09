<?php

namespace library\service\user;

use library\logic\DictLogic;
use library\logic\WalletLogic;
use library\service\sys\FlowNumbersService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\user\RechargeOrderModel;
use Webman\Event\Event;

class RechargeOrderService extends Service
{
    public function __construct(RechargeOrderModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取订单编号
     * @param string $suffix
     * @return mixed
     */
    public function getOrderNo($suffix=''){
        $flowNumberServer = Container::get(FlowNumbersService::class);
        return $flowNumberServer->getFlowOrderNo($this->model->getTable(),$suffix);
    }

    /**
     * 获取所有客户的订单数量
     */
    public function getGroupAllStatusCnt($params=[])
    {
        $selector = $this->groupBySelector(['order_status'],$params)->selectRaw('order_status, count(*) as ct,sum(money) money');
        $rows = $selector->get()->toArray();
        $data = ['total'=>['ct'=>0,'money'=>0]];
        foreach($rows as $v){
            $data[$v['order_status']] = $v;
            $data['total']['ct']+=$v['ct'];
            $data['total']['money']+=$v['money'];
        }
        return $data;
    }

    /**
     * 充值审核
     * @param $id
     */
    public function verify($id,$status,$descr='',$admin_id=0){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $rechargeOrderObj = $this->get($id);
            if(empty($rechargeOrderObj) || $rechargeOrderObj['status']!='0'){
                throw new BusinessException("状态异常，不能审核");
            }
            $update = [
                'descr'=>$descr,
                'status'=>$status,
                'admin_id'=>$admin_id
            ];
            $res = $rechargeOrderObj->update($update);
            if($res && $status==1){
                $this->finishPayRechargeOrder($rechargeOrderObj);
                $pay_data = [
                    'pay_status'=>'paid',
                    'pay_time'=>time(),
                    'status'=>2
                ];
            }
            else{
                $pay_data = [
                    'pay_status'=>'closed',
                    'status'=>2
                ];
            }
            $payOrderService = Container::get(PaymentOrderService::class);
            $payOrderObj = $payOrderService->getFromPayObj($rechargeOrderObj['order_no']);
            if(!empty($payOrderObj) && $payOrderObj['user_id']==$rechargeOrderObj['user_id']){
                $payOrderObj->update($pay_data);
            }
            $conn->commit();
            return $res;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * 完成充值订单
     * @param RechargeOrderModel $orderObj
     * @return bool
     */
    public function finishPayRechargeOrder(RechargeOrderModel $orderObj){
        $walletLogic = Container::get(WalletLogic::class);
        $userExtendObj = $walletLogic->getUserExtendObj($orderObj['user_id']);
        $dictLogic = Container::get(DictLogic::class);
        $rechargeConfig = $dictLogic->getDictConfigs('recharge');
        $walletLogic->addUserWallet($orderObj['user_id'],$orderObj['money'],11,'用户钱包充值');
        //赠送积分
        $reward_point = $orderObj['money']*$rechargeConfig['reward_point']/100;
        $walletLogic->addUserPoint($orderObj['user_id'],$reward_point,'充值赠送');
        $updateData = [
            'pay_time'=>time(),
            'status'=>2,
            'order_status'=>'paid',
        ];
        //首次充值
        if($userExtendObj['recharge_money']<1 && $rechargeConfig['reward_open']=="Y" ){
            $reward_money = $orderObj['money']*$rechargeConfig['reward_money']/100;
            if($reward_money>$rechargeConfig['reward_max']){
                $reward_money = $rechargeConfig['reward_max'];
            }
            $updateData['reward_money'] = $reward_money;
            $walletLogic->addUserWallet($orderObj['user_id'],$reward_money,13,'首次充值奖励');
        }
        $res = $orderObj->update($updateData);
        if($res){
            Event::emit('user.payRechargeOrder',$orderObj);
        }
        return $res;
    }
}
