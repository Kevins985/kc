<?php

namespace library\model\sys;

use support\extend\Model;

class JobLogModel extends Model
{
    public $table = 'sys_job_log';
    public $primaryKey = 'log_id';
    public $connection = 'mysql';
    const UPDATED_AT = null; 
    protected $fillable = [
		"log_id",
		"job_id",
		"job_command",
		"run_start_time",
		"run_end_time",
		"duration",
		"message",
		"exception_info",
		"status",
    ];
}
