<?php

namespace library\model\operate;

use support\extend\Model;

class HelpCategoryModel extends Model
{
    public $table = 'operate_help_category';
    public $primaryKey = 'category_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"category_id",
		"category_name",
		"sort",
		"position_left",
		"position_foot",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function help(){
        return $this->hasMany(HelpModel::class,'category_id','category_id');
    }
}
