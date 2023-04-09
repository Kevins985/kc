<?php

namespace app\backend\controller\sys;

use library\service\sys\AdminLoginLogsService;
use library\service\sys\OperationLogsService;
use library\service\user\MemberLoginLogsService;
use library\service\user\MemberService;
use library\service\user\MemberWalletLogService;
use support\Container;
use support\controller\Backend;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class Logs extends Backend
{
    /**
     * @Inject
     * @var MemberLoginLogsService
     */
    private $loginLogsService;

    /**
     * @Inject
     * @var OperationLogsService
     */
    private $operationLogsService;

    /**
     * @Inject
     * @var MemberWalletLogService
     */
    private $walletLogsService;

    /**
     * 登陆日志列表
     */
    public function login(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->loginLogsService->paginate('/backend/logs/login',$params,['id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('sys/logs/login');
    }

    /**
     * 操作日志
     */
    public function operation(Request $request)
    {
        $params = $this->getAllRequest();
        if(!empty($params['request_url'])){
            $params['request_url'] = ['left_like',$params['request_url']];
        }
        $data = $this->operationLogsService->paginate('/backend/logs/operation',$params,['log_id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('sys/logs/operation');
    }


    /**
     * 获取操作日志数据
     */
    public function getOperationData(Request $request)
    {
        try {
            $id = $this->getParams('id',0);
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $logObj = $this->operationLogsService->get($id);
            if(empty($logObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            return $this->response->json(true,$logObj->toArray());
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 钱包日志列表
     */
    public function wallet(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->walletLogsService->paginate('/backend/logs/wallet',$params,['id'=>'desc']);
        if(!empty($params['type'])){
            unset($params['type']);
        }
        $countList = $this->walletLogsService->getGroupAllTypeCnt($params);
        $this->response->assign('countList',$countList);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $eventList = $this->walletLogsService->getEventList();
        $this->response->assign('eventList',$eventList);
        $user_ids = Data::toFlatArray($data->items(),'user_id');
        $memberService = Container::get(MemberService::class);
        $memberList = $memberService->getMemberList($user_ids);
        $this->response->assign('memberList',$memberList);
        return $this->response->layout('sys/logs/wallet');
    }
}