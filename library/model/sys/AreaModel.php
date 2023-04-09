<?php

namespace library\model\sys;

use support\extend\Model;

class AreaModel extends Model
{
    public $table = 'sys_area';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"id",
		"name",
		"parent_id",
		"pinyin",
		"pinyin_short",
		"lng",
		"lat",
		"level",
		"is_rec",
		"sort",
		"location",
		"status",
    ];
}
