<?php
namespace app\process;

use library\service\sys\JobLogService;
use library\service\sys\JobService;
use support\Container;
use support\extend\Log;
use Workerman\Crontab\Crontab;

class CrontabTask
{
    public function onWorkerStart()
    {
        $jobService = Container::get(JobService::class);
        $jobLogsService = Container::get(JobLogService::class);
        $jobList = $jobService->getRunJobList();
        foreach($jobList as $v){
            try{
                new Crontab($v['cron_expression'], function() use ($v,$jobLogsService){
                    $data = [
                        'job_id'=>$v['job_id'],
                        'job_command'=>$v['job_command'],
                        'run_start_time'=>microtime(true)*1000,
                    ];
                    $jobLogsObj = $jobLogsService->create($data);
                    if(!empty($jobLogsObj)){
                        $v->update(['prev_time'=>time(),'exec_cnt'=>($v['exec_cnt']+1)]);
                        addQueue(QueueJobLogs,$jobLogsObj->toArray());
                    }
                },md5($v['job_command']));
            }
            catch (\Exception $e){
                Log::channel("crontab")->error($e->getMessage(),['command'=>$v['job_command']]);
            }
        }
    }
}