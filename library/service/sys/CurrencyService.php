<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\CurrencyModel;

class CurrencyService extends Service
{
    public function __construct(CurrencyModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取主货币币种
     */
    public function getRecCurrencyList(){
        $rows = $this->fetchAll(['is_rec'=>1,'status'=>1],['sort'=>'asc'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['currency_code']] = $v;
        }
        return $data;
    }

    /**
     * 获取可选择币种
     * @return array
     */
    public function getSelectList($status=null){
        $rows = $this->fetchAll(['status'=>$status],['sort'=>'asc'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['currency_code']] = $v;
        }
        return $data;
    }
}
