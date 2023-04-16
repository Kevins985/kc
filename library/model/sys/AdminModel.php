<?php

namespace library\model\sys;

use support\extend\Model;

class AdminModel extends Model
{
    public $table = 'sys_admin';
    public $primaryKey = 'user_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"user_id",
		"account",
		"password",
		"email",
		"mobile",
		"realname",
		"token",
		"role_id",
		"is_admin",
		"menu_ids",
		"photo_url",
		"modify_pwd_time",
		"login_cnt",
		"login_time",
		"project_id",
		"verify_ip",
		"client_ip",
		"descr",
		"status",
    ];

    function adminLoginLogs(){
        return $this->hasMany('library\model\sys\AdminLoginLogsModel','user_id','user_id');
    }

    public function getAccount(){
        return $this->account;
    }
}
