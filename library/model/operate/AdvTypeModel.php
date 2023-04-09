<?php

namespace library\model\operate;

use support\extend\Model;

class AdvTypeModel extends Model
{
    public $table = 'operate_adv_type';
    public $primaryKey = 'type_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"type_id",
		"type_name",
		"type_code",
		"from_term",
		"limit_num",
		"sort",
		"descr",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function adv(){
        return $this->hasMany(AdvModel::class,'type_id','type_id');
    }
}
