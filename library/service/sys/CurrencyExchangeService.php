<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\CurrencyExchangeModel;

class CurrencyExchangeService extends Service
{
    public function __construct(CurrencyExchangeModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取货币汇率列表
     * @return array
     */
    public function getCurrencyList($currency_id){
        $rows = $this->fetchAll(['currency_id'=>$currency_id],['sort'=>'asc']);
        return $rows->toArray();
    }

    /**
     * 获取可选择币种
     * @return array
     */
    public function getCurrencyRate($current_currency,$target_currency){
        return $this->value('currency_rate',['current_currency'=>$current_currency,'target_currency'=>$target_currency]);
    }
}
