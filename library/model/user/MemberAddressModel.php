<?php

namespace library\model\user;

use library\model\sys\AreaModel;
use support\extend\Model;

class MemberAddressModel extends Model
{
    public $table = 'user_member_address';
    public $primaryKey = 'address_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"address_id",
		"user_id",
		"country",
		"name",
		"mobile",
		"prov_id",
		"city_id",
		"area_id",
		"local",
		"post_code",
		"address",
		"is_default",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }
}
