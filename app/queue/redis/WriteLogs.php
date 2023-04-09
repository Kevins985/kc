<?php

namespace app\queue\redis;

use library\service\sys\IpVisitService;
use library\service\sys\OperationLogsService;
use support\Container;
use support\extend\Redis;
use support\utils\Ip2Region;
use Webman\RedisQueue\Consumer;
use support\extend\Log;

class WriteLogs implements Consumer
{
    // 要消费的队列名
    public $queue = 'write_logs';

    // 连接名，对应 config/redis_queue.php 里的连接`
    public $connection = 'default';

    /**
     * @var OperationLogsService
     */
    private $service;


    public function __construct(OperationLogsService $service)
    {
        $this->service = $service;
    }

    /**
     * 消费数据
     * @param $data {app,request_url,request_method,refer_url,client_ip,request_date,user_id,request_data}
     */
    public function consume($data)
    {
        try{
            if(!in_array($data['request_url'],['/api/account/uploadImage'])){
                $this->service->create($data);
            }
            if($data['app']=='api'){
                $ipVisitService = Container::get(IpVisitService::class);
                $ipVisitObj = $ipVisitService->get($data['client_ip'],'client_ip');
                if(empty($ipVisitObj)){
                    $ipVisitService->createIpVisit([
                        'client_ip'=>$data['client_ip'],
                        'user_id'=>$data['user_id'],
                        'last_visit_time'=>date('Y-m-d H:i:s')
                    ]);
                }
                else{
                    $cache_key = 'visit_ip';
                    Redis::hIncrBy($cache_key,$data['client_ip'],1);
                }
            }
        }
        catch (\Exception $e){
            Log::channel("queue")->error($e->getMessage(),["type"=>"operation_log"]);
        }
    }
}