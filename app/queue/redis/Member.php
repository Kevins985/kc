<?php

namespace app\queue\redis;

use library\logic\ProjectOrderLogic;
use library\service\user\MemberTeamService;
use support\Container;
use Webman\Event\Event;
use Webman\RedisQueue\Consumer;
use support\extend\Log;

class Member implements Consumer
{
    // 要消费的队列名
    public $queue = 'member';

    // 连接名，对应 config/redis_queue.php 里的连接`
    public $connection = 'default';

    /**
     * 消费数据
     * @param $data
     */
    public function consume($data)
    {
        try{
            Log::channel("queue")->info('member queue',$data);
            $memberTeamService = Container::get(MemberTeamService::class);
            $memberTeamService->updateTeamInviteData($data);
        }
        catch (\Exception $e){
            Log::channel('queue')->error('member queue:'.$e->getMessage());
        }
    }
}