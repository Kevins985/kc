<?php

namespace library\model\operate;

use support\extend\Model;

class NoticeModel extends Model
{
    public $table = 'operate_notice';
    public $primaryKey = 'notice_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"notice_id",
		"category_id",
		"title",
		"content",
		"sort",
		"is_rec",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function noticeCategory(){
        return $this->belongsTo(NoticeCategoryModel::class,'category_id','category_id');
    }
}
