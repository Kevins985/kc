<?php

namespace library\model\user;

use support\extend\Model;

class ProjectOrderModel extends Model
{
    public $table = 'user_project_order';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"id",
		"order_id",
		"project_id",
		"project_number",
		"user_id",
		"user_number",
		"order_status",
		"status",
    ];
}
