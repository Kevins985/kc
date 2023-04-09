<?php


namespace library\task;


use Carbon\Carbon;
use library\service\sys\JobLogService;
use support\Container;
use support\extend\Log;
use support\extend\Redis;

class System
{
    /**
     * 同步正在进行中的任务
     */
    public function syncPendingJobTask(){
        $jobService = Container::get(JobLogService::class);
        $rows = $jobService->fetchALl(['status'=>1]);
        foreach($rows as $v){
            $start_time = Carbon::now()->getTimestampMs();
            try{
                $exists = Redis::hExists('exec_jobs',$v['log_id']);
                if(!$exists){
                    Redis::hSet('exec_jobs',$v['log_id'],time());
                    $arr = explode(':',$v['job_command']);
                    $jobObj = Container::get($arr[0]);
                    $result = call_user_func([$jobObj,$arr[1]]);
                    $update = [
                        'run_end_time'=>Carbon::now()->getTimestampMs(),
                        'message'=>$result,
                        'status'=>2
                    ];
                    $update['duration'] = $update['run_end_time'] - $start_time;
                    $v->update($update);
                }

            }
            catch (\Exception $e){
                $update = [
                    'run_end_time'=>Carbon::now()->getTimestampMs(),
                    'exception_info'=>$e->getMessage(),
                    'status'=>3
                ];
                $update['duration'] = $update['run_end_time'] - $start_time;
                $v->update($update);
            }
        }
    }
}