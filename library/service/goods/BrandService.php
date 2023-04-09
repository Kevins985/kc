<?php

namespace library\service\goods;

use support\extend\Service;
use library\model\goods\BrandModel;

class BrandService extends Service
{
    public function __construct(BrandModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选择的产品品牌
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll([],['sort'=>'desc'],['brand_id','brand_name'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['brand_id']] = $v;
        }
        return $data;
    }
}
