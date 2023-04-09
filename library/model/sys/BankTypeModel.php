<?php

namespace library\model\sys;

use support\extend\Model;

class BankTypeModel extends Model
{
    public $table = 'sys_bank_type';
    public $primaryKey = 'type_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"type_id",
		"type_name",
		"type_color",
		"source",
		"image",
		"sort",
		"descr",
		"status",
    ];
}
