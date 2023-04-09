<?php

namespace library\logic;

use library\service\sys\UploadFilesService;
use library\service\user\MemberExpLogService;
use library\service\user\MemberExtendService;
use library\service\user\MemberPointLogService;
use library\service\user\MemberProfitLogService;
use library\service\user\MemberService;
use library\service\user\MemberWalletLogService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Cache;
use support\extend\Log;
use support\extend\Logic;
use support\persist\QueueInterface;

/**
 * 用户钱包
 * @author Kevin
 */
class WalletLogic extends Logic
{
    /**
     * @Inject
     * @var MemberExtendService
     */
    public $extendService;

    /**
     * 获取用户资金对象
     * @param $userid
     * @param false $is_lock
     */
    public function getUserWallet($userid,$is_lock=false){
        if($is_lock){
            $userExtendObj = $this->extendService->selector(['user_id'=>$userid])->lock()->first();
        }
        else{
            $userExtendObj = $this->extendService->get($userid);
        }
        return $userExtendObj['wallet'] - $userExtendObj['frozen'];;
    }

    /**
     * 获取用户资金对象
     * @param $userid
     * @param false $is_lock
     */
    public function getUserExtendObj($userid,$is_lock=false){
        if($is_lock){
            $userExtendObj = $this->extendService->selector(['user_id'=>$userid])->lock()->first();
        }
        else{
            $userExtendObj = $this->extendService->get($userid);
        }
        return $userExtendObj;
    }

