<?php

namespace library\model\user;

use library\model\goods\CategoryModel;
use library\model\goods\ProjectModel;
use library\model\goods\SpuModel;
use support\extend\Model;

class OrderModel extends Model
{
    public $table = 'user_order';
    public $primaryKey = 'order_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"order_id",
		"order_no",
		"project_id",
		"project_sort",
		"user_id",
		"spu_id",
		"qty",
		"money",
		"point",
		"address_id",
		"order_status",
		"pay_money",
		"verify_time",
		"invite_cnt",
		"tracking_name",
		"tracking_number",
		"tracking_url",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function project(){
        return $this->belongsTo(ProjectModel::class,'project_id','project_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function spu(){
        return $this->belongsTo(SpuModel::class,'spu_id','spu_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }


}
