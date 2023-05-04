<?php

namespace library\logic;
use library\service\goods\ProjectNumberService;
use library\service\goods\ProjectService;
use library\service\goods\SpuService;
use library\service\user\MemberBankService;
use library\service\user\MemberService;
use library\service\user\MemberTeamService;
use library\service\user\OrderService;
use library\service\user\ProjectOrderService;
use library\service\user\RechargeOrderService;
use library\service\user\WithdrawOrderService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Log;
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
            $count = $orderService->count(['user_id'=>$data['user_id'],'status'=>1]);
            if($count>0){
                throw new BusinessException('你已经报名参加项目了');
            }
            $project_id = 0;
            $memberTeamService = Container::get(MemberTeamService::class);
            $memberTeam = $memberTeamService->get($data['user_id']);
            if(!empty($memberTeam) && !empty($memberTeam['parents_path'])){
                $parentArr = $memberTeam->getParentUserIds();
                if(!empty($parentArr)){
                    $projectOrderService = Container::get(ProjectOrderService::class);
                    $parentProjectOrderObj = $projectOrderService->fetch(['user_id'=>['in',$parentArr],'status'=>1]);
                    if(!empty($parentProjectOrderObj)){
                        $projectObj = $parentProjectOrderObj->project;
                    }
                    else{
                        $parentOrderObj = $orderService->fetch(['project_id'=>['gt',0],'user_id'=>['in',$parentArr]],['status'=>'asc']);
                        if(!empty($parentOrderObj)){
                            $projectObj = $parentOrderObj->project;
                        }
                    }
                    if(!empty($projectObj)){
                        $buy_number = $orderService->getBuyProjectOrderCount($projectObj['project_id'],['user_id'=>$data['user_id']]);
                        if($buy_number<$projectObj['limit_num'] || $projectObj['limit_num']==0){
                            $project_id = $projectObj['project_id'];
                        }
                    }
                }
            }
            if(empty($project_id)){
                $projectService = Container::get(ProjectService::class);
                $projectObj = $projectService->getActiveProject($data['user_id']);
                if(!empty($projectObj)){
                    $project_id = $projectObj['project_id'];
                }
            }
            $conn->beginTransaction();
            $data['order_no'] = $orderService->getOrderNo();
            $data['project_id'] = $project_id;
            $data['qty'] = 1;
            $data['point'] = $spuObj['point2'];
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
        $spuObj = $orderObj->spu;
        $projectNumberObj = null;
        $projectObj = null;
        $parentProjectOrderObj = null;
        if(!empty($memberTeam) && !empty($memberTeam['parents_path'])){
            $parentArr = $memberTeam->getParentUserIds();
            if(!empty($parentArr)){
                $projectOrderService = Container::get(ProjectOrderService::class);
                $where = ['user_id'=>['in',$parentArr],'status'=>1];
                if(!empty($orderObj['project_id'])){
                    $where['project_id'] = $orderObj['project_id'];
                }
                $parentProjectOrderObj = $projectOrderService->fetch($where);
                if(!empty($parentProjectOrderObj)){
                    $projectNumberObj = $parentProjectOrderObj->getProjectNumber();
                    $projectObj = $parentProjectOrderObj->project;
                }
                else{
                    unset($where['status']);
                    if(!isset($where['project_id'])){
                        $where['project_id']=['gt',0];
                    }
                    $parentOrderObj = $orderService->fetch($where,['status'=>'asc']);
                    if(!empty($parentOrderObj)){
                        $projectObj = $parentOrderObj->project;
                        $projectNumberObj = $projectObj->getProjectNumber();
                    }
                }
            }
        }
        if(empty($projectObj)){
            $projectObj = $projectService->getActiveProject($orderObj['user_id']);
            if(!empty($projectObj)){
                $projectNumberObj = $projectObj->getProjectNumber();
            }
        }
        if(empty($projectObj)){
            throw new BusinessException("暂未找到匹配该用户的项目");
        }
        elseif(empty($projectNumberObj)){
            throw new BusinessException("暂未找到适合该用户的项目期");
        }
        $buy_number = $orderService->getBuyProjectOrderCount($projectObj['project_id'],['user_id'=>$orderObj['user_id'],'order_status'=>['neq','pending']]);
        if($buy_number>=$projectObj['limit_num'] && $projectObj['limit_num']>0){
            throw new BusinessException($projectObj['project_name']."最多只能购买".$projectObj['limit_num'].'次');
        }
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            if(!empty($memberTeam) && !empty($memberTeam['parent_id'])){
                $parentOrderWhere = [
                    'user_id'=>$memberTeam['parent_id'],
                    'status'=>1,
                    'project_id'=>$projectObj['project_id']
                ];
                $orderService->updateAll($parentOrderWhere,['point'=>$spuObj['point'],'invite_cnt'=>$orderService->raw('invite_cnt+1')]);
            }
            $projectService->selector(['project_id'=>$projectObj['project_id']])->lockForUpdate();
            //修改项目数据
            $projectObj->update([
                'sales_cnt'=> ($projectObj['sales_cnt']+1),
                'sales_money'=> $projectService->raw('sales_money+'.$orderObj['pay_money'])
            ]);
            //修改订单数据
            $orderObj->update([
                'project_id'=>$projectObj['project_id'],
                'project_sort'=>$projectObj['sales_cnt'],
                'order_status'=>'paid',
                'pay_money'=>$orderObj['money'],
                'verify_time'=>time(),
            ]);
            //添加排期订单和修改项目数据
            $projectOrderObj = $projectOrderService->createProjectOrder($projectNumberObj,$order_id,$orderObj['user_id']);
            if(empty($projectOrderObj)){
                throw new BusinessException('创建项目订单失败');
            }
            elseif($projectOrderObj['user_number']>$projectObj['user_cnt']){
                throw new BusinessException('用户排序号大于项目最多人数');
            }
            $outProjectOrder = $projectOrderService->getOutProjectOrder($projectOrderObj['project_id'],$projectOrderObj['project_number']);
            if(empty($outProjectOrder)){
                throw new BusinessException('出彩用户订单不存在');
            }
            $outProjectOrder->increase('user_progress')->save();
            $conn->commit();
            if($outProjectOrder['user_progress']>=ProjectUserCnt){
                $this->outProjectOrder($outProjectOrder);
            }
            if($projectOrderObj['user_number']==$projectObj['user_cnt']){
                $this->finishProjectOrder($projectOrderObj);
            }
            if(!empty($memberTeam) && !empty($memberTeam['parents_path'])){
                $queueData = [
                    'user_id'=>$memberTeam['user_id'],
                    'order_id'=>$orderObj['order_id'],
                    'order_money'=>$orderObj['money'],
                    'project_id'=>$projectNumberObj['project_id'],
                    'project_number'=>$projectNumberObj['project_number'],
                ];
                pushQueue(QueueProject,$queueData);
            }
            return $orderObj;
        }
        catch (\Exception $e){
            $conn->rollBack();
            Log::channel('server')->error('verifyOrder:'.$order_id.'-'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * 项目订单出彩
     * @param $projectOrderObj 要出彩的项目订单
     * @throws \Throwable
     */
    public function outProjectOrder($projectOrderObj,$user_progress){
        if(!empty($projectOrderObj)){
            $conn = $this->connection();
            try {
                $conn->beginTransaction();
                $orderObj = $projectOrderObj->order;
                $projectOrderObj->update([
                    'user_progress'=>ProjectUserCnt,
                    'order_status'=>'completed',
                    'status'=>2,
                ]);
                $orderObj->update([
                    'order_status'=>'completed',
                    'status'=>2
                ]);
                $memberService = Container::get(MemberService::class);
                $memberObj = $orderObj->member;
                $memberObj->update(['project_cnt'=>$memberService->raw('project_cnt+1')]);
                $walletLogic = Container::get(WalletLogic::class);
                $walletLogic->addUserPoint($projectOrderObj['user_id'],$orderObj['point'],$orderObj['order_no'].'结算');
                $conn->commit();
            }
            catch (\Throwable $e){
                Log::channel('server')->error('outProjectOrder:'.$projectOrderObj['id'].'-'.$e->getMessage());
                $conn->rollBack();
            }
        }
    }

    /**
     * 完成项目期的拆分
     * @param $projectOrderObj 触发拆分的项目订单
     * @throws \Throwable
     */
    public function finishProjectOrder($projectOrderObj){
        if(!empty($projectOrderObj)){
            $conn = $this->connection();
            try {
                $conn->beginTransaction();
                $projectObj = $projectOrderObj->project;
                $projectNumberObj = $projectOrderObj->projectNumber;
                $projectNumberObj->update([
                    'status'=>2
                ]);
                $projectOrderService = Container::get(ProjectOrderService::class);
                $projectOrderList = $projectOrderService->getActiveProjectOrderList($projectOrderObj['project_id'],$projectOrderObj['project_number']);
                $projectNumberAry = [];
                $projectNumberService = Container::get(ProjectNumberService::class);
                for($i=0;$i<ProjectUserCnt;$i++){
                    $projectNumberAry[$i] = $projectNumberService->createProjectNumber($projectObj['project_id'],$projectObj['project_prefix'],($projectObj['number']+$i),$projectOrderObj['project_number']);
                }
                foreach($projectOrderList as $v){
                    $index = ($v['user_number']%ProjectUserCnt) - 1;
                    if($index<0){
                        $index = ProjectUserCnt-1;
                    }
                    if(isset($projectNumberAry[$index])){
                        $createProjectOrderObj = $projectOrderService->createProjectOrder($projectNumberAry[$index],$v['order_id'],$v['user_id']);
                        if(!empty($createProjectOrderObj)){
                            $v->update(['status'=>0]);
                        }
                    }
                }
                $conn->commit();
            }
            catch (\Throwable $e){
                Log::channel('server')->error('finishProjectOrder:'.$projectOrderObj['id'].'-'.$e->getMessage());
                $conn->rollBack();
            }
        }
    }
}

