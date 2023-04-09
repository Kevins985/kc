<?php

namespace library\model\sys;

use support\extend\Model;

class FlowNumbersModel extends Model
{
    public $table = 'sys_flow_numbers';
    public $primaryKey = 'flow_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"flow_id",
		"flow_name",
		"from_table",
		"flow_prefix",
		"flow_rule",
		"flow_random",
		"flow_start_val",
		"flow_digit",
		"flow_suffix",
		"flow_sn",
		"descr",
		"status",
    ];
}
