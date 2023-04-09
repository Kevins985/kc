<?php

namespace library\model\sys;

use support\extend\Model;

class RouteModel extends Model
{
    public $table = 'sys_route';
    public $primaryKey = 'id';
    public $connection = 'mysql';
    const UPDATED_AT = null;
    protected $keyType = 'string';
    protected $fillable = [
		"id",
		"module",
		"controller",
		"action",
		"method",
		"url",
		"class",
		"middleware",
		"verify",
		"status",
    ];
}
