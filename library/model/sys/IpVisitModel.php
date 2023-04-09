<?php

namespace library\model\sys;

use support\extend\Model;

class IpVisitModel extends Model
{
    public $table = 'sys_ip_visit';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"client_ip",
		"user_id",
		"country",
		"total_visit_num",
		"today_visit_num",
		"last_visit_time",
		"limit_type",
		"descr",
		"status",
    ];
}
