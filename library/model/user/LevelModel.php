<?php

namespace library\model\user;

use support\extend\Model;

class LevelModel extends Model
{
    public $table = 'user_level';
    public $primaryKey = 'level_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"level_id",
		"level_name",
		"level_name_en",
		"grade",
		"icon",
		"discount",
		"exp_num",
		"descr",
		"status",
    ];
}
