<?php

namespace app\process;

use Webman\Channel\Client;
use Workerman\RedisQueue\Client as RedisClient;
use Workerman\Timer;

class ChannelServer extends \Channel\Server
{
    /**
     * @var RedisClient
     */
    private $redisClient;

    private $type = "";

    public function __construct()
    {
        $this->initSubscribe();
    }

    /**
     * 消息订阅
     */
    private function initSubscribe(){
        $this->redisClient = new RedisClient('redis://127.0.0.1:6379');
        $this->redisClient->subscribe('user-1', function($data){
            echo "user-1\n";
        });
        Client::connect('127.0.0.1', 2206);
        Client::on('event-1', function($event_data) {
            echo "event-1\n";
            print_r($event_data);
        });
    }

    public function sendMsg($data){
        // 每秒发布一次事件
        $this->redisClient->send('user-1',$data);
        Timer::add(3, function () use($data){
            $data['time'] = time();
            Client::publish('event-1', $data);
        });
    }

    public function onWorkerStart($worker)
    {
        $this->_worker = $worker;
        $worker->channels = [];
        $this->sendMsg(['id'=>1,'name'=>'aaaa','time'=>time()]);
    }
}
