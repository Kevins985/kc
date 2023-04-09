<?php

namespace library\model\goods;

use support\extend\Model;

class CategoryModel extends Model
{
    public $table = 'goods_category';
    public $primaryKey = 'category_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"category_id",
		"category_name",
		"parent_id",
		"sort",
		"icon",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function spu(){
        return $this->hasMany(SpuModel::class,'category_id','category_id');
    }

    function spu_cnt(){
        if($this->parent_id==0){
            $cnt =0;
            $category_ids = $this->children()->pluck('category_id')->toArray();
            if(!empty($category_ids)){
                return SpuModel::query()->whereIn('category_id',$category_ids)->count();
            }
            return $cnt;
        }
        return $this->spu()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function children(){
        return $this->hasMany(CategoryModel::class,'parent_id','category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allChildren()
    {
        return $this->children()->with(['allChildren' => function($query) {
            $query->select('category_id', 'category_name', 'parent_id');
        }])->get();
    }
}
