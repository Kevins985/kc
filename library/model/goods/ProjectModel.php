<?php

namespace library\model\goods;

use library\model\user\MemberModel;
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
		"user_id",
		"user_cnt",
		"sales_cnt",
		"sales_money",
		"number",
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function projectNumber(){
        return $this->hasMany(ProjectNumberModel::class,'project_id','project_id');
    }

    public function getProjectNumber(){
        return $this->projectNumber()->where('status',1)->orderBy('id','asc')->first();
    }
}
