<?php

namespace library\model\user;

use support\extend\Model;

class MemberAuthModel extends Model
{
    public $table = 'user_member_auth';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"user_id",
		"token_type",
		"access_token",
		"refresh_token",
		"client_type",
		"client_ip",
		"expires_in",
		"refresh_expires_in",
		"status",
    ];
}
