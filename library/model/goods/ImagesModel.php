<?php

namespace library\model\goods;

use support\extend\Model;

class ImagesModel extends Model
{
    public $table = 'goods_images';
    public $primaryKey = 'id';
    public $connection = 'mysql';
    const UPDATED_AT = null;
    protected $keyType = 'string';
    protected $fillable=[
		"id",
		"image_url",
		"spu_id",
		"type",
		"sort",
    ];
}
