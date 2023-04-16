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
            Log::channel("queue")->info('project queue',$data);

        }
        catch (\Exception $e){
            Log::channel("queue")->error('project queue:'.$e->getMessage());
        }
    }
}