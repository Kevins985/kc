<?php

namespace library\model\sys;

use support\extend\Model;

class MakeLogsModel extends Model
{
    public $table = 'sys_make_logs';
    public $primaryKey = 'log_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"log_id",
		"type",
		"table",
		"file_class",
		"is_modify",
		"make_date",
		"status",
    ];
}
