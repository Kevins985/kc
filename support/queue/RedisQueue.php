<?php

namespace support\queue;

use support\extend\Log;
use Webman\RedisQueue\Client;
use support\persist\QueueInterface;

class RedisQueue implements QueueInterface
{

    /**
     * 获取队列名称
     * @param int $queueID 队列编号
     * @return string
     */
    public function getQueueName(int $queueID){
        $data = [
            QueueSendMessage =>"send_message",
            QueueWriteLogs=>"write_logs",
            QueueJobLogs=>"job_logs",
            QueueProject=>"project",
            QueueMember=>"member",
        ];
        return isset($data[$queueID])?$data[$queueID]:"";
    }

    /**
     * 获取连接的适配参数(对应 config/redis_queue.php里的连接`)
     * @param int $queueID
     */
    public function getQueueAdapter(int $queueID)
    {
        return 'default';
    }

    /**
     * 发送队列数据
     * @param int $queueID 队列编号
     * @param array $data 数据
     * @param int $delay 延迟时间
     */
    public function send(int $queueID,array $data,int $delay=0,array $headers=[])
    {
        try{
            $adapter = $this->getQueueAdapter($queueID);
            $queue_name = $this->getQueueName($queueID);
            Client::connection($adapter)->send($queue_name,$data,$delay);
            return true;
        }
        catch (\Exception $e){
            Log::channel("queue")->error($e->getMessage(),["type"=>"redis_queue"]);
            return false;
        }
    }
}