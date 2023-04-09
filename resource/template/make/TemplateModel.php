<?php

namespace library\model\module;

use support\extend\Model;

class TemplateModel extends Model
{
    public $table = '{table}';
    public $primaryKey = '{pk}';
    public $connection = '{adapter}';
    const UPDATED_AT = '{update_time}'; 
    protected $fillable = [];
}
