<?php

namespace library\model\sys;

use support\extend\Model;

class RoleModel extends Model
{
    public $table = 'sys_role';
    public $primaryKey = 'role_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"role_id",
		"role_name",
		"parent_id",
		"descr",
		"sort",
		"menu_ids",
		"status",
    ];
}
