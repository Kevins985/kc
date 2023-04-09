<?php

namespace library\model\sys;

use support\extend\Model;

class LangValueModel extends Model
{
    public $table = 'sys_lang_value';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"id",
		"lang_id",
		"lang_key_id",
		"value_name",
    ];
}
