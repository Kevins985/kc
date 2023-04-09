<?php

namespace library\model\operate;

use library\model\sys\UploadFilesModel;
use support\extend\Model;

class ArticleModel extends Model
{
    public $table = 'operate_article';
    public $primaryKey = 'id';
    public $connection = 'mysql';

    protected $appends = ['image_url'];

    protected $fillable=[
		"id",
		"title",
		"content",
		"lang_id",
		"category_id",
		"image",
		"is_rec",
		"url",
		"visit_num",
		"sort",
		"style",
		"color",
		"descr",
		"seo_keywords",
		"seo_description",
		"status",
    ];

    public function getImageUrlAttribute()
    {
        return $this->uploadFiles()->value('file_url');
    }

    public function uploadFiles(){
        return $this->hasOne(UploadFilesModel::class,'file_md5','image');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function articleCategory(){
        return $this->belongsTo(ArticleCategoryModel::class,'category_id','category_id');
    }
}
