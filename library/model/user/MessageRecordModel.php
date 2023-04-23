<?php

namespace library\model\user;

use support\extend\Model;

class MessageRecordModel extends Model
{
    public $table = 'user_message_record';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"type",
		"message_id",
		"message_type",
		"user_id",
		"identity",
		"content",
		"status",
    ];

    function message(){
        return $this->belongsTo(MessageModel::class,'message_id','message_id');
    }

    function member(){
        return $this->belongsTo(MemberModel::class,'user_id','user_id');
    }
}
