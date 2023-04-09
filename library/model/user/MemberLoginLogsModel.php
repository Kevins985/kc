<?php

namespace library\model\user;

use support\extend\Model;

class MemberLoginLogsModel extends Model
{
    public $table = 'user_member_login_logs';
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
