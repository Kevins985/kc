<?php

namespace library\model\sys;

use support\extend\Model;

class JobModel extends Model
{
    public $table = 'sys_job';
    public $primaryKey = 'job_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"job_id",
		"job_name",
		"job_group_id",
		"job_command",
		"cron_expression",
		"timeout",
		"admin_id",
		"is_notify",
		"notify_email",
		"descr",
		"exec_cnt",
		"prev_time",
		"status",
    ];

    function jobGroup(){
        return $this->belongsTo(JobGroupModel::class,'group_id','job_group_id');
    }
}
