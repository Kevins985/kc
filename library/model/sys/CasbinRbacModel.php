<?php

namespace library\model\sys;

use support\extend\Model;

class CasbinRbacModel extends Model
{
    public $table = 'sys_casbin_rbac';
    public $primaryKey = 'id';
    public $connection = 'mysql';
    const UPDATED_AT = null; 
    protected $fillable = [
		"id",
		"ptype",
		"v0",
		"v1",
		"v2",
		"v3",
		"v4",
		"v5",
    ];
}
