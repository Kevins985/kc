<?php

namespace library\model\sys;

use support\extend\Model;

class CurrencyExchangeModel extends Model
{
    public $table = 'sys_currency_exchange';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"currency_id",
		"current_name",
		"current_currency",
		"target_name",
		"target_currency",
		"currency_rate",
		"currency_range",
		"currency_time",
    ];
}
