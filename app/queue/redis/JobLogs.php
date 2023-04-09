<?php

namespace app\queue\redis;

use Carbon\Carbon;
use library\service\sys\JobLogService;
use support\Container;
use Webman\RedisQueue\Consumer;
use support\extend\Log;

class JobLogs implements Consumer
{
    // 要消费的队列名
    public $queue = 'job_logs';

    // 连接名，对应 config/redis_queue.php 里的连接`
    public $connection = 'default';

    /**
     * @var JobLogService
     */
    private $service;

    public function __construct(JobLogService $service)
    {
        $this->service = $service;
    }

    /**
     * 消费数据
     * @param $data {log_id,job_id,job_command,run_start_time,status}
     */
    public function consume($data)
    {
        $start_time = Carbon::now()->getTimestampMs();
        try{
            $this->service->update($data['log_id'],[
                'run_start_time'=>$start_time,
                'status'=>1
            ]);
            $arr = explode(':',$data['job_command']);
            $jobObj = Container::get($arr[0]);
            $result = call_user_func([$jobObj,$arr[1]]);
            $update = [
                'run_end_time'=>Carbon::now()->getTimestampMs(),
                'message'=>$result,
                'status'=>2
            ];
            $update['duration'] = $update['run_end_time'] - $start_time;
            Log::channel("crontab")->info('JobLogs:success',$data);
            $this->service->update($data['log_id'],$update);
        }
        catch (\Exception $e){
            $update = [
                'run_end_time'=>Carbon::now()->getTimestampMs(),
                'exception_info'=>$e->getMessage(),
                'status'=>3
            ];
            $update['duration'] = $update['run_end_time'] - $start_time;
            Log::channel("crontab")->error('JobLogs:'.$e->getMessage(),$update);
            $this->service->update($data['log_id'],$update);
        }
    }
}