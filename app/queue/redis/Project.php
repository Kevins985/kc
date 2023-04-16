<?php

namespace app\queue\redis;

use library\logic\ProjectOrderLogic;
use library\service\user\MemberTeamService;
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
     * @param $data {user_id,order_id,order_money,project_id,project_number}
     */
    public function consume($data)
    {
        try{
            Log::channel("queue")->info('project queue',$data);
            $memberTeamService = Container::get(MemberTeamService::class);
            $memberTeamObj = $memberTeamService->get($data['user_id']);
            $cdata = $memberTeamObj->toArray();
            $data = array_merge($data,$cdata);
            $memberTeamService = Container::get(MemberTeamService::class);
            $memberTeamService->updateTeamProjectData($data);
        }
        catch (\Exception $e){
            Log::channel("queue")->error('project queue:'.$e->getMessage());
        }
    }
}