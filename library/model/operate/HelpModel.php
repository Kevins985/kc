<?php

namespace library\model\operate;

use support\extend\Model;

class HelpModel extends Model
{
    public $table = 'operate_help';
    public $primaryKey = 'help_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"help_id",
		"category_id",
		"title",
		"url",
		"content",
		"sort",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function helpCategory(){
        return $this->belongsTo(HelpCategoryModel::class,'category_id','category_id');
    }
}
