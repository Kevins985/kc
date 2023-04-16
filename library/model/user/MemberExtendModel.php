<?php

namespace library\model\user;

use support\extend\Model;

class MemberExtendModel extends Model
{
    public $table = 'user_member_extend';
    public $primaryKey = 'user_id';
    public $connection = 'mysql';
    protected $fillable=[
		"user_id",
		"exp",
		"point",
		"wallet",
		"profit",
		"recharge_money",
		"withdraw_money",
		"profit_money",
		"frozen",
		"deposit",
		"used_point",
		"status",
    ];

    protected $where=['user_no','source'];

    public function getWallet(){
        return $this->wallet - $this->frozen;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function memberPointLog(){
        return $this->hasMany(MemberPointLogModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function memberWalletLog(){
        return $this->hasMany(MemberWalletLogModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function member(){
        return $this->hasOne(MemberModel::class,'user_id','user_id');
    }

    function projectMember($project_id){

    }
}
