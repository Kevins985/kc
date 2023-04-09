<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\DictListModel;

class DictListService extends Service
{
    public function __construct(DictListModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取字典数据
     * @param string $dict_code
     * @return array
     */
    public function getDictList(string $dict_code){
        $rows = $this->fetchAll(['dict_code'=>$dict_code,'status'=>1],['field_sort'=>'asc'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['field_code']] = $v;
        }
        return $data;
    }
}
