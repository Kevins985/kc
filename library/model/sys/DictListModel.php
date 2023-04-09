<?php

namespace library\model\sys;

use support\extend\Model;

class DictListModel extends Model
{
    public $table = 'sys_dict_list';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"dict_code",
		"field_name",
		"field_code",
		"field_type",
		"field_value",
		"field_required",
		"field_tips",
		"field_sort",
		"value_range_txt",
		"value_range",
		"addon",
		"status",
    ];
}
