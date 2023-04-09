<?php

namespace library\model\user;

use support\extend\Model;

class RechargeOrderModel extends Model
{
    public $table = 'user_recharge_order';
    public $primaryKey = 'order_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"order_id",
		"order_no",
		"user_id",
		"image_url",
		"money",
		"reward_money",
		"order_status",
		"descr",
		"memo",
		"admin_id",
		"status",
    ];

    protected $where=['user_no'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }
}
