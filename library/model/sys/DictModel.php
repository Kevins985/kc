<?php

namespace library\model\sys;

use support\extend\Model;

class DictModel extends Model
{
    public $table = 'sys_dict';
    public $primaryKey = 'dict_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"dict_id",
		"dict_name",
		"dict_code",
		"dict_type",
		"sort",
		"descr",
		"status",
    ];
}
