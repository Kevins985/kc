<?php

namespace library\model\sys;

use support\extend\Model;

class LangModel extends Model
{
    public $table = 'sys_lang';
    public $primaryKey = 'lang_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"lang_id",
		"lang_name",
		"lang_code",
		"image",
		"is_default",
		"sort",
		"descr",
		"status",
    ];
}
