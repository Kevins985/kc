<?php

namespace library\model\sys;

use support\extend\Model;

class LangKeyModel extends Model
{
    public $table = 'sys_lang_key';
    public $primaryKey = 'key_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"key_id",
		"key_name",
		"parent_id",
		"sort",
		"descr",
		"status",
    ];
}
