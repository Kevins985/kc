<?php

namespace library\model\user;

use library\model\store\MerchantModel;
use support\extend\Model;

class MemberModel extends Model
{
    public $table = 'user_member';
    public $primaryKey = 'user_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"user_id",
		"user_no",
		"account",
		"password",
		"pay_password",
		"nickname",
		"email",
		"num_code",
		"mobile",
		"auth_type",
		"source",
		"level_id",
		"tags",
		"photo_url",
		"prov_id",
		"city_id",
		"area_id",
		"address",
		"descr",
		"token",
		"login_cnt",
		"client_ip",
		"identity",
		"login_time",
		"modify_pwd_time",
		"remark",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function memberAuth(){
        return $this->hasMany(MemberAuthModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function memberExtend(){
        return $this->hasOne(MemberExtendModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function realAuth(){
        return $this->hasOne(RealAuthModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function team(){
        return $this->hasOne(MemberTeamModel::class,'user_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function teamList(){
        return $this->hasMany(MemberTeamModel::class,'parent_id','user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function addressList()
    {
        return $this->hasMany(MemberAddressModel::class,'user_id','user_id');
    }
}
