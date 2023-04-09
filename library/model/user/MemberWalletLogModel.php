<?php

namespace library\model\user;

use support\extend\Model;

class MemberWalletLogModel extends Model
{
    public $table = 'user_member_wallet_log';
    public $primaryKey = 'id';
    public $connection = 'mysql';
    const UPDATED_AT = null; 
    protected $fillable = [
		"id",
		"user_id",
		"type",
		"event",
		"change",
		"before_money",
		"after_money",
		"log_date",
		"admin_id",
		"descr",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }
}
