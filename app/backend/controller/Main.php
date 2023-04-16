<?php

namespace app\backend\controller;

use library\service\goods\ProjectNumberService;
use library\service\goods\ProjectService;
use library\service\goods\SpuService;
use library\service\sys\RouteService;
use library\service\user\MemberExtendService;
use library\service\user\MemberService;
use library\service\user\OrderService;
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
            $projectService = Container::get(ProjectService::class);
            $projectObj = null;
            if(!empty($this->loginUser['project_id'])){
                $projectObj = $projectService->get($this->loginUser['project_id']);
            }
            $this->response->assign('project',$projectObj);
            $memberService = Container::get(MemberService::class);
            $data['member_cnt'] = $memberService->count();
            $extendService = Container::get(MemberExtendService::class);
            $res = $extendService->selector()->selectRaw('sum(point) as point')->first()->toArray();
            $data['member_point'] = $res['point'];
            $spuService = Container::get(SpuService::class);
            $data['spu_cnt'] = $spuService->count();
            $projectNumberService = Container::get(ProjectNumberService::class);
            $data['project_number_cnt'] = $projectNumberService->count();
            $orderService = Container::get(OrderService::class);
            $res = $orderService->selector(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null)])->selectRaw('count(*) as ct,sum(pay_money) as pay_money')->first()->toArray();
            $data['order_cnt'] = $res['ct'];
            $data['order_money'] = $res['pay_money'];
            $data['order_pending_cnt'] = $spuService->count(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null),'order_status'=>'pending']);
            $data['order_completed_cnt'] = $spuService->count(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null),'order_status'=>'completed']);
            //周报表统计
            $start_time = (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600);
            $end_time = time();
            $data['week_member_cnt'] = $memberService->selector(['source'=>(!empty($projectObj)?$projectObj['project_no']:null),'created_time'=>['between',[$start_time,$end_time]]])->count();
            $res = $orderService->selector(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null),'verify_time'=>['between',[$start_time,$end_time]]])->selectRaw('count(*) as ct,sum(pay_money) as pay_money')->first()->toArray();
            $data['week_order_cnt'] = $res['ct'];
            $data['week_order_money'] = $res['pay_money'];
            $data['week_order_finish'] = $orderService->selector(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null),'order_status'=>'completed','updated_time'=>['between',[$start_time,$end_time]]])->count();
            //日报表统计
            $start_time = strtotime(date('Y-m-d'));
            $end_time = strtotime(date('Y-m-d').' 23:59:59');
            $data['today_member_cnt'] = $memberService->selector(['source'=>(!empty($projectObj)?$projectObj['project_no']:null),'created_time'=>['between',[$start_time,$end_time]]])->count();
            $res = $orderService->selector(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null),'verify_time'=>['between',[$start_time,$end_time]]])->selectRaw('count(*) as ct,sum(pay_money) as pay_money')->first()->toArray();
            $data['today_order_cnt'] = $res['ct'];
            $data['today_order_money'] = $res['pay_money'];
            $data['today_order_finish'] = $orderService->selector(['project_id'=>(!empty($projectObj)?$projectObj['project_id']:null),'order_status'=>'completed','updated_time'=>['between',[$start_time,$end_time]]])->count();
            return $this->response->layout("main/index", $data);
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
