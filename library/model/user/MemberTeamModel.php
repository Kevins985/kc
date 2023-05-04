<?php

namespace library\model\user;

use support\extend\Model;

class MemberTeamModel extends Model
{
    public $table = 'user_member_team';
    public $primaryKey = 'user_id';
    public $connection = 'mysql';
    protected $fillable=[
		"user_id",
		"account",
        "name",
		"invite_code",
		"parent_id",
		"parents_path",
		"invite_path",
		"invite_cnt",
		"invite_income_money",
		"invite_money",
		"team_cnt",
		"team_income_money",
		"team_money",
		"reward",
		"sync_time",
        "status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function member(){
        return $this->hasOne(MemberModel::class,'user_id','user_id');
    }

    function getParentUserIds(){
        $data = [];
        if(!empty($this->parents_path)){
            $data = explode(',',$this->parents_path);
            array_pop($data);
        }
        return $data;
    }
}
