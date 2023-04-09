<?php

namespace library\model\operate;

use support\extend\Model;

class AdvLocationModel extends Model
{
    public $table = 'operate_adv_location';
    public $primaryKey = 'location_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"location_id",
		"location_name",
		"location_code",
		"from_term",
		"width",
		"height",
		"limit_num",
		"sort",
		"descr",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function adv(){
        return $this->hasMany(AdvModel::class,'location_id','location_id');
    }
}
