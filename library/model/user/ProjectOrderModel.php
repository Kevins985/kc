<?php

namespace library\model\user;

use library\model\goods\ProjectModel;
use library\model\goods\ProjectNumberModel;
use support\extend\Model;

class ProjectOrderModel extends Model
{
    public $table = 'user_project_order';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"id",
		"order_id",
		"project_id",
		"project_number",
		"user_id",
		"user_number",
		"order_status",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function order(){
        return $this->belongsTo(OrderModel::class,'order_id','order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function project(){
        return $this->belongsTo(ProjectModel::class,'project_id','project_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function projectNumber(){
        return $this->belongsTo(ProjectNumberModel::class,'project_number','project_number');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function memberTeam(){
        return $this->belongsTo(MemberTeamModel::class,'user_id','user_id');
    }

    /**
     * 获取下级
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getOrderChildList(){
        return $this->hasMany(ProjectOrderModel::class,'project_number','project_number')->where('project_id',$this->project_id)->get();
    }

    /**
     * 获取进度
     * @param $total_number
     * @return float|int
     */
    public function getProgress($total_number){
        $progress = $total_number - ($this->user_number * ProjectUserCnt-3);
        if($progress<0){
            $progress = 0;
        }
        elseif($progress>ProjectUserCnt){
            $progress = ProjectUserCnt;
        }
        return $progress;
    }
}
