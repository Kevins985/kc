<?php

namespace library\model\user;

use library\model\sys\AdminModel;
use support\extend\Model;

class MessageModel extends Model
{
    public $table = 'user_message';
    public $primaryKey = 'message_id';
    public $connection = 'mysql';

    protected $appends = ['user_message_cnt','admin_message_cnt'];


    protected $fillable=[
		"message_id",
		"type",
		"title",
		"content",
		"user_id",
		"admin_id",
		"status",
    ];

    /**
     * 获取用户未读数量
     */
    public function getUserMessageCntAttribute()
    {
        return $this->records()->where('identity',0)->where('status',1)->count();
    }

    /**
     * 获取商户未读数量
     */
    public function getAdminMessageCntAttribute()
    {
        return $this->records()->where('identity',1)->where('status',1)->count();
    }

    function records(){
        return $this->hasMany(MessageRecordModel::class,'message_id','message_id');
    }

    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }

    function admin(){
        return $this->belongsTo(AdminModel::class,'admin_id','user_id');
    }
}
