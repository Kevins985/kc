<?php

namespace library\model\sys;

use support\extend\Model;

class MenuModel extends Model
{
    public $table = 'sys_menu';
    public $primaryKey = 'menu_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"menu_id",
		"menu_name",
		"menu_type",
		"parent_id",
		"menu_path",
		"icon",
		"btn_class",
		"route_id",
		"route_url",
		"param",
		"choice_ids",
		"descr",
		"sort",
		"status",
    ];

    public function getMenuPath(){
        $path = trim($this->menu_path,',');
        return explode(',',$path);
    }
}
