<?php

namespace library\model\user;

use support\extend\Model;

class TagsExtendModel extends Model
{
    public $table = 'user_tags_extend';
    public $primaryKey = 'id';
    public $connection = 'mysql';

    const UPDATED_AT = null;

    protected $fillable = [
		"id",
		"tag_id",
		"value_id",
    ];


}
