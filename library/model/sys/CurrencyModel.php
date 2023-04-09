<?php

namespace library\model\sys;

use support\extend\Model;

class CurrencyModel extends Model
{
    public $table = 'sys_currency';
    public $primaryKey = 'currency_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"currency_id",
		"currency_name",
		"currency_code",
		"currency_symbol",
		"sort",
		"is_rec",
		"status",
    ];
}
