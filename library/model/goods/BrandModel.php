<?php

namespace library\model\goods;

use support\extend\Model;

class BrandModel extends Model
{
    public $table = 'goods_brand';
    public $primaryKey = 'brand_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"brand_id",
		"brand_name",
		"logo",
		"url",
		"is_rec",
		"sort",
		"descr",
		"status",
    ];

    function spu(){
        return $this->hasMany(SpuModel::class,'brand_id','brand_id');
    }
}
