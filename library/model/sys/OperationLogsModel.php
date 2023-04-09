<?php

namespace library\model\sys;

use support\extend\Model;

class OperationLogsModel extends Model
{
    public $table = 'sys_operation_logs';
    public $primaryKey = 'log_id';
    public $connection = 'mysql';
    const UPDATED_AT = null; 
    protected $fillable = [
		"log_id",
		"app",
		"user_id",
		"request_url",
		"request_method",
		"request_data",
		"refer_url",
		"client_ip",
		"request_date",
    ];
}
