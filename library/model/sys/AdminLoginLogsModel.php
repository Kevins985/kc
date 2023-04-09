<?php

namespace library\model\sys;

use support\extend\Model;

class AdminLoginLogsModel extends Model
{
    public $table = 'sys_admin_login_logs';
    public $primaryKey = 'log_id';
    public $connection = 'mysql';
    const UPDATED_AT = null; 
    protected $fillable = [
		"log_id",
		"account",
		"type",
		"token",
		"os",
		"browser",
		"client_ip",
		"login_date",
		"result",
    ];
}
