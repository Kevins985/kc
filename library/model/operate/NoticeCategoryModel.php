<?php

namespace library\model\operate;

use support\extend\Model;

class NoticeCategoryModel extends Model
{
    public $table = 'operate_notice_category';
    public $primaryKey = 'category_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"category_id",
		"category_name",
		"sort",
		"descr",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function notice(){
        return $this->hasMany(NoticeModel::class,'category_id','category_id');
    }
}