    /**
     * 加钱
     * @param $userid
     * @param $money
     * @param $event 操作类型(0:后台操作,10:注册赠送,11:充值到钱包,12:退款到钱包,13:充值奖励,14:邀请奖励,15:订单收益,16:推广收益,17:订单本金)
     * @param string $descr
     */
    public function addUserWallet($userid,$money,$event=11,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid,true);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        //钱包日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'add',
            'event'=>$event,
            'change'=>$money,
            'before_money'=>$extendObj['wallet'],
            'after_money'=>($extendObj['wallet']+$money),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $walletLogService = Container::get(MemberWalletLogService::class);
        $walletLogService->create($logData);
        //主表数据变动
        $data = [
            'wallet'=>$extendObj->raw('wallet+'.$money),
        ];
        if($event==11){
            $data['recharge_money'] = $extendObj->raw('recharge_money+'.$money);
        }
        //主表数据变动
        return $extendObj->update($data);
    }

    /**
     * 扣钱
     * @param $userid
     * @param $money
     * @param $event 操作类型(0:后台操作,1:从钱包提现,2:从钱包支付,5:其他扣款,6:支付货款)
     * @param string $descr
     */
    public function minusUserWallet($userid,$money,$event=1,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid,true);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['wallet']<$money){
            throw new BusinessException('钱包余额不足'.$money);
        }
        //钱包日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'minus',
            'event'=>$event,
            'change'=>$money,
            'before_money'=>$extendObj['wallet'],
            'after_money'=>($extendObj['wallet']-$money),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $walletLogService = Container::get(MemberWalletLogService::class);
        $walletLogService->create($logData);
        $update = ['wallet'=>$extendObj->raw('wallet-'.$money)];
        if($event==1){
            if($extendObj['frozen']<$money){
                throw new BusinessException('冻结金额不足');
            }
            $update['frozen'] = $extendObj->raw('frozen-'.$money);
        }
        //主表数据变动
        return $extendObj->update($update);
    }



    /**
     * 添加金额冻结
     */
    public function addUserFrozen($userid,$money)
    {
        $extendObj = $this->getUserExtendObj($userid,true);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['wallet']<$money){
            throw new BusinessException('钱包余额不足');
        }
        //主表数据变动
        $data = [
            'frozen'=>$extendObj->raw('frozen+'.$money),
        ];
        return $extendObj->update($data);
    }

    /**
     * 减少金额冻结
     */
    public function minuUserFrozen($userid,$money)
    {
        $extendObj = $this->getUserExtendObj($userid,true);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['frozen']<$money || $extendObj['wallet']<$money){
            throw new BusinessException('冻结金额或余额不足');
        }
        //主表数据变动
        $data = [
            'frozen'=>$extendObj->raw('frozen-'.$money),
        ];
        return $extendObj->update($data);
    }

    /**
     * 加积分
     * @param $userid
     * @param $point
     * @param string $descr
     */
    public function addUserPoint($userid,$point,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        //积分日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'add',
            'change'=>$point,
            'before_money'=>$extendObj['point'],
            'after_money'=>($extendObj['point']+$point),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $pointLogService = Container::get(MemberPointLogService::class);
        $pointLogService->create($logData);
        //主表数据变动
        $extendObj->increase('point',$point);
        return $extendObj->save();
    }

    /**
     * 扣钱
     * @param $userid
     * @param $point
     * @param string $descr
     */
    public function minusUserPoint($userid,$point,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['point']<$point){
            throw new BusinessException('积分不足'.$point);
        }
        //钱包日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'minus',
            'change'=>$point,
            'before_money'=>$extendObj['point'],
            'after_money'=>($extendObj['point']-$point),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $pointLogService = Container::get(MemberPointLogService::class);
        $pointLogService->create($logData);
        //主表数据变动
        $extendObj->decrease('point',$point);
        return $extendObj->save();
    }

    /**
     * 加经验
     * @param $userid
     * @param $exp
     * @param string $descr
     */
    public function addUserExp($userid,$exp,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        //经验日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'add',
            'change'=>$exp,
            'before_money'=>$extendObj['exp'],
            'after_money'=>($extendObj['exp']+$exp),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $expLogService = Container::get(MemberExpLogService::class);
        $expLogService->create($logData);
        //主表数据变动
        $extendObj->increase('exp',$exp);
        return $extendObj->save();
    }

    /**
     * 扣经验
     * @param $userid
     * @param $exp
     * @param string $descr
     */
    public function minusUserExp($userid,$exp,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['exp']<$exp){
            throw new BusinessException('经验不足'.$exp);
        }
        //经验日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'minus',
            'change'=>$exp,
            'before_money'=>$extendObj['exp'],
            'after_money'=>($extendObj['exp']-$exp),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $expLogService = Container::get(MemberExpLogService::class);
        $expLogService->create($logData);
        //主表数据变动
        $extendObj->decrease('exp',$exp);
        return $extendObj->save();
    }

    /**
     * 加收益金
     * @param $userid
     * @param $profit
     * @param string $descr
     */
    public function addUserProfit($userid,$profit,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        //收益金日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'add',
            'change'=>$profit,
            'before_money'=>$extendObj['profit'],
            'after_money'=>($extendObj['profit']+$profit),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $profitLogService = Container::get(MemberProfitLogService::class);
        $profitLogService->create($logData);
        //主表数据变动
        $extendObj->increase('profit',$profit);
        return $extendObj->save();
    }

    /**
     * 扣收益金
     * @param $userid
     * @param $profit
     * @param string $descr
     */
    public function minusUserProfit($userid,$profit,$descr='',$admin_id=0){
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['profit']<$profit){
            throw new BusinessException('收益金不足'.$profit);
        }
        //收益金日志
        $logData = [
            'user_id'=>$userid,
            'type'=>'minus',
            'change'=>$profit,
            'before_money'=>$extendObj['profit'],
            'after_money'=>($extendObj['profit']-$profit),
            'log_date'=>date('Y-m-d'),
            'admin_id'=>$admin_id,
            'descr'=>$descr,
        ];
        $profitLogService = Container::get(MemberProfitLogService::class);
        $profitLogService->create($logData);
        //主表数据变动
        $extendObj->decrease('profit',$profit);
        return $extendObj->save();
    }

    /**
     * 收益金额转钱包
     * @param $userid
     * @param $money
     */
    public function profitTransferToWallet($userid,$money){
        $conn = $this->connection();
        $extendObj = $this->getUserExtendObj($userid);
        if(empty($extendObj)){
            throw new BusinessException('资产数据暂未找到');
        }
        if($extendObj['profit']<$money){
            throw new BusinessException('收益金不足'.$money);
        }
        try{
            $conn->beginTransaction();
            //收益金日志
            $logData = [
                'user_id'=>$userid,
                'type'=>'minus',
                'change'=>$money,
                'before_money'=>$extendObj['profit'],
                'after_money'=>($extendObj['profit']-$money),
                'log_date'=>date('Y-m-d'),
                'admin_id'=>0,
                'descr'=>'收益金额提取',
            ];
            $profitLogService = Container::get(MemberProfitLogService::class);
            $profitLogService->create($logData);
            //钱包日志
            $logData = [
                'user_id'=>$userid,
                'type'=>'add',
                'event'=>15,
                'change'=>$money,
                'before_money'=>$extendObj['wallet'],
                'after_money'=>($extendObj['wallet']+$money),
                'log_date'=>date('Y-m-d'),
                'admin_id'=>0,
                'descr'=>'收益金额提取到钱包',
            ];
            $walletLogService = Container::get(MemberWalletLogService::class);
            $walletLogService->create($logData);
            //主表数据变动
            $extendObj->decrease('profit',$money);
            $extendObj->increase('wallet',$money);
            $extendObj->save();
            $conn->commit();
        }
        catch (\Throwable $e){
            $conn->rollBack();
            throw $e;
        }
    }
}
