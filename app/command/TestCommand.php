<?php

namespace app\command;

use library\logic\OrderLogic;
use library\service\goods\ProjectNumberService;
use library\service\goods\ProjectService;
use library\service\user\MemberExtendService;
use library\service\user\MemberService;
use library\service\user\OrderService;
use library\service\user\ProjectOrderService;
use support\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'test';
    protected static $defaultDescription = '测试脚本';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $this->addProjectNumber(2);
//        $this->addOrder(1,500);
        $this->verifyOrder(50);

//        $projectNumberService = Container::get(ProjectOrderService::class);
//        $projectNumberObj  = $projectNumberService->get(50);
//        $orderLogic = Container::get(OrderLogic::class);
//        $orderLogic->finishProjectOrder($projectNumberObj);

//        $memberService = Container::get(MemberService::class);
//        $memberList = $memberService->fetchAll(['user_id'=>['lte',10000]]);
//        $memberExtendService = Container::get(MemberExtendService::class);
//        foreach($memberList as $v){
//            try{
//                $memberExtendService->create(['user_id'=>$v['user_id']]);
//            }
//            catch (\Throwable $e){
//
//            }
//        }
        return self::SUCCESS;
    }

    private function addProjectNumber($project_id){
        $projectService = Container::get(ProjectService::class);
        $projectObj = $projectService->get($project_id);
        $projectNumberService = Container::get(ProjectNumberService::class);
        for($i=0;$i<4;$i++){
            $projectNumberService->createProjectNumber($projectObj['project_id'],$projectObj['project_prefix'],($projectObj['number']+$i));
        }
    }

    private function addOrder($spu_id,$count=100){
        $orderLogic = Container::get(OrderLogic::class);
        $memberService = Container::get(MemberService::class);
        $memberList = $memberService->fetchAll(['size'=>$count]);
        $memberExtendService = Container::get(MemberExtendService::class);
        foreach($memberList as $v){
            $memberExtendService->firstOrCreate(['user_id'=>$v['user_id']],['user_id'=>$v['user_id']]);
            $res = $orderLogic->createOrder([
                'user_id'=>$v['user_id'],
                'address_id'=>0,
                'spu_id'=>$spu_id
            ]);
            echo $v['user_id'].'-'.($res?'success':'fail').PHP_EOL;
        }
    }

    private function verifyOrder($count=1){
        $orderLogic = Container::get(OrderLogic::class);
        $orderService = Container::get(OrderService::class);
        $orderList = $orderService->fetchAll(['order_status'=>'pending','size'=>$count]);
        foreach($orderList as $v){
            $res = $orderLogic->verifyOrder($v['order_id'],'paid');
            echo $v['order_id'].'-'.($res?'success':'fail').PHP_EOL;
        }
    }
}