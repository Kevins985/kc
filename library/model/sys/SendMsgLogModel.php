<?php

namespace library\model\sys;

use support\extend\Model;

class SendMsgLogModel extends Model
{
    public $table = 'sys_send_msg_log';
    public $primaryKey = 'log_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"log_id",
		"send_type",
		"send_to",
		"title",
		"content",
		"result",
		"status",
    ];
}
