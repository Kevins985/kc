<?php

namespace library\model\goods;

use library\model\user\OrderModel;
use support\extend\Model;

class SpuModel extends Model
{
    public $table = 'goods_spu';
    public $primaryKey = 'spu_id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"spu_id",
		"spu_no",
		"title",
		"image_url",
		"category_id",
		"brand_id",
		"market_price",
		"sell_price",
		"point",
		"point2",
		"weight",
		"up_time",
		"down_time",
		"store_num",
		"sales_cnt",
		"fav_cnt",
		"visit_cnt",
		"sort",
		"brief",
		"description",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function images(){
        return $this->hasMany(ImagesModel::class,'spu_id','spu_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function orders(){
        return $this->hasMany(OrderModel::class,'spu_id','spu_id');
    }

    /**
     * @return array
     */
    function getImagesList(){
        $rows = $this->images()->where('type',1)->get();
        $data = [];
        foreach($rows as $v){
            $data[] = $v['image_url'];
        }
        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function category(){
        return $this->belongsTo(CategoryModel::class,'category_id','category_id');
    }

    function getCategoryName(){
        if(empty($this->category_id)){
            return '暂无分类';
        }
        return $this->category()->category_name;
    }
}
