<?php

namespace library\model\operate;

use support\extend\Model;

class ArticleCategoryModel extends Model
{
    public $table = 'operate_article_category';
    public $primaryKey = 'category_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"category_id",
		"category_name",
		"parent_id",
		"type",
		"sort",
		"seo_keywords",
		"seo_description",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function article(){
        return $this->hasMany(ArticleModel::class,'category_id','category_id');
    }
}
