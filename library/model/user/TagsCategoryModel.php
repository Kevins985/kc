<?php

namespace library\model\user;

use support\extend\Model;

class TagsCategoryModel extends Model
{
    public $table = 'user_tags_category';
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
    function tags(){
        return $this->hasMany(TagsModel::class,'category_id','category_id');
    }
}
