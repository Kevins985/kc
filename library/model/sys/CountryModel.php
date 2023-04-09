<?php

namespace library\model\sys;

use support\extend\Model;

class CountryModel extends Model
{
    public $table = 'sys_country';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"name",
		"name_en",
		"continent",
		"code",
		"three_code",
		"num_code",
		"sort",
		"descr",
		"status",
    ];
}
