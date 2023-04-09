<?php

namespace library\model\user;

use support\extend\Model;

class TagsModel extends Model
{
    public $table = 'user_tags';
    public $primaryKey = 'tag_id';
    public $connection = 'mysql';

    protected $appends = ['category_name'];

    protected $fillable = [
        "tag_id",
        "tag_name",
        "category_id",
        "sort",
        "status",
    ];

    public function getCategoryNameAttribute()
    {
        return $this->TagsCategory()->value('category_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function tagsCategory(){
        return $this->belongsTo(TagsCategoryModel::class,'category_id','category_id');
    }
}
