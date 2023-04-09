<?php

namespace app\queue\redis;

use library\logic\ProjectOrderLogic;
use support\Container;
use Webman\Event\Event;
use Webman\RedisQueue\Consumer;
use support\extend\Log;

class Project implements Consumer
{
    // 要消费的队列名
    public $queue = 'project';

    // 连接名，对应 config/redis_queue.php 里的连接`
    public $connection = 'default';

    /**
     * 消费数据
     * @param $data {type,date,order_id}
     */
    public function consume($data)
    {
        try{
            Log::channel("project")->info('project queue',$data);
            $projectOrderLogic = Container::get(ProjectOrderLogic::class);
            if($data['type']=='finishProjectOrderIncome'){
                //计算用户每天的收益数据
                $order_id = (!empty($data['order_id'])?$data['order_id']:0);
                $projectOrderLogic->finishProjectOrderIncome($data['date'],$order_id);
            }
            elseif($data['type']=='finishProjectMoneyToWallet'){
                $projectOrderLogic->finishProjectMoneyToWallet($data['date']);
            }
            elseif($data['type']=='finishProjectOrderProfitToWallet'){
                $projectOrderLogic->finishProjectOrderProfitToWallet($data['date']);
            }
        }
        catch (\Exception $e){
            Log::channel("project")->error('project queue:'.$e->getMessage());
        }
    }
}