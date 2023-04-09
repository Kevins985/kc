<?php

namespace app\backend\controller;

use library\service\sys\RouteService;
use library\service\user\RechargeOrderService;
use library\service\user\WithdrawOrderService;
use library\validator\sys\SystemValidation;
use support\Container;
use support\exception\BusinessException;
use support\extend\Request;
use support\controller\Backend;
use support\utils\Random;

class Main extends Backend
{

    public function __construct(SystemValidation $validation)
    {
        $this->validation = $validation;
    }

    /**
     * 首页
     */
    public function index(Request $request)
    {
        try {

            return $this->response->layout("main/index");
        } catch (\Exception $e) {
            return $this->response->output($e->getMessage());
        }
    }

    /**
     * 路由列表
     */
    public function route(Request $request)
    {
//        $list = Route::getRouteList();
        $routeService = Container::get(RouteService::class);
        $list = $routeService->fetchAll();
        return $this->response->layout("main/route",['data'=>$list]);
    }

    /**
     * 获取随机字符
     */
    public function getRandomStr(Request $request)
    {
        try {
            $type = $this->getPost('type', 6);
            $length = $this->getPost('length', 10);
            if (!$request->isAjax()) {
                throw new \Exception('Exception request');
            }
            $str = Random::getRandStr($length, $type);
            return $this->response->json(true, ['random' => $str]);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 提示
     * @param Request $request
     */
    public function getMessageTip(Request $request){
        try {
            $msg = '';
            $withdrawOrderService = Container::get(WithdrawOrderService::class);
            $withdraw_num = $withdrawOrderService->count(['status'=>0]);
            if($withdraw_num>0){
                $msg.=',待审核提现订单+'.$withdraw_num;
            }
            $rechargeOrderService = Container::get(RechargeOrderService::class);
            $recharge_num = $rechargeOrderService->count(['status'=>0]);
            if($recharge_num>0){
                $msg.=',待审核充值订单+'.$recharge_num;
            }
            if(empty($msg)){
                throw new BusinessException("暂无信息");
            }
            $msg= '新的消息'.$msg;
            return $this->response->json(true,[],$msg);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}
