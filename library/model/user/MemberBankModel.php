<?php

namespace library\model\user;

use support\extend\Model;

class MemberBankModel extends Model
{
    public $table = 'user_member_bank';
    public $primaryKey = 'bank_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"bank_id",
		"user_id",
		"bank_type_id",
		"bank_name",
		"bank_card",
		"real_name",
		"bank_address",
		"mobile",
		"is_default",
		"status",
    ];
}
