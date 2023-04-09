<?php

namespace app\backend\controller\user;

use library\logic\WalletLogic;
use library\service\user\MemberExpLogService;
use library\service\user\MemberExtendService;
use library\service\user\MemberPointLogService;
use library\service\user\MemberProfitLogService;
use library\service\user\MemberService;
use library\service\user\MemberWalletLogService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class MemberExtend extends Backend
{
    public function __construct(MemberExtendService $service,WalletLogic $logic)
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
        if(!empty($params['user_no'])){
            $params['user_no'] = ['has','Member',$params['user_no']];
        }
        $data = $this->service->paginate('/backend/memberExtend/list',$params,['user_id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $memberList = [];
        $user_ids = Data::toFlatArray($data->items(),'user_id');
        if(!empty($user_ids)){
            $memberService = Container::get(MemberService::class);
            $memberList = $memberService->getMemberList($user_ids);
        }
        $this->response->assign('memberList',$memberList);
        return $this->response->layout('user/memberExtend/list');
    }

    /**
     * 添加
     * @params {user_id,type,}
     */
    public function add(Request $request)
    {
        try {
            $userid = $this->getPost('userid');
            $type = $this->getPost('type','wallet');
            $num = $this->getPost('num');
            if (!$request->isAjax() || empty($userid) || empty($num)) {
                throw new VerifyException('Exception request');
            }
            if($type=='wallet'){
                $res = $this->logic->addUserWallet($userid,$num,0,'官方充值',$request->getUserID());
            }
            elseif($type=='point'){
                $res = $this->logic->addUserPoint($userid,$num,'官方充值',$request->getUserID());
            }
            elseif($type=='exp'){
                $res = $this->logic->addUserExp($userid,$num,'官方充值',$request->getUserID());
            }
            elseif($type=='profit'){
                $res = $this->logic->addUserProfit($userid,$num,'官方充值',$request->getUserID());
            }
            else{
                throw new BusinessException('暂无该类型');
            }
            if(empty($res)){
                throw new BusinessException('操作失败');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 扣除
     */
    public function minus(Request $request)
    {
        try {
            $userid = $this->getPost('userid');
            $type = $this->getPost('type','wallet');
            $num = $this->getPost('num');
            if (!$request->isAjax() || empty($userid) || empty($num)) {
                throw new VerifyException('Exception request');
            }
            if($type=='wallet'){
                $res = $this->logic->minusUserWallet($userid,$num,0,'官方扣款',$request->getUserID());
            }
            elseif($type=='point'){
                $res = $this->logic->minusUserPoint($userid,$num,'官方扣款',$request->getUserID());
            }
            elseif($type=='exp'){
                $res = $this->logic->minusUserExp($userid,$num,'官方扣款',$request->getUserID());
            }
            elseif($type=='profit'){
                $res = $this->logic->minusUserProfit($userid,$num,'官方扣款',$request->getUserID());
            }
            else{
                throw new BusinessException('暂无该类型');
            }
            if(empty($res)){
                throw new BusinessException('操作失败');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 用户日志列表
     */
    public function logs(Request $request,int $id)
    {
        $params = $this->getAllRequest();
        $ctype = $this->getParams('ctype','wallet');
        if(!empty($params['start_date']) && !empty($params['end_date'])){
            $params['created_time'] = ['between',[strtotime($params['start_date']),strtotime($params['end_date'])]];
        }
        elseif(!empty($params['start_date'])){
            $params['created_time'] = ['gt',strtotime($params['start_date'])];
        }
        elseif(!empty($params['end_date'])){
            $params['created_time'] = ['lt',strtotime($params['end_date'])];
        }
        if($ctype=='point'){
            $logService = Container::get(MemberPointLogService::class);
        }
        elseif($ctype=='exp'){
            $logService = Container::get(MemberExpLogService::class);
        }
        elseif($ctype=='profit'){
            $logService = Container::get(MemberProfitLogService::class);
        }
        else{
            $logService = Container::get(MemberWalletLogService::class);
        }
        unset($params['ctype']);
        unset($params['start_date']);
        unset($params['end_date']);
        $params['user_id'] = $id;
        $data = $logService->paginate('/backend/memberExtend/logs/'.$id,$params,['id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $this->response->assign('userid',$id);
        $this->response->assign('ctype',$ctype);
        return $this->response->layout('user/memberExtend/logs');
    }
}