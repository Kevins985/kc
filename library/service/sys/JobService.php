<?php

namespace library\service\sys;

use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\sys\JobModel;

class JobService extends Service
{
    public function __construct(JobModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取所有的任务脚本
     * @return array
     */
    public function getJobCommandList(){
        return $this->pluck('job_command',['status'=>1]);
    }

    public function getRunJobList(){
        return $this->fetchAll(['status'=>1]);
    }

    /**
     * 手动执行任务
     * @param $id
     */
    public function execJobTask($id){
        $jobObj = $this->get($id);
        if(empty($jobObj)){
            throw new BusinessException('Exception request');
        }
        elseif($jobObj['status']!=1){
            throw new BusinessException('只能执行正常的任务');
        }
        $data = [
            'job_id'=>$jobObj['job_id'],
            'job_command'=>$jobObj['job_command'],
            'run_start_time'=>microtime(true)*1000,
        ];
        $jobLogsService = Container::get(JobLogService::class);
        $jobLogsObj = $jobLogsService->create($data);
        if(!empty($jobLogsObj)){
            $jobObj->update(['prev_time'=>time(),'exec_cnt'=>($jobObj['exec_cnt']+1)]);
            addQueue(QueueJobLogs,$jobLogsObj->toArray());
        }
        return true;
    }
}
