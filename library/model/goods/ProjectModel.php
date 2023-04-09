<?php

namespace library\model\goods;

use library\service\user\OrderService;
use support\extend\Model;

class ProjectModel extends Model
{
    public $table = 'goods_project';
    public $primaryKey = 'project_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"project_id",
		"project_no",
		"project_type",
		"project_name",
		"project_prefix",
		"user_cnt",
		"number",
		"sales_money",
		"sales_cnt",
		"start_time",
		"end_time",
		"limit_num",
		"descr",
		"status",
    ];

    public function getViewNumber(){
        return $this->project_prefix.$this->number;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function orders(){
        return $this->hasMany(OrderService::class,'project_id','project_id');
    }
}
