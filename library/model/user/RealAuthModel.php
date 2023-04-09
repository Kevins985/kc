<?php

namespace library\model\user;

use support\extend\Model;

class RealAuthModel extends Model
{
    public $table = 'user_real_auth';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"user_id",
		"real_name",
		"card_id",
		"front_pic",
		"reverse_pic",
		"hand_pic",
		"descr",
		"status",
    ];

    protected $where=['user_no'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function member(){
        return $this->hasOne(MemberModel::class,'user_id','user_id');
    }
}
