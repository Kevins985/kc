<?php

namespace library\service\user;

use library\logic\WalletLogic;
use library\service\sys\FlowNumbersService;
use support\Container;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Service;
use library\model\user\WithdrawOrderModel;

class WithdrawOrderService extends Service
{
    public function __construct(WithdrawOrderModel $model)
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
     * 提交提现记录
     * @param $data
     */
    public function addWithdraw($data){
        $data['order_no'] = $this->getOrderNo();
        $withdrawObj = $this->create($data);
        if($withdrawObj){
            $walletLogic = Container::get(WalletLogic::class);
            return $walletLogic->addUserFrozen($data['user_id'],$data['money']);
        }
        return true;
    }

    /**
     * 提交提现记录
     * @param $data
     */
    public function updateWithdraw($id,$data){
        $withdrawObj = $this->get($id);
        if(empty($withdrawObj) && $withdrawObj['user_id']!=$data['user_id']){
            throw new VerifyException('Exception request');
        }
        elseif($withdrawObj['order_status']!='refused'){
            throw new VerifyException('Exception request');
        }
        $data['status'] = 0;
        $data['order_status'] = 'pending';
        return $withdrawObj->update($data);
    }

    /**
     * 提交提现记录
     * @param $data
     */
    public function closeWithdraw($id){
        $withdrawObj = $this->get($id);
        if(empty($withdrawObj) && ($withdrawObj['order_status']!='pending') || $withdrawObj['status']!=0){
            throw new VerifyException('只能关闭未审核的订单');
        }
        $res = $withdrawObj->update(['status'=>2,'order_status'=>'closed']);
        if($res){
            $walletLogic = Container::get(WalletLogic::class);
            return $walletLogic->minuUserFrozen($withdrawObj['user_id'],$withdrawObj['money']);
        }
        return true;
    }

    /**
     * 审核支付订单
     * @param $id
     */
    public function verifyOrder($id,$status,$admin_id,$memo=''){
        $withdrawOrderObj = $this->get($id);
        if(empty($withdrawOrderObj) || $withdrawOrderObj['order_status']!='pending'){
            throw new BusinessException("订单状态异常，不能审核");
        }
        $update = [
            'admin_id'=>$admin_id,
            'memo'=>$memo,
            'status'=>1,
        ];
        if($status==2){
            $update['order_status']='refused';
        }
        return $withdrawOrderObj->update($update);
    }

    /**
     * 审核完成支付订单
     */
    public function verifyFinishOrder($order_id){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $withdrawOrderObj = $this->get($order_id);
            if(empty($withdrawOrderObj) || $withdrawOrderObj['order_status']!='pending'){
                throw new BusinessException("订单状态异常，不能完成审核");
            }
            $data['status'] = 2;
            $data['order_status'] = 'completed';
            $res = $withdrawOrderObj->update($data);
            $walletLogic = Container::get(WalletLogic::class);
            $walletLogic->minusUserWallet($withdrawOrderObj['user_id'],$withdrawOrderObj['money'],1,'用户钱包提现');
            $conn->commit();
            return $res;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }
}
