<?php

namespace library\model\operate;

use library\model\sys\UploadFilesModel;
use support\extend\Model;

class AdvModel extends Model
{
    public $table = 'operate_adv';
    public $primaryKey = 'adv_id';
    public $connection = 'mysql';

    protected $appends = ['image_url'];

    protected $where=['type_code','location_code'];

    protected $fillable = [
		"adv_id",
		"adv_name",
		"adv_image",
		"adv_url",
		"type_id",
		"location_id",
		"from_term",
		"start_date",
		"end_date",
		"click_num",
		"content",
		"sort",
		"status",
    ];

    public function getImageUrlAttribute()
    {
        return $this->uploadFiles()->value('file_url');
    }

    public function uploadFiles(){
        return $this->hasOne(UploadFilesModel::class,'file_md5','adv_image');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function advLocation(){
        return $this->belongsTo(AdvLocationModel::class,'location_id','location_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function advType(){
        return $this->belongsTo(AdvTypeModel::class,'type_id','type_id');
    }
}
