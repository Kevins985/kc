<?php

namespace library\model\goods;

use library\model\user\ProjectOrderModel;
use support\extend\Model;

class ProjectNumberModel extends Model
{
    public $table = 'goods_project_number';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"project_id",
		"project_number",
		"from_number",
		"user_cnt",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function project(){
        return $this->belongsTo(ProjectModel::class,'project_id','project_id');
    }

    /**
     * 获取下级
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getOrderChildList(){
        return $this->hasMany(ProjectOrderModel::class,'project_number','project_number')->get();
    }
}
