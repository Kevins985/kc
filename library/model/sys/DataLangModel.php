<?php

namespace library\model\sys;

use support\extend\Model;

class DataLangModel extends Model
{
    public $table = 'sys_data_lang';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"id",
		"lang_id",
		"data_type",
		"data_id",
		"data_value",
    ];
}
