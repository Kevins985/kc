<?php

namespace library\model\sys;

use support\extend\Model;

class JobGroupModel extends Model
{
    public $table = 'sys_job_group';
    public $primaryKey = 'group_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"group_id",
		"group_name",
		"sort",
		"admin_id",
		"descr",
		"status",
    ];

    function job(){
        return $this->hasMany(JobModel::class,'job_group_id','group_id');
    }
}
