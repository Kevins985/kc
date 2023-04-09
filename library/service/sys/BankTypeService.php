<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\BankTypeModel;

class BankTypeService extends Service
{
    public function __construct(BankTypeModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取银行卡类型
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll(['status'=>1],['sort'=>'asc']);
        $data = [];
        foreach($rows as $v){
            $data[$v['type_id']] = $v;
        }
        return $data;
    }
}
